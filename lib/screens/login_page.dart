import 'package:flutter/material.dart';
import '../utils/app_constants.dart';
import '../utils/performance_config.dart';
import '../utils/route_builders.dart';
import '../widgets/gara_primary_button.dart';
import '../widgets/hybrid_wrapper.dart';
import '../utils/app_config.dart';
import '../services/InfinityAuthService.dart';
import 'pilih_mapel_page.dart';
import '../services/InfinityBypassEngine.dart';

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

  late final AnimationController _animCtrl = AnimationController(
    vsync: this,
    duration: PerformanceConfig.loginFadeDuration,
  )..forward();

  late final Animation<double> _fadeAnim = CurvedAnimation(
    parent: _animCtrl,
    curve: PerformanceConfig.loginFadeCurve,
  );

  late final AnimationController _entranceCtrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 600),
  )..forward();

  late final Animation<double> _entranceFade = CurvedAnimation(
    parent: _entranceCtrl,
    curve: Curves.easeOut,
  );

  late final Animation<Offset> _entranceSlide = Tween<Offset>(
    begin: const Offset(0, 0.04),
    end: Offset.zero,
  ).animate(CurvedAnimation(
    parent: _entranceCtrl,
    curve: Curves.easeOutCubic,
  ));


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
        try {
          // Warm up HeadlessInAppWebView secara silent di background tanpa UI popup
          await InfinityBypassEngine.getValidCookie(forceRefresh: retryCount > 0);
        } catch (e) {
          debugPrint('[LoginPage] Silent bypass error: $e');
        }
      }

      result = await InfinityAuthService.login(
        identifier: _identifierCtrl.text.trim(),
        password: _passwordCtrl.text,
      );

      if (result.errorMessage == '__RETRY_BYPASS__') {
        retryCount++;
        continue;
      }
      break;
    }

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result == null || !result.success) {
      _showErrorDialog(
        result?.isMaintenance == true ? '🔧 Sistem dalam Pemeliharaan' : 'Gagal Masuk',
        result?.errorMessage == '__RETRY_BYPASS__'
            ? 'Sistem sibuk. Silakan coba lagi.'
            : (result?.errorMessage ?? 'Terjadi kesalahan. Coba lagi.'),
      );
      return;
    }

    final role = result.role ?? GaraRoles.siswa;
    final token = result.token ?? '';

    Widget nextScreen;
    if (role == GaraRoles.siswa) {
      nextScreen = const PilihMapelPage();
    } else {
      final handoffUrl = AppConfig.getHandoffDashboardUrl(
        token: token,
        role: role,
      );
      nextScreen = HybridWrapper(
        url: handoffUrl,
        pageTitle: 'Dashboard ${role.toUpperCase()}',
      );
    }

    if (!mounted) return;
    Navigator.pushReplacement(context, garaFadeSlideRoute(nextScreen));
  }

  void _showErrorDialog(String title, String message) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(title,
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w700, color: Colors.black87)),
        content: Text(message,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.black87)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // ── Clean gradient background (matching WelcomePage) ──
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Color(0xFFF0F6FF),
                  Color(0xFFFFFFFF),
                  Color(0xFFEFF4FF),
                ],
                stops: [0.0, 0.5, 1.0],
              ),
            ),
          ),
          // ── Login content ──
          FadeTransition(
            opacity: _entranceFade,
            child: SlideTransition(
              position: _entranceSlide,
              child: _LoginScreen(
                fadeAnim: _fadeAnim,
                identifierCtrl: _identifierCtrl,
                passwordCtrl: _passwordCtrl,
                identifierFocus: _identifierFocus,
                passwordFocus: _passwordFocus,
                formKey: _formKey,
                isPasswordVisible: _isPasswordVisible,
                isLoading: _isLoading,
                onTogglePassword: () =>
                    setState(() => _isPasswordVisible = !_isPasswordVisible),
                onSubmit: _handleLogin,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _LoginScreen extends StatelessWidget {
  final Animation<double> fadeAnim;
  final TextEditingController identifierCtrl;
  final TextEditingController passwordCtrl;
  final FocusNode identifierFocus;
  final FocusNode passwordFocus;
  final GlobalKey<FormState> formKey;
  final bool isPasswordVisible;
  final bool isLoading;
  final VoidCallback onTogglePassword;
  final VoidCallback onSubmit;

  const _LoginScreen({
    required this.fadeAnim,
    required this.identifierCtrl,
    required this.passwordCtrl,
    required this.identifierFocus,
    required this.passwordFocus,
    required this.formKey,
    required this.isPasswordVisible,
    required this.isLoading,
    required this.onTogglePassword,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return Center(
      child: SingleChildScrollView(
        physics: const ClampingScrollPhysics(),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 400),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: FadeTransition(
              opacity: fadeAnim,
              child: Form(
                key: formKey,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 20),
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.08),
                            blurRadius: 20,
                            spreadRadius: 1,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(20),
                        child: Image.asset(
                          'launchericon-512x512.png',
                          height: 76,
                          width: 76,
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'GARA',
                      style: tt.headlineSmall?.copyWith(
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E293B),
                        letterSpacing: 3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Masukkan kredensial untuk melanjutkan',
                      style: tt.bodySmall?.copyWith(color: GaraColors.studentTextMuted),
                    ),
                    const SizedBox(height: 36),
                    _InputField(
                      controller: identifierCtrl,
                      focusNode: identifierFocus,
                      nextFocus: passwordFocus,
                      icon: Icons.person_outline_rounded,
                      placeholder: 'Username / NIS',
                      isPassword: false,
                      isPasswordVisible: false,
                      onTogglePassword: onTogglePassword,
                    ),
                    const SizedBox(height: 16),
                    _InputField(
                      controller: passwordCtrl,
                      focusNode: passwordFocus,
                      icon: Icons.lock_outline_rounded,
                      placeholder: 'Password',
                      isPassword: true,
                      isPasswordVisible: isPasswordVisible,
                      onTogglePassword: onTogglePassword,
                      onSubmit: onSubmit,
                    ),
                    const SizedBox(height: 28),
                    GaraPrimaryButton(
                      label: 'LOGIN',
                      onPressed: onSubmit,
                      isLoading: isLoading,
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _InputField extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode focusNode;
  final FocusNode? nextFocus;
  final IconData icon;
  final String placeholder;
  final bool isPassword;
  final bool isPasswordVisible;
  final VoidCallback onTogglePassword;
  final VoidCallback? onSubmit;

  const _InputField({
    required this.controller,
    required this.focusNode,
    this.nextFocus,
    required this.icon,
    required this.placeholder,
    required this.isPassword,
    required this.isPasswordVisible,
    required this.onTogglePassword,
    this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    final baseRadius = BorderRadius.circular(28);

    return TextFormField(
      controller: controller,
      focusNode: focusNode,
      obscureText: isPassword && !isPasswordVisible,
      textInputAction:
          nextFocus != null ? TextInputAction.next : TextInputAction.done,
      autocorrect: false,
      enableSuggestions: !isPassword,
      style: tt.bodyMedium?.copyWith(color: const Color(0xFF1E293B), fontSize: 15),
      onFieldSubmitted: (_) {
        if (nextFocus != null) {
          FocusScope.of(context).requestFocus(nextFocus);
        } else {
          onSubmit?.call();
        }
      },
      validator: (v) {
        if (v == null || v.trim().isEmpty) {
          return isPassword ? 'Password wajib diisi' : 'Username/NIS wajib diisi';
        }
        return null;
      },
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.white,
        hintText: placeholder,
        hintStyle: tt.bodyMedium?.copyWith(
            color: GaraColors.studentTextMuted, fontSize: 14),
        prefixIcon: Icon(icon, color: GaraColors.studentTextMuted, size: 18),
        suffixIcon: isPassword
            ? IconButton(
                icon: Icon(
                  isPasswordVisible
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: GaraColors.studentTextMuted,
                  size: 18,
                ),
                onPressed: onTogglePassword,
              )
            : null,
        contentPadding: const EdgeInsets.symmetric(vertical: 15),
        border: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.studentBorder)),
        enabledBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.studentBorder)),
        focusedBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: GaraColors.studentPrimary, width: 1.5)),
        errorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: Colors.redAccent)),
        focusedErrorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: Colors.redAccent, width: 1.5)),
        errorStyle: tt.bodySmall?.copyWith(
            color: Colors.redAccent, fontSize: 11),
      ),
    );
  }
}
