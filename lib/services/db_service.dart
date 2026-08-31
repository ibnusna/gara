// ─────────────────────────────────────────────────────────────────────────────
// BUG-001 Fix: Migrasi dari SharedPreferences ke SQLite (sqflite)
// Root cause: SharedPreferences XML write-all menyebabkan frame drop pada
// operasi auto-save data catatan & fokus yang besar.
// ─────────────────────────────────────────────────────────────────────────────

import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:path/path.dart' as p;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sqflite/sqflite.dart';

class DbService {
  DbService._();

  static Database? _db;

  // ──────────────────────────────────────────────────────────────────────────
  // Singleton getter — lazy-init, aman dipanggil berulang kali
  // ──────────────────────────────────────────────────────────────────────────
  static Future<Database> get database async {
    if (_db != null && _db!.isOpen) return _db!;
    _db = await _open();
    return _db!;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Inisialisasi database + DDL schema
  // ──────────────────────────────────────────────────────────────────────────
  static Future<Database> _open() async {
    final dbPath = p.join(await getDatabasesPath(), 'gara_local.db');
    return openDatabase(
      dbPath,
      version: 1,
      onCreate: (db, version) async {
        await _createSchema(db);
        // Setelah schema dibuat, coba migrasi data lama dari SharedPreferences
        await _migrateFromSharedPreferences(db);
      },
      // onUpgrade: untuk versi mendatang jika schema berubah
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // DDL: Schema tabel
  // ──────────────────────────────────────────────────────────────────────────
  static Future<void> _createSchema(Database db) async {
    // Tabel catatan — setiap baris = 1 dokumen catatan
    // blocks_json hanya menyimpan blok catatan itu saja (bukan seluruh list)
    await db.execute('''
      CREATE TABLE IF NOT EXISTS notes (
        id         TEXT PRIMARY KEY,
        title      TEXT    NOT NULL DEFAULT '',
        category   TEXT    NOT NULL DEFAULT 'umum',
        blocks_json TEXT   NOT NULL DEFAULT '[]',
        created_at TEXT    NOT NULL,
        updated_at TEXT    NOT NULL
      )
    ''');

    // Tabel fokus — singleton (hanya 1 baris, id = 1)
    await db.execute('''
      CREATE TABLE IF NOT EXISTS focus_data (
        id                INTEGER PRIMARY KEY DEFAULT 1,
        settings_json     TEXT    NOT NULL DEFAULT '{}',
        stats_json        TEXT    NOT NULL DEFAULT '{}',
        streak_json       TEXT    NOT NULL DEFAULT '{}',
        plant_water_points INTEGER NOT NULL DEFAULT 0,
        plant_level       INTEGER NOT NULL DEFAULT 1,
        history_json      TEXT    NOT NULL DEFAULT '{}'
      )
    ''');

    // Seed focus_data dengan row default (singleton)
    await db.insert(
      'focus_data',
      {
        'id': 1,
        'settings_json': '{}',
        'stats_json': '{}',
        'streak_json': '{}',
        'plant_water_points': 0,
        'plant_level': 1,
        'history_json': '{}',
      },
      conflictAlgorithm: ConflictAlgorithm.ignore,
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Migrasi data lama dari SharedPreferences → SQLite (zero data loss)
  // Dipanggil SEKALI saat database baru pertama kali dibuat.
  // ──────────────────────────────────────────────────────────────────────────
  static Future<void> _migrateFromSharedPreferences(Database db) async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // ── Migrasi Catatan ──────────────────────────────────────────────────
      final notesRaw = prefs.getString('garuda_akademi_ruang_catatan');
      if (notesRaw != null && notesRaw.isNotEmpty) {
        debugPrint('[DbService] Migrasi catatan dari SharedPreferences...');
        final List notesList = jsonDecode(notesRaw) as List;
        final batch = db.batch();
        for (final j in notesList) {
          final map = j as Map<String, dynamic>;
          // blocks: serialisasi kembali hanya array blocks satu note ini
          final blocksJson = jsonEncode(map['blocks'] ?? []);
          batch.insert(
            'notes',
            {
              'id':          map['id'] as String,
              'title':       map['title'] as String? ?? '',
              'category':    map['category'] as String? ?? 'umum',
              'blocks_json': blocksJson,
              'created_at':  map['created_at'] as String,
              'updated_at':  map['updated_at'] as String,
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
        await batch.commit(noResult: true);
        // Hapus data lama dari SharedPreferences setelah migrasi sukses
        await prefs.remove('garuda_akademi_ruang_catatan');
        debugPrint('[DbService] ✅ Migrasi ${notesList.length} catatan selesai.');
      }

      // ── Migrasi Fokus ────────────────────────────────────────────────────
      final focusRaw = prefs.getString('garuda_akademi_ruang_fokus');
      if (focusRaw != null && focusRaw.isNotEmpty) {
        debugPrint('[DbService] Migrasi data fokus dari SharedPreferences...');
        final Map<String, dynamic> fd =
            jsonDecode(focusRaw) as Map<String, dynamic>;

        final plant = fd['plant'] as Map<String, dynamic>? ?? {};
        await db.update(
          'focus_data',
          {
            'settings_json':      jsonEncode(fd['settings'] ?? {}),
            'stats_json':         jsonEncode(fd['stats'] ?? {}),
            'streak_json':        jsonEncode(fd['streak'] ?? {}),
            'plant_water_points': plant['water_points'] as int? ?? 0,
            'plant_level':        plant['level'] as int? ?? 1,
            'history_json':       jsonEncode(fd['history'] ?? {}),
          },
          where: 'id = ?',
          whereArgs: [1],
        );
        // Hapus data lama setelah migrasi sukses
        await prefs.remove('garuda_akademi_ruang_fokus');
        debugPrint('[DbService] ✅ Migrasi data fokus selesai.');
      }
    } catch (e) {
      // Migrasi gagal = data lama aman di SharedPreferences, DB kosong
      // Tidak crash — app tetap berjalan dengan data kosong
      debugPrint('[DbService] ⚠️ Migrasi SharedPreferences gagal: $e');
    }
  }
}
