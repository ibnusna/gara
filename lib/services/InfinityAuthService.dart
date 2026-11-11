// GARA Mobile — InfinityFree AES JS Bypass Authentication Service
import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/mapel_model.dart';
import '../models/infinity_auth_result.dart';
import 'infinity_bypass_exception.dart';
import 'InfinityBypassEngine.dart';
import 'InfinityApiClient.dart';

export 'infinity_bypass_exception.dart';
export '../models/infinity_auth_result.dart';

class InfinityAuthService {
  InfinityAuthService._();

  static const Duration _requestTimeout = Duration(seconds: 15);
  static String get _loginUrl => '${AppConfig.apiMobileUrl}/login';

  static Future<InfinityAuthResult> login({
    required String identifier,
    required String password,
  }) async {
    final trimmedId = identifier.trim();
    if (trimmedId.isEmpty) return InfinityAuthResult.error('Username/NIS tidak boleh kosong.');
    if (password.isEmpty) return InfinityAuthResult.error('Password tidak boleh kosong.');

    try {
      String rawText = '';
      String testCookie = '';
      final prefs = await SharedPreferences.getInstance();
      testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      debugPrint('[InfinityAuth] Mengirim HTTP POST langsung ke: $_loginUrl');
      final response = await http.post(
        Uri.parse(_loginUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
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
      rawText = response.body.trim();

      if (rawText.contains('javascript') && (rawText.contains('document.cookie') || rawText.contains('/aes.js'))) {
        debugPrint('[InfinityAuth] Terdeteksi JS Challenge InfinityFree. Memulai loginViaHeadlessWebView');
        rawText = await InfinityBypassEngine.loginViaHeadlessWebView(
          identifier: trimmedId,
          password: password,
        );
        testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
        if (rawText.contains('javascript') && (rawText.contains('document.cookie') || rawText.contains('/aes.js'))) {
          await prefs.remove(GaraPrefKeys.bypassTestCookie);
          await prefs.remove(GaraPrefKeys.bypassCookieTimestamp);
          return InfinityAuthResult.error('__RETRY_BYPASS__');
        }
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

    if (testCookie.isNotEmpty) {
      try {
        final baseUrl = AppConfig.baseUrl;
        await CookieManager.instance().setCookie(
          url: WebUri(baseUrl),
          name: '__test',
          value: testCookie,
          isSecure: baseUrl.startsWith('https'),
        );
      } catch (e) {
        debugPrint('[InfinityAuth] Warning setCookie: $e');
      }
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(GaraPrefKeys.isLoggedIn, true);
    await prefs.setString(GaraPrefKeys.authToken, data['token'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.userRole, data['role'] as String? ?? 'siswa');
    await prefs.setString(GaraPrefKeys.namaLengkap, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.namaSiswa, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.bypassTestCookie, testCookie);
    await prefs.setInt(GaraPrefKeys.bypassCookieTimestamp, DateTime.now().millisecondsSinceEpoch);

    final mustChange = data['must_change_password'] as bool? ?? false;
    final isForced = data['is_forced_change'] as bool? ?? false;

    return InfinityAuthResult(
      success: true,
      token: data['token'] as String?,
      role: data['role'] as String?,
      nama: data['nama'] as String?,
      username: data['username'] as String?,
      profilePhotoUrl: data['profile_photo'] as String?,
      mustChangePassword: mustChange,
      isForcedChange: isForced,
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
      await CookieManager.instance().deleteCookies(url: WebUri(AppConfig.baseUrl));
    } catch (_) {}
  }

  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false) &&
        (prefs.getString(GaraPrefKeys.authToken)?.isNotEmpty ?? false);
  }

  static Future<String?> getToken() async =>
      (await SharedPreferences.getInstance()).getString(GaraPrefKeys.authToken);
}
