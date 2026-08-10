







import 'package:flutter/foundation.dart';





class AppConfig {
  AppConfig._(); 

  
  
  static const String _devUrl = 'http://localhost:8000';

  // ── URL Produksi InfinityFree (rf.gd) ──────────────────────────────────────
  // InfinityFree menerapkan AES JavaScript Challenge pada semua HTTP request.
  // Semua request WAJIB melalui HeadlessInAppWebView (lihat InfinityAuthService
  // dan InfinityApiClient) agar JS challenge dapat dieksekusi secara native.
  static const String _prodUrl = 'https://garaedu.rf.gd';

  
  
  
  static bool _debugOverride = false;
  static bool get isDebugMode => kDebugMode || _debugOverride;
  static set isDebugMode(bool value) => _debugOverride = value;

  /// Override base URL dynamically (e.g. set by SmartConnect or activation)
  static String? overrideBaseUrl;

  static String get baseUrl {
    if (overrideBaseUrl != null && overrideBaseUrl!.isNotEmpty) {
      String url = overrideBaseUrl!;
      // Fix for Firebase tokens pointing to physical subdirectories
      if (url.contains('garaedu.rf.gd/ite/gara')) {
        url = url.replaceAll('/ite/gara', '');
      } else if (url.contains('garaedu.rf.gd/ite')) {
        url = url.replaceAll('/ite', '');
      }
      if (url.endsWith('/')) {
        url = url.substring(0, url.length - 1);
      }
      return url;
    }
    return isDebugMode ? _devUrl : _prodUrl;
  }

  
  
  
  
  static const String _examDevUrl  = 'http://127.0.0.1:8001';
  static const String _examProdUrl = 'https://garaedu.rf.gd'; // InfinityFree hosting

  
  static String get examBaseUrl => isDebugMode ? _examDevUrl : _examProdUrl;

  // URL ujian eksternal (sistem lama Garuda Akademi)
  static const String garudakademiBaseUrl = 'https://garudakademi.netlify.app';
  static const String garudakademiExamPath = '/summary.html';
  static String get garudakademiExamUrl => '$garudakademiBaseUrl$garudakademiExamPath';

  
  static String get apiMobileUrl => '$baseUrl/api/mobile';

  /// True jika server target adalah lokal (debug/ADB), tidak perlu bypass InfinityFree
  static bool get isLocalServer {
    final url = baseUrl;
    return url.contains('127.0.0.1') ||
        url.contains('localhost') ||
        url.contains('10.0.2.2');
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
    // PIN task aktif saat di halaman arena ujian (LMS atau Netlify)
    //
    // Netlify garudakademi.netlify.app:
    //   ON  → summary.html (konfirmasi peserta, entry point ujian dari LMS)
    //   ON  → ujian.html (halaman soal)
    //   OFF → index.html  (halaman LOGIN — jangan disentuh, ini trap)
    //   OFF → hasil.html  (halaman hasil ujian)
    //
    // LMS:
    //   ON  → /ruang-ujian/arena
    //   ON  → /student/ruang-kompetensi/ujian/{id}  ← hanya saat masuk ujian
    //   OFF → /student/ruang-kompetensi             ← halaman daftar, JANGAN pin task
    if (url.contains('garudakademi.netlify.app')) {
      // Aktifkan PIN task hanya di summary.html dan ujian.html
      // JANGAN aktifkan di index.html (login) dan hasil.html (selesai)
      return (url.contains('/summary.html') || url.contains('/ujian.html')) &&
          !url.contains('/index.html') &&
          !url.contains('/hasil.html') &&
          !url.contains('?exam_done=1');
    }
    return url.contains(ruangUjianPath) ||
        url.contains('/ruang-ujian/arena') ||
        url.contains('/student/ruang-kompetensi/ujian'); // hanya sub-path ujian, bukan root kompetensi
  }


  static bool isExamResultUrl(String url) {
    // PIN task dinonaktifkan saat di halaman hasil (LMS atau Netlify)
    return url.contains(ruangUjianHasilPath) ||
        url.contains('/ruang-ujian/hasil') ||
        url.contains('/hasil.html') ||          // Netlify: halaman hasil
        url.contains('?exam_done=1');           // Universal exit signal
  }

  /// True jika URL adalah halaman login (/index.html) Netlify.
  /// Halaman ini adalah TRAP — jika pengguna sampai ke sini dari dalam ujian,
  /// berarti sesi habis atau logout. Harus langsung kembali ke dashboard Flutter.
  static bool isNetlifyIndexTrap(String url) {
    return url.contains('garudakademi.netlify.app') &&
        (url.contains('/index.html') || url.endsWith('garudakademi.netlify.app/'));
  }

  
  static bool isExamUrl(String url) {
    return url.contains('/ruang-ujian');
  }

  
  
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
