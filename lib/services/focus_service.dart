import 'package:sqflite/sqflite.dart';
import '../models/focus_model.dart';
import 'local_database_service.dart';

class FocusService {
  FocusService._();

  static Future<Database> get _db => LocalDatabaseService.instance.database;

  static Future<FocusData> load() async {
    final db = await _db;
    final metaRows = await db.query('focus_meta', where: 'id = ?', whereArgs: [1]);
    final histRows = await db.query('focus_history');

    final Map<String, int> history = {};
    for (final r in histRows) {
      history[r['focus_date'] as String] = r['completed_sessions'] as int? ?? 0;
    }

    if (metaRows.isEmpty) {
      return FocusData(history: history);
    }

    final m = metaRows.first;
    return FocusData(
      settings: FocusSettings(
        focusDuration: m['focus_duration'] as int? ?? 25,
        shortBreak: m['short_break'] as int? ?? 5,
        longBreak: m['long_break'] as int? ?? 15,
      ),
      stats: FocusStats(
        totalSessions: m['total_sessions'] as int? ?? 0,
        totalFocusMinutes: m['total_focus_minutes'] as int? ?? 0,
      ),
      streak: FocusStreak(
        current: m['current_streak'] as int? ?? 0,
        longest: m['longest_streak'] as int? ?? 0,
        lastFocusDate: m['last_focus_date'] as String?,
      ),
      plantWaterPoints: m['plant_water_points'] as int? ?? 0,
      plantLevel: m['plant_level'] as int? ?? 1,
      history: history,
    );
  }

  static Future<void> save(FocusData data) async {
    final db = await _db;
    await db.transaction((txn) async {
      await txn.insert(
        'focus_meta',
        {
          'id': 1,
          'focus_duration': data.settings.focusDuration,
          'short_break': data.settings.shortBreak,
          'long_break': data.settings.longBreak,
          'total_sessions': data.stats.totalSessions,
          'total_focus_minutes': data.stats.totalFocusMinutes,
          'current_streak': data.streak.current,
          'longest_streak': data.streak.longest,
          'last_focus_date': data.streak.lastFocusDate,
          'plant_water_points': data.plantWaterPoints,
          'plant_level': data.plantLevel,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );

      for (final entry in data.history.entries) {
        await txn.insert(
          'focus_history',
          {
            'focus_date': entry.key,
            'completed_sessions': entry.value,
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

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
}
