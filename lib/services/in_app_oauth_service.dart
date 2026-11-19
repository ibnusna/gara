import 'package:flutter/material.dart';
import 'package:flutter_custom_tabs/flutter_custom_tabs.dart';
import '../utils/app_constants.dart';

/// Handles in-app OAuth authentication flows (such as Google Workspace)
/// using Chrome Custom Tabs (Android) and SFSafariViewController (iOS)
/// to prevent launching external Chrome standalone apps.
/// Conforms to <= 200 lines per file policy.
class InAppOAuthService {
  InAppOAuthService._();

  /// Launches Google Workspace OAuth URL in an in-app browser tab.
  static Future<void> launchGoogleWorkspaceAuth({
    required String authUrl,
    required BuildContext context,
  }) async {
    try {
      const primaryColor = GaraColors.primary;

      await launchUrl(
        Uri.parse(authUrl),
        customTabsOptions: CustomTabsOptions(
          colorSchemes: CustomTabsColorSchemes.defaults(
            toolbarColor: primaryColor,
          ),
          shareState: CustomTabsShareState.off,
          urlBarHidingEnabled: true,
          showTitle: true,
          closeButton: CustomTabsCloseButton(
            icon: CustomTabsCloseButtonIcons.back,
          ),
        ),
        safariVCOptions: const SafariViewControllerOptions(
          preferredBarTintColor: primaryColor,
          preferredControlTintColor: Colors.white,
          barCollapsingEnabled: true,
          entersReaderIfAvailable: false,
          dismissButtonStyle: SafariViewControllerDismissButtonStyle.close,
        ),
      );
    } catch (e) {
      debugPrint('[InAppOAuthService.launchGoogleWorkspaceAuth] Error: $e');
    }
  }

  /// Determines if a URL belongs to Google OAuth redirect / callback.
  static bool isGoogleOAuthUrl(String url) {
    final lower = url.toLowerCase();
    return lower.contains('accounts.google.com') ||
        lower.contains('/auth/google') ||
        lower.contains('/google/redirect') ||
        lower.contains('/google/callback');
  }
}
