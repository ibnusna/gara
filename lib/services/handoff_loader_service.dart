import 'package:flutter/foundation.dart';
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
      }
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

    // Fallback URL with ticket omitted (never leak token in URL)
    return '${AppConfig.baseUrl}$resolvedTarget';
  }
}
