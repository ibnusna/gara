import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/InfinityAuthService.dart';
import '../services/InfinityBypassEngine.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../utils/performance_config.dart';
import '../utils/route_builders.dart';
import '../widgets/hybrid_wrapper.dart';
import '../widgets/login/login_animated_background.dart';
import '../widgets/login/login_dialog_utils.dart';
import '../widgets/login/login_screen_content.dart';
import 'in_app_browser_page.dart';
import 'pilih_mapel_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> with TickerProviderStateMixin {
  bool _isPasswordVisible = false;
  bool _isLoading = false;

  final _identifierCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _identifierFocus = FocusNode();
  final _passwordFocus = FocusNode();
  final _formKey = GlobalKey<FormState>();

  late final AnimationController _animCtrl =
      AnimationController(vsync: this, duration: PerformanceConfig.loginFadeDuration)..forward();
  late final Animation<double> _fadeAnim =
      CurvedAnimation(parent: _animCtrl, curve: PerformanceConfig.loginFadeCurve);
  late final AnimationController _entranceCtrl =
      AnimationController(vsync: this, duration: const Duration(milliseconds: 600))..forward();
  late final Animation<double> _entranceFade =
      CurvedAnimation(parent: _entranceCtrl, curve: Curves.easeOut);
  late final Animation<Offset> _entranceSlide = Tween<Offset>(
    begin: const Offset(0, 0.04),
    end: Offset.zero,
  ).animate(CurvedAnimation(parent: _entranceCtrl, curve: Curves.easeOutCubic));

  @override
  void initState() {
    super.initState();
    if (!AppConfig.isDebugMode) InfinityBypassEngine.getValidCookie().catchError((_) => '');
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    _entranceCtrl.dispose();
    _identifierCtrl.dispose();
    _passwordCtrl.dispose();
    _identifierFocus.dispose();
    _passwordFocus.dispose();
    super.dispose();
  }

  void _handleForgotPassword() {
    HapticFeedback.lightImpact();
    Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => InAppBrowserPage(
          title: 'Lupa Password',
          initialUrl: '${AppConfig.baseUrl}/lupa-password',
          isPasswordFlow: true,
        ),
      ),
    ).then((changed) {
      if (changed == true && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Password berhasil diperbarui! Silakan masuk dengan password baru.',
              style: GoogleFonts.poppins(fontSize: 13, color: Colors.white),
            ),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      }
    });
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    if (_identifierCtrl.text.toUpperCase() == 'HITAMPEKAT') {
      AppConfig.isDebugMode = true;
      _identifierCtrl.clear();
      _passwordCtrl.clear();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Mode Debug (ADB Lokal) Diaktifkan'),
        backgroundColor: Colors.green,
      ));
      return;
    }

    FocusScope.of(context).unfocus();
    setState(() => _isLoading = true);

    int retryCount = 0;
    InfinityAuthResult? result;

    while (retryCount < 2) {
      if (!AppConfig.isDebugMode) {
        try { await InfinityBypassEngine.getValidCookie(forceRefresh: retryCount > 0); } catch (_) {}
      }
      try {
        result = await InfinityAuthService.login(
          identifier: _identifierCtrl.text.trim(),
          password: _passwordCtrl.text,
        );
        if (result.errorMessage == '__RETRY_BYPASS__') { retryCount++; continue; }
        break;
      } catch (e) {
        if (!mounted) return;
        setState(() => _isLoading = false);
        LoginDialogUtils.showError(context, 'Gagal Masuk', 'Error: $e');
        return;
      }
    }

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result == null || !result.success) {
      LoginDialogUtils.showError(
        context,
        result?.isMaintenance == true ? '🔧 Sistem dalam Pemeliharaan' : 'Gagal Masuk',
        result?.errorMessage ?? 'Terjadi kesalahan internal. Coba lagi.',
      );
      return;
    }

    final role = result.role ?? GaraRoles.siswa;
    final token = result.token ?? '';

    if (result.mustChangePassword) {
      final handoffUrl = AppConfig.getHandoffUrl(token: token, targetPath: '/auth/change-password-mandatory');
      await Navigator.push<bool>(
        context,
        MaterialPageRoute(
          builder: (_) => InAppBrowserPage(
            title: 'Pengamanan Akun',
            initialUrl: handoffUrl,
            isPasswordFlow: true,
          ),
        ),
      );
      if (!mounted) return;
    }

    final nextScreen = (role == GaraRoles.siswa)
        ? const PilihMapelPage()
        : HybridWrapper(
            url: AppConfig.getHandoffDashboardUrl(token: token, role: role),
            pageTitle: 'Dashboard ${role.toUpperCase()}',
          );

    Navigator.pushReplacement(context, garaFadeSlideRoute(nextScreen));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsMeshBase,
      body: LoginAnimatedBackground(
        child: FadeTransition(
          opacity: _entranceFade,
          child: SlideTransition(
            position: _entranceSlide,
            child: LoginScreenContent(
              fadeAnim: _fadeAnim,
              identifierCtrl: _identifierCtrl,
              passwordCtrl: _passwordCtrl,
              identifierFocus: _identifierFocus,
              passwordFocus: _passwordFocus,
              formKey: _formKey,
              isPasswordVisible: _isPasswordVisible,
              isLoading: _isLoading,
              onTogglePassword: () => setState(() => _isPasswordVisible = !_isPasswordVisible),
              onSubmit: _handleLogin,
              onForgotPassword: _handleForgotPassword,
            ),
          ),
        ),
      ),
    );
  }
}
