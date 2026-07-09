




import 'package:flutter/material.dart';


class GaraColors {
  GaraColors._();

  
  static const Color primary = Color(0xFF0056B3);
  static const Color primaryLight = Color(0xFF4DABF7);
  static const Color primaryDark = Color(0xFF003D80);
  static const Color bgDark = Color(0xFF020B16);
  static const Color bgOverlay = Color(0xD9050F1E); 
  static const Color inputBg = Color(0x99122037);   
  static const Color glassBorder = Color(0x26FFFFFF); 
  static const Color textWhite = Color(0xFFFFFFFF);
  static const Color textMuted = Color(0xFFD1D5DB);

  
  static const Color studentPrimary = Color(0xFF0B57D0);
  static const Color studentPrimaryDark = Color(0xFF08429E);
  static const Color studentPrimaryLight = Color(0xFFE8F0FE);
  static const Color studentBgBody = Color(0xFFF0F3F5);
  static const Color studentSurface = Color(0xFFFFFFFF);
  static const Color studentBorder = Color(0xFFE2E8F0);
  static const Color studentTextMain = Color(0xFF1E293B);
  static const Color studentTextMuted = Color(0xFF64748B);
  static const Color studentAccent = Color(0xFFFBBD05);

  
  static const Color examGradientStart = Color(0xFFFF6B6B);
  static const Color examGradientEnd   = Color(0xFFFF8E53);

  
  static const Color danger      = Color(0xFFDC3545);
  static const Color dangerLight = Color(0x1ADC3545);

  
  
  

  
  static const Color dsBgBody   = Color(0xFFF8FAFC);

  
  static const Color dsMeshBase = Color(0xFFF1F5F9);

  
  static const Color dsBlob1    = Color(0xFF93C5FD);

  
  static const Color dsBlob2    = Color(0xFFD8B4FE);

  
  static const Color dsBlob3    = Color(0xFF67E8F9);

  
  static const Color dsPrimaryDeep  = Color(0xFF1D4ED8);

  
  static const Color dsPrimaryBright = Color(0xFF2563EB);

  
  static const Color dsGaraBlue = Color(0xFF1D4ED8);

  
  static const Color dsGaraLight = Color(0xFF3B82F6);

  
  static const Color dsSlate800 = Color(0xFF1E293B);

  
  static const Color dsSlate500 = Color(0xFF64748B);

  
  static const Color dsSlate400 = Color(0xFF94A3B8);

  
  static const Color dsSlate200 = Color(0xFFE2E8F0);

  
  static const Color dsRose500  = Color(0xFFF43F5E);

  
  static const Color dsGlassBg     = Color(0x59FFFFFF);

  
  static const Color dsGlassBgStrong = Color(0xB3FFFFFF);

  
  static const Color dsGlassBorder  = Color(0xB3FFFFFF);

  
  static const Color dsGlassBorderStrong = Color(0xE6FFFFFF);

  
  static const Color dsIconBlue    = Color(0xFF3B82F6); 
  static const Color dsIconOrange  = Color(0xFFF97316); 
  static const Color dsIconRose    = Color(0xFFF43F5E); 
  static const Color dsIconPurple  = Color(0xFFA855F7); 
  static const Color dsIconEmerald = Color(0xFF10B981); 
  static const Color dsIconAmber   = Color(0xFFF59E0B); 
}


class GaraRoutes {
  GaraRoutes._();
  static const String welcome = '/';
  static const String login = '/login';
  static const String pilihMapel = '/pilih-mapel';
  static const String dashboard = '/dashboard';

  
  static const String ruangBelajar = '/ruang-belajar';
  static const String ruangTugas = '/ruang-tugas';
  static const String ruangKompetensi = '/ruang-kompetensi';
  static const String ruangFokus = '/ruang-fokus';
  static const String ruangDiskusi = '/ruang-diskusi';
  static const String ruangCatatan = '/ruang-catatan';

  
  static const String notifikasi = '/notifikasi';
  static const String akunSaya = '/akun-saya';
}


class GaraPrefKeys {
  GaraPrefKeys._();
  static const String isLoggedIn      = 'is_logged_in';
  static const String userRole        = 'user_role'; 
  static const String namaSiswa       = 'nama_siswa';
  static const String kelasSiswa      = 'kelas_siswa';
  static const String selectedMapel   = 'selected_mapel';
  static const String selectedMapelId = 'selected_mapel_id';

  
  static const String authToken       = 'auth_token';       
  static const String namaLengkap     = 'nama_lengkap';     
  static const String profilePhotoUrl = 'profile_photo_url'; 

  
  
  static const String bypassTestCookie = 'infinityfree_test_cookie';

  
  
  static const String bypassSessionCookie = 'infinityfree_laravel_session';

  
  static const String bypassCookieTimestamp = 'infinityfree_cookie_timestamp';

  // Cache data mapel untuk mode offline
  static const String cachedMapelList      = 'cached_mapel_list_json';
  static const String cachedMapelTimestamp = 'cached_mapel_timestamp';
  static const String cachedGateUjian      = 'cached_gate_ujian_open';
  static const String cachedNamaSiswa      = 'cached_nama_siswa';
  static const String cachedKelasSiswa     = 'cached_kelas_siswa';
  static const String cachedSekolahNama    = 'cached_sekolah_nama';
}


class GaraRoles {
  GaraRoles._();
  static const String superAdmin = 'superadmin';
  static const String operator = 'operator';
  static const String guru = 'guru';
  static const String kepsek = 'kepsek';
  static const String siswa = 'siswa';
}


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
