import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/activation_service.dart';
import '../services/update_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import 'activation_page.dart';
import 'status_page.dart';
import 'update_page.dart';
import 'welcome_page.dart';
import 'pilih_mapel_page.dart';
import '../widgets/hybrid_wrapper.dart';

class SplashLogicPage extends StatefulWidget {
  const SplashLogicPage({super.key});

  @override
  State<SplashLogicPage> createState() => _SplashLogicPageState();
}

class _SplashLogicPageState extends State<SplashLogicPage>
    with TickerProviderStateMixin {
  final ActivationService _activationService = ActivationService();
  final UpdateService _updateService = UpdateService();

  // Entry animation
  late final AnimationController _animCtrl;
  late final Animation<double> _scaleAnim;
  late final Animation<double> _fadeAnim;
  late final Animation<double> _pulseAnim;

  // Mesh background animators (same as PilihMapelPage)
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  @override
  void initState() {
    super.initState();

    // --- Entry animations ---
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    );
    _fadeAnim = CurvedAnimation(
      parent: _animCtrl,
      curve: const Interval(0.0, 0.4, curve: Curves.easeIn),
    );
    _scaleAnim = Tween<double>(begin: 0.75, end: 1.0).animate(
      CurvedAnimation(
        parent: _animCtrl,
        curve: const Interval(0.0, 0.6, curve: Curves.elasticOut),
      ),
    );
    _pulseAnim = Tween<double>(begin: 1.0, end: 1.05).animate(
      CurvedAnimation(
        parent: _animCtrl,
        curve: const Interval(0.6, 1.0, curve: Curves.easeInOutSine),
      ),
    );

    // --- Mesh blob animators ---
    _blobCtrl1 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 25000))
      ..repeat(reverse: true);
    _blobCtrl2 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 22000))
      ..repeat(reverse: true);
    _blobCtrl3 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 28000))
      ..repeat(reverse: true);
    _blobCtrl2.value = 0.2;
    _blobCtrl3.value = 0.4;

    _animCtrl.forward();
    _initializeApp();
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    super.dispose();
  }

  Future<void> _initializeApp() async {
    final startTime = DateTime.now();

    try {
      // Clean old APK without awaiting — fire & forget
      unawaited(_updateService.cleanOldApk());

      final isActivated = await _activationService.isActivated();
      if (!isActivated) {
        _navigateTo(const ActivationPage());
        return;
      }

      final connectivityResult = await Connectivity()
          .checkConnectivity()
          .timeout(const Duration(milliseconds: 500),
              onTimeout: () => [ConnectivityResult.none]);

      if (connectivityResult.contains(ConnectivityResult.none)) {
        await _ensureMinDurationAndNavigate(startTime);
        return;
      }

      final prefs = await SharedPreferences.getInstance();
      final savedBaseUrl = prefs.getString(ActivationService.keyBaseUrl);
      if (savedBaseUrl != null && savedBaseUrl.isNotEmpty) {
        AppConfig.overrideBaseUrl = savedBaseUrl;
      }

      final schoolKey = prefs.getString(ActivationService.keySchoolKey);
      final schoolName =
          prefs.getString(ActivationService.keySchoolName) ?? "Sekolah Anda";

      if (schoolKey != null) {
        if (schoolKey.toUpperCase() == 'HITAMPEKAT') {
          final isUpdating = await _checkAndUpdateIfNeeded();
          if (isUpdating) return;
          await _ensureMinDurationAndNavigate(startTime);
          return;
        }
        try {
          final doc = await FirebaseFirestore.instance
              .collection('schools')
              .doc(schoolKey)
              .get()
              .timeout(const Duration(milliseconds: 800));

          if (doc.exists) {
            final data = doc.data()!;
            final status = data['status'] as String? ?? 'active';

            if (status == 'maintenance' || status == 'suspend') {
              _navigateTo(StatusPage(status: status, schoolName: schoolName));
              return;
            }
            if (data['url'] != null) {
              await prefs.setString(ActivationService.keyBaseUrl, data['url']);
            }
          }
        } catch (e) {
          debugPrint("Cek status sekolah timeout/gagal: $e");
        }
      }

      // --- UPDATE CHECK FOR ALL OTHER BRANCHES ---
      final isUpdating = await _checkAndUpdateIfNeeded();
      if (isUpdating) return;
    } catch (e) {
      debugPrint("Splash init error: $e");
    }

    await _ensureMinDurationAndNavigate(startTime);
  }

  /// Single unified update check method
  Future<bool> _checkAndUpdateIfNeeded() async {
    try {
      await _updateService
          .initialize()
          .timeout(const Duration(milliseconds: 3000));
      final hasUpdate = await _updateService.isUpdateAvailable();
      final releaseNotes = _updateService.getReleaseNotes();
      final latestVersion = _updateService.getLatestVersion();

      if (hasUpdate && _updateService.isForceUpdate()) {
        // Update wajib \u2014 tidak bisa ditutup, user HARUS update
        debugPrint("[Splash] PEMBARUAN WAJIB (v$latestVersion) \u2014 Navigasi ke UpdatePage (force).");
        _navigateTo(UpdatePage(
          isForceUpdate: true,
          releaseNotes: releaseNotes,
          latestVersion: latestVersion,
        ));
        return true;
      } else if (hasUpdate && !_updateService.isForceUpdate()) {
        // Update tersedia tapi tidak wajib \u2014 tampilkan halaman update dengan tombol 'Nanti Saja'
        debugPrint("[Splash] PEMBARUAN OPSIONAL (v$latestVersion) \u2014 Navigasi ke UpdatePage (non-force).");
        _navigateTo(UpdatePage(
          isForceUpdate: false,
          releaseNotes: releaseNotes,
          latestVersion: latestVersion,
        ));
        return true;
      }
    } catch (e) {
      debugPrint("[Splash] Cek update timeout/error: $e");
    }
    return false;
  }

  Future<void> _ensureMinDurationAndNavigate(DateTime startTime) async {
    final elapsed = DateTime.now().difference(startTime).inMilliseconds;
    const targetMinMs = 600;
    if (elapsed < targetMinMs) {
      await Future.delayed(Duration(milliseconds: targetMinMs - elapsed));
    }

    await _checkSessionAndNavigate();
  }

  Future<void> _checkSessionAndNavigate() async {
    final prefs = await SharedPreferences.getInstance();
    final isLoggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;

    if (isLoggedIn) {
      final role = prefs.getString(GaraPrefKeys.userRole) ?? GaraRoles.siswa;
      final token = prefs.getString(GaraPrefKeys.authToken) ?? '';

      if (token.isNotEmpty) {
        if (role == GaraRoles.siswa) {
          _navigateTo(const PilihMapelPage());
        } else {
          final handoffUrl = AppConfig.getHandoffDashboardUrl(
            token: token,
            role: role,
          );
          _navigateTo(HybridWrapper(
            url: handoffUrl,
            pageTitle: 'Dashboard ${role.toUpperCase()}',
          ));
        }
        return;
      }
    }

    _navigateTo(const GaraWelcomePage());
  }

  void _navigateTo(Widget page) {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (_, __, ___) => page,
        transitionsBuilder: (_, anim, __, child) =>
            FadeTransition(opacity: anim, child: child),
        transitionDuration: const Duration(milliseconds: 300),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      body: Stack(
        children: [
          // ── Animated Gradient Mesh Background (identical to PilihMapelPage) ──
          Positioned.fill(
            child: AnimatedBuilder(
              animation:
                  Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
              builder: (context, _) => CustomPaint(
                painter: _SplashMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                ),
              ),
            ),
          ),

          // ── Centered Content ──
          Center(
            child: FadeTransition(
              opacity: _fadeAnim,
              child: AnimatedBuilder(
                animation: _animCtrl,
                builder: (context, child) {
                  final scale = _scaleAnim.value * _pulseAnim.value;
                  return Transform.scale(scale: scale, child: child);
                },
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // ── GARA Blue Logo ──
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(28),
                        boxShadow: [
                          BoxShadow(
                            color:
                                GaraColors.dsPrimaryDeep.withOpacity(0.18),
                            blurRadius: 32,
                            spreadRadius: 2,
                            offset: const Offset(0, 10),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(28),
                        child: Container(
                          width: 110,
                          height: 110,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(28),
                          ),
                          padding: const EdgeInsets.all(16),
                          child: ColorFiltered(
                            colorFilter: const ColorFilter.mode(
                              GaraColors.dsPrimaryDeep,
                              BlendMode.srcIn,
                            ),
                            child: Image.asset(
                              'assets/images/FARA_BLACK.png',
                              fit: BoxFit.contain,
                            ),
                          ),
                        ),
                      ),
                    ),

                    const SizedBox(height: 28),

                    // ── App Name ──
                    Text(
                      'GARA',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 30,
                        fontWeight: FontWeight.w900,
                        color: GaraColors.dsSlate800,
                        letterSpacing: 5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Garuda Akademi Mobile',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: GaraColors.dsSlate500,
                        letterSpacing: 1.2,
                      ),
                    ),

                    const SizedBox(height: 48),

                    // ── Loading Indicator ──
                    const SizedBox(
                      width: 26,
                      height: 26,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          GaraColors.dsPrimaryDeep,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Mesh Painter — identical algorithm to _PilihMapelMeshPainter
// ─────────────────────────────────────────────────────────────────────────────
class _SplashMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _SplashMeshPainter(
      {required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );
    _blob(canvas, size,
        color: GaraColors.dsBlob1.withOpacity(0.75),
        radius: size.width * 0.38,
        baseX: size.width * 0.0,
        baseY: size.height * 0.0,
        t: t1,
        dx: 25,
        dy: -40,
        blur: 55);
    _blob(canvas, size,
        color: GaraColors.dsBlob2.withOpacity(0.70),
        radius: size.width * 0.42,
        baseX: size.width * 0.85,
        baseY: size.height * 0.35,
        t: t2,
        dx: -20,
        dy: 25,
        blur: 65);
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.65),
        radius: size.width * 0.32,
        baseX: size.width * 0.15,
        baseY: size.height * 0.88,
        t: t3,
        dx: 18,
        dy: -25,
        blur: 50);
  }

  void _blob(Canvas canvas, Size size,
      {required Color color,
      required double radius,
      required double baseX,
      required double baseY,
      required double t,
      required double dx,
      required double dy,
      required double blur}) {
    final progress = math.sin(t * math.pi);
    canvas.drawCircle(
      Offset(baseX + dx * progress, baseY + dy * progress),
      radius * (1.0 + 0.08 * progress),
      Paint()
        ..color = color
        ..maskFilter = MaskFilter.blur(BlurStyle.normal, blur),
    );
  }

  @override
  bool shouldRepaint(_SplashMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
