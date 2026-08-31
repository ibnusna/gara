// ─────────────────────────────────────────────────────────────────────────────
// BUG-001 Fix: NotesService migrated from SharedPreferences to SQLite
//
// BEFORE: _save() encode seluruh list notes → prefs.setString() → XML write-all
// AFTER:  Setiap operasi hanya menyentuh 1 row di tabel `notes`
//         → INSERT OR REPLACE / DELETE WHERE id = ? → O(1) per operasi
// ─────────────────────────────────────────────────────────────────────────────

import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import '../models/note_model.dart';
import 'db_service.dart';

class NotesService {
  NotesService._();

  // Helper: dapatkan instance DB
  static Future<Database> get _db => DbService.database;

  // ──────────────────────────────────────────────────────────────────────────
  // load() — ambil semua catatan, diurutkan terbaru dulu
  // Query: SELECT * FROM notes ORDER BY updated_at DESC
  // ──────────────────────────────────────────────────────────────────────────
  static Future<List<NoteModel>> load() async {
    final db = await _db;
    final rows = await db.query(
      'notes',
      orderBy: 'updated_at DESC',
    );
    return rows.map(_rowToNote).toList();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // saveOne() — insert atau update SATU catatan
  // Query: INSERT OR REPLACE INTO notes VALUES (...)
  // Mengembalikan list catatan terbaru (untuk UI state update)
  // ──────────────────────────────────────────────────────────────────────────
  static Future<List<NoteModel>> saveOne(
    List<NoteModel> notes, // tetap ada untuk backward-compat dengan UI caller
    NoteModel note,
  ) async {
    note.updatedAt = DateTime.now();
    final db = await _db;
    await db.insert(
      'notes',
      _noteToRow(note),
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
    // Kembalikan list terbaru dari DB (sudah ter-sort)
    return load();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // add() — tambah catatan baru
  // ──────────────────────────────────────────────────────────────────────────
  static Future<List<NoteModel>> add(
    List<NoteModel> notes,
    NoteModel note,
  ) async {
    final db = await _db;
    await db.insert(
      'notes',
      _noteToRow(note),
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
    return load();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // update() — perbarui catatan yang sudah ada
  // Query: UPDATE notes SET ... WHERE id = ?
  // ──────────────────────────────────────────────────────────────────────────
  static Future<List<NoteModel>> update(
    List<NoteModel> notes,
    NoteModel updated,
  ) async {
    updated.updatedAt = DateTime.now();
    final db = await _db;
    await db.update(
      'notes',
      _noteToRow(updated),
      where: 'id = ?',
      whereArgs: [updated.id],
    );
    return load();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // delete() — hapus satu catatan berdasarkan ID
  // Query: DELETE FROM notes WHERE id = ?
  // ──────────────────────────────────────────────────────────────────────────
  static Future<List<NoteModel>> delete(
    List<NoteModel> notes,
    String id,
  ) async {
    final db = await _db;
    await db.delete('notes', where: 'id = ?', whereArgs: [id]);
    return load();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Konversi: Map baris SQLite → NoteModel
  // ──────────────────────────────────────────────────────────────────────────
  static NoteModel _rowToNote(Map<String, dynamic> row) {
    List<NoteBlock> blocks;
    try {
      final List blocksList = jsonDecode(row['blocks_json'] as String) as List;
      blocks = blocksList
          .map((b) => NoteBlock.fromJson(b as Map<String, dynamic>))
          .toList();
    } catch (_) {
      blocks = [];
    }

    return NoteModel(
      id:        row['id'] as String,
      title:     row['title'] as String,
      category:  NoteCategory.values.firstWhere(
        (e) => e.name == (row['category'] as String? ?? 'umum'),
        orElse: () => NoteCategory.umum,
      ),
      blocks:    blocks,
      createdAt: DateTime.parse(row['created_at'] as String),
      updatedAt: DateTime.parse(row['updated_at'] as String),
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Konversi: NoteModel → Map baris SQLite
  // ──────────────────────────────────────────────────────────────────────────
  static Map<String, dynamic> _noteToRow(NoteModel note) => {
        'id':          note.id,
        'title':       note.title,
        'category':    note.category.name,
        // Hanya serialize blocks satu note — bukan seluruh list!
        'blocks_json': jsonEncode(
            note.blocks.map((b) => b.toJson()).toList()),
        'created_at':  note.createdAt.toIso8601String(),
        'updated_at':  note.updatedAt.toIso8601String(),
      };
}
