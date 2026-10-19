import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../widgets/gara_logo.dart';
import '../utils/app_constants.dart';

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

class _InAppBrowserPageState extends State<InAppBrowserPage> {
  InAppWebViewController? webViewController;
  double progress = 0;
  String currentTitle = '';
  bool canGoBack = false;
  bool _showOfflineOverlay = false;
  bool _isRetrying = false;

  late InAppWebViewSettings settings;
  PullToRefreshController? pullToRefreshController;

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
      settings: PullToRefreshSettings(
        color: GaraColors.studentPrimary,
      ),
      onRefresh: () async {
        if (webViewController != null) {
          webViewController?.reload();
        }
      },
    );
  }

  @override
  void dispose() {
    super.dispose();
  }

  Future<void> _saveAndOpenDataUri(String dataUri, String suggestedFilename) async {
    try {
      final parts = dataUri.split(',');
      if (parts.length < 2) return;
      final String base64Str = parts[1];
      final List<int> bytes = base64.decode(base64Str);
      
      final Directory? tempDir = Platform.isAndroid 
          ? await getExternalStorageDirectory() 
          : await getTemporaryDirectory();
      
      if (tempDir == null) return;
      
      final String filePath = '${tempDir.path}/$suggestedFilename';
      final File file = File(filePath);
      await file.writeAsBytes(bytes);
      
      debugPrint('[GARA Download] File saved to: $filePath');
      await OpenFilex.open(filePath);
    } catch (e) {
      debugPrint('[GARA Download] Error saving/opening base64 file: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.studentBgBody,
      appBar: AppBar(
        backgroundColor: GaraColors.studentSurface,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded, color: GaraColors.studentTextMain),
          onPressed: () => Navigator.pop(context),
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
                      controller.addJavaScriptHandler(
                        handlerName: 'blobDownloaded',
                        callback: (args) async {
                          if (args.length >= 2) {
                            final String dataUri = args[0].toString();
                            final String filename = args[1].toString();
                            await _saveAndOpenDataUri(dataUri, filename);
                          }
                        },
                      );
                    },
                    onLoadStart: (controller, url) {
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

                      if (_isRetrying && mounted) {
                        setState(() {
                          _showOfflineOverlay = false;
                          _isRetrying = false;
                        });
                      }

                      String? title = await controller.getTitle();
                      bool checkCanGoBack = await controller.canGoBack();
                      setState(() {
                        if (title != null && title.isNotEmpty) currentTitle = title;
                        canGoBack = checkCanGoBack;
                      });
                    },
                    onReceivedError: (controller, request, error) async {
                      pullToRefreshController?.endRefreshing();
                      if (request.isForMainFrame ?? false) {
                        final desc = error.description;
                        if (desc.contains('ERR_ABORTED')) return;
                        debugPrint("[GARA InAppBrowser] Error: ${error.description}");
                        if (mounted) {
                          setState(() {
                            _showOfflineOverlay = true;
                            _isRetrying = false;
                          });
                        }
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
                      
                      final filename = downloadStartRequest.suggestedFilename ?? 'Dokumen_GARA';
                      if (dlUrl.startsWith('blob:')) {
                        try {
                          await controller.evaluateJavascript(source: """
                            (async function() {
                              try {
                                var response = await fetch('${dlUrl.replaceAll("'", "\\'")}');
                                var blob = await response.blob();
                                var reader = new FileReader();
                                reader.onloadend = function() {
                                  var base64data = reader.result;
                                  if (window.flutter_inappwebview) {
                                    window.flutter_inappwebview.callHandler('blobDownloaded', base64data, '${filename.replaceAll("'", "\\'")}');
                                  }
                                };
                                reader.readAsDataURL(blob);
                              } catch (e) {
                                console.error('Blob fetch failed: ' + e);
                              }
                            })();
                          """);
                        } catch (e) {
                          debugPrint('[GARA InAppBrowser Download] JS blob download error: $e');
                        }
                      } else if (dlUrl.startsWith('data:')) {
                        _saveAndOpenDataUri(dlUrl, filename);
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

                      if (["http", "https"].contains(uri.scheme)) {
                        return NavigationActionPolicy.ALLOW;
                      }
                      return NavigationActionPolicy.CANCEL;
                    },
                  ),
                ),
                if (_showOfflineOverlay)
                  Positioned.fill(
                    child: Container(
                      color: const Color(0xFFF8FAFC),
                      child: SafeArea(
                        child: Center(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 32),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 88,
                                  height: 88,
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFFFE4E6),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.wifi_off_rounded,
                                      color: Color(0xFFDC2626), size: 40),
                                ),
                                const SizedBox(height: 24),
                                Text(
                                  'Koneksi Terputus',
                                  style: GoogleFonts.poppins(
                                    fontSize: 20,
                                    fontWeight: FontWeight.w700,
                                    color: const Color(0xFF1E293B),
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 10),
                                Text(
                                  'Tidak dapat memuat halaman.\nPeriksa koneksi internet Anda.',
                                  style: GoogleFonts.poppins(
                                    fontSize: 13.5,
                                    color: const Color(0xFF64748B),
                                    height: 1.55,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 32),
                                SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton.icon(
                                    onPressed: _isRetrying
                                        ? null
                                        : () {
                                            if (mounted) {
                                              setState(() => _isRetrying = true);
                                            }
                                            webViewController?.reload();
                                          },
                                    icon: _isRetrying
                                        ? const SizedBox(
                                            width: 18,
                                            height: 18,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2,
                                              valueColor: AlwaysStoppedAnimation<Color>(
                                                  Colors.white),
                                            ),
                                          )
                                        : const Icon(Icons.refresh_rounded, size: 18),
                                    label: Text(
                                      _isRetrying ? 'Menghubungkan...' : 'Mulai Ulang',
                                      style: GoogleFonts.poppins(
                                          fontWeight: FontWeight.w600, fontSize: 14),
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: GaraColors.studentPrimary,
                                      foregroundColor: Colors.white,
                                      disabledBackgroundColor:
                                          GaraColors.studentPrimary.withOpacity(0.7),
                                      disabledForegroundColor: Colors.white,
                                      padding:
                                          const EdgeInsets.symmetric(vertical: 14),
                                      shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(14)),
                                      elevation: 0,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
