// ════════════════════════════════════════════════════════════════════════════
// lib/services/InfinityAuthService.dart
//
// GARA Mobile — InfinityFree AES JS Bypass Authentication Service
//
// MASALAH: InfinityFree (rf.gd) memblokir request HTTP mentah menggunakan
//          AES JavaScript Challenge.
//
// SOLUSI:
// 1. Panggil InfinityBypassEngine.getValidCookie() untuk menyelesaikan
//    JS challenge secara asinkron di HeadlessWebView dan mengambil cookie '__test'.
// 2. Kirim POST request login menggunakan client HTTP standar (http package)
//    dengan menyertakan cookie '__test' di header.
// 3. Ini JAUH LEBIH STABIL & CEPAT dibandingkan melakukan POST langsung lewat WebView.
// ════════════════════════════════════════════════════════════════════════════

import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/mapel_model.dart';
import 'infinity_bypass_exception.dart';
import 'InfinityBypassEngine.dart';
import 'InfinityApiClient.dart';

export 'infinity_bypass_exception.dart';

class InfinityAuthResult {
  final bool success;
  final String? token;
  final String? role;
  final String? nama;
  final String? username;
  final String? profilePhotoUrl;
  final String? errorMessage;
  final bool isMaintenance;

  const InfinityAuthResult({
    required this.success,
    this.token,
    this.role,
    this.nama,
    this.username,
    this.profilePhotoUrl,
    this.errorMessage,
    this.isMaintenance = false,
  });

  factory InfinityAuthResult.error(String msg, {bool maintenance = false}) =>
      InfinityAuthResult(
        success: false,
        errorMessage: msg,
        isMaintenance: maintenance,
      );
}

class InfinityAuthService {
  InfinityAuthService._();

  static const Duration _requestTimeout = Duration(seconds: 15);
  static String get _loginUrl => '${AppConfig.apiMobileUrl}/login';

