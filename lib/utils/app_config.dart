







import 'package:flutter/foundation.dart';





class AppConfig {
  AppConfig._(); 

  
  
  static const String _devUrl = 'http://localhost:8000';

  // ── URL Produksi InfinityFree (rf.gd) ──────────────────────────────────────
  // InfinityFree menerapkan AES JavaScript Challenge pada semua HTTP request.
  // Semua request WAJIB melalui HeadlessInAppWebView (lihat InfinityAuthService
  // dan InfinityApiClient) agar JS challenge dapat dieksekusi secara native.
  static const String _prodUrl = 'https://garaedu.rf.gd';

  
  
  
  static bool isDebugMode = false;

  static String get baseUrl => isDebugMode ? _devUrl : _prodUrl;

  
  
  
  
  static const String _examDevUrl  = 'http://127.0.0.1:8001';
  static const String _examProdUrl = 'https://garaedu.rf.gd'; // InfinityFree hosting

  
  static String get examBaseUrl => isDebugMode ? _examDevUrl : _examProdUrl;

  // URL ujian eksternal (sistem lama Garuda Akademi)
  static const String garudakademiBaseUrl = 'https://garudakademi.netlify.app';
  static const String garudakademiExamPath = '/summary.html';
  static String get garudakademiExamUrl => '$garudakademiBaseUrl$garudakademiExamPath';

  
  static String get apiMobileUrl => '$baseUrl/api/mobile';

  
  

  
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

  
  
  static const String ruangBelajarBabListPath   = '/ruang-belajar/bab-list';
  static const String ruangBelajarTopicListPath = '/ruang-belajar/topic-list';
  static const String ruangBelajarDetailPath    = '/ruang-belajar/detail';
  static const String ruangBelajarMarkSelesaiPath = '/ruang-belajar/mark-selesai';

  
  
  static const String ruangTugasListPath   = '/ruang-tugas/list';
  static const String ruangTugasDetailPath = '/ruang-tugas/detail';
  static const String ruangTugasSubmitPath = '/ruang-tugas/submit';
  static const String ruangTugasDeletePath = '/ruang-tugas/delete';

  
  static const String studentDashboardPath    = '/student/dashboard';
  static const String guruDashboardPath       = '/guru/pilih-sesi'; 
  static const String operatorDashboardPath   = '/operator/dashboard';
  static const String kepsekDashboardPath     = '/kepsek/dashboard';
  static const String superAdminDashboardPath = '/super-admin/dashboard';

  
  
  static const String operatorUsersPath        = '/operator/users';
  static const String operatorAsesmenJadwal    = '/operator/asesmen/jadwal';
  static const String operatorAsesmenHasil     = '/operator/asesmen/hasil';
  static const String operatorMappingCurriculum= '/operator/mapping/curriculum';
  static const String operatorMappingCompetency= '/operator/mapping/competency';
  static const String operatorMappingAssignment= '/operator/mapping/assignments';
  static const String operatorMasterPath       = '/operator/master';
  static const String operatorProfilPath       = '/operator/profil';
  static const String operatorPasswordPath     = '/operator/ubah-password';

  
  static const String guruMateriPath           = '/guru/materi';
  static const String guruTugasPath            = '/guru/tugas';
  static const String guruPenilaianPath        = '/guru/penilaian';
  static const String guruProfilPath           = '/guru/profil';

  
  static const String studentPasswordPath      = '/student/ubah-password';  
  static const String studentTentangSayaPath   = '/student/tentang-saya';   


  
  static String get studentDashboardUrl    => '$baseUrl$studentDashboardPath';
  static String get guruDashboardUrl       => '$baseUrl$guruDashboardPath';
  static String get operatorDashboardUrl   => '$baseUrl$operatorDashboardPath';
  static String get kepsekDashboardUrl     => '$baseUrl$kepsekDashboardPath';
  static String get superAdminDashboardUrl => '$baseUrl$superAdminDashboardPath';

  

  
  
  
  
  
  
  
  static String getHandoffUrl({
    required String token,
    required String targetPath,
    String? mapelId,
  }) {
    final params = <String, String>{
      'token':  token,
      'target': targetPath,
      if (mapelId != null && mapelId.isNotEmpty) 'mapel_id': mapelId,
    };
    return Uri.parse('$baseUrl$handoffPath')
        .replace(queryParameters: params)
        .toString();
  }

  
  
  static String getHandoffDashboardUrl({
    required String token,
    required String role,
  }) {
    final dashPath = _dashboardPathForRole(role);
    return getHandoffUrl(token: token, targetPath: dashPath);
  }

  static String _dashboardPathForRole(String role) {
    switch (role) {
      case 'guru':        return guruDashboardPath;
      case 'operator':    return operatorDashboardPath;
      case 'kepsek':      return kepsekDashboardPath;
      case 'super_admin': return superAdminDashboardPath;
      default:            return studentDashboardPath;
    }
  }

  

  
  
  
  static bool isAllowedDomain(String urlOrHost) {
    return urlOrHost.contains('localhost') ||
        urlOrHost.contains('127.0.0.1') ||
        urlOrHost.contains('garaedu.rf.gd') ||
        urlOrHost.contains('rf.gd') ||
        urlOrHost.contains('ifastnet.com') ||
        urlOrHost.contains('infinityfree.net') ||
        urlOrHost.contains('garudakademi.netlify.app') ||
        urlOrHost.contains('netlify.app');
  }

  
  static bool isExamArenaUrl(String url) {
    return url.contains(ruangUjianPath) || url.contains('/ruang-ujian/arena') || url.contains(ruangKompetensiPath) || url.contains(garudakademiBaseUrl) || url.contains('garudakademi.netlify.app');
  }

  
  static bool isExamResultUrl(String url) {
    return url.contains(ruangUjianHasilPath) || url.contains('/ruang-ujian/hasil');
  }

  
  static bool isExamUrl(String url) {
    return url.contains('/ruang-ujian');
  }

  
  
  static bool isRoleDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;

    return path == studentDashboardPath ||
        path == guruDashboardPath ||
        path == '/guru/dashboard' || 
        path == operatorDashboardPath ||
        path == kepsekDashboardPath ||
        path == superAdminDashboardPath;
  }

  
  static bool isDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;

    return path == '/' || path == '/login' || path.isEmpty;
  }
}
