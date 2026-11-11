import 'package:flutter/foundation.dart';

class AppConfig {
  AppConfig._();

  static const String _devUrl = 'http://localhost:8000';
  static const String _prodUrl = 'https://garaedu.rf.gd';

  static bool _debugOverride = false;
  static bool get isDebugMode => kDebugMode || _debugOverride;
  static set isDebugMode(bool value) => _debugOverride = value;

  /// Dynamic base URL override (from SmartConnect or Firebase school activation)
  static String? overrideBaseUrl;

  static String get baseUrl {
    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      String url = overrideBaseUrl!;
      if (url.contains('garaedu.rf.gd/ite/gara')) {
        url = url.replaceAll('/ite/gara', '');
      } else if (url.contains('garaedu.rf.gd/ite')) {
        url = url.replaceAll('/ite', '');
      }
      return url.endsWith('/') ? url.substring(0, url.length - 1) : url;
    }
    return isDebugMode ? _devUrl : _prodUrl;
  }

  static const String _examDevUrl = 'http://127.0.0.1:8001';
  static const String _examProdUrl = 'https://garaedu.rf.gd';

  static String get examBaseUrl => (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty)
      ? baseUrl
      : (isDebugMode ? _examDevUrl : _examProdUrl);

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

  static bool isAllowedDomain(String urlOrHost) {
    final cleaned = urlOrHost.toLowerCase().trim();
    if (cleaned.isEmpty) return true;

    String host = cleaned;
    if (host.contains('://')) {
      final parsed = Uri.tryParse(host);
      if (parsed != null && parsed.host.isNotEmpty) host = parsed.host.toLowerCase();
    }
    if (host.contains(':')) host = host.split(':').first;

    bool matchesTarget(String targetHost) {
      if (targetHost.isEmpty) return false;
      String th = targetHost.contains(':') ? targetHost.split(':').first : targetHost;
      return host == th || host.endsWith('.$th') || th == host || th.endsWith('.$host');
    }

    try {
      final bHost = Uri.tryParse(baseUrl)?.host.toLowerCase();
      if (bHost != null && bHost.isNotEmpty && matchesTarget(bHost)) return true;
    } catch (_) {}

    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      try {
        final oHost = Uri.tryParse(overrideBaseUrl!)?.host.toLowerCase();
        if (oHost != null && oHost.isNotEmpty && matchesTarget(oHost)) return true;
      } catch (_) {}
    }

    try {
      final eHost = Uri.tryParse(examBaseUrl)?.host.toLowerCase();
      if (eHost != null && eHost.isNotEmpty && matchesTarget(eHost)) return true;
    } catch (_) {}

    return host.contains('localhost') ||
        host.contains('127.0.0.1') ||
        host.contains('garaedu.rf.gd') ||
        host.contains('rf.gd') ||
        host.contains('ifastnet.com') ||
        host.contains('infinityfree.net');
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