  // ──────────────────────────────────────────────────────────────────────────
  // login() — Bypass InfinityFree & Autentikasi User
  // ──────────────────────────────────────────────────────────────────────────
  static Future<InfinityAuthResult> login({
    required String identifier,
    required String password,
  }) async {
    final trimmedId = identifier.trim();

    if (trimmedId.isEmpty) {
      return InfinityAuthResult.error('Username/NIS tidak boleh kosong.');
    }
    if (password.isEmpty) {
      return InfinityAuthResult.error('Password tidak boleh kosong.');
    }

    try {
      // ── Step 2: Dapatkan cookie __test yang valid dari SharedPreferences ──────
      // (Cookie ini sudah didapatkan oleh InfinityBypassDialog di UI)
      final prefs = await SharedPreferences.getInstance();
      String testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      
      if (testCookie.isEmpty) {
        return InfinityAuthResult.error('Sesi keamanan belum siap. Silakan ulangi login.');
      }

      debugPrint('[InfinityAuth] Menggunakan cookie __test: $testCookie');

      // ── Step 3: Kirim POST request menggunakan HTTP client biasa ──────────
      final response = await http.post(
        Uri.parse(_loginUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          // User-Agent HARUS SAMA PERSIS dengan WebView agar InfinityFree AES tidak memblokir cookie
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          // Suntik cookie bypass InfinityFree ke header HTTP request
          'Cookie': '__test=$testCookie',
        },
        body: jsonEncode({
          'identifier': trimmedId,
          'password': password,
        }),
      ).timeout(_requestTimeout);

      if (response.statusCode == 503) {
        return InfinityAuthResult.error(
          'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
          maintenance: true,
        );
      }

      final rawText = response.body.trim();

      // Cek apakah response diblokir oleh InfinityFree (mengembalikan HTML challenge)
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        debugPrint('[InfinityAuth] Cookie __test ditolak/expired oleh server InfinityFree.');
        
        // Bersihkan cookie kadaluarsa
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove(GaraPrefKeys.bypassTestCookie);
        await prefs.remove(GaraPrefKeys.bypassCookieTimestamp);
        
        // Kirim kode rahasia agar UI me-restart BypassDialog secara otomatis tanpa menyuruh user nge-klik
        return InfinityAuthResult.error('__RETRY_BYPASS__');
      }

      return _handleLoginResponse(rawText, testCookie);
    } catch (e) {
      debugPrint('[InfinityAuth] Error: $e');
      return InfinityAuthResult.error('Koneksi gagal: $e');
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // _handleLoginResponse() — Memproses response dari server Laravel
  // ──────────────────────────────────────────────────────────────────────────
  static Future<InfinityAuthResult> _handleLoginResponse(String rawText, String testCookie) async {
    Map<String, dynamic> data;
    try {
      data = jsonDecode(rawText) as Map<String, dynamic>;
    } catch (_) {
      // Jika masih bukan JSON, throw exception khusus
      throw InfinityFreeBypassException(
        message: 'Respon server tidak valid (Bukan JSON).',
        rawHtmlSnippet: rawText,
      );
    }

    if (data['status'] == 'maintenance') {
      return InfinityAuthResult.error(
        'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
        maintenance: true,
      );
    }

    if (data['status'] != 'success') {
      return InfinityAuthResult.error(
        data['message'] as String? ?? 'Login gagal.',
      );
    }

    // Ambil laravel_session dari cookie response header jika ada,
    // atau biarkan CookieManager WebView menangkapnya nanti via Handoff.
    // Kita juga sinkronkan cookies ke CookieManager global agar WebView
    // langsung memiliki cookie ini saat dibuka.
    final targetUrl = WebUri('https://garaedu.rf.gd');
    await CookieManager.instance().setCookie(
      url: targetUrl,
      name: '__test',
      value: testCookie,
      domain: 'garaedu.rf.gd',
      isSecure: true,
    );

    // Simpan data sesi ke SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(GaraPrefKeys.isLoggedIn, true);
    await prefs.setString(GaraPrefKeys.authToken, data['token'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.userRole, data['role'] as String? ?? 'siswa');
    await prefs.setString(GaraPrefKeys.namaLengkap, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.namaSiswa, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');

    // Simpan cookie __test agar API Client lain bisa langsung pakai
    await prefs.setString(GaraPrefKeys.bypassTestCookie, testCookie);
    await prefs.setInt(GaraPrefKeys.bypassCookieTimestamp, DateTime.now().millisecondsSinceEpoch);

    return InfinityAuthResult(
      success: true,
      token: data['token'] as String?,
      role: data['role'] as String?,
      nama: data['nama'] as String?,
      username: data['username'] as String?,
      profilePhotoUrl: data['profile_photo'] as String?,
    );
  }

  // Wrapper backward compatibility untuk mapel
  static Future<MapelResponse> getMapelList() async {
    try {
      final client = InfinityApiClient();
      final data = await client.getJson('/api/mobile/mapel');

      if (data['status'] == 'success') {
        final List mapelJson = data['mapel_list'] as List;
        final mapelList = mapelJson.map((j) => MapelModel.fromJson(j)).toList();

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(GaraPrefKeys.namaSiswa, data['nama_siswa'] as String? ?? '');
        await prefs.setString(GaraPrefKeys.kelasSiswa, data['kelas_siswa'] as String? ?? '');

        return MapelResponse(
          success: true,
          namaSiswa: data['nama_siswa'] as String? ?? '',
          kelasSiswa: data['kelas_siswa'] as String? ?? '',
          sekolahNama: data['sekolah_nama'] as String? ?? 'Garuda Akademi',
          gateUjianOpen: data['gate_ujian_open'] as bool? ?? false,
          mapelList: mapelList,
        );
      }

      return MapelResponse.error(data['message'] as String? ?? 'Gagal mengambil data mapel.');
    } catch (e) {
      debugPrint('[InfinityAuthService.getMapelList] Error: $e');
      return MapelResponse.error('Terjadi kesalahan koneksi: $e');
    }
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    try {
      await CookieManager.instance().deleteCookies(url: WebUri('https://garaedu.rf.gd'));
    } catch (_) {}
  }

  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final loggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;
    final token = prefs.getString(GaraPrefKeys.authToken) ?? '';
    return loggedIn && token.isNotEmpty;
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }
}
