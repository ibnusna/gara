import 'package:sqflite/sqflite.dart';
import '../models/note_model.dart';
import 'local_database_service.dart';

class NotesService {
  NotesService._();

  static Future<Database> get _db => LocalDatabaseService.instance.database;

  static Future<List<NoteModel>> load() async {
    final db = await _db;
    final noteRows = await db.query('notes', orderBy: 'updated_at DESC');
    if (noteRows.isEmpty) return [];

    final blockRows = await db.query('note_blocks', orderBy: 'sort_order ASC');
    final Map<String, List<NoteBlock>> blocksMap = {};
    for (final row in blockRows) {
      final noteId = row['note_id'] as String;
      blocksMap.putIfAbsent(noteId, () => []).add(NoteBlock(
            type: BlockType.values.firstWhere(
              (e) => e.name == (row['type'] as String),
              orElse: () => BlockType.text,
            ),
            content: row['content'] as String? ?? '',
            checked: (row['checked'] as int? ?? 0) == 1,
          ));
    }

    return noteRows.map((r) {
      final id = r['id'] as String;
      return NoteModel(
        id: id,
        title: r['title'] as String? ?? '',
        category: NoteCategory.values.firstWhere(
          (e) => e.name == (r['category'] as String? ?? 'umum'),
          orElse: () => NoteCategory.umum,
        ),
        blocks: blocksMap[id] ?? [],
        createdAt: DateTime.tryParse(r['created_at'] as String? ?? '') ?? DateTime.now(),
        updatedAt: DateTime.tryParse(r['updated_at'] as String? ?? '') ?? DateTime.now(),
      );
    }).toList();
  }

  static Future<void> saveNote(NoteModel note) async {
    note.updatedAt = DateTime.now();
    final db = await _db;
    await db.transaction((txn) async {
      await txn.insert(
        'notes',
        {
          'id': note.id,
          'title': note.title,
          'category': note.category.name,
          'created_at': note.createdAt.toIso8601String(),
          'updated_at': note.updatedAt.toIso8601String(),
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );

      await txn.delete('note_blocks', where: 'note_id = ?', whereArgs: [note.id]);
      for (var i = 0; i < note.blocks.length; i++) {
        final b = note.blocks[i];
        await txn.insert('note_blocks', {
          'note_id': note.id,
          'sort_order': i,
          'type': b.type.name,
          'content': b.content,
          'checked': b.checked ? 1 : 0,
        });
      }
    });
  }

  static Future<List<NoteModel>> saveOne(List<NoteModel> notes, NoteModel note) async {
    await saveNote(note);
    return load();
  }

  static Future<List<NoteModel>> add(List<NoteModel> notes, NoteModel note) =>
      saveOne(notes, note);

  static Future<List<NoteModel>> update(List<NoteModel> notes, NoteModel note) =>
      saveOne(notes, note);

  static Future<List<NoteModel>> delete(List<NoteModel> notes, String id) async {
    final db = await _db;
    await db.delete('notes', where: 'id = ?', whereArgs: [id]);
    return load();
  }

  static Future<List<NoteModel>> search(String query) async {
    final q = '%$query%';
    final db = await _db;
    final noteRows = await db.rawQuery('''
      SELECT * FROM notes 
      WHERE title LIKE ? OR id IN (
        SELECT note_id FROM note_blocks WHERE content LIKE ?
      )
      ORDER BY updated_at DESC
    ''', [q, q]);

    final List<String> noteIds = noteRows.map((r) => r['id'] as String).toList();
    if (noteIds.isEmpty) return [];

    final blockRows = await db.query(
      'note_blocks',
      where: 'note_id IN (${List.filled(noteIds.length, '?').join(',')})',
      whereArgs: noteIds,
      orderBy: 'sort_order ASC',
    );

    final Map<String, List<NoteBlock>> blocksMap = {};
    for (final row in blockRows) {
      final noteId = row['note_id'] as String;
      blocksMap.putIfAbsent(noteId, () => []).add(NoteBlock(
            type: BlockType.values.firstWhere(
              (e) => e.name == (row['type'] as String),
              orElse: () => BlockType.text,
            ),
            content: row['content'] as String? ?? '',
            checked: (row['checked'] as int? ?? 0) == 1,
          ));
    }

    return noteRows.map((r) {
      final id = r['id'] as String;
      return NoteModel(
        id: id,
        title: r['title'] as String? ?? '',
        category: NoteCategory.values.firstWhere(
          (e) => e.name == (r['category'] as String? ?? 'umum'),
          orElse: () => NoteCategory.umum,
        ),
        blocks: blocksMap[id] ?? [],
        createdAt: DateTime.tryParse(r['created_at'] as String? ?? '') ?? DateTime.now(),
        updatedAt: DateTime.tryParse(r['updated_at'] as String? ?? '') ?? DateTime.now(),
      );
    }).toList();
  }
}
