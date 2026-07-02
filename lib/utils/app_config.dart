// ============================================================
//  GARA Flutter — App Config (Pusat Kendali URL)
//  Fase 2: Hybrid WebView Integration + Sanctum Handoff
//
//  ATURAN: Jangan pernah menulis URL langsung di kode UI.
//  Semua rute harus bersumber dari class ini.
// ============================================================

import 'package:flutter/foundation.dart';

/// Pusat kendali seluruh URL, path endpoint, dan helper GARA.
///
/// Jika domain atau struktur folder Laravel berubah,
/// hanya file ini yang perlu dimodifikasi.
class AppConfig {
  AppConfig._(); // Non-instantiable

  // ── Domain Utama ─────────────────────────────────────────
  /// URL pengembangan: ADB Reverse → localhost:8000
  static const String _devUrl = 'http://localhost:8000';

  /// URL produksi — ganti dengan domain resmi saat deploy.
  /// Contoh: 'https://garudakademi.ct.ws'
  static const String _prodUrl = 'https://garudakademi.ct.ws';

  /// Base URL aktif berdasarkan mode build Flutter.
  /// - DEBUG   → _devUrl  (ADB Reverse, localhost:8000)
  /// - RELEASE → _prodUrl (domain produksi)
  static String get baseUrl => kReleaseMode ? _prodUrl : _devUrl;

  // ── Exam Engine Base URL (Port 8001) ─────────────────
  /// URL khusus untuk Ruang Ujian yang berjalan di port 8001.
  /// Selalu menggunakan localhost (tidak ada versi produksi terpisah
  /// karena exam server berjalan di jaringan sekolah lokal).
  static const String _examDevUrl  = 'http://127.0.0.1:8001';
  static const String _examProdUrl = 'https://garudakademi.ct.ws'; // Fallback ke domain utama jika prod

  /// Base URL aktif untuk Exam Engine.
  static String get examBaseUrl => kReleaseMode ? _examProdUrl : _examDevUrl;

  /// Base URL untuk endpoint API Mobile (MobileAuthController).
  static String get apiMobileUrl => '$baseUrl/api/mobile';

  // ── Path Statik per Ruang Siswa ──────────────────────────
  // CATATAN: Semua path harus cocok dengan routes/web.php di Laravel.

  /// Ruang Belajar — daftar materi dari guru.
  static const String ruangBelajarPath = '/student/materi';

  /// Ruang Tugas — pengumpulan tugas siswa.
  static const String ruangTugasPath = '/student/tugas';

  /// Ruang Kompetensi — sumber belajar yang dikurasi guru.
  /// PERBAIKAN: Rute Laravel adalah /student/ruang-kompetensi (bukan /student/kompetensi).
  static const String ruangKompetensiPath = '/student/ruang-kompetensi';

  /// Ruang Diskusi — thread diskusi antara siswa dan guru.
  static const String ruangDiskusiPath = '/student/diskusi';

  /// Ruang Ujian — halaman login/entry exam engine.
  static const String ruangUjianEntryPath = '/ruang-ujian';

  /// Ruang Ujian Konfirmasi — halaman ringkasan sebelum mulai.
  static const String ruangUjianKonfirmasiPath = '/ruang-ujian/konfirmasi';

  /// Ruang Ujian Arena — lembar soal aktif. STRICT MODE wajib di sini.
  /// Mengandung prefix ini → aktifkan Exam Security Engine.
  static const String ruangUjianPath = '/ruang-ujian/arena';

  /// Ruang Ujian Hasil — halaman skor + sertifikat PDF.
  /// Security DIMATIKAN di sini agar PDF download bisa berjalan.
  static const String ruangUjianHasilPath = '/ruang-ujian/hasil';

  /// Endpoint status ujian (Sanctum protected, untuk dashboard).
  static const String examStatusPath = '/api/student/exam-status';

  /// Rute bypass login web (Sanctum Handoff).
  static const String handoffPath = '/auth/webview-handoff';

  // ── Ruang Belajar Native API Paths ────────────────────────────────────────
  // Sesuai routes/web.php: prefix('api/mobile')->prefix('ruang-belajar')
  static const String ruangBelajarBabListPath   = '/ruang-belajar/bab-list';
  static const String ruangBelajarTopicListPath = '/ruang-belajar/topic-list';
  static const String ruangBelajarDetailPath    = '/ruang-belajar/detail';
  static const String ruangBelajarMarkSelesaiPath = '/ruang-belajar/mark-selesai';

  // ── Ruang Tugas Native API Paths ──────────────────────────────────────────
  // Sesuai routes/web.php: prefix('api/mobile')->prefix('ruang-tugas')
  static const String ruangTugasListPath   = '/ruang-tugas/list';
  static const String ruangTugasDetailPath = '/ruang-tugas/detail';
  static const String ruangTugasSubmitPath = '/ruang-tugas/submit';
  static const String ruangTugasDeletePath = '/ruang-tugas/delete';

