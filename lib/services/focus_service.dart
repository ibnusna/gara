// ─────────────────────────────────────────────────────────────────────────────
// BUG-001 Fix: FocusService migrated from SharedPreferences to SQLite
//
// BEFORE: save() encode FocusData → prefs.setString() → full XML write
// AFTER:  UPDATE focus_data SET ... WHERE id = 1  → partial column update
//         History semakin besar per hari tapi tidak re-serialize seluruh list
// ─────────────────────────────────────────────────────────────────────────────

import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import '../models/focus_model.dart';
import 'db_service.dart';

class FocusService {
  FocusService._();

  static Future<Database> get _db => DbService.database;

  // ──────────────────────────────────────────────────────────────────────────
  // load() — baca singleton focus_data dari SQLite
  // Query: SELECT * FROM focus_data WHERE id = 1
  // ──────────────────────────────────────────────────────────────────────────
  static Future<FocusData> load() async {
    final db = await _db;
    final rows = await db.query('focus_data', where: 'id = ?', whereArgs: [1]);
    if (rows.isEmpty) return FocusData();
    try {
      return _rowToFocusData(rows.first);
    } catch (_) {
      return FocusData();
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // save() — update singleton focus_data
  // Query: UPDATE focus_data SET ... WHERE id = 1
  // ──────────────────────────────────────────────────────────────────────────
  static Future<void> save(FocusData data) async {
    final db = await _db;
    await db.update(
      'focus_data',
      _focusDataToRow(data),
      where: 'id = ?',
      whereArgs: [1],
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // completeSession() — mutasi in-memory lalu simpan
  // Logic bisnis tidak berubah, hanya persistence backend yang berbeda
  // ──────────────────────────────────────────────────────────────────────────
  static void completeSession(FocusData data) {
    data.stats.totalSessions++;
    data.stats.totalFocusMinutes += data.settings.focusDuration;
    data.plantWaterPoints++;
    _updateStreak(data);
    _updatePlantLevel(data);
    data.history[data.todayKey] = data.todaySessions + 1;
  }

  static void _updateStreak(FocusData data) {
    final today = data.todayKey;
    if (data.streak.lastFocusDate == today) return;
    final yesterday = DateTime.now()
        .subtract(const Duration(days: 1))
        .toIso8601String()
        .substring(0, 10);
    if (data.streak.lastFocusDate == yesterday ||
        data.streak.lastFocusDate == null) {
      data.streak.current++;
    } else {
      data.streak.current = 1;
    }
    if (data.streak.current > data.streak.longest) {
      data.streak.longest = data.streak.current;
    }
    data.streak.lastFocusDate = today;
  }

  static void _updatePlantLevel(FocusData data) {
    final pts = data.plantWaterPoints;
    final newLevel =
        pts >= 40 ? 5 : pts >= 25 ? 4 : pts >= 15 ? 3 : pts >= 8 ? 2 : 1;
    if (newLevel > data.plantLevel) data.plantLevel = newLevel;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Konversi: Map baris SQLite → FocusData
  // ──────────────────────────────────────────────────────────────────────────
  static FocusData _rowToFocusData(Map<String, dynamic> row) {
    Map<String, dynamic> decodeMap(String key) {
      try {
        return jsonDecode(row[key] as String? ?? '{}') as Map<String, dynamic>;
      } catch (_) {
        return {};
      }
    }

    Map<String, int> decodeHistory() {
      try {
        final raw =
            jsonDecode(row['history_json'] as String? ?? '{}') as Map<String, dynamic>;
        return raw.map((k, v) => MapEntry(k, (v as num).toInt()));
      } catch (_) {
        return {};
      }
    }

    return FocusData(
      settings:         FocusSettings.fromJson(decodeMap('settings_json')),
      stats:            FocusStats.fromJson(decodeMap('stats_json')),
      streak:           FocusStreak.fromJson(decodeMap('streak_json')),
      plantWaterPoints: row['plant_water_points'] as int? ?? 0,
      plantLevel:       row['plant_level'] as int? ?? 1,
      history:          decodeHistory(),
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Konversi: FocusData → Map baris SQLite
  // ──────────────────────────────────────────────────────────────────────────
  static Map<String, dynamic> _focusDataToRow(FocusData data) => {
        'settings_json':      jsonEncode(data.settings.toJson()),
        'stats_json':         jsonEncode(data.stats.toJson()),
        'streak_json':        jsonEncode(data.streak.toJson()),
        'plant_water_points': data.plantWaterPoints,
        'plant_level':        data.plantLevel,
        'history_json':       jsonEncode(data.history),
      };
}
