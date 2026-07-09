// Service: baca/tulis FocusData ke shared_preferences
// Key: 'garuda_akademi_ruang_fokus' — sama persis dengan Laravel
import 'package:shared_preferences/shared_preferences.dart';
import '../models/focus_model.dart';

const _kKey = 'garuda_akademi_ruang_fokus';

class FocusService {
  static Future<FocusData> load() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kKey);
    if (raw == null || raw.isEmpty) return FocusData();
    try {
      return FocusData.decode(raw);
    } catch (_) {
      return FocusData();
    }
  }

  static Future<void> save(FocusData data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kKey, data.encode());
  }

  // Catat sesi selesai — identik dengan completeSession() di blade
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
    final yesterday = DateTime.now().subtract(const Duration(days: 1))
        .toIso8601String().substring(0, 10);
    if (data.streak.lastFocusDate == yesterday || data.streak.lastFocusDate == null) {
      data.streak.current++;
    } else {
      data.streak.current = 1;
    }
    if (data.streak.current > data.streak.longest) {
      data.streak.longest = data.streak.current;
    }
    data.streak.lastFocusDate = today;
  }

  // Threshold identik dari blade: 8→L2, 15→L3, 25→L4, 40→L5
  static void _updatePlantLevel(FocusData data) {
    final pts = data.plantWaterPoints;
    final newLevel = pts >= 40 ? 5 : pts >= 25 ? 4 : pts >= 15 ? 3 : pts >= 8 ? 2 : 1;
    if (newLevel > data.plantLevel) data.plantLevel = newLevel;
  }
}
