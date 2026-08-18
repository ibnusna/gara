

import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:flutter_windowmanager/flutter_windowmanager.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import '../services/auth_service.dart';


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
  bool   _isPerformingLogout = false; 
  bool   _showOfflineOverlay = false; 
  DateTime? _lastBackPressTime;

  
  bool _showBlackout = false;



  // Path ke halaman error lokal (asset HTML untuk tampilkan error offline)
  final String _errorPageLocalPath =
      'file:///android_asset/flutter_assets/assets/helpers/eror.html';



  
  late final InAppWebViewSettings _webSettings = InAppWebViewSettings(
    useHybridComposition:             true,
    domStorageEnabled:                true,
    databaseEnabled:                  true,
    javaScriptEnabled:                true,
    safeBrowsingEnabled:              true,
    mixedContentMode:                 MixedContentMode.MIXED_CONTENT_ALWAYS_ALLOW,
    cacheEnabled:                     true,
    cacheMode:                        CacheMode.LOAD_DEFAULT,
    
    useShouldOverrideUrlLoading:      true,
    allowFileAccessFromFileURLs:      true,
    allowUniversalAccessFromFileURLs: true,
    allowContentAccess:               true,
    
    mediaPlaybackRequiresUserGesture: false,
    
    allowsInlineMediaPlayback:        true,
    
    javaScriptCanOpenWindowsAutomatically: true,
    supportMultipleWindows:           true,
    useOnDownloadStart:               true,
    transparentBackground:            false,
    supportZoom:                      true,
    thirdPartyCookiesEnabled:         true,
    disableContextMenu:               false,
    applicationNameForUserAgent:      'GARA_OFFICIAL_APP',
    userAgent:                        'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
  );

  

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    _pullToRefreshController = PullToRefreshController(
      settings: PullToRefreshSettings(
        enabled: true,
        color: GaraColors.studentPrimary,
        backgroundColor: GaraColors.studentBgBody,
      ),
      onRefresh: () async => await _webController?.reload(),
    );

    // ── WebView Handoff Bridge: Inject bypass cookies ──────────────────────
    // Sebelum WebView pertama kali load, kita inject cookie __test (InfinityFree
    // bypass token) dan laravel_session (PHP session) ke CookieManager.
    // Cookies ini di-set oleh InfinityAuthService saat login berhasil.
    // Tanpa injection ini, WebView akan mendapat 403 atau redirect ke /login.
    _injectBypassCookiesOnInit();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    if (_isExamModeActive) _deactivateExamSecuritySync();
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

  

  
  bool _isCookieReady = false;

  // ──────────────────────────────────────────────────────────────────────────
  // _injectBypassCookiesOnInit() — WebView Handoff Bridge
  // ──────────────────────────────────────────────────────────────────────────
  Future<void> _injectBypassCookiesOnInit() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
      final sessionCookie = prefs.getString(GaraPrefKeys.bypassSessionCookie) ?? '';

      if (testCookie.isEmpty && sessionCookie.isEmpty) {
        debugPrint('[HybridWrapper] Tidak ada bypass cookies tersimpan.');
      } else {
        final baseUrl = AppConfig.baseUrl;
        final targetUrl = WebUri(baseUrl);
        final cookieMgr = CookieManager.instance();

        if (testCookie.isNotEmpty) {
          await cookieMgr.setCookie(
            url: targetUrl,
            name: '__test',
            value: testCookie,
            isSecure: baseUrl.startsWith('https'),
            isHttpOnly: false,
          );
          debugPrint('[HybridWrapper] Cookie __test ter-inject sukses.');
        }

        if (sessionCookie.isNotEmpty) {
          await cookieMgr.setCookie(
            url: targetUrl,
            name: 'laravel_session',
            value: sessionCookie,
            isSecure: baseUrl.startsWith('https'),
            isHttpOnly: true,
          );
        }
      }
    } catch (e) {
      debugPrint('[HybridWrapper] Cookie injection warning: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isCookieReady = true;
        });
      }
    }
  }


  // ──────────────────────────────────────────────────────────────────────────
  // Exam Security Mode
  // ──────────────────────────────────────────────────────────────────────────
  Future<void> _activateExamMode() async {
    if (_isExamModeActive) {
      
      await _enforceExamSecurity();
      return;
    }
    _isExamModeActive = true;
    if (mounted) setState(() {});

    await _enforceExamSecurity();

    
    
    try { await _kSecurityChannel.invokeMethod('startVolumeWatch'); } catch (_) {}

    debugPrint('[GARA Security] ExamMode ACTIVATED — LockTask + FLAG_SECURE + VolumeWatch + ScreenOn');
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
    } catch (e) {
      debugPrint('[GARA Security] _enforceExamSecurity error: $e');
    }
    
    try {
      await FlutterWindowManager.addFlags(FlutterWindowManager.FLAG_SECURE);
    } catch (_) {}
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
    } catch (e) {
      debugPrint('[GARA Security] _deactivateExamMode error: $e');
    }
    try {
      await FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE);
    } catch (_) {}

    debugPrint('[GARA Security] ExamMode DEACTIVATED — semua flags dibersihkan');
  }

  
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

  
  void _applySecurityForUrl(String urlStr) {
    if (AppConfig.isExamArenaUrl(urlStr)) {
      
      _activateExamMode();
    } else if (AppConfig.isExamResultUrl(urlStr)) {
      
      _deactivateExamMode();
    } else if (_isExamModeActive) {
      
      _deactivateExamMode();
    }
  }

  

  Future<bool> _onPopInvoked() async {
    if (_isExamModeActive) {
      _showExamBlockedSnackBar();
      return false;
    }

    if (_webController != null) {
      final url     = await _webController!.getUrl();
      final urlStr  = url?.toString() ?? '';
      final canBack = await _webController!.canGoBack();

      
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

          // Jika halaman sebelumnya adalah handoff atau login, pop Flutter langsung
          if (backUrl.contains('handoff') || backUrl.contains('/login')) {
            return true;
          }

          // Jika halaman sebelumnya adalah halaman siswa (/student/*),
          // pop Flutter agar kembali ke native app — ini perilaku yang benar.
          // User sudah di titik terdalam web LMS; back harusnya keluar ke Flutter.
          final prevUri = Uri.tryParse(backUrl);
          final prevPath = prevUri?.path ?? '';
          if (prevPath.contains('/student/') || prevPath.endsWith('/student')) {
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
      
      Navigator.pushNamedAndRemoveUntil(context, GaraRoutes.login, (r) => false);
    }
    
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

  
  
  
  
  Future<void> _onReceivedError(
    InAppWebViewController controller,
    WebResourceRequest request,
    WebResourceError error,
  ) async {
    
    if (request.isForMainFrame != true) return;

    final desc    = error.description;
    final urlStr  = request.url.toString();

    
    if (desc.contains('ERR_ABORTED')) return;

    debugPrint('[GARA Error] $desc — URL: $urlStr');

    
    final isConnectivityError = desc.contains('ERR_CONNECTION_REFUSED') ||
        desc.contains('ERR_INTERNET_DISCONNECTED') ||
        desc.contains('ERR_NAME_NOT_RESOLVED') ||
        desc.contains('ERR_NETWORK_CHANGED') ||
        desc.contains('net::ERR');

    final isNonStudentPath = !urlStr.contains('/student/') &&
        !urlStr.contains('/ruang-ujian');

    if (isConnectivityError && isNonStudentPath) {
      
      if (mounted) setState(() => _showOfflineOverlay = true);
      return;
    }

    
    await controller.loadUrl(
      urlRequest: URLRequest(url: WebUri(_errorPageLocalPath)),
    );
  }



  
  
  
  
  
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

  
  
  
  
  
  
  static const String _csrfRefreshJs = """
    (function() {
      if (window.__garaCsrfPatched) return;
      window.__garaCsrfPatched = true;

      
      function getToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
      }

      
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

      
      if (typeof jQuery !== 'undefined') {
        const token = getToken();
        if (token) {
          jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
        }
      }

      
      
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
      
      const observer = new MutationObserver(patchForms);
      observer.observe(document.body, { childList: true, subtree: true });
    })();
  """;

  
  
  
  
  static const String _restoreRoleNavJs = """
    (function() {
      
      document.body.classList.remove('sidebar-collapse');
      document.body.classList.remove('sidebar-closed');
    })();
  """;


  // ──────────────────────────────────────────────────────────────────────────
  // _netlifyLogoutInterceptJs — Netlify Return-to-App Bridge
  // ──────────────────────────────────────────────────────────────────────────
  // Script ini diinjeksikan ke halaman Netlify (summary.html, ujian.html,
  // hasil.html) saat diakses dari dalam Flutter WebView.
  //
  // Fungsinya:
  // 1. Menandai halaman dengan class 'is-gara-official-app' di <html>
  // 2. Menangkap klik pada tombol logout / kembali-beranda
  // 3. Memanggil Flutter handler 'netlifyLogout' agar app bisa kembali ke
  //    dashboard Flutter secara mulus — bukan redirect ke garudakademi.ct.ws
  // ──────────────────────────────────────────────────────────────────────────
  static const String _netlifyLogoutInterceptJs = """
    (function() {
      if (window.__garaNetlifyPatched) return;
      window.__garaNetlifyPatched = true;

      // 1. Tandai halaman sebagai "mode app" agar CSS bisa menyesuaikan diri
      document.documentElement.classList.add('is-gara-official-app');
      document.documentElement.classList.add('is-ruang-ujian');

      // 2. Fungsi helper: kirim sinyal kembali ke Flutter
      function returnToFlutterDashboard(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        // Panggil Flutter handler
        if (window.flutter_inappwebview) {
          window.flutter_inappwebview.callHandler('netlifyLogout');
        }
        return false;
      }

      // 3. Intercept semua tombol / link logout & kembali-beranda
      function patchLogoutButtons() {
        // Selector: button logout header, anchor btn-kembali-beranda, selesai
        var selectors = [
          'a.header-logout-btn',
          '.btn-kembali-beranda',
          'a[href*="index.html"]'
        ];

        selectors.forEach(function(sel) {
          document.querySelectorAll(sel).forEach(function(el) {
            if (!el.dataset.garaPatched) {
              el.dataset.garaPatched = 'true';
              el.addEventListener('click', returnToFlutterDashboard, true);
            }
          });
        });
      }

      // Patch segera + observe DOM untuk elemen yang muncul belakangan
      patchLogoutButtons();
      var observer = new MutationObserver(patchLogoutButtons);
      observer.observe(document.body, { childList: true, subtree: true });

      console.log('[GARA App] Netlify logout bridge: aktif');
    })();
  """;




  
  bool _isStudentUrl(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return false;
    final path = uri.path;
    return path.startsWith('/student/') ||
        path.startsWith('/ruang-ujian') ||
        path == '/student';
  }

  

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) async {
        if (didPop) return;
        final shouldPop = await _onPopInvoked();
        if (shouldPop && mounted) {
          
          
          final canPop = Navigator.of(context).canPop();
          if (canPop) {
            Navigator.of(context).pop();
          }
          
        }
      },
      child: Scaffold(
        backgroundColor: GaraColors.studentBgBody,
        body: SafeArea(
          // bottom: true memastikan konten WebView tidak terpotong oleh
          // OS navigation bar (gesture bar / button bar Android).
          // Ini KRITIS untuk menjaga footer 'Powered by Ibnusz' tetap visible
          // dan tidak tersembunyi di balik navigation bar sistem.
          top: true,
          bottom: true,
          left: false,
          right: false,
          child: Stack(
            children: [
              
              if (!_isCookieReady)
                const Center(
                  child: CircularProgressIndicator(color: GaraColors.primaryLight),
                )
              else
                InAppWebView(
                  initialUrlRequest: URLRequest(url: WebUri(widget.url)),
                  initialSettings: _webSettings,
                  pullToRefreshController: _pullToRefreshController,

                  onWebViewCreated: (c) {
                    _webController = c;

                    // ── GARA: Netlify Logout Bridge Handler ──────────────────
                    // Saat JS di halaman Netlify memanggil:
                    //   window.flutter_inappwebview.callHandler('netlifyLogout')
                    // Flutter akan deactivate exam mode dan kembali ke dashboard.
                    c.addJavaScriptHandler(
                      handlerName: 'netlifyLogout',
                      callback: (args) async {
                        debugPrint('[GARA] netlifyLogout handler dipanggil dari Netlify');
                        await _deactivateExamMode();
                        if (mounted) Navigator.pop(context);
                      },
                    );
                    c.addJavaScriptHandler(
                      handlerName: 'triggerPrint',
                      callback: (args) async {
                        final currentUrl = (await c.getUrl())?.toString();
                        if (currentUrl != null && currentUrl.isNotEmpty) {
                          final uri = Uri.tryParse(currentUrl);
                          if (uri != null) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          }
                        }
                      },
                    );
                    c.addJavaScriptHandler(
                      handlerName: 'blobDownloaded',
                      callback: (args) async {
                        if (args.length >= 2) {
                          final String dataUri = args[0].toString();
                          final String filename = args[1].toString();
                          await _saveAndOpenDataUri(dataUri, filename);
                        }
                      },
                    );
                    // ─────────────────────────────────────────────────────────
                  },

                  onLoadStart: (controller, url) async {
                    if (url == null) return;
                    if (url.toString() == _errorPageLocalPath) return;
                    final urlStr = url.toString();
                    final uri    = Uri.tryParse(urlStr);
                    final path   = uri?.path ?? '';

                    // ── GARA KHUSUS: Netlify /index.html trap ──────────────────
                    // Jika Flutter mendeteksi navigasi ke halaman LOGIN Netlify,
                    // hentikan segera & kembali ke dashboard Flutter.
                    // Ini terjadi saat sesi Netlify habis atau user klik logout.
                    if (AppConfig.isNetlifyIndexTrap(urlStr)) {
                      debugPrint('[GARA] Netlify /index.html trap terdeteksi — kembali ke dashboard');
                      await _deactivateExamMode();
                      // Hentikan navigasi WebView ke trap URL
                      await controller.stopLoading();
                      if (mounted) Navigator.pop(context);
                      return;
                    }
                    // ──────────────────────────────────────────────────────────

                    
                    if (path.endsWith('/logout') && !_isPerformingLogout) {
                      _isPerformingLogout = true;
                      await _performManualLogout();
                      return;
                    }
                    
                    
                    
                    
                    if (path.endsWith('/login') && !urlStr.contains('handoff')) {
                      if (mounted) {
                        Navigator.pushNamedAndRemoveUntil(
                          context,
                          GaraRoutes.login,
                          (route) => false,
                        );
                      }
                      return;
                    }
                    
                    // Saat web navigasi ke /student/dashboard (misal: user tekan back
                    // dari Ruang Belajar), pop WebView dan kembali ke native Flutter app.
                    // Ini adalah behavior yang benar dan disengaja.
                    if (path.endsWith(AppConfig.studentDashboardPath) || path.endsWith('/student/') || path.endsWith('/student')) {
                      if (mounted) Navigator.pop(context);
                      return;
                    }

                    if (mounted) {
                      setState(() {
                        _isLoading = true;
                      });
                    }

                    
                    _applySecurityForUrl(urlStr);
                  },

                  onProgressChanged: (controller, progress) {
                    if (mounted) {
                      if (progress >= 75 && _isLoading) {
                        setState(() {
                          _isLoading = false;
                        });
                      }
                    }
                  },

                  onUpdateVisitedHistory: (controller, url, isReload) async {
                    
                  },

                  onLoadStop: (controller, url) async {
                    if (!mounted) return;

                    try {
                      _pullToRefreshController?.endRefreshing();
                    } catch (e) {
                      debugPrint('Error ending refresh: $e');
                    }
                    
                    final urlStr = url?.toString() ?? '';

                    
                    
                    
                    
                    if (urlStr.contains('/auth/webview-handoff')) {
                      debugPrint('[GARA Handoff] Server will redirect instantly. No force nav needed.');
                      
                      return;
                    }

                    try {
                      
                      await controller.evaluateJavascript(source: """
                        document.body.style.visibility = 'visible';
                        document.body.style.webkitUserSelect = 'none';
                        document.body.style.userSelect = 'none';
                        document.oncontextmenu = function() { return false; };
                      """);

                      
                      
                      
                      await controller.evaluateJavascript(source: _csrfRefreshJs);

                      
                      if (_isStudentUrl(urlStr)) {
                        await controller.evaluateJavascript(source: _hideStudentNavCss);
                      } else {
                        
                        await controller.evaluateJavascript(source: _restoreRoleNavJs);
                      }

                      // ── GARA: Inject Netlify Logout Interceptor ──────────────
                      // Saat halaman Netlify dimuat (summary/ujian/hasil), inject JS
                      // yang menangkap klik pada tombol logout/kembali-beranda dan
                      // mengirimkan sinyal ke Flutter agar bisa kembali ke dashboard.
                      if (urlStr.contains('garudakademi.netlify.app') &&
                          !urlStr.contains('/index.html')) {
                        await controller.evaluateJavascript(source: _netlifyLogoutInterceptJs);
                      }
                      // ────────────────────────────────────────────────────────

                      
                      
                      
                      if (AppConfig.isExamArenaUrl(urlStr)) {
                        await controller.evaluateJavascript(source: """
                          (function() {
                            var audio = document.getElementById('violationAudio');
                            if (audio) {
                              audio.load();
                              
                              var ctx = new (window.AudioContext || window.webkitAudioContext)();
                              if (ctx.state === 'suspended') { ctx.resume(); }
                            }
                          })();
                        """);
                      }

                      
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

                  
                  
                  onDownloadStartRequest: (controller, downloadStartRequest) async {
                    final dlUrl = downloadStartRequest.url.toString();
                    debugPrint('[GARA Download] Download request: $dlUrl');
                    
                    final uri = Uri.tryParse(dlUrl);
                    if (uri != null && ['http', 'https'].contains(uri.scheme.toLowerCase())) {
                      try {
                        await launchUrl(uri, mode: LaunchMode.externalApplication);
                        return;
                      } catch (e) {
                        debugPrint('[GARA Download] launchUrl error: $e');
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
                        debugPrint('[GARA Download] JS blob download error: $e');
                      }
                    } else if (dlUrl.startsWith('data:')) {
                      _saveAndOpenDataUri(dlUrl, filename);
                    }
                  },

                  onCreateWindow: (controller, createWindowAction) async {
                    final reqUrl = createWindowAction.request.url;
                    if (reqUrl != null) {
                      debugPrint('[GARA Window] Popup requested: $reqUrl');
                      if (['http', 'https'].contains(reqUrl.scheme.toLowerCase())) {
                        try {
                          await launchUrl(reqUrl, mode: LaunchMode.externalApplication);
                          return true;
                        } catch (e) {
                          debugPrint('[GARA Window] launchUrl popup error: $e');
                        }
                      }
                    }
                    return false;
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

                  
                  
                  
                  shouldOverrideUrlLoading: (controller, navigationAction) async {
                    final uri = navigationAction.request.url;
                    if (uri == null) return NavigationActionPolicy.ALLOW;

                    final scheme = uri.scheme.toLowerCase();
                    final host   = uri.host.toLowerCase();

                    
                    if (!['http', 'https'].contains(scheme)) {
                      return NavigationActionPolicy.ALLOW;
                    }

                    
                    if (AppConfig.isAllowedDomain(host) ||
                        host.isEmpty) {
                      return NavigationActionPolicy.ALLOW;
                    }

                    
                    debugPrint('[GARA External] Launching externally: ${uri.toString()}');
                    try {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    } catch (e) {
                      debugPrint('Could not launch $uri');
                    }
                    return NavigationActionPolicy.CANCEL;
                  },
                ),

              
              if (_isLoading)
                Positioned.fill(
                  child: AbsorbPointer(
                    child: const _SkeletonOverlay(),
                  ),
                ),

              
              
              
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


class _SkeletonOverlay extends StatefulWidget {
  const _SkeletonOverlay();

  @override
  State<_SkeletonOverlay> createState() => _SkeletonOverlayState();
}

class _SkeletonOverlayState extends State<_SkeletonOverlay> with SingleTickerProviderStateMixin {
  late final AnimationController _shimmerCtrl;

  @override
  void initState() {
    super.initState();
    _shimmerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat();
  }

  @override
  void dispose() {
    _shimmerCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: GaraColors.studentBgBody,
      child: AnimatedBuilder(
        animation: _shimmerCtrl,
        builder: (context, child) {
          return ShaderMask(
            blendMode: BlendMode.srcATop,
            shaderCallback: (bounds) {
              return LinearGradient(
                colors: const [
                  Color(0xFFE8EDF5),
                  Color(0xFFFDFDFD),
                  Color(0xFFE8EDF5),
                ],
                stops: const [0.1, 0.5, 0.9],
                begin: const Alignment(-2.0, -0.3),
                end: const Alignment(2.0, 0.3),
                transform: _SlidingGradientTransform(slidePercent: _shimmerCtrl.value),
              ).createShader(bounds);
            },
            child: child,
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 12),
              
              _bar(double.infinity, 56, radius: 16),
              const SizedBox(height: 30),
              
              _bar(180, 24, radius: 8),
              const SizedBox(height: 20),
              
              _bar(double.infinity, 180, radius: 24),
              const SizedBox(height: 30),
              
              _bar(140, 18, radius: 6),
              const SizedBox(height: 20),
              
              _bar(double.infinity, 90, radius: 20),
              const SizedBox(height: 16),
              _bar(double.infinity, 90, radius: 20),
              const SizedBox(height: 16),
              _bar(double.infinity, 90, radius: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _bar(double width, double height, {double radius = 8}) => Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: const Color(0xFFE8EDF5),
          borderRadius: BorderRadius.circular(radius),
        ),
      );
}

class _SlidingGradientTransform extends GradientTransform {
  final double slidePercent;
  const _SlidingGradientTransform({required this.slidePercent});
  @override
  Matrix4 transform(Rect bounds, {TextDirection? textDirection}) {
    return Matrix4.translationValues(bounds.width * (slidePercent * 3.0 - 1.5), 0.0, 0.0);
  }
}





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
