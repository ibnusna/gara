

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:flutter_windowmanager/flutter_windowmanager.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import '../utils/route_builders.dart';
import '../services/auth_service.dart';
import '../screens/in_app_browser_page.dart';

// ── Platform Channel (ke Kotlin MainActivity) ─────────────────────
const _kSecurityChannel = MethodChannel('com.lms.gara/security');

class HybridWrapper extends StatefulWidget {
  final String url;
  final String pageTitle;
  final bool enableExamMode;

  const HybridWrapper({
    super.key,
    required this.url,
    required this.pageTitle,
    this.enableExamMode = false,
  });

  @override
  State<HybridWrapper> createState() => _HybridWrapperState();
}

class _HybridWrapperState extends State<HybridWrapper>
    with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  InAppWebViewController? _webController;
  PullToRefreshController? _pullToRefreshController;

  bool   _isLoading          = true;
  bool   _isExamModeActive   = false;
  bool   _isPerformingLogout = false; // Guard anti double-logout
  bool   _showOfflineOverlay = false; // Native offline error screen
  DateTime? _lastBackPressTime;

  // Blackout overlay — aktif saat focus hilang dalam exam mode
  bool _showBlackout = false;

  // Skeleton shimmer
  late AnimationController _shimmerCtrl;
  late Animation<double>   _shimmerAnim;

  final String _errorPageLocalPath =
      'file:///android_asset/flutter_assets/assets/helpers/eror.html';



  // ── WebView Settings ─────────────────────────────────────
  late final InAppWebViewSettings _webSettings = InAppWebViewSettings(
    useHybridComposition:             true,
    domStorageEnabled:                true,
    databaseEnabled:                  true,
    javaScriptEnabled:                true,
    safeBrowsingEnabled:              true,
    mixedContentMode:                 MixedContentMode.MIXED_CONTENT_ALWAYS_ALLOW,
    cacheEnabled:                     true,
    cacheMode:                        CacheMode.LOAD_DEFAULT,
    // FIX v9: Aktifkan shouldOverrideUrlLoading untuk intercept external link
    useShouldOverrideUrlLoading:      true,
    allowFileAccessFromFileURLs:      true,
    allowUniversalAccessFromFileURLs: true,
    allowContentAccess:               true,
    // FIX v9: Wajib false agar audio violation dapat diputar tanpa gesture user
    mediaPlaybackRequiresUserGesture: false,
    // FIX v9: iOS — izinkan audio/video inline (tanpa fullscreen native player)
    allowsInlineMediaPlayback:        true,
    // FIX v9: Izinkan JS membuka Audio() object tanpa interaksi user
    javaScriptCanOpenWindowsAutomatically: true,
    transparentBackground:            false,
    supportZoom:                      true,
    thirdPartyCookiesEnabled:         true,
    disableContextMenu:               false,
    applicationNameForUserAgent:      'GARA_OFFICIAL_APP',
  );

  // ── Lifecycle ────────────────────────────────────────────

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    // Shimmer controller
    _shimmerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat(reverse: true);
    _shimmerAnim = Tween<double>(begin: 0.3, end: 0.85).animate(
      CurvedAnimation(parent: _shimmerCtrl, curve: Curves.easeInOut),
    );

    _pullToRefreshController = PullToRefreshController(
      settings: PullToRefreshSettings(
        enabled: true,
        color: GaraColors.studentPrimary,
        backgroundColor: GaraColors.studentBgBody,
      ),
      onRefresh: () async => await _webController?.reload(),
    );
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _shimmerCtrl.dispose();
    if (_isExamModeActive) _deactivateExamSecuritySync();
    super.dispose();
  }

  // ── App Lifecycle Observer ───────────────────────────────
  // Setara dengan onWindowFocusChanged di Kotlin:
  // Tampilkan blackout saat app di-background selama exam mode.
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (!_isExamModeActive) return;
    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused ||
        state == AppLifecycleState.hidden) {
      if (mounted) setState(() => _showBlackout = true);
    } else if (state == AppLifecycleState.resumed) {
      // Re-enforce semua security saat app kembali ke foreground
      _enforceExamSecurity();
      if (mounted) setState(() => _showBlackout = false);
    }
  }

  // ── Exam Security Engine ─────────────────────────────────

  /// Aktifkan seluruh lapisan keamanan ujian via Platform Channel.
  /// Setara dengan activateExamMode() di Kotlin contoh.
  Future<void> _activateExamMode() async {
    if (_isExamModeActive) {
      // Sudah aktif — re-enforce saja
      await _enforceExamSecurity();
      return;
    }
    _isExamModeActive = true;
    if (mounted) setState(() {});

    await _enforceExamSecurity();

    // Mulai VolumeObserver persistent via native channel
    // Setara dengan VolumeObserver inner class di contoh Kotlin
    try { await _kSecurityChannel.invokeMethod('startVolumeWatch'); } catch (_) {}

    debugPrint('[GARA Security] ExamMode ACTIVATED — LockTask + FLAG_SECURE + VolumeWatch + ScreenOn');
  }

  /// Terapkan semua flag keamanan via channel (dipanggil juga saat resume).
  Future<void> _enforceExamSecurity() async {
    try {
      await Future.wait([
        _kSecurityChannel.invokeMethod('addFlagSecure'),
        _kSecurityChannel.invokeMethod('keepScreenOn'),
        _kSecurityChannel.invokeMethod('enforceMaxVolume'),
        _kSecurityChannel.invokeMethod('hideSystemBars'),
        _kSecurityChannel.invokeMethod('startLockTask'),
      ]);
    } catch (e) {
      debugPrint('[GARA Security] _enforceExamSecurity error: $e');
    }
    // Gunakan flutter_windowmanager sebagai fallback
    try {
      await FlutterWindowManager.addFlags(FlutterWindowManager.FLAG_SECURE);
    } catch (_) {}
  }

  /// Nonaktifkan exam mode — lepaskan semua flags.
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
    } catch (e) {
      debugPrint('[GARA Security] _deactivateExamMode error: $e');
    }
    try {
      await FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE);
    } catch (_) {}

    debugPrint('[GARA Security] ExamMode DEACTIVATED — semua flags dibersihkan');
  }

  /// Versi sync untuk dispose() — tidak bisa await di sana.
  void _deactivateExamSecuritySync() {
    try {
      _kSecurityChannel.invokeMethod('clearFlagSecure');
      _kSecurityChannel.invokeMethod('clearKeepScreenOn');
      _kSecurityChannel.invokeMethod('showSystemBars');
      _kSecurityChannel.invokeMethod('stopLockTask');
      _kSecurityChannel.invokeMethod('stopVolumeWatch');
    } catch (_) {}
    try { FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE); } catch (_) {}
  }

  // ── Path-Based Security Router ───────────────────────────
  void _applySecurityForUrl(String urlStr) {
    if (AppConfig.isExamArenaUrl(urlStr)) {
      // /ruang-ujian/arena → STRICT MODE penuh
      _activateExamMode();
    } else if (AppConfig.isExamResultUrl(urlStr)) {
      // /ruang-ujian/hasil → Matikan security, izinkan PDF
      _deactivateExamMode();
    } else if (_isExamModeActive) {
      // Keluar dari ruang ujian → matikan
      _deactivateExamMode();
    }
  }

  // ── Back Button Handler ──────────────────────────────────

  Future<bool> _onPopInvoked() async {
    if (_isExamModeActive) {
      _showExamBlockedSnackBar();
      return false;
    }

    if (_webController != null) {
      final url     = await _webController!.getUrl();
      final urlStr  = url?.toString() ?? '';
      final canBack = await _webController!.canGoBack();

      // Double-tap logout untuk role dashboard (non-siswa)
      if (AppConfig.isRoleDashboardUrl(urlStr)) {
        final now = DateTime.now();
        if (_lastBackPressTime == null ||
            now.difference(_lastBackPressTime!) > const Duration(seconds: 2)) {
          _lastBackPressTime = now;
          _showLogoutHintSnackBar();
          return false;
        } else {
          await _performManualLogout();
          return true;
        }
      }

      if (canBack) {
        final history = await _webController!.getCopyBackForwardList();
        if (history?.list != null &&
            history!.currentIndex != null &&
            history.currentIndex! > 0) {
          final backUrl = history.list![history.currentIndex! - 1].url.toString();
          // Jika halaman sebelumnya adalah handoff/login, jangan goBack ke sana
          // — pop ke native Flutter sebagai gantinya.
          if (backUrl.contains('handoff') || backUrl.contains('/login')) {
            return true;
          }
          // FIX: Jika halaman sebelumnya adalah halaman siswa yang lain
          // (misal Tentang Saya di belakang Ganti Password), tetap pop ke native.
          // Siswa tidak boleh navigate antar halaman WebView — setiap HybridWrapper
          // adalah satu halaman mandiri yang dibuka dari native.
          final prevUri = Uri.tryParse(backUrl);
          final prevPath = prevUri?.path ?? '';
          if (prevPath.startsWith('/student/')) {
            // Halaman sebelumnya masih di student space → pop ke native
            return true;
          }
        }
        await _webController!.goBack();
        return false;
      }
    }
    return true;
  }

  Future<void> _performManualLogout() async {
    await AuthService.logout();
    if (mounted) {
      // Selalu pushNamedAndRemoveUntil — aman untuk halaman root maupun non-root
      Navigator.pushNamedAndRemoveUntil(context, GaraRoutes.login, (r) => false);
    }
    // Reset guard setelah selesai (fallback, seharusnya widget sudah di-unmount)
    _isPerformingLogout = false;
  }

  void _showLogoutHintSnackBar() {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text('Tekan sekali lagi untuk Logout dari sistem.',
          style: GoogleFonts.poppins(fontSize: 12.5)),
      backgroundColor: Colors.redAccent,
      behavior: SnackBarBehavior.floating,
      duration: const Duration(seconds: 2),
    ));
  }

  void _showExamBlockedSnackBar() {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(
        'Ujian sedang berlangsung. Tekan tombol "Selesai" di halaman ujian.',
        style: GoogleFonts.poppins(fontSize: 12.5),
      ),
      backgroundColor: GaraColors.studentPrimary,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      duration: const Duration(seconds: 2),
    ));
  }

  // ── Error Handler ─────────────────────────────────────────
  // FIX v9: Deteksi connectivity error untuk non-siswa → tampilkan native
  // offline overlay dengan tombol "Muat Ulang" dan "Kembali ke Login".
  // Untuk siswa / error lain → fallback ke halaman error lokal HTML.
  Future<void> _onReceivedError(
    InAppWebViewController controller,
    WebResourceRequest request,
    WebResourceError error,
  ) async {
    // Hanya tangkap error halaman utama, abaikan error gambar/script
    if (request.isForMainFrame != true) return;

    final desc    = error.description;
    final urlStr  = request.url.toString();

    // Abaikan error navigasi dibatalkan
    if (desc.contains('ERR_ABORTED')) return;

    debugPrint('[GARA Error] $desc — URL: $urlStr');

    // FIX v9: Tampilkan native overlay untuk connectivity errors pada non-siswa
    final isConnectivityError = desc.contains('ERR_CONNECTION_REFUSED') ||
        desc.contains('ERR_INTERNET_DISCONNECTED') ||
        desc.contains('ERR_NAME_NOT_RESOLVED') ||
        desc.contains('ERR_NETWORK_CHANGED') ||
        desc.contains('net::ERR');

    final isNonStudentPath = !urlStr.contains('/student/') &&
        !urlStr.contains('/ruang-ujian');

    if (isConnectivityError && isNonStudentPath) {
      // Native offline overlay untuk non-siswa
      if (mounted) setState(() => _showOfflineOverlay = true);
      return;
    }

    // Fallback: halaman error lokal untuk siswa / error lain
    await controller.loadUrl(
      urlRequest: URLRequest(url: WebUri(_errorPageLocalPath)),
    );
  }



  // ── CSS Nav Hide — HANYA untuk halaman siswa ─────────────
  // Role lain (guru, operator, kepsek, superadmin) TIDAK disembunyikan
  // karena mereka butuh web nav mereka.
  // CATATAN: Ini adalah layer 2 (fallback). Layer 1 adalah deteksi UA di siswa.blade.php.
  // Class .bottom-nav adalah class aktual di siswa.blade.php.
  static const String _hideStudentNavCss = """
    (function() {
      if (document.getElementById('gara-student-nav-hide')) return;
      const s = document.createElement('style');
      s.id = 'gara-student-nav-hide';
      s.textContent = `
        /* Sembunyikan navigasi web siswa di WebView — Flutter punya BottomNav sendiri */
        .bottom-nav,
        .student-bottom-nav, .bottom-nav-student,
        #studentBottomNav, .siswa-nav-bottom,
        [class*='student-bottom'], [class*='siswa-bottom'] {
          display: none !important;
        }
        /* Hapus spacer bawah yang dibuat untuk bottom nav */
        div[style*='height: 80px'] {
          display: none !important;
        }
        /* Konten tidak perlu padding bawah karena bottom nav sudah hilang */
        .student-content-wrapper, .siswa-content,
        #app-main {
          padding-bottom: 0 !important;
          margin-bottom: 0 !important;
        }
      `;
      document.head.appendChild(s);
    })();
  """;

  // ── CSRF Token Refresh ────────────────────────────────────
  // FIX: Inject ke setiap halaman agar semua form POST/CRUD tidak 419
  // Cara kerja:
  //  1. Fetch CSRF token terbaru dari /sanctum/csrf-cookie
  //  2. Override XMLHttpRequest.setRequestHeader agar SELALU membawa X-CSRF-TOKEN
  //  3. Override fetch() juga (untuk SweetAlert confirm/HTMX)
  static const String _csrfRefreshJs = """
    (function() {
      if (window.__garaCsrfPatched) return;
      window.__garaCsrfPatched = true;

      // Ambil CSRF token dari meta tag (sudah ada di semua layout Laravel)
      function getToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
      }

      // Override XMLHttpRequest untuk menyertakan X-CSRF-TOKEN
      const origOpen = XMLHttpRequest.prototype.open;
      XMLHttpRequest.prototype.open = function(method, url) {
        this.__method = method;
        return origOpen.apply(this, arguments);
      };
      const origSend = XMLHttpRequest.prototype.send;
      XMLHttpRequest.prototype.send = function() {
        const token = getToken();
        const method = (this.__method || '').toUpperCase();
        if (token && ['POST','PUT','PATCH','DELETE'].includes(method)) {
          this.setRequestHeader('X-CSRF-TOKEN', token);
        }
        return origSend.apply(this, arguments);
      };

      // Override fetch() untuk HTMX/SweetAlert confirm AJAX
      const origFetch = window.fetch;
      window.fetch = function(input, init) {
        init = init || {};
        const method = ((init.method || 'GET')).toUpperCase();
        if (['POST','PUT','PATCH','DELETE'].includes(method)) {
          const token = getToken();
          if (token) {
            init.headers = Object.assign({}, init.headers || {}, {
              'X-CSRF-TOKEN': token,
              'X-Requested-With': 'XMLHttpRequest'
            });
          }
        }
        return origFetch.call(this, input, init);
      };

      // Patch jQuery AJAX (digunakan AdminLTE/DataTables)
      if (typeof jQuery !== 'undefined') {
        const token = getToken();
        if (token) {
          jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
        }
      }

      // Patch semua form HTML (termasuk file upload multipart)
      // Pastikan setiap form punya _token input dengan nilai fresh
      function patchForms() {
        const token = getToken();
        if (!token) return;
        document.querySelectorAll('form').forEach(function(form) {
          let input = form.querySelector('input[name="_token"]');
          if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            form.appendChild(input);
          }
          input.value = token;
        });
      }
      patchForms();
      // Jalankan ulang saat ada form baru dimuat (SPA/HTMX)
      const observer = new MutationObserver(patchForms);
      observer.observe(document.body, { childList: true, subtree: true });
    })();
  """;

  // ── Restore Role Nav ─────────────────────────────────────
  // Hanya hapus class 'sidebar-collapse' dari body.
  // AdminLTE akan otomatis re-layout sidebar via JS-nya sendiri.
  // JANGAN override margin/position/display — itu urusan AdminLTE.
  static const String _restoreRoleNavJs = """
    (function() {
      // Hapus class yang di-set AdminLTE saat mobile viewport
      document.body.classList.remove('sidebar-collapse');
      document.body.classList.remove('sidebar-closed');
    })();
  """;




  // Cek apakah URL adalah halaman siswa
  bool _isStudentUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;
    return path.startsWith('/student/') ||
        path.startsWith('/ruang-ujian') ||
        path == '/student';
  }

  // ── BUILD ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) async {
        if (didPop) return;
        final shouldPop = await _onPopInvoked();
        if (shouldPop && mounted) {
          // FIX: Gunakan maybePop() — aman saat HybridWrapper adalah root route
          // Navigator.pop() akan crash jika stack kosong (_history.isNotEmpty assertion)
          final canPop = Navigator.of(context).canPop();
          if (canPop) {
            Navigator.of(context).pop();
          }
          // Jika tidak bisa pop (root route), biarkan — sistem handle back gesture
        }
      },
      child: Scaffold(
        backgroundColor: GaraColors.studentBgBody,
        body: SafeArea(
          child: Stack(
            children: [
              // ── WebView ─────────────────────────────────
              InAppWebView(
                initialUrlRequest: URLRequest(url: WebUri(widget.url)),
                initialSettings: _webSettings,
                pullToRefreshController: _pullToRefreshController,

                onWebViewCreated: (c) => _webController = c,

                onLoadStart: (controller, url) async {
                  if (url == null) return;
                  if (url.toString() == _errorPageLocalPath) return;
                  final urlStr = url.toString();
                  final uri    = Uri.tryParse(urlStr);
                  final path   = uri?.path ?? '';

                  // ── Logout Interceptor ──────────────────
                  // Guard: Cegah double-execution jika tombol logout diklik 2x
                  if (path == '/logout' && !_isPerformingLogout) {
                    _isPerformingLogout = true;
                    await _performManualLogout();
                    return;
                  }
                  // Post-logout redirect ke /login (bukan handoff)
                  // FIX v8: Gunakan path == '/login' (bukan contains) untuk mencegah
                  // false-positive saat URL mengandung 'login' sebagai substring
                  // (misal: query param, atau path internal seperti /settings/login-policy)
                  if (path == '/login' && !urlStr.contains('handoff')) {
                    if (mounted) {
                      Navigator.pushNamedAndRemoveUntil(
                        context,
                        GaraRoutes.login,
                        (route) => false,
                      );
                    }
                    return;
                  }
                  // Student dashboard intercept — kembali ke native dashboard
                  // FIX v8: Gunakan path comparison (bukan endsWith) untuk akurasi
                  if (path == AppConfig.studentDashboardPath || path == '/student/') {
                    // FIX: pop() aman karena siswa masuk via Navigator.push
                    if (mounted) Navigator.pop(context);
                    return;
                  }

                  if (mounted) {
                    setState(() {
                      _isLoading = true;
                    });
                  }

                  // ── Path-Based Exam Security ─────────────
                  _applySecurityForUrl(urlStr);
                },

                onProgressChanged: (controller, progress) {
                  if (mounted) {
                    setState(() {
                      if (progress == 100) _isLoading = false;
                    });
                  }
                },

                onUpdateVisitedHistory: (controller, url, isReload) async {
                  // History tracking could be done here if needed
                },

                onLoadStop: (controller, url) async {
                  if (!mounted) return;

                  try {
                    _pullToRefreshController?.endRefreshing();
                  } catch (e) {
                    debugPrint('Error ending refresh: $e');
                  }
                  
                  final urlStr = url?.toString() ?? '';

                  // ── Handoff (Sekarang server redirect langsung) ─────
                  // Server sudah redirect() ke target tanpa HTML bridge.
                  // Tidak perlu force navigation manual — biarkan redirect berjalan.
                  // Handler ini hanya sebagai guard jika ada sisa handoff URL.
                  if (urlStr.contains('/auth/webview-handoff')) {
                    debugPrint('[GARA Handoff] Server will redirect instantly. No force nav needed.');
                    // Tidak perlu melakukan apa-apa — server sudah redirect
                    return;
                  }

                  try {
                    // ── Security Injection ───────────────────
                    await controller.evaluateJavascript(source: """
                      document.body.style.visibility = 'visible';
                      document.body.style.webkitUserSelect = 'none';
                      document.body.style.userSelect = 'none';
                      document.oncontextmenu = function() { return false; };
                    """);

                    // ── CSRF Token Refresh Injection ─────────
                    // FIX: Refresh CSRF token untuk mencegah 419 di form POST/CRUD
                    // Juga fix XMLHttpRequest & fetch untuk menyertakan X-CSRF-TOKEN header
                    await controller.evaluateJavascript(source: _csrfRefreshJs);

                    // ── Nav Hide: HANYA halaman siswa ────────
                    if (_isStudentUrl(urlStr)) {
                      await controller.evaluateJavascript(source: _hideStudentNavCss);
                    } else {
                      // ── Restore Nav: Pastikan sidebar/header role lain terlihat
                      await controller.evaluateJavascript(source: _restoreRoleNavJs);
                    }

                    // ── FIX v9: Audio Preload untuk Exam Arena ──
                    // Paksa browser muat audio pelanggaran.mp3 agar siap diputar
                    // segera tanpa memerlukan gesture user.
                    if (AppConfig.isExamArenaUrl(urlStr)) {
                      await controller.evaluateJavascript(source: """
                        (function() {
                          var audio = document.getElementById('violationAudio');
                          if (audio) {
                            audio.load();
                            // Unlock AudioContext untuk iOS/Android WebView
                            var ctx = new (window.AudioContext || window.webkitAudioContext)();
                            if (ctx.state === 'suspended') { ctx.resume(); }
                          }
                        })();
                      """);
                    }

                    // Tambahkan jembatan JS agar tombol "Coba Lagi" di eror.html bisa jalan
                    if (urlStr == _errorPageLocalPath) {
                      await controller.evaluateJavascript(source: '''
                        window.AndroidInterface = {
                          retryLastUrl: function() {
                            window.flutter_inappwebview.callHandler('retryLastUrl');
                          }
                        };
                      ''');
                    }
                  } catch (e) {
                    debugPrint('Error evaluating javascript: $e');
                  }
                },

                // ── PDF Download Handler ─────────────────
                // Blob URL dari jsPDF → decode via JS → simpan file
                onDownloadStartRequest: (controller, downloadStartRequest) async {
                  final dlUrl = downloadStartRequest.url.toString();
                  debugPrint('[GARA Download] PDF download: $dlUrl');
                  // Trigger download via JS untuk blob URL
                  await controller.evaluateJavascript(source: """
                    (function() {
                      var a = document.createElement('a');
                      a.href = '${dlUrl.replaceAll("'", "\\'")}';
                      a.download = 'Bukti_Ujian_GARA.pdf';
                      document.body.appendChild(a);
                      a.click();
                      document.body.removeChild(a);
                    })();
                  """);
                },

                onReceivedError: (controller, request, error) {
                  _onReceivedError(controller, request, error);
                },

                onReceivedHttpError: (controller, request, response) {
                  debugPrint('[GARA HTTP] ${response.statusCode} — ${request.url}');
                },

                onConsoleMessage: (controller, msg) {
                  debugPrint('[GARA Console] ${msg.message}');
                },

                // ── FIX v9: External Link Interceptor ───────────────────────
                // Izinkan navigasi HANYA untuk domain GARA.
                // Semua link eksternal (YouTube, Drive, PDF) dibuka di InAppBrowserPage.
                shouldOverrideUrlLoading: (controller, navigationAction) async {
                  final uri = navigationAction.request.url;
                  if (uri == null) return NavigationActionPolicy.ALLOW;

                  final scheme = uri.scheme.toLowerCase();
                  final host   = uri.host.toLowerCase();

                  // Izinkan: bukan http/https (blob, data, file, javascript)
                  if (!['http', 'https'].contains(scheme)) {
                    return NavigationActionPolicy.ALLOW;
                  }

                  // Izinkan: domain GARA (localhost, 127.0.0.1, domain produksi)
                  if (AppConfig.isAllowedDomain(host) ||
                      host.isEmpty) {
                    return NavigationActionPolicy.ALLOW;
                  }

                  // Buka di external app/browser untuk domain eksternal
                  debugPrint('[GARA External] Launching externally: ${uri.toString()}');
                  try {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  } catch (e) {
                    debugPrint('Could not launch $uri');
                  }
                  return NavigationActionPolicy.CANCEL;
                },
              ),

              // ── Skeleton Loading Overlay ─────────────────
              if (_isLoading)
                Positioned.fill(
                  child: AbsorbPointer(
                    child: _SkeletonOverlay(shimmerAnim: _shimmerAnim),
                  ),
                ),

              // ── Blackout Overlay (Exam Focus Lost) ───────
              // Setara dengan onWindowFocusChanged di Kotlin:
              // Tampilkan layar hitam saat app kehilangan focus dalam exam mode.
              if (_showBlackout && _isExamModeActive)
                Positioned.fill(
                  child: Container(
                    color: Colors.black,
                    child: Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.lock_rounded,
                              color: Colors.white, size: 64),
                          const SizedBox(height: 16),
                          Text(
                            'Ujian sedang berlangsung',
                            style: GoogleFonts.poppins(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Kembali ke aplikasi untuk melanjutkan',
                            style: GoogleFonts.poppins(
                                color: Colors.white70, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

              // ── FIX v9: Native Offline Overlay (Non-Siswa) ──────────────
              // Tampil jika non-siswa mengalami connectivity error.
              if (_showOfflineOverlay)
                Positioned.fill(
                  child: _OfflineOverlay(
                    onRetry: () {
                      setState(() => _showOfflineOverlay = false);
                      _webController?.reload();
                    },
                    onLogout: () async {
                      setState(() => _showOfflineOverlay = false);
                      await AuthService.logout();
                      if (mounted) {
                        Navigator.pushNamedAndRemoveUntil(
                          context, GaraRoutes.login, (r) => false);
                      }
                    },
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Skeleton Overlay Widget ───────────────────────────────────────
class _SkeletonOverlay extends StatelessWidget {
  final Animation<double> shimmerAnim;
  const _SkeletonOverlay({required this.shimmerAnim});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: shimmerAnim,
      builder: (_, __) {
        final shimmerColor = Color.lerp(
          const Color(0xFFE8EDF5),
          const Color(0xFFC8D4E8),
          shimmerAnim.value,
        )!;
        final highlightColor = Color.lerp(
          const Color(0xFFEEF3FB),
          const Color(0xFFDDE6F6),
          shimmerAnim.value,
        )!;

        return Container(
          color: GaraColors.studentBgBody,
          padding: const EdgeInsets.all(16),
          child: SingleChildScrollView(
            physics: const NeverScrollableScrollPhysics(),
            child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 12),
              // App Bar Skeleton
              _bar(highlightColor, double.infinity, 56, radius: 8),
              const SizedBox(height: 24),
              
              // Page Title Skeleton
              _bar(highlightColor, 180, 20, radius: 6),
              const SizedBox(height: 16),
              
              // Hero Banner / Main Content Box
              _bar(shimmerColor, double.infinity, 160, radius: 16),
              const SizedBox(height: 24),
              
              // Subtitle Skeleton
              _bar(highlightColor, 120, 16, radius: 4),
              const SizedBox(height: 16),
              
              // List Items (Generic Cards)
              _bar(shimmerColor, double.infinity, 80, radius: 12),
              const SizedBox(height: 12),
              _bar(shimmerColor, double.infinity, 80, radius: 12),
              const SizedBox(height: 12),
              _bar(shimmerColor, double.infinity, 80, radius: 12),
            ],
          ),
          ),
        );
      },
    );
  }

  Widget _bar(Color color, double width, double height, {double radius = 8}) =>
      Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(radius),
        ),
      );
}

// ── Native Offline Overlay (Non-Siswa) ───────────────────────────
// FIX v9: Ditampilkan saat non-siswa mengalami connectivity error di WebView.
// Memberikan pilihan Muat Ulang atau Kembali ke Login.
class _OfflineOverlay extends StatelessWidget {
  final VoidCallback onRetry;
  final VoidCallback onLogout;

  const _OfflineOverlay({required this.onRetry, required this.onLogout});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF8FAFC),
      child: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Ikon
                Container(
                  width: 88,
                  height: 88,
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFE4E6),
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
                  'Tidak dapat terhubung ke server GARA.\nPeriksa koneksi internet Anda dan coba lagi.',
                  style: GoogleFonts.poppins(
                    fontSize: 13.5,
                    color: const Color(0xFF64748B),
                    height: 1.55,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                // Tombol Muat Ulang
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: onRetry,
                    icon: const Icon(Icons.refresh_rounded, size: 18),
                    label: Text('Muat Ulang',
                        style: GoogleFonts.poppins(
                            fontWeight: FontWeight.w600, fontSize: 14)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: GaraColors.studentPrimary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                      elevation: 0,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                // Tombol Kembali ke Login
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: onLogout,
                    icon: const Icon(Icons.logout_rounded, size: 18),
                    label: Text('Kembali ke Login',
                        style: GoogleFonts.poppins(
                            fontWeight: FontWeight.w600, fontSize: 14)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFDC2626),
                      side: const BorderSide(color: Color(0xFFDC2626)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
