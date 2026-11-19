import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/auth_models.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import 'secure_token_storage.dart';

class AuthProfileService {
  static Future<AuthProfileStatus> getProfileWithStatus() async {
    try {
      final token = await SecureTokenStorage.instance.getToken();
      if (token == null || token.isEmpty) {
        return AuthProfileStatus.sessionExpired;
      }

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/profile'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 401) {
        debugPrint('[AuthProfileService] 401 Unauthenticated.');
        return AuthProfileStatus.sessionExpired;
      }

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        return AuthProfileStatus.failure;
      }

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final profile = data['data'] as Map<String, dynamic>;
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(GaraPrefKeys.namaSiswa, profile['nama'] as String? ?? '');
          await prefs.setString(GaraPrefKeys.kelasSiswa, profile['kelas'] as String? ?? '');
          await prefs.setInt('student_points', profile['poin'] as int? ?? 0);
          await prefs.setInt('student_attendance', profile['absensi_persen'] as int? ?? 0);
          await prefs.setInt('student_pending_tasks', profile['tugas_pending'] as int? ?? 0);
          return AuthProfileStatus.success;
        }
      }
    } catch (e) {
      debugPrint('[AuthProfileService.getProfileWithStatus] Error: $e');
    }
    return AuthProfileStatus.failure;
  }

  static Future<bool> refreshUserData() async {
    try {
      final token = await SecureTokenStorage.instance.getToken();
      if (token == null) return false;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 10));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) return false;

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(GaraPrefKeys.namaLengkap, data['nama'] as String? ?? '');
          await prefs.setString(GaraPrefKeys.namaSiswa, data['nama'] as String? ?? '');
          await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
          return true;
        }
      }
    } catch (e) {
      debugPrint('[AuthProfileService.refreshUserData] $e');
    }
    return false;
  }

  static Future<Map<String, dynamic>?> getStudentAccountDetail() async {
    try {
      final token = await SecureTokenStorage.instance.getToken();
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/account-detail'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 10));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) return null;

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['data'] as Map<String, dynamic>?;
        }
      }
    } catch (e) {
      debugPrint('[AuthProfileService.getStudentAccountDetail] Error: $e');
    }
    return null;
  }

  static Future<bool?> getExamStatus() async {
    try {
      final token = await SecureTokenStorage.instance.getToken();
      if (token == null || token.isEmpty) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/exam-status'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['is_active'] == true;
        }
      }
    } catch (e) {
      debugPrint('[AuthProfileService.getExamStatus] $e');
    }
    return null;
  }
}
