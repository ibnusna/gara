import 'dart:math' as math;
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


  // ── Mesh blob controllers ──
  late final AnimationController _blob1;
  late final AnimationController _blob2;
  late final AnimationController _blob3;

  // ── Floating icon controllers ──
  late final List<AnimationController> _floatCtrls;
  late final List<Animation<double>> _floatY;
  late final List<Animation<double>> _floatOpacity;
  late final List<Animation<double>> _floatRotate;

  static const _floatIcons = [
    Icons.menu_book_rounded, Icons.star_rounded, Icons.school_rounded,
    Icons.edit_rounded, Icons.lightbulb_rounded, Icons.lock_rounded,
  ];
  static const _floatColors = [
    Color(0xFF3B82F6), Color(0xFFFBBD05), Color(0xFF8B5CF6),
    Color(0xFF10B981), Color(0xFFF97316), Color(0xFF06B6D4),
  ];
  static const _floatX  = [0.06, 0.78, 0.12, 0.82, 0.04, 0.70];
  static const _floatY0 = [0.10, 0.06, 0.42, 0.38, 0.68, 0.65];
  static const _floatSz = [30.0, 24.0, 22.0, 28.0, 26.0, 20.0];
  static const _floatMs = [3200, 2800, 3600, 2500, 3100, 2900];

  @override
  void initState() {
    super.initState();
    // ── Jalankan Bypass secara silent di Background (DI AWAL) ──
    if (!AppConfig.isDebugMode) {
      InfinityBypassEngine.getValidCookie().catchError((e) {
        debugPrint('[LoginPage] Background bypass error: $e');
        return '';
      });
    }

    _blob1 = AnimationController(vsync: this, duration: const Duration(milliseconds: 18000))..repeat(reverse: true);
    _blob2 = AnimationController(vsync: this, duration: const Duration(milliseconds: 22000))..repeat(reverse: true);
    _blob3 = AnimationController(vsync: this, duration: const Duration(milliseconds: 15000))..repeat(reverse: true);
    _blob2.value = 0.33;
    _blob3.value = 0.66;

    _floatCtrls = List.generate(_floatIcons.length, (i) {
      final c = AnimationController(vsync: this, duration: Duration(milliseconds: _floatMs[i]))..repeat(reverse: true);
      c.value = i / _floatIcons.length;
      return c;
    });
    _floatY = _floatCtrls.map((c) => Tween<double>(begin: -12, end: 12)
        .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))).toList();
    _floatOpacity = _floatCtrls.map((c) => Tween<double>(begin: 0.25, end: 0.70)
        .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))).toList();
    _floatRotate = _floatCtrls.map((c) => Tween<double>(begin: -0.12, end: 0.12)
        .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))).toList();
  }
  @override
  void dispose() {
    _animCtrl.dispose();
    _entranceCtrl.dispose();
    _blob1.dispose(); _blob2.dispose(); _blob3.dispose();
    for (final c in _floatCtrls) { c.dispose(); }
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

      try {
        result = await InfinityAuthService.login(
          identifier: _identifierCtrl.text.trim(),
          password: _passwordCtrl.text,
        );

        if (result.errorMessage == '__RETRY_BYPASS__') {
          retryCount++;
          continue;
        }
        break;
      } catch (e) {
        if (!mounted) return;
        setState(() => _isLoading = false);
        debugPrint('[LoginPage] Exception: $e');
        _showErrorDialog('Gagal Masuk', 'Error: $e');
        return;
      }
    }

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result == null) {
      _showErrorDialog('Gagal Masuk', 'Terjadi kesalahan internal. Coba lagi.');
      return;
    }

    if (!result.success) {
      _showErrorDialog(
        result.isMaintenance ? '🔧 Sistem dalam Pemeliharaan' : 'Gagal Masuk',
        result.errorMessage == '__RETRY_BYPASS__'
            ? 'Gagal menghubungkan ke server keamanan. Silakan coba lagi.'
            : (result.errorMessage ?? 'Terjadi kesalahan. Coba lagi.'),
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
    final size = MediaQuery.of(context).size;
    return Scaffold(
      backgroundColor: GaraColors.dsMeshBase,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // ── Layer 1: Animated mesh blob background ──
          AnimatedBuilder(
            animation: Listenable.merge([_blob1, _blob2, _blob3]),
            builder: (_, __) => CustomPaint(
              painter: _LoginMeshPainter(t1: _blob1.value, t2: _blob2.value, t3: _blob3.value),
            ),
          ),
          // ── Layer 2: Floating animated icons ──
          for (int i = 0; i < _floatIcons.length; i++)
            AnimatedBuilder(
              animation: _floatCtrls[i],
              builder: (_, __) => Positioned(
                left: _floatX[i] * size.width,
                top: _floatY0[i] * size.height + _floatY[i].value,
                child: Opacity(
                  opacity: _floatOpacity[i].value,
                  child: Transform.rotate(
                    angle: _floatRotate[i].value,
                    child: Icon(_floatIcons[i], size: _floatSz[i], color: _floatColors[i]),
                  ),
                ),
              ),
            ),
          // ── Layer 3: Login content ──
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

// ─────────────────────────────────────────────────────────────────────────────
// _LoginMeshPainter — animated gradient blobs (matching WelcomePage)
// ─────────────────────────────────────────────────────────────────────────────
class _LoginMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _LoginMeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    // Blob 1 — biru (kiri atas)
    _drawBlob(canvas,
      color: GaraColors.dsBlob1.withOpacity(0.75),
      radius: size.width * 0.50,
      baseX: size.width * 0.05, baseY: size.height * 0.02,
      t: t1, dx: 35, dy: 30, blur: 70,
    );

    // Blob 2 — ungu (kanan tengah)
    _drawBlob(canvas,
      color: GaraColors.dsBlob2.withOpacity(0.68),
      radius: size.width * 0.46,
      baseX: size.width * 0.88, baseY: size.height * 0.45,
      t: t2, dx: -30, dy: 35, blur: 78,
    );

    // Blob 3 — cyan (bawah kiri)
    _drawBlob(canvas,
      color: GaraColors.dsBlob3.withOpacity(0.65),
      radius: size.width * 0.40,
      baseX: size.width * 0.10, baseY: size.height * 0.88,
      t: t3, dx: 28, dy: -30, blur: 65,
    );
  }

  void _drawBlob(Canvas canvas, {
    required Color color, required double radius,
    required double baseX, required double baseY,
    required double t, required double dx, required double dy, required double blur,
  }) {
    final p = math.sin(t * math.pi);
    canvas.drawCircle(
      Offset(baseX + dx * p, baseY + dy * p),
      radius * (1.0 + 0.10 * p),
      Paint()
        ..color = color
        ..maskFilter = MaskFilter.blur(BlurStyle.normal, blur),
    );
  }

  @override
  bool shouldRepaint(_LoginMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
