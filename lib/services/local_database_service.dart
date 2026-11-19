import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:path/path.dart' as p;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sqflite/sqflite.dart';

class LocalDatabaseService {
  LocalDatabaseService._();
  static final LocalDatabaseService instance = LocalDatabaseService._();

  static const String _dbName = 'gara_offline.db';
  static const int _dbVersion = 1;
  static const String migrationKey = 'sqlite_migration_v1_done';

  Database? _db;

  Future<Database> get database async {
    if (_db != null && _db!.isOpen) return _db!;
    _db = await _initDatabase();
    return _db!;
  }

  Future<Database> _initDatabase({String? customPath}) async {
    final dbPath = customPath ?? p.join(await getDatabasesPath(), _dbName);
    return await openDatabase(
      dbPath,
      version: _dbVersion,
      onCreate: (db, version) async {
        await _createSchema(db);
        await _migratePreferences(db);
      },
      onOpen: (db) async {
        await db.execute('PRAGMA foreign_keys = ON');
        await _migratePreferences(db);
      },
    );
  }

  Future<void> _createSchema(DatabaseExecutor db) async {
    await db.execute('''
      CREATE TABLE IF NOT EXISTS notes (
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      );
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS note_blocks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        note_id TEXT NOT NULL,
        sort_order INTEGER NOT NULL,
        type TEXT NOT NULL,
        content TEXT NOT NULL,
        checked INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
      );
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_notes_category_updated 
      ON notes(category, updated_at DESC);
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_note_blocks_note_id 
      ON note_blocks(note_id, sort_order ASC);
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS focus_meta (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        focus_duration INTEGER NOT NULL DEFAULT 25,
        short_break INTEGER NOT NULL DEFAULT 5,
        long_break INTEGER NOT NULL DEFAULT 15,
        total_sessions INTEGER NOT NULL DEFAULT 0,
        total_focus_minutes INTEGER NOT NULL DEFAULT 0,
        current_streak INTEGER NOT NULL DEFAULT 0,
        longest_streak INTEGER NOT NULL DEFAULT 0,
        last_focus_date TEXT,
        plant_water_points INTEGER NOT NULL DEFAULT 0,
        plant_level INTEGER NOT NULL DEFAULT 1
      );
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS focus_history (
        focus_date TEXT PRIMARY KEY,
        completed_sessions INTEGER NOT NULL DEFAULT 0
      );
    ''');
  }

  Future<void> _migratePreferences(Database db) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final isDone = prefs.getBool(migrationKey) ?? false;
      if (isDone) return;

      final notesJsonStr = prefs.getString('garuda_akademi_ruang_catatan');
      final focusJsonStr = prefs.getString('garuda_akademi_ruang_fokus');

      await db.transaction((txn) async {
        if (notesJsonStr != null && notesJsonStr.isNotEmpty) {
          final List list = jsonDecode(notesJsonStr);
          for (final item in list) {
            final m = item as Map<String, dynamic>;
            final noteId = m['id'] as String;
            await txn.insert('notes', {
              'id': noteId,
              'title': m['title'] ?? '',
              'category': m['category'] ?? 'umum',
              'created_at': m['created_at'] ?? DateTime.now().toIso8601String(),
              'updated_at': m['updated_at'] ?? DateTime.now().toIso8601String(),
            }, conflictAlgorithm: ConflictAlgorithm.replace);

            final blocks = m['blocks'] as List? ?? [];
            for (var i = 0; i < blocks.length; i++) {
              final b = blocks[i] as Map<String, dynamic>;
              await txn.insert('note_blocks', {
                'note_id': noteId,
                'sort_order': i,
                'type': b['type'] ?? 'text',
                'content': b['content'] ?? '',
                'checked': (b['checked'] == true) ? 1 : 0,
              });
            }
          }
        }

        if (focusJsonStr != null && focusJsonStr.isNotEmpty) {
          final f = jsonDecode(focusJsonStr) as Map<String, dynamic>;
          final s = f['settings'] as Map<String, dynamic>? ?? {};
          final st = f['stats'] as Map<String, dynamic>? ?? {};
          final str = f['streak'] as Map<String, dynamic>? ?? {};
          final pl = f['plant'] as Map<String, dynamic>? ?? {};

          await txn.insert('focus_meta', {
            'id': 1,
            'focus_duration': s['focus_duration'] ?? 25,
            'short_break': s['short_break'] ?? 5,
            'long_break': s['long_break'] ?? 15,
            'total_sessions': st['total_sessions'] ?? 0,
            'total_focus_minutes': st['total_focus_minutes'] ?? 0,
            'current_streak': str['current'] ?? 0,
            'longest_streak': str['longest'] ?? 0,
            'last_focus_date': str['last_focus_date'],
            'plant_water_points': pl['water_points'] ?? 0,
            'plant_level': pl['level'] ?? 1,
          }, conflictAlgorithm: ConflictAlgorithm.replace);

          final hist = f['history'] as Map<String, dynamic>? ?? {};
          for (final entry in hist.entries) {
            await txn.insert('focus_history', {
              'focus_date': entry.key,
              'completed_sessions': entry.value as int? ?? 0,
            }, conflictAlgorithm: ConflictAlgorithm.replace);
          }
        }
      });

      await prefs.remove('garuda_akademi_ruang_catatan');
      await prefs.remove('garuda_akademi_ruang_fokus');
      await prefs.setBool(migrationKey, true);
      debugPrint('[LocalDatabaseService] SQLite migration successfully committed.');
    } catch (e) {
      debugPrint('[LocalDatabaseService] Migration error: $e');
    }
  }

  Future<void> close() async {
    if (_db != null && _db!.isOpen) {
      await _db!.close();
      _db = null;
    }
  }
}
