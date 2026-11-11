import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../utils/browser_file_downloader.dart';
import '../widgets/browser/browser_app_bar.dart';
import '../widgets/browser/browser_offline_overlay.dart';

class InAppBrowserPage extends StatefulWidget {
  final String title;
  final String initialUrl;
  final bool isPasswordFlow;

  const InAppBrowserPage({
    super.key,
    required this.title,
    required this.initialUrl,
    this.isPasswordFlow = false,
  });

  @override
  State<InAppBrowserPage> createState() => _InAppBrowserPageState();
}

class _InAppBrowserPageState extends State<InAppBrowserPage> {
  InAppWebViewController? webViewController;
  double progress = 0;
  String currentTitle = '';
  bool canGoBack = false;
  bool _showOfflineOverlay = false;
  bool _isRetrying = false;
  bool _hasVisitedPasswordPage = false;
  bool _hasClosed = false;

  late InAppWebViewSettings settings;
  PullToRefreshController? pullToRefreshController;

  void _closeWithSuccess() {
    if (_hasClosed || !mounted) return;
    _hasClosed = true;
    Navigator.pop(context, true);
  }

  @override
  void initState() {
    super.initState();
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
      settings: PullToRefreshSettings(color: GaraColors.studentPrimary),
      onRefresh: () async => webViewController?.reload(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.studentBgBody,
      appBar: BrowserAppBar(
        title: currentTitle,
        canGoBack: canGoBack,
        onBack: () => webViewController?.goBack(),
        onGetCurrentUrl: () async => (await webViewController?.getUrl())?.toString(),
        fallbackUrl: widget.initialUrl,
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
                      controller.addJavaScriptHandler(
                        handlerName: 'passwordChanged',
                        callback: (_) => _closeWithSuccess(),
                      );
                      controller.addJavaScriptHandler(
                        handlerName: 'blobDownloaded',
                        callback: (args) async {
                          if (args.length >= 2) {
                            await BrowserFileDownloader.saveAndOpenDataUri(args[0].toString(), args[1].toString());
                          }
                        },
                      );
                    },
                    onLoadStart: (_, __) => setState(() => progress = 0),
                    onProgressChanged: (_, p) {
                      if (p == 100) pullToRefreshController?.endRefreshing();
                      setState(() => progress = p / 100);
                    },
                    onLoadStop: (controller, url) async {
                      pullToRefreshController?.endRefreshing();
                      if (_isRetrying && mounted) setState(() => _showOfflineOverlay = _isRetrying = false);
                      final title = await controller.getTitle();
                      final back = await controller.canGoBack();
                      if (mounted) setState(() { canGoBack = back; if (title != null && title.isNotEmpty) currentTitle = title; });

                      if (widget.isPasswordFlow && url != null) {
                        final path = url.path.toLowerCase();
                        if (path.contains('lupa-password') || path.contains('reset-password') || path.contains('change-password')) {
                          _hasVisitedPasswordPage = true;
                        } else if (_hasVisitedPasswordPage && (path == '/login' || path.endsWith('/login') || path.contains('/dashboard'))) {
                          _closeWithSuccess();
                        }
                      }
                    },
                    onReceivedError: (_, req, err) async {
                      pullToRefreshController?.endRefreshing();
                      if (req.isForMainFrame ?? false) {
                        if (!err.description.contains('ERR_ABORTED') && mounted) {
                          setState(() => _showOfflineOverlay = true);
                        }
                      }
                    },
                    onCreateWindow: (controller, action) async {
                      final req = action.request.url;
                      if (req != null && ['http', 'https'].contains(req.scheme.toLowerCase())) {
                        if (AppConfig.isAllowedDomain(req.host.toLowerCase())) {
                          controller.loadUrl(urlRequest: URLRequest(url: req));
                          return true;
                        }
                        try { await launchUrl(req, mode: LaunchMode.externalApplication); return true; } catch (_) {}
                      }
                      return false;
                    },
                    shouldOverrideUrlLoading: (_, nav) async {
                      final uri = nav.request.url;
                      if (uri == null) return NavigationActionPolicy.CANCEL;

                      if (widget.isPasswordFlow) {
                        final path = uri.path.toLowerCase();
                        if (_hasVisitedPasswordPage && (path == '/login' || path.endsWith('/login') || path.contains('/dashboard'))) {
                          _closeWithSuccess();
                          return NavigationActionPolicy.CANCEL;
                        }
                      }
                      return ['http', 'https'].contains(uri.scheme) ? NavigationActionPolicy.ALLOW : NavigationActionPolicy.CANCEL;
                    },
                  ),
                ),
                if (_showOfflineOverlay)
                  BrowserOfflineOverlay(
                    isRetrying: _isRetrying,
                    onRetry: () {
                      if (mounted) setState(() => _isRetrying = true);
                      webViewController?.reload();
                    },
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
