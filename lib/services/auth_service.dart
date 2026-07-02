// ============================================================
//  GARA Flutter — AuthService
//  Fase 2: Sanctum API Integration
//
//  Tanggung jawab:
//   - login()   → POST /api/mobile/login → simpan token + user data
//   - logout()  → POST /api/mobile/logout → hapus token di server
//   - me()      → GET  /api/mobile/user   → refresh data user
//   - clearSession() → hapus semua data lokal (SharedPreferences)
// ============================================================

import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/mapel_model.dart';

/// Hasil dari proses login.
class AuthResult {
  final bool success;
  final String? token;
  final String? role;
  final String? nama;
  final String? username;
  final String? profilePhotoUrl;
  final String? errorMessage;
  final bool isMaintenance;

  const AuthResult({
    required this.success,
    this.token,
    this.role,
    this.nama,
    this.username,
    this.profilePhotoUrl,
    this.errorMessage,
    this.isMaintenance = false,
  });
}

/// Service terpusat untuk semua operasi autentikasi GARA.
///
/// Semua metode bersifat static — tidak perlu diinstansiasi.
class AuthService {
  AuthService._();

  // ── Login ─────────────────────────────────────────────────

  /// Login dengan username & password ke Laravel via Sanctum.
  ///
  /// Jika berhasil, menyimpan token + data user ke SharedPreferences
  /// dan mengembalikan [AuthResult] dengan data lengkap.
  static Future<AuthResult> login({
    required String identifier,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.apiMobileUrl}/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-App':        'GARA_MOBILE',
        },
        body: jsonEncode({
          'identifier': identifier.trim(),
          'password':   password,
        }),
      ).timeout(const Duration(seconds: 15));

      // Maintenance mode
      if (response.statusCode == 503) {
        return const AuthResult(
          success:      false,
          isMaintenance: true,
          errorMessage: 'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
        );
      }

      if (response.statusCode >= 500) {
        return const AuthResult(
          success:      false,
          errorMessage: 'Server sedang mengalami gangguan (500). Silakan hubungi admin.',
        );
      }

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        // Simpan ke SharedPreferences
        await _saveSession(data);

        return AuthResult(
          success:         true,
          token:           data['token'] as String?,
          role:            data['role']  as String?,
          nama:            data['nama']  as String?,
          username:        data['username'] as String?,
          profilePhotoUrl: data['profile_photo'] as String?,
        );
      }

      return AuthResult(
        success:      false,
        errorMessage: data['message'] as String? ?? 'Login gagal.',
      );
    } on SocketException {
      return const AuthResult(
        success:      false,
        errorMessage: 'Tidak ada koneksi internet. Periksa jaringan Anda.',
      );
    } on http.ClientException {
      return const AuthResult(
        success:      false,
        errorMessage: 'Tidak dapat terhubung ke server GARA.',
      );
    } catch (e) {
      debugPrint('[AuthService.login] Error: $e');
      return AuthResult(
        success:      false,
        errorMessage: 'Terjadi kesalahan. Coba lagi.',
      );
    }
  }

  // ── Logout ────────────────────────────────────────────────

  /// Revoke token di server, lalu hapus semua data lokal.
  ///
  /// Jika server tidak bisa dijangkau, tetap hapus data lokal
  /// agar user tidak terjebak dalam kondisi "logged in tapi offline".
  static Future<void> logout() async {
    try {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        await http.post(
          Uri.parse('${AppConfig.apiMobileUrl}/logout'),
          headers: {
            'Authorization': 'Bearer $token',
            'Accept':        'application/json',
          },
        ).timeout(const Duration(seconds: 8));
      }
    } catch (e) {
      debugPrint('[AuthService.logout] Server revoke gagal (OK — hapus lokal): $e');
    } finally {
      // Selalu hapus data lokal, terlepas dari hasil server
      await clearSession();
    }
  }

  // ── Fetch Mapel List ──────────────────────────────────────

  /// Mengambil daftar mata pelajaran asli dari server untuk siswa.
  static Future<MapelResponse> getMapelList() async {
    try {
      final token = await getToken();
      if (token == null) return MapelResponse.error('Sesi berakhir. Silakan login ulang.');

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/mapel'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        final List mapelJson = data['mapel_list'] as List;
        final mapelList = mapelJson.map((j) => MapelModel.fromJson(j)).toList();

        // Update info siswa terbaru di SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(GaraPrefKeys.namaSiswa,  data['nama_siswa']  as String? ?? '');
        await prefs.setString(GaraPrefKeys.kelasSiswa, data['kelas_siswa'] as String? ?? '');

        return MapelResponse(
          success:       true,
          namaSiswa:     data['nama_siswa']  as String? ?? '',
          kelasSiswa:    data['kelas_siswa'] as String? ?? '',
          sekolahNama:   data['sekolah_nama'] as String? ?? 'Garuda Akademi',
          gateUjianOpen: data['gate_ujian_open'] as bool? ?? false,
          mapelList:     mapelList,
        );
      }

      return MapelResponse.error(data['message'] as String? ?? 'Gagal mengambil data mapel.');
    } catch (e) {
      debugPrint('[AuthService.getMapelList] Error: $e');
      return MapelResponse.error('Terjadi kesalahan koneksi.');
    }
  }

  // ── Exam Status ───────────────────────────────────────

  /// Cek apakah pintu ujian saat ini terbuka untuk siswa ini.
  ///
  /// Digunakan oleh dashboard untuk menampilkan/menyembunyikan tombol
  /// "Ruang Asesmen" secara real-time tanpa memuat ulang seluruh mapel.
  ///
  /// Mengembalikan:
  /// - `true`  — ujian aktif (tampilkan tombol)
  /// - `false` — ujian tidak aktif (sembunyikan tombol)
  /// - `null`  — terjadi kesalahan jaringan (UI menampilkan skeleton)
  static Future<bool?> getExamStatus() async {
    try {
      final token = await getToken();
      if (token == null || token.isEmpty) return false;

      final response = await http.get(
        Uri.parse('${AppConfig.baseUrl}${AppConfig.examStatusPath}'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'X-App':         'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['is_active'] as bool? ?? false;
        }
      }
      return false;
    } catch (e) {
      debugPrint('[AuthService.getExamStatus] Error: $e');
      return null; // Sinyal error — UI tampilkan skeleton
    }
  }

  // ── Refresh User Data ─────────────────────────────────────

  /// Ambil data user terbaru dari server menggunakan token tersimpan.
  /// Berguna untuk memperbarui nama/foto setelah update profil.
  static Future<bool> refreshUserData() async {
    try {
      final token = await getToken();
      if (token == null) return false;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(GaraPrefKeys.namaLengkap,    data['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.namaSiswa,      data['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
          return true;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.refreshUserData] $e');
    }
    return false;
  }

  // ── Token Helper ──────────────────────────────────────────

  /// Ambil token Sanctum dari SharedPreferences.
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  /// Cek apakah sesi login masih tersimpan lokal.
  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final loggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;
    final token    = prefs.getString(GaraPrefKeys.authToken) ?? '';
    return loggedIn && token.isNotEmpty;
  }

  // ── Session Helpers ───────────────────────────────────────

  /// Simpan semua data sesi ke SharedPreferences setelah login berhasil.
  static Future<void> _saveSession(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool  (GaraPrefKeys.isLoggedIn,      true);
    await prefs.setString(GaraPrefKeys.authToken,       data['token']         as String? ?? '');
    await prefs.setString(GaraPrefKeys.userRole,        data['role']          as String? ?? 'siswa');
    await prefs.setString(GaraPrefKeys.namaLengkap,     data['nama']          as String? ?? '');
    await prefs.setString(GaraPrefKeys.namaSiswa,       data['nama']          as String? ?? '');
    await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
  }

  /// Mengambil data profil siswa mendalam (Fase 3)
  static Future<bool> getProfile() async {
    try {
      final token = await getToken();
      if (token == null) return false;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/profile'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final profile = data['data'] as Map<String, dynamic>;
          final prefs = await SharedPreferences.getInstance();
          
          await prefs.setString(GaraPrefKeys.namaSiswa,   profile['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.kelasSiswa,  profile['kelas'] as String? ?? '');
          
          // Simpan data tambahan Fase 3
          await prefs.setInt('student_points',            profile['poin'] as int? ?? 0);
          await prefs.setInt('student_attendance',        profile['absensi_persen'] as int? ?? 0);
          await prefs.setInt('student_pending_tasks',     profile['tugas_pending'] as int? ?? 0);
          
          return true;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.getProfile] Error: $e');
    }
    return false;
  }

  /// Mengambil data akun siswa lengkap:
  /// - NIS siswa
  /// - is_default_password (masih pakai password bawaan?)
  /// - nama_sekolah, tahun_ajaran, semester
  ///
  /// Endpoint: GET /api/mobile/account-detail
  /// Dipakai oleh AkunTab untuk menampilkan info yang setara web tentang-saya.
  ///
  /// Returns null jika terjadi error (UI tetap tampilkan data dari prefs).
  static Future<Map<String, dynamic>?> getStudentAccountDetail() async {
    try {
      final token = await getToken();
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/account-detail'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'X-App':         'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['data'] as Map<String, dynamic>?;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.getStudentAccountDetail] Error: $e');
    }
    return null;
  }

  /// Hapus semua data sesi dari SharedPreferences.
  /// Dipanggil saat logout atau token expired.
  static Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }
}
