// ════════════════════════════════════════════════════════════════════════════
// lib/services/InfinityApiClient.dart
//
// GARA Mobile — InfinityFree Generic HTTP API Client
//
// Deskripsi:
// Mengganti WebView yang lambat dengan HTTP request (http package) biasa.
// Cookie bypass '__test' diambil dari InfinityBypassEngine dan disuntikkan
// ke setiap header request.
// Jika request mengembalikan HTML challenge (session/cookie expire),
// client akan otomatis me-refresh cookie (warm-up ulang) dan mengulangi request.
// ════════════════════════════════════════════════════════════════════════════

import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import 'infinity_bypass_exception.dart';

class InfinityApiClient {
  static const Duration _requestTimeout = Duration(seconds: 15);
  static const String _userAgent = 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

  // ──────────────────────────────────────────────────────────────────────────
  // getJson() — HTTP GET Request dengan cookie injection
  // ──────────────────────────────────────────────────────────────────────────
  Future<Map<String, dynamic>> getJson(String endpoint) async {
    final fullUrl = endpoint.startsWith('http')
        ? endpoint
        : '${AppConfig.baseUrl}$endpoint';

    debugPrint('[InfinityApiClient] GET: $fullUrl');

    try {
      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      final authToken = prefs.getString(GaraPrefKeys.authToken) ?? '';

      final response = await http.get(
        Uri.parse(fullUrl),
        headers: {
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(_requestTimeout);

      final rawText = response.body.trim();

      // Cek apakah terblokir/challenge muncul (karena cookie expired/invalid)
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        debugPrint('[InfinityApiClient] Cookie __test ditolak/expired. Melempar error REFRESH.');
        throw InfinityFreeBypassException(
          message: '__RETRY_BYPASS__',
          rawHtmlSnippet: rawText,
        );
      }

      return _parseResponse(rawText, fullUrl);
    } catch (e) {
      debugPrint('[InfinityApiClient] GET Error: $e');
      rethrow;
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // postJson() — HTTP POST Request dengan cookie injection
  // ──────────────────────────────────────────────────────────────────────────
  Future<Map<String, dynamic>> postJson(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    final fullUrl = endpoint.startsWith('http')
        ? endpoint
        : '${AppConfig.baseUrl}$endpoint';

    debugPrint('[InfinityApiClient] POST: $fullUrl');

    try {
      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      final authToken = prefs.getString(GaraPrefKeys.authToken) ?? '';

      final response = await http.post(
        Uri.parse(fullUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          'Cookie': '__test=$testCookie',
        },
        body: jsonEncode(body),
      ).timeout(_requestTimeout);

      final rawText = response.body.trim();

      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        debugPrint('[InfinityApiClient] Cookie __test expired pada POST. Melempar error REFRESH.');
        throw InfinityFreeBypassException(
          message: '__RETRY_BYPASS__',
          rawHtmlSnippet: rawText,
        );
      }

      return _parseResponse(rawText, fullUrl);
    } catch (e) {
      debugPrint('[InfinityApiClient] POST Error: $e');
      rethrow;
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // _parseResponse() — Parser JSON response
  // ──────────────────────────────────────────────────────────────────────────
  Map<String, dynamic> _parseResponse(String rawText, String url) {
    try {
      return jsonDecode(rawText) as Map<String, dynamic>;
    } catch (_) {
      throw InfinityFreeBypassException(
        message: 'Gagal parse JSON response dari: $url',
        rawHtmlSnippet: rawText,
      );
    }
  }
}
