// ════════════════════════════════════════════════════════════════════════════
// lib/services/InfinityBypassEngine.dart
//
// GARA Mobile — InfinityFree AES JS Challenge Bypass Engine
//
// PENJELASAN MEKANISME BYPASS:
// 1. InfinityFree (rf.gd) memblokir semua request HTTP non-browser menggunakan
//    AES JavaScript challenge. Challenge ini mendeteksi kemampuan browser,
//    menghitung kunci dekripsi, lalu menyimpannya dalam cookie '__test'.
// 2. Jika kita langsung mengirim POST request ke API endpoint (/api/mobile/login),
//    request tersebut akan diblokir karena tidak menyertakan cookie '__test'.
// 3. Solusinya: Kita lakukan "GET Warm-up" ke root URL (https://garaedu.rf.gd/)
//    menggunakan HeadlessInAppWebView. WebView akan mengeksekusi JS challenge,
//    menyimpan cookie '__test', lalu melakukan redirect/reload halaman.
// 4. Setelah onLoadStop mendeteksi cookie '__test' berhasil dibuat, kita simpan
//    cookie tersebut ke SharedPreferences.
// 5. Selanjutnya, request API berikutnya bisa menggunakan paket HTTP standar
//    (http.get/http.post) yang JAUH LEBIH CEPAT, asalkan menyertakan header:
//    'Cookie': '__test=$testCookieValue; laravel_session=$sessionCookieValue'
// ════════════════════════════════════════════════════════════════════════════

import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';
import 'infinity_bypass_exception.dart';

class InfinityBypassEngine {
  InfinityBypassEngine._();

  static const String targetDomain = 'garaedu.rf.gd';
  static final WebUri targetUri = WebUri('https://$targetDomain');
  static const Duration warmUpTimeout = Duration(seconds: 20);

