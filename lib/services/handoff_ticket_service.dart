import 'package:flutter/foundation.dart';
import '../utils/app_config.dart';
import 'auth_service.dart';

/// Service to resolve, exchange, and sanitize handoff URLs to use secure
/// one-time 15-second handoff tickets instead of plaintext query tokens.
/// Conforms to <= 200 lines per file policy.
class HandoffTicketService {
  HandoffTicketService._();

  /// Resolves an incoming URL. If it's a handoff URL with legacy tokens or
  /// a raw target path, it requests a one-time ticket and returns the safe URL.
  static Future<String> resolveHandoffUrl(String rawUrl) async {
    try {
      final uri = Uri.tryParse(rawUrl);
      if (uri == null) return rawUrl;

      // Case 1: Already has ticket query parameter
      if (uri.queryParameters.containsKey('ticket')) {
        return rawUrl;
      }

      // Case 2: Standard webview handoff URL
      if (uri.path.endsWith('/auth/webview-handoff') ||
          rawUrl.contains('/auth/webview-handoff')) {
        final target = uri.queryParameters['target'] ?? AppConfig.studentDashboardPath;
        final mapelId = uri.queryParameters['mapel_id'];

        final ticket = await AuthService.requestHandoffTicket(
          targetPath: target,
          mapelId: mapelId,
        );

        if (ticket != null && ticket.isNotEmpty) {
          return '${AppConfig.baseUrl}/auth/webview-handoff?ticket=$ticket';
        }
      }

      // Case 3: Plain target path passed directly (e.g., '/student/materi')
      if (rawUrl.startsWith('/') && !rawUrl.startsWith('//')) {
        final ticket = await AuthService.requestHandoffTicket(
          targetPath: rawUrl,
        );
        if (ticket != null && ticket.isNotEmpty) {
          return '${AppConfig.baseUrl}/auth/webview-handoff?ticket=$ticket';
        }
      }
    } catch (e) {
      debugPrint('[HandoffTicketService.resolveHandoffUrl] Error: $e');
    }
    return rawUrl;
  }

  /// Builds a secure ticket handoff URL given a ticket.
  static String buildTicketUrl(String ticket) {
    return '${AppConfig.baseUrl}/auth/webview-handoff?ticket=$ticket';
  }
}
