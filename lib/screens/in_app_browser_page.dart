import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:flutter_windowmanager/flutter_windowmanager.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../widgets/gara_logo.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';

const _kSecurityChannel = MethodChannel('com.lms.gara/security');

class InAppBrowserPage extends StatefulWidget {
  final String title;
  final String initialUrl;

  const InAppBrowserPage({
    super.key,
    required this.title,
    required this.initialUrl,
  });

  @override
  State<InAppBrowserPage> createState() => _InAppBrowserPageState();
}

class _InAppBrowserPageState extends State<InAppBrowserPage> with WidgetsBindingObserver {
  InAppWebViewController? webViewController;
  double progress = 0;
  String currentTitle = '';
  bool canGoBack = false;

  late InAppWebViewSettings settings;
  PullToRefreshController? pullToRefreshController;

  bool _isExamModeActive = false;
  bool _showBlackout = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    currentTitle = widget.title;
    
    settings = InAppWebViewSettings(
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

    pullToRefreshController = PullToRefreshController(
      settings: PullToRefreshSettings(
        color: GaraColors.studentPrimary,
      ),
      onRefresh: () async {
        if (webViewController != null) {
          webViewController?.reload();
        }
      },
    );

    _applySecurityForUrl(widget.initialUrl);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    if (_isExamModeActive) {
      _deactivateExamModeSync();
    }
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (!_isExamModeActive) return;
    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused ||
        state == AppLifecycleState.hidden) {
      if (mounted) setState(() => _showBlackout = true);
    } else if (state == AppLifecycleState.resumed) {
      _enforceExamSecurity();
      if (mounted) setState(() => _showBlackout = false);
    }
  }

  void _applySecurityForUrl(String url) {
    if (AppConfig.isExamArenaUrl(url)) {
      _activateExamMode();
    } else if (AppConfig.isExamResultUrl(url)) {
      _deactivateExamMode();
    } else if (_isExamModeActive) {
      _deactivateExamMode();
    }
  }

  Future<void> _activateExamMode() async {
    if (_isExamModeActive) {
      await _enforceExamSecurity();
      return;
    }
    _isExamModeActive = true;
    if (mounted) setState(() {});
    await _enforceExamSecurity();
    try { await _kSecurityChannel.invokeMethod('startVolumeWatch'); } catch (_) {}
    debugPrint('[GARA Security] ExamMode ACTIVATED di Browser Eksternal');
  }

  Future<void> _enforceExamSecurity() async {
    try {
      await Future.wait([
        _kSecurityChannel.invokeMethod('addFlagSecure'),
        _kSecurityChannel.invokeMethod('keepScreenOn'),
        _kSecurityChannel.invokeMethod('enforceMaxVolume'),
        _kSecurityChannel.invokeMethod('hideSystemBars'),
        _kSecurityChannel.invokeMethod('startLockTask'),
      ]);
    } catch (_) {}
    try { await FlutterWindowManager.addFlags(FlutterWindowManager.FLAG_SECURE); } catch (_) {}
  }

  Future<void> _deactivateExamMode() async {
    if (!_isExamModeActive) return;
    _isExamModeActive = false;
    if (mounted) setState(() => _showBlackout = false);
    try {
      await Future.wait([
        _kSecurityChannel.invokeMethod('clearFlagSecure'),
        _kSecurityChannel.invokeMethod('clearKeepScreenOn'),
        _kSecurityChannel.invokeMethod('showSystemBars'),
        _kSecurityChannel.invokeMethod('stopLockTask'),
        _kSecurityChannel.invokeMethod('stopVolumeWatch'),
      ]);
    } catch (_) {}
    try { await FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE); } catch (_) {}
    debugPrint('[GARA Security] ExamMode DEACTIVATED di Browser Eksternal');
  }

  void _deactivateExamModeSync() {
    try {
      _kSecurityChannel.invokeMethod('clearFlagSecure');
      _kSecurityChannel.invokeMethod('clearKeepScreenOn');
      _kSecurityChannel.invokeMethod('showSystemBars');
      _kSecurityChannel.invokeMethod('stopLockTask');
      _kSecurityChannel.invokeMethod('stopVolumeWatch');
    } catch (_) {}
    try { FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE); } catch (_) {}
  }

  void _showExamBlockedSnackBar() {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(
        'Selesaikan ujian terlebih dahulu (Klik tombol Selesai/Kembali di dalam web).',
        style: GoogleFonts.poppins(fontSize: 12.5),
      ),
      backgroundColor: GaraColors.studentPrimary,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      duration: const Duration(seconds: 2),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_isExamModeActive,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop && _isExamModeActive) {
          _showExamBlockedSnackBar();
        }
      },
      child: Scaffold(
        backgroundColor: GaraColors.studentBgBody,
        appBar: AppBar(
          backgroundColor: GaraColors.studentSurface,
          elevation: 0,
          centerTitle: true,
          leading: IconButton(
            icon: const Icon(Icons.close_rounded, color: GaraColors.studentTextMain),
            onPressed: () {
              if (_isExamModeActive) {
                _showExamBlockedSnackBar();
              } else {
                Navigator.pop(context);
              }
            },
          ),
          title: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const GaraLogoBlack(height: 24),
              const SizedBox(width: 8),
              Text(
                'Garuda Akademi',
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  fontSize: 15,
                  color: GaraColors.studentTextMain,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
          actions: [
            if (canGoBack)
              IconButton(
                icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18, color: GaraColors.studentTextMain),
                onPressed: () {
                  webViewController?.goBack();
                },
                tooltip: 'Kembali',
              ),
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert_rounded, color: GaraColors.studentTextMuted),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              onSelected: (value) async {
                if (value == 'open_browser') {
                  final currentUrl = (await webViewController?.getUrl())?.toString()
                      ?? widget.initialUrl;
                  final uri = Uri.tryParse(currentUrl);
                  if (uri != null) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                }
              },
              itemBuilder: (_) => [
                PopupMenuItem<String>(
                  value: 'open_browser',
                  child: Row(
                    children: [
                      const Icon(Icons.open_in_browser_rounded,
                          size: 18, color: GaraColors.studentTextMain),
                      const SizedBox(width: 10),
                      Text('Buka di Browser',
                          style: GoogleFonts.poppins(fontSize: 13)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(width: 4),
          ],
        ),
        body: Column(
          children: [
            if (progress < 1.0)
              LinearProgressIndicator(
                value: progress,
                backgroundColor: GaraColors.studentBgBody,
                valueColor: const AlwaysStoppedAnimation<Color>(GaraColors.studentPrimary),
                minHeight: 3,
              ),
            Expanded(
              child: Stack(
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(16),
                      topRight: Radius.circular(16),
                    ),
                    child: InAppWebView(
                      initialUrlRequest: URLRequest(url: WebUri(widget.initialUrl)),
                      initialSettings: settings,
                      pullToRefreshController: pullToRefreshController,
                      onWebViewCreated: (controller) {
                        webViewController = controller;

                        // GARA: Netlify Logout Bridge
                        controller.addJavaScriptHandler(
                          handlerName: 'netlifyLogout',
                          callback: (args) async {
                            debugPrint('[GARA InAppBrowser] netlifyLogout dipanggil');
                            await _deactivateExamMode();
                            if (mounted) Navigator.pop(context);
                          },
                        );
                        controller.addJavaScriptHandler(
                          handlerName: 'triggerPrint',
                          callback: (args) async {
                            final currentUrl = (await controller.getUrl())?.toString();
                            if (currentUrl != null && currentUrl.isNotEmpty) {
                              final uri = Uri.tryParse(currentUrl);
                              if (uri != null) {
                                await launchUrl(uri, mode: LaunchMode.externalApplication);
                              }
                            }
                          },
                        );
                      },
                      onLoadStart: (controller, url) {
                        if (url != null) {
                          final urlStr = url.toString();

                          // Intercept Netlify login page trap
                          if (AppConfig.isNetlifyIndexTrap(urlStr)) {
                            debugPrint('[GARA InAppBrowser] Netlify index trap — kembali ke dashboard');
                            _deactivateExamMode();
                            controller.stopLoading();
                            if (mounted) Navigator.pop(context);
                            return;
                          }

                          _applySecurityForUrl(urlStr);
                        }
                        setState(() {
                          progress = 0; 
                        });
                      },
                      onProgressChanged: (controller, progressPercentage) {
                        if (progressPercentage == 100) {
                          pullToRefreshController?.endRefreshing();
                        }
                        setState(() {
                          progress = progressPercentage / 100;
                        });
                      },
                      onLoadStop: (controller, url) async {
                        pullToRefreshController?.endRefreshing();
                        String? title = await controller.getTitle();
                        bool checkCanGoBack = await controller.canGoBack();
                        setState(() {
                          if (title != null && title.isNotEmpty) currentTitle = title;
                          canGoBack = checkCanGoBack;
                        });

                        // Inject GARA Bridge JS saat halaman Netlify dimuat
                        final urlStr = url?.toString() ?? '';
                        if (urlStr.contains('garudakademi.netlify.app') &&
                            !urlStr.contains('/index.html')) {
                          try {
                            // Tandai halaman dan intercept tombol logout
                            await controller.evaluateJavascript(source: """
                              (function() {
                                if (window.__garaNetlifyPatched) return;
                                window.__garaNetlifyPatched = true;
                                document.documentElement.classList.add('is-gara-official-app');
                                document.documentElement.classList.add('is-ruang-ujian');
                                function returnToApp(e) {
                                  if (e) { e.preventDefault(); e.stopPropagation(); }
                                  if (window.flutter_inappwebview) {
                                    window.flutter_inappwebview.callHandler('netlifyLogout');
                                  }
                                  return false;
                                }
                                function patch() {
                                  ['a.header-logout-btn','.btn-kembali-beranda',
                                   'a[href*="index.html"]'
                                   ].forEach(function(s) {
                                    document.querySelectorAll(s).forEach(function(el) {
                                      if (!el.dataset.garaBridgePatched) {
                                        el.dataset.garaBridgePatched = 'true';
                                        el.addEventListener('click', returnToApp, true);
                                      }
                                    });
                                  });
                                }
                                patch();
                                new MutationObserver(patch).observe(document.body, {childList:true,subtree:true});
                              })();
                            """);
                          } catch (_) {}
                        }
                      },
                      onReceivedError: (controller, request, error) async {
                        pullToRefreshController?.endRefreshing();
                        if (request.isForMainFrame ?? false) {
                          debugPrint("[GARA WebView] Error: ${error.description} (Code: ${error.type})");
                          controller.loadUrl(
                            urlRequest: URLRequest(url: WebUri('file:///android_asset/flutter_assets/assets/helpers/eror.html')),
                          );
                        }
                      },
                      onDownloadStartRequest: (controller, downloadStartRequest) async {
                        final dlUrl = downloadStartRequest.url.toString();
                        debugPrint('[GARA InAppBrowser Download] Download request: $dlUrl');
                        
                        final uri = Uri.tryParse(dlUrl);
                        if (uri != null && ['http', 'https'].contains(uri.scheme.toLowerCase())) {
                          try {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                            return;
                          } catch (e) {
                            debugPrint('[GARA InAppBrowser Download] launchUrl error: $e');
                          }
                        }
                        
                        if (dlUrl.startsWith('data:') || dlUrl.startsWith('blob:')) {
                          try {
                            final filename = downloadStartRequest.suggestedFilename ?? 'Dokumen_GARA';
                            await controller.evaluateJavascript(source: """
                              (function() {
                                var a = document.createElement('a');
                                a.href = '${dlUrl.replaceAll("'", "\\'")}';
                                a.download = '${filename.replaceAll("'", "\\'")}';
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                              })();
                            """);
                          } catch (e) {
                            debugPrint('[GARA InAppBrowser Download] JS blob download error: $e');
                          }
                        }
                      },
                      onCreateWindow: (controller, createWindowAction) async {
                        final reqUrl = createWindowAction.request.url;
                        if (reqUrl != null) {
                          debugPrint('[GARA InAppBrowser Window] Popup requested: $reqUrl');
                          if (['http', 'https'].contains(reqUrl.scheme.toLowerCase())) {
                            try {
                              await launchUrl(reqUrl, mode: LaunchMode.externalApplication);
                              return true;
                            } catch (e) {
                              debugPrint('[GARA InAppBrowser Window] launchUrl popup error: $e');
                            }
                          }
                        }
                        return false;
                      },
                      shouldOverrideUrlLoading: (controller, navigationAction) async {
                        final uri = navigationAction.request.url;
                        if (uri == null) return NavigationActionPolicy.CANCEL;

                        // Handle custom GARA scheme: gara://exam-done
                        // Dikirim oleh exam JS saat user selesai/keluar ujian di dalam app
                        if (uri.scheme == 'gara') {
                          if (uri.host == 'exam-done') {
                            await _deactivateExamMode();
                            if (mounted) Navigator.pop(context);
                          }
                          return NavigationActionPolicy.CANCEL;
                        }

                        if (["http", "https"].contains(uri.scheme)) {
                          return NavigationActionPolicy.ALLOW;
                        }
                        return NavigationActionPolicy.CANCEL;
                      },
                    ),
                  ),
                  if (_showBlackout)
                    Positioned.fill(
                      child: Container(
                        color: Colors.black,
                        child: Center(
                          child: Text(
                            'Ujian Sedang Berlangsung\nMohon kembali ke aplikasi.',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.poppins(color: Colors.white, fontSize: 16),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
