import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../utils/browser_file_downloader.dart';
import '../widgets/browser/browser_app_bar.dart';
import '../widgets/browser/browser_offline_overlay.dart';
import '../widgets/browser/browser_settings_factory.dart';

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
  bool canGoBack = false, _showOfflineOverlay = false, _isRetrying = false;
  bool _passwordChangedSuccess = false, _hasClosed = false;

  late final InAppWebViewSettings settings = BrowserSettingsFactory.createDefaultSettings();
  PullToRefreshController? pullToRefreshController;

  void _closeWithSuccess() {
    if (_hasClosed || !mounted) return;
    _hasClosed = true;
    Navigator.pop(context, true);
  }

  void _closeWithCancel() {
    if (_hasClosed || !mounted) return;
    _hasClosed = true;
    Navigator.pop(context, false);
  }

  @override
  void initState() {
    super.initState();
    currentTitle = widget.title;
    BrowserSettingsFactory.injectBypassCookie();

    pullToRefreshController = PullToRefreshController(
      settings: PullToRefreshSettings(color: GaraColors.studentPrimary),
      onRefresh: () async => webViewController?.reload(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (didPop) return;
        _closeWithCancel();
      },
      child: Scaffold(
        backgroundColor: GaraColors.studentBgBody,
        appBar: BrowserAppBar(
          title: currentTitle,
          canGoBack: canGoBack,
          onBack: () => webViewController?.goBack(),
          onClose: _closeWithCancel,
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
                      onWebViewCreated: (c) {
                        webViewController = c;
                        c.addJavaScriptHandler(
                          handlerName: 'passwordChanged',
                          callback: (_) {
                            _passwordChangedSuccess = true;
                            _closeWithSuccess();
                          },
                        );
                        c.addJavaScriptHandler(
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
                      onLoadStop: (c, url) async {
                        pullToRefreshController?.endRefreshing();
                        if (_isRetrying && mounted) setState(() => _showOfflineOverlay = _isRetrying = false);
                        if (widget.title.isEmpty) {
                          final t = await c.getTitle();
                          if (mounted && t != null && t.isNotEmpty && !BrowserAppBar.isRawDomainOrUrl(t)) {
                            setState(() => currentTitle = t);
                          }
                        }
                        final back = await c.canGoBack();
                        if (mounted) setState(() => canGoBack = back);

                        if (widget.isPasswordFlow && url != null) {
                          final path = url.path.toLowerCase();
                          if (_passwordChangedSuccess && (path == '/login' || path.endsWith('/login') || path.contains('/dashboard'))) {
                            _closeWithSuccess();
                          } else if (!_passwordChangedSuccess && (path == '/login' || path.endsWith('/login'))) {
                            _closeWithCancel();
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
                      onCreateWindow: (c, action) async {
                        final req = action.request.url;
                        if (req != null && ['http', 'https'].contains(req.scheme.toLowerCase())) {
                          if (AppConfig.isAllowedDomain(req.host.toLowerCase())) {
                            c.loadUrl(urlRequest: URLRequest(url: req));
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
                          if (_passwordChangedSuccess && (path == '/login' || path.endsWith('/login') || path.contains('/dashboard'))) {
                            _closeWithSuccess();
                            return NavigationActionPolicy.CANCEL;
                          } else if (!_passwordChangedSuccess && (path == '/login' || path.endsWith('/login'))) {
                            _closeWithCancel();
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
      ),
    );
  }
}
