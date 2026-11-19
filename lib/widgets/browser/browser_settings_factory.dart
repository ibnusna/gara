import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/InfinityBypassEngine.dart';
import '../../utils/app_config.dart';
import '../../utils/app_constants.dart';

class BrowserSettingsFactory {
  BrowserSettingsFactory._();

  static const String garaUserAgent =
      'Mozilla/5.0 (Linux; Android 13; GARA_APP) '
      'AppleWebKit/537.36 (KHTML, like Gecko) '
      'Chrome/120.0.0.0 Mobile Safari/537.36';

  static bool isInfinityCookieErrorUrl(Uri uri) {
    final host = uri.host.toLowerCase();
    final path = uri.path.toLowerCase();
    return host.contains('ifastnet.com') ||
        host.contains('infinityfree') ||
        path.endsWith('/cookies.html') ||
        path == '/cookies.html';
  }

  static InAppWebViewSettings createDefaultSettings() {
    return createHardenedSettings(isExam: false);
  }

  static InAppWebViewSettings createHardenedSettings({bool isExam = false}) {
    return InAppWebViewSettings(
      useHybridComposition: true,
      domStorageEnabled: true,
      databaseEnabled: false,
      javaScriptEnabled: true,
      safeBrowsingEnabled: true,
      mixedContentMode: MixedContentMode.MIXED_CONTENT_NEVER_ALLOW,
      cacheEnabled: !isExam,
      cacheMode: isExam ? CacheMode.LOAD_NO_CACHE : CacheMode.LOAD_DEFAULT,
      useShouldOverrideUrlLoading: true,
      useOnDownloadStart: true,
      allowFileAccessFromFileURLs: false,
      allowUniversalAccessFromFileURLs: false,
      allowFileAccess: false,
      allowContentAccess: false,
      mediaPlaybackRequiresUserGesture: false,
      allowsInlineMediaPlayback: true,
      javaScriptCanOpenWindowsAutomatically: false,
      supportMultipleWindows: false,
      transparentBackground: false,
      supportZoom: !isExam,
      thirdPartyCookiesEnabled: false,
      disableContextMenu: isExam,
      applicationNameForUserAgent: 'GARA_OFFICIAL_APP',
      userAgent: garaUserAgent,
    );
  }

  static Future<void> injectBypassCookie() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      if (testCookie.isEmpty) return;

      final targetUri = WebUri(AppConfig.baseUrl);
      final cookieMgr = CookieManager.instance();

      final existing = await cookieMgr.getCookies(url: targetUri);
      final hasSame = existing.any((c) => c.name == '__test' && c.value.toString() == testCookie);
      if (hasSame) return;

      await cookieMgr.setCookie(
        url: targetUri,
        name: '__test',
        value: testCookie,
        path: '/',
        isSecure: false,
        isHttpOnly: false,
      );
    } catch (_) {}
  }

  static Future<void> recoverInfinityCookie(
    InAppWebViewController? controller,
    String targetUrl,
  ) async {
    try {
      await CookieManager.instance().deleteCookies(url: WebUri(AppConfig.baseUrl));
      await InfinityBypassEngine.getValidCookie(forceRefresh: true);
      await injectBypassCookie();
      await controller?.loadUrl(urlRequest: URLRequest(url: WebUri(targetUrl)));
    } catch (_) {}
  }
}
