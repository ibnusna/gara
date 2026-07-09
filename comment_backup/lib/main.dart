// ============================================================
//  GARA Flutter — main.dart (Performance Optimized v10.0)
//  Garuda Akademi Mobile
//
//  Perubahan v10.0 (Low-End Optimization):
//   1. PerformanceConfig.initialize() dipanggil sebelum runApp
//   2. Shader warm-up via scheduleWarmUpFrame
//   3. GoogleFonts di-cache sekali via TextTheme, bukan per-widget
//   4. FutureBuilder diganti StatefulWidget untuk menghindari rebuild
//   5. Route transitions via garaFadeSlideRoute (adaptive)
//   6. Tidak ada animasi pada initial route (instant load)
// ============================================================

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'utils/app_constants.dart';
import 'utils/app_config.dart';
import 'utils/performance_config.dart';
import 'utils/route_builders.dart';
import 'screens/login_page.dart';
import 'screens/pilih_mapel_page.dart';
import 'screens/dashboard_page.dart';
import 'widgets/hybrid_wrapper.dart';

Future<void> main() async {
  final widgetsBinding = WidgetsFlutterBinding.ensureInitialized();

  // ── [OPT 1] Preserve splash — harus sebelum await lain
  FlutterNativeSplash.preserve(widgetsBinding: widgetsBinding);

  // ── [OPT 2] Deteksi low-end SEBELUM runApp ─────────────────
  // Ini satu-satunya saat yang aman untuk operasi I/O sync sebelum UI
  await PerformanceConfig.initialize();

  // ── [OPT 3] Izinkan semua orientasi — mendukung tablet & split-screen Android
  // Portrait tetap default di phone, tablet dapat rotate landscape dengan bebas.
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
  ]);

  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ),
  );

  runApp(const GaraApp());
}

class GaraApp extends StatefulWidget {
  const GaraApp({super.key});

  @override
  State<GaraApp> createState() => _GaraAppState();
}

class _GaraAppState extends State<GaraApp> {
  // ── [OPT 4] Simpan future sebagai field — TIDAK buat ulang di build()
  late final Future<Map<String, dynamic>> _sessionFuture = _loadSession();
  bool _assetsCached = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_assetsCached) {
      _assetsCached = true;
      _warmUp();
    }
  }

  Future<Map<String, dynamic>> _loadSession() async {
    final prefs = await SharedPreferences.getInstance();
    final isLoggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;
    final role       = prefs.getString(GaraPrefKeys.userRole) ?? GaraRoles.siswa;
    final token      = prefs.getString(GaraPrefKeys.authToken) ?? '';
    
    final validSession = isLoggedIn && token.isNotEmpty;
    if (isLoggedIn && token.isEmpty) {
      await prefs.clear();
    }
    
    return {'isLoggedIn': validSession, 'role': role, 'token': token};
  }

  /// Warm-up: pre-cache gambar BERAT saja; SVG dimuat on-demand via flutter_svg.
  /// Tidak perlu delay buatan — splash sudah di-preserve hingga _init selesai.
  Future<void> _warmUp() async {
    try {
      // ── [OPT 5] Hanya pre-cache bglogin.jpg (gambar bitmap besar)
      // SVG tidak perlu pre-cache karena flutter_svg menggunakan parser ringan
      if (mounted) {
        await precacheImage(
          const AssetImage('assets/images/bglogin.jpg'),
          context,
        );
      }
    } catch (e) {
      debugPrint('[GARA Warmup] Error pre-cache: $e');
    }
    // ── [OPT 6] Hapus splash setelah asset ready — tanpa delay buatan
    FlutterNativeSplash.remove();
  }

  // ── [OPT 7] TextTheme di-cache di level MaterialApp —
  // Semua widget mendapatkan Poppins dari Theme.of(context) tanpa
  // memanggil GoogleFonts.poppins() berulang di setiap widget
  static final TextTheme _poppinsTextTheme = GoogleFonts.poppinsTextTheme();

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Garuda Akademi',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: GaraColors.studentPrimary,
          brightness: Brightness.light,
        ),
        // ── [OPT 8] TextTheme shared — tidak rebuild per widget
        textTheme: _poppinsTextTheme,
        scaffoldBackgroundColor: GaraColors.studentBgBody,
        // ── [OPT 9] Matikan ink splash global (hemat GPU pada tap)
        splashFactory: NoSplash.splashFactory,
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
        // ── [OPT 10] Matikan divider theme overhead
        dividerTheme: const DividerThemeData(space: 0),
      ),
      home: FutureBuilder<Map<String, dynamic>>(
        future: _sessionFuture,
        builder: (context, snapshot) {
          // Tampilkan layar kosong (gelap) selama cek sesi
          // Splash native sudah menutupi ini — user tidak melihat flicker
          if (!snapshot.hasData) {
            return const Scaffold(
              backgroundColor: GaraColors.bgDark,
              body: SizedBox.shrink(),
            );
          }

          final data = snapshot.data!;
          if (data['isLoggedIn'] == true) {
            final role  = data['role'] as String;
            if (role == GaraRoles.siswa) {
              return const PilihMapelPage();
            } else {
              final token = data['token'] as String? ?? '';
              final handoffUrl = AppConfig.getHandoffDashboardUrl(
                token: token,
                role:  role,
              );
              return HybridWrapper(
                url:       handoffUrl,
                pageTitle: 'Dashboard ${role.toUpperCase()}',
              );
            }
          }

          return const LoginPage();
        },
      ),
      routes: {
        GaraRoutes.login:      (_) => const LoginPage(),
        GaraRoutes.pilihMapel: (_) => const PilihMapelPage(),
      },
      onGenerateRoute: (settings) {
        if (settings.name == GaraRoutes.dashboard) {
          final mapel = settings.arguments as String? ?? '';
          // ── [OPT 11] Gunakan route builder adaptive
          return garaSlideRightRoute(
            DashboardPage(selectedMapel: mapel),
            settings: settings,
          );
        }
        return null;
      },
    );
  }
}
