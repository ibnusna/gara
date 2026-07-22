












import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'utils/app_constants.dart';
import 'utils/performance_config.dart';
import 'utils/route_builders.dart';
import 'screens/login_page.dart';
import 'screens/pilih_mapel_page.dart';
import 'screens/dashboard_page.dart';
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';
import 'screens/splash_logic_page.dart';

Future<void> main() async {
  final widgetsBinding = WidgetsFlutterBinding.ensureInitialized();

  
  FlutterNativeSplash.preserve(widgetsBinding: widgetsBinding);

  GoogleFonts.config.allowRuntimeFetching = true;

  await PerformanceConfig.initialize();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  
  
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
  bool _assetsCached = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_assetsCached) {
      _assetsCached = true;
      _warmUp();
    }
  }

  
  
  Future<void> _warmUp() async {
    try {
      if (mounted) {
        await precacheImage(
          const AssetImage('launchericon-512x512.png'),
          context,
        );
      }
    } catch (e) {
      debugPrint('[GARA Warmup] Error pre-cache logo: $e');
    }
    FlutterNativeSplash.remove();
  }

  
  
  
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
        
        textTheme: _poppinsTextTheme,
        scaffoldBackgroundColor: GaraColors.studentBgBody,
        
        splashFactory: NoSplash.splashFactory,
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
        
        dividerTheme: const DividerThemeData(space: 0),
      ),
      home: const SplashLogicPage(),
      routes: {
        GaraRoutes.login:      (_) => const LoginPage(),
        GaraRoutes.pilihMapel: (_) => const PilihMapelPage(),
      },
      onGenerateRoute: (settings) {
        if (settings.name == GaraRoutes.dashboard) {
          final mapel = settings.arguments as String? ?? '';
          
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
