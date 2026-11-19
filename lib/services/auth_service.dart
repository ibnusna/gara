import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/auth_models.dart';
import '../models/mapel_model.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import 'auth_mapel_service.dart';
import 'auth_profile_service.dart';
import 'secure_token_storage.dart';

export '../models/auth_models.dart';

class AuthService {
  AuthService._();

  static Future<AuthResult> login({
    required String identifier,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.apiMobileUrl}/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
        body: jsonEncode({
          'identifier': identifier.trim(),
          'password': password,
        }),
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 503) {
        return const AuthResult(
          success: false,
          isMaintenance: true,
          errorMessage: 'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
        );
      }
      if (response.statusCode >= 500) {
        return const AuthResult(
          success: false,
          errorMessage: 'Server sedang mengalami gangguan (500). Hubungi admin.',
        );
      }

      final data = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode == 200 && data['status'] == 'success') {
        final token = data['token'] as String?;
        if (token != null && token.isNotEmpty) {
          await SecureTokenStorage.instance.saveToken(token);
        }
        await _saveUserPreferences(data);

        return AuthResult(
          success: true,
          token: token,
          role: data['role'] as String?,
          nama: data['nama'] as String?,
          username: data['username'] as String?,
          profilePhotoUrl: data['profile_photo'] as String?,
        );
      }

      return AuthResult(
        success: false,
        errorMessage: data['message'] as String? ?? 'Login gagal.',
      );
    } on SocketException {
      return const AuthResult(
        success: false,
        errorMessage: 'Tidak ada koneksi internet. Periksa jaringan Anda.',
      );
    } catch (e) {
      debugPrint('[AuthService.login] Error: $e');
      return AuthResult(success: false, errorMessage: 'Debug Error: $e');
    }
  }

  static Future<void> logout() async {
    try {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        await http.post(
          Uri.parse('${AppConfig.apiMobileUrl}/logout'),
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
            'X-App': 'GARA_MOBILE',
          },
        ).timeout(const Duration(seconds: 8));
      }
    } catch (e) {
      debugPrint('[AuthService.logout] Server revoke gagal: $e');
    } finally {
      await clearSession();
    }
  }

  static Future<String?> requestHandoffTicket({
    required String targetPath,
    String? mapelId,
  }) async {
    try {
      final token = await getToken();
      if (token == null || token.isEmpty) return null;

      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}/api/mobile/handoff-ticket'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
        body: jsonEncode({
          'target': targetPath,
          if (mapelId != null) 'mapel_id': int.tryParse(mapelId),
        }),
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['ticket'] as String?;
        }
      }
      debugPrint('[AuthService.requestHandoffTicket] Code: ${response.statusCode}');
      return null;
    } catch (e) {
      debugPrint('[AuthService.requestHandoffTicket] Error: $e');
      return null;
    }
  }

  static Future<String?> getToken() => SecureTokenStorage.instance.getToken();

  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final loggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;
    final token = await getToken();
    return loggedIn && token != null && token.isNotEmpty;
  }

  static Future<void> migrateLegacyTokenIfNeeded() =>
      SecureTokenStorage.migrateLegacyTokenIfNeeded();

  static Future<void> _saveUserPreferences(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(GaraPrefKeys.isLoggedIn, true);
    await prefs.remove(GaraPrefKeys.authToken); // Zero plaintext token in SharedPreferences
    await prefs.setString(GaraPrefKeys.userRole, data['role'] as String? ?? 'siswa');
    await prefs.setString(GaraPrefKeys.namaLengkap, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.namaSiswa, data['nama'] as String? ?? '');
    await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
  }

  static Future<void> clearSession() async {
    await SecureTokenStorage.instance.clearToken();
    final prefs = await SharedPreferences.getInstance();
    final keys = [
      GaraPrefKeys.isLoggedIn,
      GaraPrefKeys.userRole,
      GaraPrefKeys.namaSiswa,
      GaraPrefKeys.kelasSiswa,
      GaraPrefKeys.selectedMapel,
      GaraPrefKeys.selectedMapelId,
      GaraPrefKeys.authToken,
      GaraPrefKeys.namaLengkap,
      GaraPrefKeys.profilePhotoUrl,
      'student_points',
      'student_attendance',
      'student_pending_tasks',
    ];
    for (final k in keys) {
      await prefs.remove(k);
    }
  }

  static Future<MapelResponse> getMapelList() => AuthMapelService.getMapelList();
  static Future<MapelResponse?> getCachedMapelList() => AuthMapelService.getCachedMapelList();
  static Future<AuthProfileStatus> getProfileWithStatus() => AuthProfileService.getProfileWithStatus();
  static Future<bool> getProfile() async => (await getProfileWithStatus()) == AuthProfileStatus.success;
  static Future<bool> refreshUserData() => AuthProfileService.refreshUserData();
  static Future<Map<String, dynamic>?> getStudentAccountDetail() => AuthProfileService.getStudentAccountDetail();
  static Future<bool?> getExamStatus() => AuthProfileService.getExamStatus();
}