  // ── Dashboard per Role ───────────────────────────────────
  static const String studentDashboardPath    = '/student/dashboard';
  static const String guruDashboardPath       = '/guru/pilih-sesi'; 
  static const String operatorDashboardPath   = '/operator/dashboard';
  static const String kepsekDashboardPath     = '/kepsek/dashboard';
  static const String superAdminDashboardPath = '/super-admin/dashboard';

  // ── Path Lengkap Semua Modul (Sesuai web.php) ────────────
  // Operator Routes
  static const String operatorUsersPath        = '/operator/users';
  static const String operatorAsesmenJadwal    = '/operator/asesmen/jadwal';
  static const String operatorAsesmenHasil     = '/operator/asesmen/hasil';
  static const String operatorMappingCurriculum= '/operator/mapping/curriculum';
  static const String operatorMappingCompetency= '/operator/mapping/competency';
  static const String operatorMappingAssignment= '/operator/mapping/assignments';
  static const String operatorMasterPath       = '/operator/master';
  static const String operatorProfilPath       = '/operator/profil';
  static const String operatorPasswordPath     = '/operator/ubah-password';

  // Guru Routes
  static const String guruMateriPath           = '/guru/materi';
  static const String guruTugasPath            = '/guru/tugas';
  static const String guruPenilaianPath        = '/guru/penilaian';
  static const String guruProfilPath           = '/guru/profil';

  // Siswa Routes (Tambahan untuk native AkunTab)
  static const String studentPasswordPath      = '/student/ubah-password';  // Form ganti password siswa (sesuai web.php)
  static const String studentTentangSayaPath   = '/student/tentang-saya';   // Profil akun web


  // ── URL Lengkap per Role (tanpa handoff) ─────────────────
  static String get studentDashboardUrl    => '$baseUrl$studentDashboardPath';
  static String get guruDashboardUrl       => '$baseUrl$guruDashboardPath';
  static String get operatorDashboardUrl   => '$baseUrl$operatorDashboardPath';
  static String get kepsekDashboardUrl     => '$baseUrl$kepsekDashboardPath';
  static String get superAdminDashboardUrl => '$baseUrl$superAdminDashboardPath';

  // ── Handoff URL Builder ──────────────────────────────────

  /// Membangun URL handoff yang membawa Sanctum token ke Laravel
  /// untuk membuat sesi web otomatis, lalu redirect ke [targetPath].
  ///
  /// [token]      — Sanctum plainTextToken dari SharedPreferences
  /// [targetPath] — Path Laravel tujuan, contoh: '/student/materi'
  /// [mapelId]    — (Opsional) ID mapel untuk pre-set session mapel_id
  ///               sehingga IsSiswa middleware tidak redirect ke pilih-mapel.
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

  /// Membangun handoff URL untuk dashboard role tertentu.
  /// Dipakai saat non-siswa (guru, operator, kepsek) login via native.
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

  // ── Utility Methods ──────────────────────────────────────

  /// Cek apakah [urlOrHost] berasal dari domain resmi GARA.
  /// Mengizinkan localhost (dev) dan domain produksi.
  /// Menerima URL penuh maupun host-only string.
  static bool isAllowedDomain(String urlOrHost) {
    return urlOrHost.contains('localhost') ||
        urlOrHost.contains('127.0.0.1') ||
        urlOrHost.contains('garudakademi.ct.ws');
  }

  /// Cek apakah [url] adalah halaman Ruang Ujian Arena (STRICT MODE).
  static bool isExamArenaUrl(String url) {
    return url.contains(ruangUjianPath) || url.contains('/ruang-ujian/arena');
  }

  /// Cek apakah [url] adalah halaman Hasil Ujian (security off, PDF on).
  static bool isExamResultUrl(String url) {
    return url.contains(ruangUjianHasilPath) || url.contains('/ruang-ujian/hasil');
  }

  /// Cek apakah [url] adalah halaman Ruang Ujian (entry/konfirmasi — no security).
  static bool isExamUrl(String url) {
    return url.contains('/ruang-ujian');
  }

  /// Cek apakah [url] adalah halaman dashboard utama role tertentu.
  /// Digunakan untuk logika "Double-Tap to Logout".
  static bool isRoleDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;

    return path == studentDashboardPath ||
        path == guruDashboardPath ||
        path == '/guru/dashboard' || // Extra check untuk Guru setelah pilih sesi
        path == operatorDashboardPath ||
        path == kepsekDashboardPath ||
        path == superAdminDashboardPath;
  }

  /// Cek apakah [url] adalah halaman yang mengharuskan WebView ditutup (Logout/Login).
  static bool isDashboardUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;

    return path == '/' || path == '/login' || path.isEmpty;
  }
}
