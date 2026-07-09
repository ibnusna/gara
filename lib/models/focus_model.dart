

import 'dart:convert';

class FocusSettings {
  int focusDuration;   
  int shortBreak;      
  int longBreak;       

  FocusSettings({
    this.focusDuration = 25,
    this.shortBreak = 5,
    this.longBreak = 15,
  });

  factory FocusSettings.fromJson(Map<String, dynamic> j) => FocusSettings(
        focusDuration: j['focus_duration'] as int? ?? 25,
        shortBreak: j['short_break'] as int? ?? 5,
        longBreak: j['long_break'] as int? ?? 15,
      );

  Map<String, dynamic> toJson() => {
        'focus_duration': focusDuration,
        'short_break': shortBreak,
        'long_break': longBreak,
      };
}

class FocusStats {
  int totalSessions;
  int totalFocusMinutes;

  FocusStats({this.totalSessions = 0, this.totalFocusMinutes = 0});

  factory FocusStats.fromJson(Map<String, dynamic> j) => FocusStats(
        totalSessions: j['total_sessions'] as int? ?? 0,
        totalFocusMinutes: j['total_focus_minutes'] as int? ?? 0,
      );

  Map<String, dynamic> toJson() => {
        'total_sessions': totalSessions,
        'total_focus_minutes': totalFocusMinutes,
      };
}

class FocusStreak {
  int current;
  int longest;
  String? lastFocusDate; 

  FocusStreak({this.current = 0, this.longest = 0, this.lastFocusDate});

  factory FocusStreak.fromJson(Map<String, dynamic> j) => FocusStreak(
        current: j['current'] as int? ?? 0,
        longest: j['longest'] as int? ?? 0,
        lastFocusDate: j['last_focus_date'] as String?,
      );

  Map<String, dynamic> toJson() => {
        'current': current,
        'longest': longest,
        'last_focus_date': lastFocusDate,
      };
}

class FocusData {
  FocusSettings settings;
  FocusStats stats;
  FocusStreak streak;
  int plantWaterPoints;
  int plantLevel;      
  Map<String, int> history; 

  FocusData({
    FocusSettings? settings,
    FocusStats? stats,
    FocusStreak? streak,
    this.plantWaterPoints = 0,
    this.plantLevel = 1,
    Map<String, int>? history,
  })  : settings = settings ?? FocusSettings(),
        stats = stats ?? FocusStats(),
        streak = streak ?? FocusStreak(),
        history = history ?? {};

  factory FocusData.fromJson(Map<String, dynamic> j) => FocusData(
        settings: FocusSettings.fromJson(j['settings'] as Map<String, dynamic>? ?? {}),
        stats: FocusStats.fromJson(j['stats'] as Map<String, dynamic>? ?? {}),
        streak: FocusStreak.fromJson(j['streak'] as Map<String, dynamic>? ?? {}),
        plantWaterPoints: (j['plant'] as Map<String, dynamic>?)?['water_points'] as int? ?? 0,
        plantLevel: (j['plant'] as Map<String, dynamic>?)?['level'] as int? ?? 1,
        history: (j['history'] as Map<String, dynamic>? ?? {})
            .map((k, v) => MapEntry(k, v as int)),
      );

  Map<String, dynamic> toJson() => {
        'settings': settings.toJson(),
        'stats': stats.toJson(),
        'streak': streak.toJson(),
        'plant': {'water_points': plantWaterPoints, 'level': plantLevel},
        'history': history,
      };

  String encode() => jsonEncode(toJson());

  static FocusData decode(String raw) =>
      FocusData.fromJson(jsonDecode(raw) as Map<String, dynamic>);

  
  String get todayKey => DateTime.now().toIso8601String().substring(0, 10);
  int get todaySessions => history[todayKey] ?? 0;
}
