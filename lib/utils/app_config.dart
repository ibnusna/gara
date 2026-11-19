import 'package:flutter/foundation.dart';
import 'app_routes.dart';

export 'app_routes.dart';

class AppConfig {
  AppConfig._();

  static const String _compiledBaseUrl = String.fromEnvironment(
    'GARA_BASE_URL',
    defaultValue: 'https://garaedu.gt.tc',
  );

  static const bool enableLegacyInfinityBypass = bool.fromEnvironment(
    'ENABLE_INFINITY_BYPASS',
    defaultValue: false,
  );

  static bool _debugOverride = false;
  static bool get isDebugMode => kDebugMode || _debugOverride;
  static set isDebugMode(bool value) => _debugOverride = value;

  static String? overrideBaseUrl;

  static String get baseUrl {
    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      String url = overrideBaseUrl!;
      return url.endsWith('/') ? url.substring(0, url.length - 1) : url;
    }
    return _compiledBaseUrl;
  }

  static String get examBaseUrl => baseUrl;
  static String get apiMobileUrl => '$baseUrl/api/mobile';

  static bool get isLocalServer {
    final url = baseUrl;
    return url.contains('127.0.0.1') || url.contains('localhost') || url.contains('10.0.2.2');
  }

  // Path constants
  static const String ruangBelajarPath = AppRoutes.ruangBelajarPath;
  static const String ruangBelajarBabListPath = AppRoutes.ruangBelajarBabListPath;
  static const String ruangBelajarTopicListPath = AppRoutes.ruangBelajarTopicListPath;
  static const String ruangBelajarDetailPath = AppRoutes.ruangBelajarDetailPath;
  static const String ruangBelajarMarkSelesaiPath = AppRoutes.ruangBelajarMarkSelesaiPath;

  static const String ruangTugasPath = AppRoutes.ruangTugasPath;
  static const String ruangTugasListPath = AppRoutes.ruangTugasListPath;
  static const String ruangTugasDetailPath = AppRoutes.ruangTugasDetailPath;
  static const String ruangTugasSubmitPath = AppRoutes.ruangTugasSubmitPath;
  static const String ruangTugasDeletePath = AppRoutes.ruangTugasDeletePath;
  static const String ruangKompetensiPath = AppRoutes.ruangKompetensiPath;
  static const String ruangDiskusiPath = AppRoutes.ruangDiskusiPath;
  static const String ruangUjianEntryPath = AppRoutes.ruangUjianEntryPath;
  static const String ruangUjianKonfirmasiPath = AppRoutes.ruangUjianKonfirmasiPath;
  static const String ruangUjianPath = AppRoutes.ruangUjianPath;
  static const String ruangUjianHasilPath = AppRoutes.ruangUjianHasilPath;
  static const String examStatusPath = AppRoutes.examStatusPath;
  static const String handoffPath = AppRoutes.handoffPath;

  static const String studentDashboardPath = AppRoutes.studentDashboardPath;
  static const String studentPilihMapelPath = AppRoutes.studentPilihMapelPath;
  static const String pilihMapelPath = AppRoutes.pilihMapelPath;
  static const String guruDashboardPath = AppRoutes.guruDashboardPath;
  static const String operatorDashboardPath = AppRoutes.operatorDashboardPath;
  static const String kepsekDashboardPath = AppRoutes.kepsekDashboardPath;
  static const String superAdminDashboardPath = AppRoutes.superAdminDashboardPath;

  static const String operatorUsersPath = AppRoutes.operatorUsersPath;
  static const String operatorAsesmenJadwal = AppRoutes.operatorAsesmenJadwal;
  static const String operatorAsesmenHasil = AppRoutes.operatorAsesmenHasil;
  static const String operatorMasterPath = AppRoutes.operatorMasterPath;
  static const String operatorProfilPath = AppRoutes.operatorProfilPath;
  static const String operatorPasswordPath = AppRoutes.operatorPasswordPath;

  static const String guruMateriPath = AppRoutes.guruMateriPath;
  static const String guruTugasPath = AppRoutes.guruTugasPath;
  static const String guruPenilaianPath = AppRoutes.guruPenilaianPath;
  static const String guruProfilPath = AppRoutes.guruProfilPath;

  static const String studentPasswordPath = AppRoutes.studentPasswordPath;
  static const String studentTentangSayaPath = AppRoutes.studentTentangSayaPath;

  static String get studentDashboardUrl => '$baseUrl$studentDashboardPath';
  static String get guruDashboardUrl => '$baseUrl$guruDashboardPath';
  static String get operatorDashboardUrl => '$baseUrl$operatorDashboardPath';
  static String get kepsekDashboardUrl => '$baseUrl$kepsekDashboardPath';
  static String get superAdminDashboardUrl => '$baseUrl$superAdminDashboardPath';

  static const List<String> allowedDomains = [
    'localhost',
    '127.0.0.1',
    '10.0.2.2',
    'garaedu.gt.tc',
    'api.sekolah.sch.id',
  ];

  static String getHandoffTicketUrl(String ticket) => '$baseUrl$handoffPath?ticket=$ticket';

  static String getHandoffUrl({
    required String token,
    required String targetPath,
    String? mapelId,
  }) {
    final params = <String, String>{
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

    if (host == 'localhost' ||
        host == '127.0.0.1' ||
        host == '10.0.2.2' ||
        host.startsWith('192.168.') ||
        host.startsWith('10.') ||
        host.startsWith('172.')) {
      return true;
    }

    for (final domain in allowedDomains) {
      if (host == domain || host.endsWith('.$domain')) return true;
    }

    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      final oHost = Uri.tryParse(overrideBaseUrl!)?.host.toLowerCase();
      if (oHost != null && (host == oHost || host.endsWith('.$oHost'))) return true;
    }

    final bHost = Uri.tryParse(baseUrl)?.host.toLowerCase();
    if (bHost != null && (host == bHost || host.endsWith('.$bHost'))) return true;

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
