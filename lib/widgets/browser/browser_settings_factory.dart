import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/app_config.dart';
import '../../utils/app_constants.dart';

class BrowserSettingsFactory {
  BrowserSettingsFactory._();

  static InAppWebViewSettings createDefaultSettings() {
    return InAppWebViewSettings(
      useShouldOverrideUrlLoading: true,
      mediaPlaybackRequiresUserGesture: false,
      javaScriptEnabled: true,
      domStorageEnabled: true,
      databaseEnabled: true,
      useHybridComposition: true,
      mixedContentMode: MixedContentMode.MIXED_CONTENT_ALWAYS_ALLOW,
      transparentBackground: true,
      supportZoom: true,
      javaScriptCanOpenWindowsAutomatically: true,
      supportMultipleWindows: true,
      useOnDownloadStart: true,
      userAgent: "GARA_OFFICIAL_APP",
    );
  }

  static Future<void> injectBypassCookie() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      if (testCookie.isNotEmpty) {
        final baseUrl = AppConfig.baseUrl;
        final host = Uri.tryParse(baseUrl)?.host;
        await CookieManager.instance().setCookie(
          url: WebUri(baseUrl),
          name: '__test',
          value: testCookie,
          domain: (host != null && host.isNotEmpty) ? host : null,
          isSecure: baseUrl.startsWith('https'),
          isHttpOnly: false,
        );
      }
    } catch (_) {}
  }
}
