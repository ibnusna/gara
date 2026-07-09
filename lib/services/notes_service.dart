

import 'package:shared_preferences/shared_preferences.dart';
import '../models/note_model.dart';

const _kKey = 'garuda_akademi_ruang_catatan';

class NotesService {
  
  static SharedPreferences? _prefs;
  static Future<SharedPreferences> _getPrefs() async {
    _prefs ??= await SharedPreferences.getInstance();
    return _prefs!;
  }

  
  static Future<List<NoteModel>> load() async {
    final prefs = await _getPrefs();
    final raw = prefs.getString(_kKey);
    if (raw == null || raw.isEmpty) return [];
    try {
      final notes = NoteModel.decodeList(raw);
      
      notes.sort((a, b) => b.updatedAt.compareTo(a.updatedAt));
      return notes;
    } catch (_) {
      return [];
    }
  }

  
  static Future<void> _save(List<NoteModel> notes) async {
    final prefs = await _getPrefs();
    await prefs.setString(_kKey, NoteModel.encodeList(notes));
  }

  
  
  
  static Future<List<NoteModel>> saveOne(
    List<NoteModel> notes,
    NoteModel note,
  ) async {
    note.updatedAt = DateTime.now();
    final idx = notes.indexWhere((n) => n.id == note.id);
    List<NoteModel> updated;
    if (idx >= 0) {
      updated = [...notes];
      updated[idx] = note;
    } else {
      updated = [note, ...notes];
    }
    
    updated.sort((a, b) => b.updatedAt.compareTo(a.updatedAt));
    await _save(updated);
    return updated;
  }


  
  static Future<List<NoteModel>> add(
    List<NoteModel> notes,
    NoteModel note,
  ) async {
    final updated = [note, ...notes];
    await _save(updated);
    return updated;
  }

  
  static Future<List<NoteModel>> update(
    List<NoteModel> notes,
    NoteModel updated,
  ) async {
    updated.updatedAt = DateTime.now();
    final list = notes.map((n) => n.id == updated.id ? updated : n).toList();
    await _save(list);
    return list;
  }

  
  static Future<List<NoteModel>> delete(
    List<NoteModel> notes,
    String id,
  ) async {
    final list = notes.where((n) => n.id != id).toList();
    await _save(list);
    return list;
  }
}
