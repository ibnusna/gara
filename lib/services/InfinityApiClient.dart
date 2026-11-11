// ════════════════════════════════════════════════════════════════════════════
// lib/services/InfinityApiClient.dart
//
// GARA Mobile — InfinityFree Generic HTTP API Client dengan Auto-Retry Bypass
//
// Deskripsi:
// Menggunakan HTTP request (http package) super cepat dengan cookie injection.
// Jika request mengembalikan HTML challenge (session/cookie expired),
// client akan OTOMATIS melakukan silent refresh cookie via InfinityBypassEngine
// dan mengulangi request (retry) secara seamless tanpa error!
// ════════════════════════════════════════════════════════════════════════════

import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import 'InfinityBypassEngine.dart';
import 'infinity_bypass_exception.dart';
import 'server_fallback_service.dart';

class InfinityApiClient {
  static const Duration _requestTimeout = Duration(seconds: 15);
  static const String _userAgent =
      'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

  /// Mendeteksi apakah response dari server berupa HTML Challenge InfinityFree (bukan JSON API)
  static bool _isChallengeHtml(String text) {
    final lower = text.toLowerCase().trim();
    if (lower.isEmpty) return true;
    if (lower.startsWith('<!doctype') || lower.startsWith('<html') || lower.startsWith('<head') || lower.startsWith('<body')) {
      return true;
    }
    if (lower.contains('/aes.js') || lower.contains('javascript') || lower.contains('slowaes') || lower.contains('document.cookie')) {
      return true;
    }
    // Jika tidak diawali { atau [, pasti bukan JSON
    if (!lower.startsWith('{') && !lower.startsWith('[')) {
      return true;
    }
    return false;
  }

  /// Mendeteksi apakah response dari server adalah halaman Suspend dari InfinityFree
  static bool _isSuspendedHtml(String text) {
    final lower = text.toLowerCase().trim();
    if (lower.contains('suspended-domain.net') || 
        lower.contains('domain suspended') || 
        lower.contains('domain has been suspended')) {
      return true;
    }
    return false;
  }

  /// HTTP GET Request dengan cookie injection & auto silent-retry
  Future<Map<String, dynamic>> getJson(String endpoint) async {
    return _executeWithRetry((cookie) async {
      final fullUrl = endpoint.startsWith('http')
          ? endpoint
          : '${AppConfig.baseUrl}$endpoint';

      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString(GaraPrefKeys.authToken) ?? '';

      debugPrint('[InfinityApiClient] GET: $fullUrl (Cookie: __test=$cookie)');

      final response = await http.get(
        Uri.parse(fullUrl),
        headers: {
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          'Cookie': '__test=$cookie',
        },
      ).timeout(_requestTimeout);

      return response.body.trim();
    }, endpoint);
  }

  /// HTTP POST Request dengan cookie injection & auto silent-retry
  Future<Map<String, dynamic>> postJson(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    return _executeWithRetry((cookie) async {
      final fullUrl = endpoint.startsWith('http')
          ? endpoint
          : '${AppConfig.baseUrl}$endpoint';

      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString(GaraPrefKeys.authToken) ?? '';

      debugPrint('[InfinityApiClient] POST: $fullUrl (Cookie: __test=$cookie)');

      final response = await http.post(
        Uri.parse(fullUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          'Cookie': '__test=$cookie',
        },
        body: jsonEncode(body),
      ).timeout(_requestTimeout);

      return response.body.trim();
    }, endpoint);
  }

  /// Eksekusi HTTP request dengan penanganan auto-retry jika cookie expired/challenge muncul
  Future<Map<String, dynamic>> _executeWithRetry(
    Future<String> Function(String cookie) requestFn,
    String url,
  ) async {
    final prefs = await SharedPreferences.getInstance();
    String testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

    // Attempt 1: Coba jalankan HTTP Request langsung dengan cookie tersimpan
    try {
      final rawText = await requestFn(testCookie);

      if (_isSuspendedHtml(rawText)) {
        throw const InfinityFreeSuspendedException();
      }

      if (!_isChallengeHtml(rawText)) {
        return jsonDecode(rawText) as Map<String, dynamic>;
      }
      debugPrint('[InfinityApiClient] Attempt 1 terdeteksi Challenge HTML / Cookie Expired.');
    } catch (e) {
      if (e is InfinityFreeSuspendedException) {
        debugPrint('[InfinityApiClient] MENDETEKSI DOMAIN SUSPEND! Mencoba fallback ke server alternatif...');
        final success = await ServerFallbackService.activateFallback();
        if (success) {
          debugPrint('[InfinityApiClient] Fallback aktif. Mengulangi request dengan server baru...');
          // Retry dengan URL yang baru ter-resolve berkat overrideBaseUrl
          final retryText = await requestFn(testCookie);
          if (!_isChallengeHtml(retryText)) {
            return jsonDecode(retryText) as Map<String, dynamic>;
          }
        }
        rethrow;
      }
      debugPrint('[InfinityApiClient] Attempt 1 Gagal: $e');
    }

    // Attempt 2: Terjadi Challenge HTML / Cookie Expired → Panggil Silent Bypass Refresh!
    debugPrint('[InfinityApiClient] Memperbarui cookie __test secara silent di background...');
    try {
      testCookie = await InfinityBypassEngine.getValidCookie(forceRefresh: true);
      debugPrint('[InfinityApiClient] Cookie baru didapatkan! Mengulangi HTTP request...');
      
      final rawText2 = await requestFn(testCookie);

      if (!_isChallengeHtml(rawText2)) {
        return jsonDecode(rawText2) as Map<String, dynamic>;
      }

      throw InfinityFreeBypassException(
        message: 'Gagal bypass InfinityFree setelah refresh cookie.',
        rawHtmlSnippet: rawText2,
      );
    } catch (e) {
      debugPrint('[InfinityApiClient] Attempt 2 Error: $e');
      rethrow;
    }
  }
}
