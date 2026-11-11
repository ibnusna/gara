import 'package:flutter/foundation.dart';

class AppConfig {
  AppConfig._();

  static const String _localUrl = 'http://127.0.0.1:8000';

  static bool _debugOverride = false;
  static bool get isDebugMode => kDebugMode || _debugOverride;
  static set isDebugMode(bool value) => _debugOverride = value;

  /// Dynamic base URL set exclusively from Google Firebase Firestore (or HITAMPEKAT)
  static String? overrideBaseUrl;

  static String get baseUrl {
    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      String url = overrideBaseUrl!;
      return url.endsWith('/') ? url.substring(0, url.length - 1) : url;
    }
    return _localUrl;
  }

  static String get examBaseUrl => baseUrl;

  static String get apiMobileUrl => '$baseUrl/api/mobile';

  static bool get isLocalServer {
    final url = baseUrl;
    return url.contains('127.0.0.1') || url.contains('localhost') || url.contains('10.0.2.2');
  }

  static const String ruangBelajarPath = '/student/materi';
  static const String ruangTugasPath = '/student/tugas';
  static const String ruangKompetensiPath = '/student/ruang-kompetensi';
  static const String ruangDiskusiPath = '/student/diskusi';
  static const String ruangUjianEntryPath = '/ruang-ujian';
  static const String ruangUjianKonfirmasiPath = '/ruang-ujian/konfirmasi';
  static const String ruangUjianPath = '/ruang-ujian/arena';
  static const String ruangUjianHasilPath = '/ruang-ujian/hasil';
  static const String examStatusPath = '/api/student/exam-status';
  static const String handoffPath = '/auth/webview-handoff';

  static const String ruangBelajarBabListPath = '/ruang-belajar/bab-list';
  static const String ruangBelajarTopicListPath = '/ruang-belajar/topic-list';
  static const String ruangBelajarDetailPath = '/ruang-belajar/detail';
  static const String ruangBelajarMarkSelesaiPath = '/ruang-belajar/mark-selesai';

  static const String ruangTugasListPath = '/ruang-tugas/list';
  static const String ruangTugasDetailPath = '/ruang-tugas/detail';
  static const String ruangTugasSubmitPath = '/ruang-tugas/submit';
  static const String ruangTugasDeletePath = '/ruang-tugas/delete';

  static const String studentDashboardPath = '/student/dashboard';
  static const String guruDashboardPath = '/guru/pilih-sesi';
  static const String operatorDashboardPath = '/operator/dashboard';
  static const String kepsekDashboardPath = '/kepsek/dashboard';
  static const String superAdminDashboardPath = '/super-admin/dashboard';

  static const String operatorUsersPath = '/operator/users';
  static const String operatorAsesmenJadwal = '/operator/asesmen/jadwal';
  static const String operatorAsesmenHasil = '/operator/asesmen/hasil';
  static const String operatorMappingCurriculum = '/operator/mapping/curriculum';
  static const String operatorMappingCompetency = '/operator/mapping/competency';
  static const String operatorMappingAssignment = '/operator/mapping/assignments';
  static const String operatorMasterPath = '/operator/master';
  static const String operatorProfilPath = '/operator/profil';
  static const String operatorPasswordPath = '/operator/ubah-password';

  static const String guruMateriPath = '/guru/materi';
  static const String guruTugasPath = '/guru/tugas';
  static const String guruPenilaianPath = '/guru/penilaian';
  static const String guruProfilPath = '/guru/profil';

  static const String studentPasswordPath = '/student/ubah-password';
  static const String studentTentangSayaPath = '/student/tentang-saya';

  static String get studentDashboardUrl => '$baseUrl$studentDashboardPath';
  static String get guruDashboardUrl => '$baseUrl$guruDashboardPath';
  static String get operatorDashboardUrl => '$baseUrl$operatorDashboardPath';
  static String get kepsekDashboardUrl => '$baseUrl$kepsekDashboardPath';
  static String get superAdminDashboardUrl => '$baseUrl$superAdminDashboardPath';

  static String getHandoffUrl({
    required String token,
    required String targetPath,
    String? mapelId,
  }) {
    final params = <String, String>{
      'token': token,
      'target': targetPath,
      if (mapelId != null && mapelId.isNotEmpty) 'mapel_id': mapelId,
    };
    return Uri.parse('$baseUrl$handoffPath').replace(queryParameters: params).toString();
  }

  static String getHandoffDashboardUrl({required String token, required String role}) {
    return getHandoffUrl(token: token, targetPath: _dashboardPathForRole(role));
  }

  static String _dashboardPathForRole(String role) => switch (role) {
        'guru' => guruDashboardPath,
        'operator' => operatorDashboardPath,
        'kepsek' => kepsekDashboardPath,
        'super_admin' => superAdminDashboardPath,
        _ => studentDashboardPath,
      };

  /// Domain validation: Only local domains (ADB / HITAMPEKAT) and Google Firebase hosting domains are allowed.
  static bool isAllowedDomain(String urlOrHost) {
    final cleaned = urlOrHost.toLowerCase().trim();
    if (cleaned.isEmpty) return true;

    String host = cleaned;
    if (host.contains('://')) {
      final parsed = Uri.tryParse(host);
      if (parsed != null && parsed.host.isNotEmpty) host = parsed.host.toLowerCase();
    }
    if (host.contains(':')) host = host.split(':').first;

    // 1. Domain lokal & schoolkey HITAMPEKAT (ADB debugging)
    if (host == 'localhost' ||
        host == '127.0.0.1' ||
        host == '10.0.2.2' ||
        host.startsWith('192.168.') ||
        host.startsWith('10.') ||
        host.startsWith('172.')) {
      return true;
    }

    bool matchesTarget(String targetHost) {
      if (targetHost.isEmpty) return false;
      String th = targetHost.contains(':') ? targetHost.split(':').first : targetHost;
      return host == th || host.endsWith('.$th') || th == host || th.endsWith('.$host');
    }

    // 2. Domain hosting resmi dari config Google Firebase
    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      try {
        final oHost = Uri.tryParse(overrideBaseUrl!)?.host.toLowerCase();
        if (oHost != null && oHost.isNotEmpty && matchesTarget(oHost)) return true;
      } catch (_) {}
    }

    try {
      final bHost = Uri.tryParse(baseUrl)?.host.toLowerCase();
      if (bHost != null && bHost.isNotEmpty && matchesTarget(bHost)) return true;
    } catch (_) {}

    return false;
  }

  static bool isExamArenaUrl(String url) =>
      url.contains(ruangUjianPath) ||
      url.contains('/ruang-ujian/arena') ||
      url.contains('/student/ruang-kompetensi/ujian');

  static bool isExamResultUrl(String url) =>
      url.contains(ruangUjianHasilPath) ||
      url.contains('/ruang-ujian/hasil') ||
      url.contains('?exam_done=1');

  static bool isExamUrl(String url) => url.contains('/ruang-ujian');

  static bool isRoleDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;
    return path.endsWith(studentDashboardPath) ||
        path.endsWith(guruDashboardPath) ||
        path.endsWith('/guru/dashboard') ||
        path.endsWith(operatorDashboardPath) ||
        path.endsWith(kepsekDashboardPath) ||
        path.endsWith(superAdminDashboardPath);
  }

  static bool isDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;
    return path == '/' || path.endsWith('/login') || path.isEmpty;
  }
}