  // ──────────────────────────────────────────────────────────────────────────
  // warmUp() — Melakukan navigasi GET ke root domain untuk memicu eksekusi
  // JS Challenge dari InfinityFree dan mengambil cookie '__test'.
  // ──────────────────────────────────────────────────────────────────────────
  static Future<String> warmUp() async {
    final completer = Completer<String>();
    HeadlessInAppWebView? headlessWebView;

    debugPrint('[BypassEngine] Memulai warm-up GET challenge ke: $targetUri');

    // Timer guard untuk mencegah gantung jika WebView gagal memproses JS
    final timer = Timer(warmUpTimeout, () {
      if (!completer.isCompleted) {
        headlessWebView?.dispose();
        completer.completeError(
          const InfinityFreeBypassException(
            message: 'Warm-up timeout. WebView gagal menyelesaikan JS Challenge.',
            rawHtmlSnippet: 'TIMEOUT_EXPIRED',
          ),
        );
      }
    });

    try {
      // Hapus cookies lama agar WebView benar-benar memicu challenge baru
      await CookieManager.instance().deleteCookies(url: targetUri);

      headlessWebView = HeadlessInAppWebView(
        initialSettings: InAppWebViewSettings(
          javaScriptEnabled: true,
          domStorageEnabled: true,
          databaseEnabled: true,
          useHybridComposition: true,
          thirdPartyCookiesEnabled: true,
          userAgent:
              'Mozilla/5.0 (Linux; Android 13; GARA_APP) '
              'AppleWebKit/537.36 (KHTML, like Gecko) '
              'Chrome/120.0.0.0 Mobile Safari/537.36',
        ),
        initialUrlRequest: URLRequest(url: targetUri),
        onLoadStop: (controller, url) async {
          if (completer.isCompleted) return;

          try {
            // Cek cookies yang tersimpan di domain target
            final cookies = await CookieManager.instance().getCookies(url: targetUri);
            String testCookieValue = '';

            for (final cookie in cookies) {
              if (cookie.name == '__test' || cookie.name.startsWith('_test')) {
                testCookieValue = cookie.value.toString();
                break;
              }
            }

            if (testCookieValue.isNotEmpty) {
              debugPrint('[BypassEngine] Sukses mendapatkan cookie __test: $testCookieValue');
              
              // Simpan cookie ke SharedPreferences agar bisa digunakan oleh HTTP Client biasa
              final prefs = await SharedPreferences.getInstance();
              await prefs.setString(GaraPrefKeys.bypassTestCookie, testCookieValue);
              await prefs.setInt(
                GaraPrefKeys.bypassCookieTimestamp,
                DateTime.now().millisecondsSinceEpoch,
              );

              timer.cancel();
              headlessWebView?.dispose();
              completer.complete(testCookieValue);
            } else {
              // Jika cookie belum terdeteksi, mungkin WebView sedang memproses challenge.
              // Kita tunggu onLoadStop berikutnya (karena challenge akan memicu reload).
              debugPrint('[BypassEngine] Cookie __test belum terdeteksi di onLoadStop untuk URL: $url');
            }
          } catch (e) {
            debugPrint('[BypassEngine] Error saat mengambil cookies: $e');
          }
        },
        onReceivedError: (controller, request, error) {
          if (request.isForMainFrame == true && !completer.isCompleted) {
            debugPrint('[BypassEngine] WebView Error: ${error.description}');
          }
        },
      );

      await headlessWebView.run();
    } catch (e) {
      timer.cancel();
      headlessWebView?.dispose();
      if (!completer.isCompleted) {
        completer.completeError(e);
      }
    }

    return completer.future;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // getValidCookie() — Mengambil cookie '__test' yang masih berlaku.
  // Jika tidak ada atau sudah terlalu lama (> 6 jam), lakukan warm-up ulang.
  // ──────────────────────────────────────────────────────────────────────────
  static Future<String> getValidCookie({bool forceRefresh = false}) async {
    final prefs = await SharedPreferences.getInstance();
    final cookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
    final timestamp = prefs.getInt(GaraPrefKeys.bypassCookieTimestamp) ?? 0;
    final now = DateTime.now().millisecondsSinceEpoch;

    // Cookie kadaluarsa setelah 6 jam (21600000 ms)
    final isExpired = (now - timestamp) > 21600000;

    if (cookie.isEmpty || isExpired || forceRefresh) {
      debugPrint('[BypassEngine] Cookie kosong/expire. Menjalankan warm-up...');
      return await warmUp();
    }

    return cookie;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // loginViaHeadlessWebView() — Melakukan POST login menggunakan HeadlessInAppWebView
  // secara silent di background (mengeksekusi JS challenge & POST request dalam browser context).
  // ──────────────────────────────────────────────────────────────────────────
  static Future<String> loginViaHeadlessWebView({
    required String identifier,
    required String password,
  }) async {
    final completer = Completer<String>();
    HeadlessInAppWebView? headlessWebView;
    bool hasSubmitted = false;

    debugPrint('[BypassEngine] Memulai Headless WebView Login ke: $targetUri');

    final timer = Timer(warmUpTimeout, () {
      if (!completer.isCompleted) {
        headlessWebView?.dispose();
        completer.completeError(
          const InfinityFreeBypassException(
            message: 'Login timeout. Headless WebView gagal memproses login.',
            rawHtmlSnippet: 'TIMEOUT_EXPIRED',
          ),
        );
      }
    });

    try {
      headlessWebView = HeadlessInAppWebView(
        initialSettings: InAppWebViewSettings(
          javaScriptEnabled: true,
          domStorageEnabled: true,
          databaseEnabled: true,
          useHybridComposition: true,
          thirdPartyCookiesEnabled: true,
          userAgent:
              'Mozilla/5.0 (Linux; Android 13; GARA_APP) '
              'AppleWebKit/537.36 (KHTML, like Gecko) '
              'Chrome/120.0.0.0 Mobile Safari/537.36',
        ),
        initialUrlRequest: URLRequest(url: targetUri),
        onWebViewCreated: (controller) {
          // Registrasikan handler hasil login ke Dart SAAT WEBVIEW DIBUAT (sebelum onLoadStop)
          controller.addJavaScriptHandler(
            handlerName: 'onLoginResult',
            callback: (args) async {
              final rawResponseText = (args.isNotEmpty ? args.first : '').toString();
              debugPrint('[BypassEngine] Received JS login result length: ${rawResponseText.length}');

              try {
                // Simpan cookie __test terbaru ke SharedPreferences
                final cookies = await CookieManager.instance().getCookies(url: targetUri);
                for (final cookie in cookies) {
                  if (cookie.name == '__test' || cookie.name.startsWith('_test')) {
                    final testCookieValue = cookie.value.toString();
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.setString(GaraPrefKeys.bypassTestCookie, testCookieValue);
                    await prefs.setInt(
                      GaraPrefKeys.bypassCookieTimestamp,
                      DateTime.now().millisecondsSinceEpoch,
                    );
                    break;
                  }
                }
              } catch (e) {
                debugPrint('[BypassEngine] Error saving cookie in handler: $e');
              }

              timer.cancel();
              headlessWebView?.dispose();

              if (!completer.isCompleted) {
                if (rawResponseText.isNotEmpty) {
                  completer.complete(rawResponseText);
                } else {
                  completer.completeError(
                    const InfinityFreeBypassException(
                      message: 'Respon dari JS fetch login kosong.',
                      rawHtmlSnippet: 'EMPTY_RESPONSE',
                    ),
                  );
                }
              }
            },
          );
        },
        onLoadStop: (controller, url) async {
          if (completer.isCompleted || hasSubmitted) return;

          try {
            debugPrint('[BypassEngine] onLoadStop dipanggil untuk URL: $url. Menjalankan JS login script...');
            hasSubmitted = true;

            // Jalankan JS fetch POST ke /api/mobile/login dari dalam WebView
            final jsCode = '''
              (async function() {
                try {
                  let attempts = 0;
                  while (!document.cookie.includes('__test') && attempts < 10) {
                    await new Promise(r => setTimeout(r, 300));
                    attempts++;
                  }
                  const res = await fetch('https://garaedu.rf.gd/api/mobile/login', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json',
                      'X-App': 'GARA_MOBILE',
                      'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                      identifier: ${jsonEncode(identifier)},
                      password: ${jsonEncode(password)}
                    })
                  });
                  const text = await res.text();
                  window.flutter_inappwebview.callHandler('onLoginResult', text);
                } catch (e) {
                  window.flutter_inappwebview.callHandler('onLoginResult', JSON.stringify({ status: 'error', message: e.toString() }));
                }
              })();
            ''';

            await controller.evaluateJavascript(source: jsCode);
          } catch (e) {
            debugPrint('[BypassEngine] Error pada Headless login onLoadStop: $e');
            timer.cancel();
            headlessWebView?.dispose();
            if (!completer.isCompleted) {
              completer.completeError(e);
            }
          }
        },
        onReceivedError: (controller, request, error) {
          if (request.isForMainFrame == true && !completer.isCompleted && !hasSubmitted) {
            debugPrint('[BypassEngine] WebView Login Error: ${error.description}');
          }
        },
      );

      await headlessWebView.run();
    } catch (e) {
      timer.cancel();
      headlessWebView?.dispose();
      if (!completer.isCompleted) {
        completer.completeError(e);
      }
    }

    return completer.future;
  }
}


