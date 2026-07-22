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

class _SplashLogicPageState extends State<SplashLogicPage> {
  final ActivationService _activationService = ActivationService();
  final UpdateService _updateService = UpdateService();
  String _loadingMessage = "Memuat aplikasi...";

  @override
  void initState() {
    super.initState();
    _initializeApp();
  }

  void _updateMessage(String msg) {
    if (mounted) {
      setState(() => _loadingMessage = msg);
    }
  }

  Future<void> _initializeApp() async {
    // 1. Bersihkan file APK lama jika ada
    await _updateService.cleanOldApk();

    // 2. Cek status aktivasi sekolah
    _updateMessage("Memeriksa status aktivasi...");
    final isActivated = await _activationService.isActivated();
    if (!isActivated) {
      _navigateTo(const ActivationPage());
      return;
    }

    // 3. Cek Koneksi Internet
    _updateMessage("Memeriksa koneksi internet...");
    final List<ConnectivityResult> connectivityResult = await Connectivity().checkConnectivity();
    if (connectivityResult.contains(ConnectivityResult.none)) {
      // OFFLINE -> Bypass langsung ke Home/Login
      await _checkSessionAndNavigate();
      return;
    }

    // 4. Sinkronisasi Firestore (Status Sekolah)
    _updateMessage("Sinkronisasi data sekolah...");
    final prefs = await SharedPreferences.getInstance();
    final schoolKey = prefs.getString(ActivationService.keySchoolKey);
    final schoolName = prefs.getString(ActivationService.keySchoolName) ?? "Sekolah Anda";

    if (schoolKey != null) {
      if (schoolKey.toUpperCase() == 'HITAMPEKAT') {
        AppConfig.isDebugMode = true;
        await _checkSessionAndNavigate();
        return;
      }
      try {
        final doc = await FirebaseFirestore.instance.collection('schools').doc(schoolKey).get();
        if (doc.exists) {
          final data = doc.data()!;
          final status = data['status'] as String? ?? 'active';
          
          if (status == 'maintenance' || status == 'suspend') {
            _navigateTo(StatusPage(status: status, schoolName: schoolName));
            return;
          }
          // Jika URL berubah, update SharedPreferences
          if (data['url'] != null) {
            await prefs.setString(ActivationService.keyBaseUrl, data['url']);
          }
        }
      } catch (e) {
        debugPrint("Gagal cek status sekolah: $e");
        // Lanjut saja jika gagal
      }
    }

    // 5. Sinkronisasi Remote Config (Cek Update)
    _updateMessage("Memeriksa pembaruan...");
    try {
      await _updateService.initialize();
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
        } else {
          // Dialog Opsional Update
          if (!mounted) return;
          final shouldUpdate = await showDialog<bool>(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              title: Text("Pembaruan Tersedia ($latestVersion)"),
              content: Text(releaseNotes),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx, false),
                  child: const Text("Nanti"),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.pop(ctx, true),
                  child: const Text("Perbarui"),
                ),
              ],
            ),
          );

          if (shouldUpdate == true) {
            _navigateTo(UpdatePage(
              isForceUpdate: false,
              releaseNotes: releaseNotes,
              latestVersion: latestVersion,
            ));
            return;
          }
        }
      }
    } catch (e) {
      debugPrint("Gagal cek pembaruan: $e");
    }

    // 6. Lanjut ke Halaman Sesi
    await _checkSessionAndNavigate();
  }

  Future<void> _checkSessionAndNavigate() async {
    _updateMessage("Memuat sesi...");
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
            role:  role,
          );
          _navigateTo(HybridWrapper(
            url:       handoffUrl,
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
      MaterialPageRoute(builder: (_) => page),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const CircularProgressIndicator(),
            const SizedBox(height: 24),
            Text(_loadingMessage, style: const TextStyle(fontSize: 16)),
          ],
        ),
      ),
    );
  }
}
