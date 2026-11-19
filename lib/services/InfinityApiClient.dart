import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import 'InfinityBypassEngine.dart';
import 'infinity_bypass_exception.dart';
import 'secure_token_storage.dart';
import 'server_fallback_service.dart';

class InfinityApiClient {
  static const Duration _requestTimeout = Duration(seconds: 15);
  static const String _userAgent =
      'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

  static bool _isChallengeHtml(String text) {
    final lower = text.toLowerCase().trim();
    if (lower.isEmpty) return true;
    if (lower.startsWith('<!doctype') ||
        lower.startsWith('<html') ||
        lower.startsWith('<head') ||
        lower.startsWith('<body')) {
      return true;
    }
    if (lower.contains('/aes.js') ||
        lower.contains('javascript') ||
        lower.contains('slowaes') ||
        lower.contains('document.cookie')) {
      return true;
    }
    if (!lower.startsWith('{') && !lower.startsWith('[')) {
      return true;
    }
    return false;
  }

  static bool _isSuspendedHtml(String text) {
    final lower = text.toLowerCase().trim();
    return lower.contains('suspended-domain.net') ||
        lower.contains('domain suspended') ||
        lower.contains('domain has been suspended');
  }

  Future<Map<String, dynamic>> getJson(String endpoint) async {
    return _executeWithRetry((cookie) async {
      final fullUrl = endpoint.startsWith('http')
          ? endpoint
          : '${AppConfig.baseUrl}$endpoint';

      final authToken = await SecureTokenStorage.instance.getToken() ?? '';
      final response = await http.get(
        Uri.parse(fullUrl),
        headers: {
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          if (cookie.isNotEmpty) 'Cookie': '__test=$cookie',
        },
      ).timeout(_requestTimeout);

      return response.body.trim();
    }, endpoint);
  }

  Future<Map<String, dynamic>> postJson(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    return _executeWithRetry((cookie) async {
      final fullUrl = endpoint.startsWith('http')
          ? endpoint
          : '${AppConfig.baseUrl}$endpoint';

      final authToken = await SecureTokenStorage.instance.getToken() ?? '';
      final response = await http.post(
        Uri.parse(fullUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App': 'GARA_MOBILE',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': _userAgent,
          if (authToken.isNotEmpty) 'Authorization': 'Bearer $authToken',
          if (cookie.isNotEmpty) 'Cookie': '__test=$cookie',
        },
        body: jsonEncode(body),
      ).timeout(_requestTimeout);

      return response.body.trim();
    }, endpoint);
  }

  Future<Map<String, dynamic>> _executeWithRetry(
    Future<String> Function(String cookie) requestFn,
    String url,
  ) async {
    final prefs = await SharedPreferences.getInstance();
    String testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

    try {
      final rawText = await requestFn(testCookie);
      if (_isSuspendedHtml(rawText)) {
        throw const InfinityFreeSuspendedException();
      }
      if (!_isChallengeHtml(rawText)) {
        return jsonDecode(rawText) as Map<String, dynamic>;
      }
      debugPrint('[InfinityApiClient] Attempt 1 detected challenge response.');
    } catch (e) {
      if (e is InfinityFreeSuspendedException) {
        final success = await ServerFallbackService.activateFallback();
        if (success) {
          final retryText = await requestFn(testCookie);
          if (!_isChallengeHtml(retryText)) {
            return jsonDecode(retryText) as Map<String, dynamic>;
          }
        }
        rethrow;
      }
      if (!AppConfig.enableLegacyInfinityBypass) {
        rethrow;
      }
      debugPrint('[InfinityApiClient] Attempt 1 error: $e');
    }

    if (!AppConfig.enableLegacyInfinityBypass) {
      throw const FormatException('Expected JSON response from clean production host.');
    }

    testCookie = await InfinityBypassEngine.getValidCookie(forceRefresh: true);
    final rawText2 = await requestFn(testCookie);
    if (!_isChallengeHtml(rawText2)) {
      return jsonDecode(rawText2) as Map<String, dynamic>;
    }

    throw InfinityFreeBypassException(
      message: 'Failed to bypass after cookie refresh.',
      rawHtmlSnippet: rawText2,
    );
  }
}
