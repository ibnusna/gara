

import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import '../services/auth_service.dart';
import '../services/device_security_service.dart';
import '../services/handoff_loader_service.dart';
import '../services/in_app_oauth_service.dart';
import '../services/authenticated_download_service.dart';
import '../utils/gara_error_mapper.dart';
import '../screens/session_expired_page.dart';
import 'browser/browser_settings_factory.dart';
import 'gara_error_view.dart';

const _kSecurityChannel = MethodChannel('com.lms.gara/security');

class HybridWrapper extends StatefulWidget {
  final String url;
  final String? targetPath;
  final String pageTitle;
  final bool enableExamMode;
  final String? mapelId;

  const HybridWrapper({
    super.key,
    required this.url,
    this.targetPath,
    required this.pageTitle,
    this.enableExamMode = false,
    this.mapelId,
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
  bool   _isRetrying         = false; // Mencegah Android error flash saat retry
  DateTime? _lastBackPressTime;

  
  bool _showBlackout = false;





  // Seluruh error handling menggunakan Flutter overlay (_showOfflineOverlay).
  // Tidak ada lagi load ke file HTML lokal (eror.html) karena menyebabkan
  // Android default error page terlihat sebentar sebelum redirect.
  
  late final InAppWebViewSettings _webSettings = BrowserSettingsFactory.createHardenedSettings(
    isExam: widget.enableExamMode || AppConfig.isExamUrl(widget.url),
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

    _resolveAndInit();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    if (_isExamModeActive) _deactivateExamSecuritySync();
    super.dispose();
  }

  
  
  
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (_isExamModeActive) {
      if (state == AppLifecycleState.inactive ||
          state == AppLifecycleState.paused ||
          state == AppLifecycleState.hidden) {
        if (mounted) setState(() => _showBlackout = true);
      } else if (state == AppLifecycleState.resumed) {
        _enforceExamSecurity();
        if (mounted) setState(() => _showBlackout = false);
      }
      return;
    }

    if (state == AppLifecycleState.resumed) {
      // Auto-reload when returning from in-app OAuth tab (EP-07)
      _webController?.reload();
    }
  }

  

  
  String? _resolvedUrl;
  bool _isCookieReady = false;
  int _cookieRetryCount = 0;

  Future<void> _resolveAndInit() async {
    DeviceSecurityService.initialize();
    DeviceSecurityService.onViolationCallback = (reason) {
      if (_isExamModeActive && mounted) {
        setState(() => _showBlackout = true);
      }
    };

    final isExam = widget.enableExamMode || AppConfig.isExamArenaUrl(widget.url);
    if (isExam) {
      final report = await DeviceSecurityService.checkDeviceIntegrity();
      if (report.hasViolation) {
        if (mounted) {
          _showSecurityViolationDialog();
        }
        return;
      }
    }

    await _injectBypassCookiesOnInit();
    final resolved = await HandoffLoaderService.resolveHandoffUrl(
      rawUrl: widget.url,
      targetPath: widget.targetPath,
      mapelId: widget.mapelId,
    );

    if (mounted) {
      setState(() {
        _resolvedUrl = resolved;
        _isCookieReady = true;
      });
      if (_webController != null) {
        _webController!.loadUrl(urlRequest: URLRequest(url: WebUri(resolved)));
      }
    }
  }

  void _showSecurityViolationDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Peringatan Keamanan Ujian'),
        content: const Text(
          'Perangkat tidak memenuhi syarat keamanan ujian digital (terdeteksi root atau perekaman layar aktif). Akses ujian dibatalkan demi integritas akademik.',
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              if (mounted) Navigator.of(context).pop();
            },
            child: const Text('Kembali'),
          ),
        ],
      ),
    );
  }

  Future<void> _injectBypassCookiesOnInit() async {
    try {
      await BrowserSettingsFactory.injectBypassCookie();
      final prefs = await SharedPreferences.getInstance();
      final sessionCookie = prefs.getString(GaraPrefKeys.bypassSessionCookie) ?? '';
      if (sessionCookie.isNotEmpty) {
        final baseUrl = AppConfig.baseUrl;
        await CookieManager.instance().setCookie(
          url: WebUri(baseUrl),
          name: 'laravel_session',
          value: sessionCookie,
          path: '/',
          isSecure: baseUrl.startsWith('https'),
          isHttpOnly: true,
        );
      }
    } catch (e) {
      debugPrint('[HybridWrapper] Cookie injection warning: $e');
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

    final desc   = error.description;
    final urlStr = request.url.toString();

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
      // Tampilkan overlay Flutter — jangan load error page Android/HTML
      if (mounted) {
        setState(() {
          _showOfflineOverlay = true;
          _isRetrying = false; // Reset flag retry jika error terjadi lagi
        });
      }
      return;
    }

    // Untuk error non-connectivity (mis. 404, 500), tampilkan overlay juga
    // agar tidak ada halaman error Android yang terekspos.
    if (mounted) {
      setState(() {
        _showOfflineOverlay = true;
        _isRetrying = false;
      });
    }
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
        if (shouldPop && context.mounted) {
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
                  initialUrlRequest: URLRequest(url: WebUri(_resolvedUrl ?? widget.url)),
                  initialSettings: _webSettings,
                  pullToRefreshController: _pullToRefreshController,

                  onWebViewCreated: (c) {
                    _webController = c;


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
                    c.addJavaScriptHandler(
                      handlerName: 'passwordChanged',
                      callback: (args) {
                        debugPrint('[HybridWrapper] Password changed event received.');
                        final isRoleDash = widget.url.contains('/guru') ||
                            widget.url.contains('/operator') ||
                            widget.url.contains('/kepsek') ||
                            widget.url.contains('/super-admin');
                        if (!isRoleDash && mounted && Navigator.canPop(context)) {
                          Navigator.pop(context, true);
                        }
                      },
                    );
                    // ─────────────────────────────────────────────────────────
                  },

                  onLoadStart: (controller, url) async {
                    if (url == null) return;
                    final urlStr = url.toString();
                    final uri    = Uri.tryParse(urlStr);
                    final path   = uri?.path ?? '';



                    
                    if (path.endsWith('/logout') && !_isPerformingLogout) {
                      _isPerformingLogout = true;
                      await _performManualLogout();
                      return;
                    }
                    
                    
                    
                    
                    if (path.endsWith('/login') && !urlStr.contains('handoff')) {
                      // Server me-redirect ke /login = sesi expired (mis. login di perangkat lain).
                      // Tampilkan halaman informatif, bukan silent redirect.
                      await controller.stopLoading();
                      if (context.mounted) {
                        Navigator.pushAndRemoveUntil(
                          context,
                          PageRouteBuilder(
                            pageBuilder: (_, __, ___) => const SessionExpiredPage(),
                            transitionsBuilder: (_, anim, __, child) =>
                                FadeTransition(opacity: anim, child: child),
                            transitionDuration: const Duration(milliseconds: 350),
                          ),
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

                    // Jika sedang dalam proses retry, sembunyikan overlay
                    // HANYA setelah WebView benar-benar berhasil load.
                    // Ini mencegah Android error page terlihat saat retry.
                    if (_isRetrying) {
                      if (mounted) {
                        setState(() {
                          _showOfflineOverlay = false;
                          _isRetrying = false;
                        });
                      }
                    }

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

                    } catch (e) {
                      debugPrint('Error evaluating javascript: $e');
                    }
                  },

                  
                  
                  onDownloadStartRequest: (controller, downloadStartRequest) async {
                    final dlUrl = downloadStartRequest.url.toString();
                    debugPrint('[GARA Download] Download request: $dlUrl');

                    if (_isExamModeActive || widget.enableExamMode) {
                      debugPrint('[GARA Download] Blocked during exam mode.');
                      return;
                    }

                    final uri = Uri.tryParse(dlUrl);
                    if (uri != null && ['http', 'https'].contains(uri.scheme.toLowerCase())) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Mengunduh berkas...'),
                            duration: Duration(seconds: 2),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                      final result = await AuthenticatedDownloadService.downloadFromWebView(
                        url: uri,
                        suggestedFilename: downloadStartRequest.suggestedFilename,
                        userAgent: downloadStartRequest.userAgent,
                      );
                      if (!context.mounted) return;
                      if (result.success && result.filePath != null) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Berhasil mengunduh: ${result.fileName}'),
                            action: SnackBarAction(
                              label: 'Buka',
                              onPressed: () => AuthenticatedDownloadService.openFile(result.filePath!),
                            ),
                            duration: const Duration(seconds: 6),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(result.errorMessage ?? 'Gagal mengunduh berkas (${result.errorCode ?? 'ERR-DL-002'}).'),
                            backgroundColor: Colors.red.shade700,
                            duration: const Duration(seconds: 4),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                      return;
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
                        if (BrowserSettingsFactory.isInfinityCookieErrorUrl(reqUrl)) {
                          return false;
                        }
                        final host = reqUrl.host.toLowerCase();
                        if (AppConfig.isAllowedDomain(host)) {
                          controller.loadUrl(urlRequest: URLRequest(url: reqUrl));
                          return true;
                        }
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
                    final urlStr = uri.toString();
                    final isExam = _isExamModeActive || widget.enableExamMode || AppConfig.isExamArenaUrl(urlStr);

                    if (scheme == 'whatsapp' || urlStr.startsWith('https://wa.me/')) {
                      if (isExam) {
                        return NavigationActionPolicy.CANCEL;
                      }
                      try {
                        await launchUrl(uri, mode: LaunchMode.externalApplication);
                      } catch (_) {}
                      return NavigationActionPolicy.CANCEL;
                    }

                    if (!['http', 'https'].contains(scheme)) {
                      return isExam ? NavigationActionPolicy.CANCEL : NavigationActionPolicy.ALLOW;
                    }

                    if (BrowserSettingsFactory.isInfinityCookieErrorUrl(uri)) {
                      debugPrint('[GARA] Blocked InfinityFree cookies.html redirect; recovering cookie...');
                      if (_cookieRetryCount < 2) {
                        _cookieRetryCount++;
                        BrowserSettingsFactory.recoverInfinityCookie(controller, widget.url);
                      }
                      return NavigationActionPolicy.CANCEL;
                    }

                    if (!isExam && InAppOAuthService.isGoogleOAuthUrl(urlStr)) {
                      String authUrl = urlStr;
                      if (!authUrl.contains('source=mobile_app')) {
                        final sep = authUrl.contains('?') ? '&' : '?';
                        authUrl = '$authUrl${sep}source=mobile_app';
                      }
                      InAppOAuthService.launchGoogleWorkspaceAuth(
                        authUrl: authUrl,
                        context: context,
                      );
                      return NavigationActionPolicy.CANCEL;
                    }

                    if (AppConfig.isAllowedDomain(host) || host.isEmpty) {
                      return NavigationActionPolicy.ALLOW;
                    }

                    if (isExam) {
                      debugPrint('[GARA Exam] Navigation to external domain blocked: $urlStr');
                      return NavigationActionPolicy.CANCEL;
                    }

                    debugPrint('[GARA External] Launching externally: $urlStr');
                    try {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    } catch (e) {
                      debugPrint('Could not launch $uri');
                    }
                    return NavigationActionPolicy.CANCEL;
                  },
                ),

              
              if (_isLoading)
                const Positioned.fill(
                  child: AbsorbPointer(
                    child: _SkeletonOverlay(),
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
                    isRetrying: _isRetrying,
                    onRetry: () {
                      // Set _isRetrying = true agar overlay TIDAK dihapus
                      // sampai WebView berhasil load (onLoadStop).
                      // Ini mencegah flash halaman error Android.
                      if (mounted) {
                        setState(() => _isRetrying = true);
                      }
                      _webController?.reload();
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
  final bool isRetrying;

  const _OfflineOverlay({
    required this.onRetry,
    this.isRetrying = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF8FAFC),
      child: SafeArea(
        child: GaraErrorView(
          error: const GaraUserError(
            title: 'Koneksi Terputus',
            userMessage: 'Periksa jaringan Wi-Fi atau data seluler Anda, lalu coba kembali.',
            errorCode: 'ERR-NET-001',
            icon: Icons.wifi_off_rounded,
          ),
          onRetry: onRetry,
        ),
      ),
    );
  }
}
