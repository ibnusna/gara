import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/activation_service.dart';
import '../services/update_service.dart';
import '../utils/app_constants.dart';
import '../utils/app_config.dart';
import 'activation_page.dart';
import 'status_page.dart';
import 'update_page.dart';
import 'login_page.dart';
import 'pilih_mapel_page.dart';
import '../widgets/hybrid_wrapper.dart';

class SplashLogicPage extends StatefulWidget {
  const SplashLogicPage({super.key});

  @override
  State<SplashLogicPage> createState() => _SplashLogicPageState();
}

class _SplashLogicPageState extends State<SplashLogicPage>
    with SingleTickerProviderStateMixin {
  final ActivationService _activationService = ActivationService();
  final UpdateService _updateService = UpdateService();
  late final AnimationController _animCtrl;
  late final Animation<double> _scaleAnim;
  late final Animation<double> _fadeAnim;
  late final Animation<double> _pulseAnim;

  @override
  void initState() {
    super.initState();
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

    _animCtrl.forward();
    _initializeApp();
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    super.dispose();
  }

  Future<void> _initializeApp() async {
    final startTime = DateTime.now();

    try {
      _updateService.cleanOldApk();

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
      final schoolKey = prefs.getString(ActivationService.keySchoolKey);
      final schoolName =
          prefs.getString(ActivationService.keySchoolName) ?? "Sekolah Anda";

      if (schoolKey != null) {
        if (schoolKey.toUpperCase() == 'HITAMPEKAT') {
          AppConfig.isDebugMode = true;
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

      try {
        await _updateService
            .initialize()
            .timeout(const Duration(milliseconds: 600));
        final hasUpdate = await _updateService.isUpdateAvailable();

        if (hasUpdate) {
          final isForce = _updateService.isForceUpdate();
          final releaseNotes = _updateService.getReleaseNotes();
          final latestVersion = _updateService.getLatestVersion();

          if (isForce) {
            _navigateTo(UpdatePage(
              isForceUpdate: true,
              releaseNotes: releaseNotes,
              latestVersion: latestVersion,
            ));
            return;
          }
        }
      } catch (e) {
        debugPrint("Cek update timeout/gagal: $e");
      }
    } catch (e) {
      debugPrint("Splash init error: $e");
    }

    await _ensureMinDurationAndNavigate(startTime);
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

    _navigateTo(const LoginPage());
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
      backgroundColor: Colors.white,
      body: Center(
        child: FadeTransition(
          opacity: _fadeAnim,
          child: AnimatedBuilder(
            animation: _animCtrl,
            builder: (context, child) {
              final scale = _scaleAnim.value * _pulseAnim.value;
              return Transform.scale(
                scale: scale,
                child: child,
              );
            },
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.08),
                        blurRadius: 24,
                        spreadRadius: 2,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(24),
                    child: Image.asset(
                      'launchericon-512x512.png',
                      width: 110,
                      height: 110,
                      fit: BoxFit.contain,
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                const Text(
                  'GARA',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF1E293B),
                    letterSpacing: 4,
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Garuda Akademi Mobile',
                  style: TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: 40),
                const SizedBox(
                  width: 28,
                  height: 28,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    valueColor: AlwaysStoppedAnimation<Color>(
                      GaraColors.studentPrimary,
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
