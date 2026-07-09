import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';

class InfinityBypassDialog extends StatefulWidget {
  const InfinityBypassDialog({super.key});

  @override
  State<InfinityBypassDialog> createState() => _InfinityBypassDialogState();
}

class _InfinityBypassDialogState extends State<InfinityBypassDialog> {
  Timer? _timeoutTimer;
  bool _hasCompleted = false;

  @override
  void initState() {
    super.initState();
    // Timeout 25 detik
    _timeoutTimer = Timer(const Duration(seconds: 25), () {
      if (!_hasCompleted) {
        _hasCompleted = true;
        if (mounted) Navigator.pop(context, null);
      }
    });
  }

  @override
  void dispose() {
    _timeoutTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Dialog loading yang transparan tapi memuat InAppWebView
    return WillPopScope(
      onWillPop: () async => false,
      child: Dialog(
        backgroundColor: Colors.transparent,
        elevation: 0,
        child: Container(
          width: 250,
          height: 250,
          decoration: BoxDecoration(
            color: GaraColors.bgDark.withOpacity(0.9),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: GaraColors.glassBorder),
          ),
          child: Stack(
            alignment: Alignment.center,
            children: [
              // WebView disembunyikan total dengan Offstage agar tidak ada flash
              // tapi tetap di-render oleh Flutter engine supaya JS Challenge bisa berjalan
              Offstage(
                offstage: true,
                child: InAppWebView(
                  initialUrlRequest: URLRequest(url: WebUri('https://garaedu.rf.gd')),
                  initialSettings: InAppWebViewSettings(
                    javaScriptEnabled: true,
                    domStorageEnabled: true,
                    databaseEnabled: true,
                    thirdPartyCookiesEnabled: true,
                    userAgent: 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                  ),
                  onLoadStop: (controller, url) async {
                    if (_hasCompleted) return;
                    try {
                      final cookies = await CookieManager.instance().getCookies(url: WebUri('https://garaedu.rf.gd'));
                      for (final cookie in cookies) {
                        if (cookie.name == '__test') {
                          final testCookieValue = cookie.value.toString();
                          final prefs = await SharedPreferences.getInstance();
                          await prefs.setString(GaraPrefKeys.bypassTestCookie, testCookieValue);
                          await prefs.setInt(GaraPrefKeys.bypassCookieTimestamp, DateTime.now().millisecondsSinceEpoch);
                          
                          if (!_hasCompleted) {
                            _hasCompleted = true;
                            if (mounted) Navigator.pop(context, testCookieValue);
                          }
                          break;
                        }
                      }
                    } catch (e) {
                      debugPrint('BypassDialog error: $e');
                    }
                  },
                ),
              ),
              
              // Tampilan loading sebenarnya
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const CircularProgressIndicator(color: GaraColors.primaryLight),
                  const SizedBox(height: 20),
                  Text(
                    'Menghubungkan ke Server...',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.white),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// Helper untuk menampilkan dialog dan menunggu hasil
Future<String?> showInfinityBypass(BuildContext context) {
  return showDialog<String?>(
    context: context,
    barrierDismissible: false,
    useRootNavigator: true,
    builder: (ctx) => const InfinityBypassDialog(),
  );
}
