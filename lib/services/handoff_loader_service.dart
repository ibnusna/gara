import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import 'auth_service.dart';

class HandoffLoaderService {
  HandoffLoaderService._();

  static Future<String> resolveHandoffUrl({
    String? rawUrl,
    String? targetPath,
    String? mapelId,
  }) async {
    String resolvedTarget = targetPath ?? '/student/dashboard';
    String? resolvedMapelId = mapelId;
    String? fallbackToken;

    if (rawUrl != null && rawUrl.isNotEmpty) {
      final uri = Uri.tryParse(rawUrl);
      if (uri != null) {
        if (uri.queryParameters.containsKey('target')) {
          resolvedTarget = uri.queryParameters['target']!;
        } else if (uri.path.isNotEmpty && uri.path != '/auth/webview-handoff') {
          resolvedTarget = uri.path;
        }
        if (uri.queryParameters.containsKey('mapel_id')) {
          resolvedMapelId = uri.queryParameters['mapel_id'];
        }
        if (uri.queryParameters.containsKey('token')) {
          fallbackToken = uri.queryParameters['token'];
        }
      }
    }

    fallbackToken ??= await AuthService.getToken();
    if (fallbackToken == null || fallbackToken.isEmpty) {
      final prefs = await SharedPreferences.getInstance();
      fallbackToken = prefs.getString(GaraPrefKeys.authToken);
    }

    try {
      final ticket = await AuthService.requestHandoffTicket(
        targetPath: resolvedTarget,
        mapelId: resolvedMapelId,
      );
      if (ticket != null && ticket.isNotEmpty) {
        return AppConfig.getHandoffTicketUrl(ticket);
      }
    } catch (e) {
      debugPrint('[HandoffLoaderService] Failed to acquire ticket: $e');
    }

    if (fallbackToken != null && fallbackToken.isNotEmpty) {
      return AppConfig.getHandoffUrl(
        token: fallbackToken,
        targetPath: resolvedTarget,
        mapelId: resolvedMapelId,
      );
    }

    return '${AppConfig.baseUrl}$resolvedTarget';
  }
}
