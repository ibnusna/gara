// ============================================================
//  GARA Flutter — App Constants
//  Diekstrak dari: login.css, root.css, pilih_mapel.blade.php
// ============================================================

import 'package:flutter/material.dart';

/// Warna-warna yang identik dengan CSS variabel di Laravel GARA
class GaraColors {
  GaraColors._();

  // Brand / Login Screen (dari login.css)
  static const Color primary = Color(0xFF0056B3);
  static const Color primaryLight = Color(0xFF4DABF7);
  static const Color primaryDark = Color(0xFF003D80);
  static const Color bgDark = Color(0xFF020B16);
  static const Color bgOverlay = Color(0xD9050F1E); // rgba(5,15,30,0.85)
  static const Color inputBg = Color(0x99122037);   // rgba(18,32,55,0.6)
  static const Color glassBorder = Color(0x26FFFFFF); // rgba(255,255,255,0.15)
  static const Color textWhite = Color(0xFFFFFFFF);
  static const Color textMuted = Color(0xFFD1D5DB);

  // Siswa Design System (dari root.css)
  static const Color studentPrimary = Color(0xFF0B57D0);
  static const Color studentPrimaryDark = Color(0xFF08429E);
  static const Color studentPrimaryLight = Color(0xFFE8F0FE);
  static const Color studentBgBody = Color(0xFFF0F3F5);
  static const Color studentSurface = Color(0xFFFFFFFF);
  static const Color studentBorder = Color(0xFFE2E8F0);
  static const Color studentTextMain = Color(0xFF1E293B);
  static const Color studentTextMuted = Color(0xFF64748B);
  static const Color studentAccent = Color(0xFFFBBD05);

  // Exam Card (dari pilih_mapel.blade.php)
  static const Color examGradientStart = Color(0xFFFF6B6B);
  static const Color examGradientEnd   = Color(0xFFFF8E53);

  // Logout
  static const Color danger      = Color(0xFFDC3545);
  static const Color dangerLight = Color(0x1ADC3545);

  // ─────────────────────────────────────────────────────────────
  //  DESIGN SYSTEM BARU — Token dari dashborad.html
  // ─────────────────────────────────────────────────────────────

  /// Background body DS baru
  static const Color dsBgBody   = Color(0xFFF8FAFC);

  /// Mesh background base
  static const Color dsMeshBase = Color(0xFFF1F5F9);

  /// Blob mesh biru (#93c5fd)
  static const Color dsBlob1    = Color(0xFF93C5FD);

  /// Blob mesh ungu (#d8b4fe)
  static const Color dsBlob2    = Color(0xFFD8B4FE);

  /// Blob mesh cyan (#67e8f9)
  static const Color dsBlob3    = Color(0xFF67E8F9);

  /// Primary gradient gelap (#1d4ed8) — MD3 Primary Container
  static const Color dsPrimaryDeep  = Color(0xFF1D4ED8);

  /// Primary gradient terang (#2563eb)
  static const Color dsPrimaryBright = Color(0xFF2563EB);

  /// Warna gara blue alias (#1d4ed8)
  static const Color dsGaraBlue = Color(0xFF1D4ED8);

  /// Warna gara light (#3b82f6)
  static const Color dsGaraLight = Color(0xFF3B82F6);

  /// Slate-800 — teks utama DS
  static const Color dsSlate800 = Color(0xFF1E293B);

  /// Slate-500 — teks muted DS
  static const Color dsSlate500 = Color(0xFF64748B);

  /// Slate-400 — inactive/placeholder DS
  static const Color dsSlate400 = Color(0xFF94A3B8);

  /// Slate-200 — border tipis DS
  static const Color dsSlate200 = Color(0xFFE2E8F0);

  /// Rose-500 — notification badge DS
  static const Color dsRose500  = Color(0xFFF43F5E);

  /// Glass bg transparan (rgba(255,255,255,0.35))
  static const Color dsGlassBg     = Color(0x59FFFFFF);

  /// Glass bg strong (rgba(255,255,255,0.70))
  static const Color dsGlassBgStrong = Color(0xB3FFFFFF);

  /// Glass border (rgba(255,255,255,0.70))
  static const Color dsGlassBorder  = Color(0xB3FFFFFF);

  /// Glass border strong (rgba(255,255,255,0.90))
  static const Color dsGlassBorderStrong = Color(0xE6FFFFFF);

  // Icon accent colors (Eksplorasi grid)
  static const Color dsIconBlue    = Color(0xFF3B82F6); // Ruang Belajar
  static const Color dsIconOrange  = Color(0xFFF97316); // Ruang Tugas
  static const Color dsIconRose    = Color(0xFFF43F5E); // Kompetensi bg
  static const Color dsIconPurple  = Color(0xFFA855F7); // Fokus bg
  static const Color dsIconEmerald = Color(0xFF10B981); // Diskusi bg
  static const Color dsIconAmber   = Color(0xFFF59E0B); // Catatan bg
}

/// Named routes untuk navigator
class GaraRoutes {
  GaraRoutes._();
  static const String welcome = '/';
  static const String login = '/login';
  static const String pilihMapel = '/pilih-mapel';
  static const String dashboard = '/dashboard';

  // ── Ruang-ruang Siswa ─────────────────────────────────────
  static const String ruangBelajar = '/ruang-belajar';
  static const String ruangTugas = '/ruang-tugas';
  static const String ruangKompetensi = '/ruang-kompetensi';
  static const String ruangFokus = '/ruang-fokus';
  static const String ruangDiskusi = '/ruang-diskusi';
  static const String ruangCatatan = '/ruang-catatan';

  // ── Bottom Nav Pages ─────────────────────────────────────
  static const String notifikasi = '/notifikasi';
  static const String akunSaya = '/akun-saya';
}

/// Field key untuk SharedPreferences
class GaraPrefKeys {
  GaraPrefKeys._();
  static const String isLoggedIn      = 'is_logged_in';
  static const String userRole        = 'user_role'; // superadmin, operator, guru, siswa
  static const String namaSiswa       = 'nama_siswa';
  static const String kelasSiswa      = 'kelas_siswa';
  static const String selectedMapel   = 'selected_mapel';
  static const String selectedMapelId = 'selected_mapel_id';

  // Fase 2 — Sanctum Auth
  static const String authToken       = 'auth_token';       // Sanctum plainTextToken
  static const String namaLengkap     = 'nama_lengkap';     // dari API response
  static const String profilePhotoUrl = 'profile_photo_url'; // URL foto profil
}

/// Definisi Role GARA
class GaraRoles {
  GaraRoles._();
  static const String superAdmin = 'superadmin';
  static const String operator = 'operator';
  static const String guru = 'guru';
  static const String kepsek = 'kepsek';
  static const String siswa = 'siswa';
}

/// Model untuk item menu utama di dashboard
class DashboardMenuItem {
  final String label;
  final IconData icon;
  final Color bgColor;
  final Color iconColor;
  final String route;

  const DashboardMenuItem({
    required this.label,
    required this.icon,
    required this.bgColor,
    required this.iconColor,
    required this.route,
  });
}
