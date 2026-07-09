












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

  
  FlutterNativeSplash.preserve(widgetsBinding: widgetsBinding);

  
  
  await PerformanceConfig.initialize();

  
  
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

  
  
  Future<void> _warmUp() async {
    try {
      
      
      if (mounted) {
        await precacheImage(
          const AssetImage('assets/images/bglogin.jpg'),
          context,
        );
      }
    } catch (e) {
      debugPrint('[GARA Warmup] Error pre-cache: $e');
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
      home: FutureBuilder<Map<String, dynamic>>(
        future: _sessionFuture,
        builder: (context, snapshot) {
          
          
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
