import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
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
            child: ClipRRect(
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
                    debugPrint("[GARA WebView] Error: ${error.description} (Code: ${error.type})");
                    
                    
                    controller.loadUrl(
                      urlRequest: URLRequest(url: WebUri('file:///android_asset/flutter_assets/assets/helpers/eror.html')),
                    );
                  }
                },
                
                
                shouldOverrideUrlLoading: (controller, navigationAction) async {
                  var uri = navigationAction.request.url;

                  if (uri != null) {
                    
                    
                    
                    if (["http", "https"].contains(uri.scheme)) {
                      
                      return NavigationActionPolicy.ALLOW;
                    }
                  }
                  
                  
                  
                  
                  return NavigationActionPolicy.CANCEL;
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}
