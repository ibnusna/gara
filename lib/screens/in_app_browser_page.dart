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
    
    // Inisialisasi pengaturan InAppWebView (Harden Settings for GARA v4.1.0)
    settings = InAppWebViewSettings(
      useShouldOverrideUrlLoading: true,
      mediaPlaybackRequiresUserGesture: false,
      javaScriptEnabled: true,
      domStorageEnabled: true, // WAJIB untuk Laravel & Alpine.js
      databaseEnabled: true,
      useHybridComposition: true, // Stabilitas rendering Android
      mixedContentMode: MixedContentMode.MIXED_CONTENT_ALWAYS_ALLOW, // Dukung localhost:8000 (HTTP)
      transparentBackground: true,
      supportZoom: true,
      userAgent: "GARA_OFFICIAL_APP", // Identifikasi request dari App Mobile
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
          // Tombol Back web (jika bisa ke halaman web sebelumnya)
          if (canGoBack)
            IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18, color: GaraColors.studentTextMain),
              onPressed: () {
                webViewController?.goBack();
              },
              tooltip: 'Kembali',
            ),
          // ── 3-Dot Menu ─────────────────────────────────────────────
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, color: GaraColors.studentTextMuted),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            onSelected: (value) async {
              if (value == 'open_browser') {
                // Ambil URL saat ini dari WebView
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
          // Loading Bar sederhana agar pengguna tahu web sedang diproses
          if (progress < 1.0)
            LinearProgressIndicator(
              value: progress,
              backgroundColor: GaraColors.studentBgBody,
              valueColor: const AlwaysStoppedAnimation<Color>(GaraColors.studentPrimary),
              minHeight: 3,
            ),
            
          // Area Web View yang di-clamp di dalam view utama
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
                    progress = 0; // Reset bar setiap memuat hal baru
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
                  // Dinamis mengubah title berdasarkan judul web
                  String? title = await controller.getTitle();
                  bool checkCanGoBack = await controller.canGoBack();
                  
                  setState(() {
                    // Update judul jika didapatkan
                    if (title != null && title.isNotEmpty) currentTitle = title;
                    canGoBack = checkCanGoBack;
                  });
                },
                onReceivedError: (controller, request, error) async {
                  pullToRefreshController?.endRefreshing();
                  if (request.isForMainFrame ?? false) {
                    debugPrint("[GARA WebView] Error: ${error.description} (Code: ${error.type})");
                    
                    // Show custom error if connection failed
                    controller.loadData(data: """
                      <html>
                        <head>
                          <meta name="viewport" content="width=device-width, initial-scale=1.0">
                          <style>
                            body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #1e293b; text-align: center; padding: 20px; }
                            .icon { font-size: 48px; margin-bottom: 16px; color: #ef4444; }
                            h2 { margin: 0 0 8px 0; font-size: 18px; }
                            p { margin: 0 0 24px 0; font-size: 14px; color: #64748b; }
                            button { background: #0284c7; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; }
                          </style>
                        </head>
                        <body>
                          <div class="icon">⚠️</div>
                          <h2>Gagal Memuat Halaman</h2>
                          <p>${error.description}<br><small>Pastikan ADB Reverse Aktif jika di Lokal</small></p>
                          <button onclick="window.location.reload()">COBA LAGI</button>
                        </body>
                      </html>
                    """);
                  }
                },
                
                // BAGIAN PENTING: MENGUNCI AGAR SELALU DI DALAM APLIKASI
                shouldOverrideUrlLoading: (controller, navigationAction) async {
                  var uri = navigationAction.request.url;

                  if (uri != null) {
                    // Cek jika scheme-nya http atau https
                    // Segala jenis link http/https yang diklik/diload akan DIPAKSA 
                    // berada di dalam WebView ini (In-App Browser).
                    if (["http", "https"].contains(uri.scheme)) {
                      // ALLOW: Mencegah sistem atau web melempar user ke Chrome / Eksternal
                      return NavigationActionPolicy.ALLOW;
                    }
                  }
                  
                  // Tolak skema selain protokol HTTP / HTTPS 
                  // jika itu bukan bagian dari fitur web normal, 
                  // Tapi kalau butuh mailto: dll, ubah cancel ke hal yang sesuai.
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
