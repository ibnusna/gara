// ============================================================
//  GARA Flutter — Screen: Login Page (Efficient Premium v10)
//  Background: bglogin.jpg | Logo: GARA_WHITE.svg
//  Alur: Welcome Screen → Login Screen → PilihMapelPage
//
//  Optimasi implementasi (TANPA downgrade visual):
//   ✅ RepaintBoundary di background & logo SVG
//   ✅ TextTheme dari Theme.of(context) — tidak alokasi ulang
//   ✅ ParticleOverlay tetap aktif (static painter, free)
//   ✅ SlideTransition switcher tetap aktif (premium feel)
//   ✅ BoxShadow tombol tetap aktif
//   ✅ filterQuality.medium (default optimal)
//   ✅ Durasi animasi premium dipertahankan
// ============================================================

import 'package:flutter/material.dart';
import '../utils/app_constants.dart';
import '../utils/performance_config.dart';
import '../utils/route_builders.dart';
import '../widgets/dot_indicator.dart';
import '../widgets/gara_logo.dart';
import '../widgets/gara_primary_button.dart';
import '../widgets/hybrid_wrapper.dart';
import '../utils/app_config.dart';
import '../services/auth_service.dart';
import 'pilih_mapel_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage>
    with SingleTickerProviderStateMixin {
  bool _showLoginScreen = false;
  bool _isPasswordVisible = false;
  bool _isLoading = false;

  final _identifierCtrl  = TextEditingController();
  final _passwordCtrl    = TextEditingController();
  final _identifierFocus = FocusNode();
  final _passwordFocus   = FocusNode();
  final _formKey         = GlobalKey<FormState>();

  late final AnimationController _animCtrl = AnimationController(
    vsync: this,
    duration: PerformanceConfig.loginFadeDuration, // 500ms premium
  )..forward();

  late final Animation<double> _fadeAnim = CurvedAnimation(
    parent: _animCtrl,
    curve: PerformanceConfig.loginFadeCurve,
  );

  // Entrance animation
  late final AnimationController _entranceCtrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1000),
  )..forward();

  late final Animation<double> _entranceFade = CurvedAnimation(
    parent: _entranceCtrl,
    curve: const Interval(0.0, 1.0, curve: Curves.easeOut),
  );

  late final Animation<Offset> _entranceSlide = Tween<Offset>(
    begin: const Offset(0, 0.05),
    end: Offset.zero,
  ).animate(CurvedAnimation(
    parent: _entranceCtrl,
    curve: const Interval(0.0, 1.0, curve: Curves.easeOutCubic),
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

  void _goToLoginScreen() {
    setState(() => _showLoginScreen = true);
    _animCtrl.reset();
    _animCtrl.forward().then((_) {
      if (mounted) FocusScope.of(context).requestFocus(_identifierFocus);
    });
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    setState(() => _isLoading = true);

    final result = await AuthService.login(
      identifier: _identifierCtrl.text.trim(),
      password:   _passwordCtrl.text,
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (!result.success) {
      _showErrorDialog(
        result.isMaintenance ? '🔧 Sistem dalam Pemeliharaan' : 'Gagal Masuk',
        result.errorMessage ?? 'Terjadi kesalahan. Coba lagi.',
      );
      return;
    }

    final role  = result.role  ?? GaraRoles.siswa;
    final token = result.token ?? '';

    Widget nextScreen;
    if (role == GaraRoles.siswa) {
      nextScreen = const PilihMapelPage();
    } else {
      final handoffUrl = AppConfig.getHandoffDashboardUrl(
        token: token,
        role:  role,
      );
      nextScreen = HybridWrapper(
        url:       handoffUrl,
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
      backgroundColor: GaraColors.bgDark,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // Layer 1: Background — RepaintBoundary: cegah repaint gambar saat konten bergerak
          RepaintBoundary(
            child: Image.asset(
              'assets/images/bglogin.jpg',
              fit: BoxFit.cover,
              filterQuality: FilterQuality.medium,
            ),
          ),

          // Layer 2: Dark overlay
          const ColoredBox(color: GaraColors.bgOverlay),

          // Layer 3: Particle decoration — tetap aktif, gratis (static CustomPainter)
          const _ParticleOverlay(),

          // Layer 4: Animated content with Entrance Animation
          FadeTransition(
            opacity: _entranceFade,
            child: SlideTransition(
              position: _entranceSlide,
              child: AnimatedSwitcher(
            duration: PerformanceConfig.loginSwitcherDuration, // 400ms
            switchInCurve: Curves.easeOut,
            switchOutCurve: Curves.easeIn,
            transitionBuilder: (child, anim) => FadeTransition(
              opacity: anim,
              child: SlideTransition(
                // Slide mikro 3% horizontal — premium feel, murah di GPU
                position: Tween<Offset>(
                  begin: const Offset(0.03, 0),
                  end: Offset.zero,
                ).animate(anim),
                child: child,
              ),
            ),
            child: _showLoginScreen
                ? _LoginScreen(
                    key: const ValueKey('login'),
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
                    onBack: () => setState(() => _showLoginScreen = false),
                  )
                : _WelcomeScreen(
                    key: const ValueKey('welcome'),
                    fadeAnim: _fadeAnim,
                    onMulaiAkses: _goToLoginScreen,
                  ),
          ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Welcome Screen ─────────────────────────────────────────────
class _WelcomeScreen extends StatelessWidget {
  final Animation<double> fadeAnim;
  final VoidCallback onMulaiAkses;

  const _WelcomeScreen({
    super.key,
    required this.fadeAnim,
    required this.onMulaiAkses,
  });

  @override
  Widget build(BuildContext context) {
    // TextTheme dari context — sudah di-cache di MaterialApp level
    final tt = Theme.of(context).textTheme;

    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 400),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28),
          child: FadeTransition(
            opacity: fadeAnim,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // RepaintBoundary di logo — SVG tidak ikut repaint saat fade berjalan
                const RepaintBoundary(child: GaraLogoWhite(height: 80)),
                const SizedBox(height: 14),
                Text(
                  'GARA',
                  style: tt.headlineMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                    letterSpacing: 3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Garuda Akademi',
                  style: tt.bodySmall?.copyWith(
                    color: GaraColors.textMuted,
                    letterSpacing: 1.5,
                  ),
                ),
                const SizedBox(height: 36),
                const _TextCarousel(),
                const SizedBox(height: 40),
                const SlideIndicators(),
                const SizedBox(height: 28),
                GaraPrimaryButton(
                  label: 'MULAI AKSES',
                  onPressed: onMulaiAkses,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Text Carousel ──────────────────────────────────────────────
class _TextCarousel extends StatefulWidget {
  const _TextCarousel();

  @override
  State<_TextCarousel> createState() => _TextCarouselState();
}

class _TextCarouselState extends State<_TextCarousel> {
  int _currentIndex = 0;
  late final List<Map<String, String>> _carouselItems = [
    {
      'title': 'Selamat Datang di\nEra Belajar Digital',
      'subtitle': 'Akses materi, tugas, dan ujian\ndalam satu genggaman. Cepat, Mudah, Efisien.',
    },
    {
      'title': 'Belajar Kapan Saja,\nDi Mana Saja',
      'subtitle': 'Jelajahi perpustakaan materi terpadu dan\ntingkatkan pemahamanmu dengan interaktif.',
    },
    {
      'title': 'Pantau Perkembangan\nBelajarmu',
      'subtitle': 'Lihat nilai, kerjakan kuis, dan raih\nprestasi akademik dengan cara yang lebih seru.',
    }
  ];

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    Future.delayed(const Duration(seconds: 4), () {
      if (mounted) {
        setState(() {
          _currentIndex = (_currentIndex + 1) % _carouselItems.length;
        });
        _startTimer();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 800),
      transitionBuilder: (child, animation) {
        return FadeTransition(
          opacity: animation,
          child: SlideTransition(
            position: Tween<Offset>(
              begin: const Offset(0.0, 0.1),
              end: Offset.zero,
            ).animate(animation),
            child: child,
          ),
        );
      },
      child: Column(
        key: ValueKey(_currentIndex),
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            _carouselItems[_currentIndex]['title']!,
            textAlign: TextAlign.center,
            style: tt.titleLarge?.copyWith(
              fontWeight: FontWeight.w600,
              color: Colors.white,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            _carouselItems[_currentIndex]['subtitle']!,
            textAlign: TextAlign.center,
            style: tt.bodyMedium?.copyWith(
              color: GaraColors.textMuted,
              height: 1.55,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Login Screen ───────────────────────────────────────────────
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
  final VoidCallback onBack;

  const _LoginScreen({
    super.key,
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
    required this.onBack,
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
                    const RepaintBoundary(child: GaraLogoWhite(height: 56)),
                    const SizedBox(height: 14),
                    Text(
                      'Masuk Akun',
                      style: tt.headlineSmall?.copyWith(
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      'Masukkan kredensial untuk melanjutkan',
                      style: tt.bodySmall?.copyWith(color: GaraColors.textMuted),
                    ),
                    const SizedBox(height: 32),
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
                    const SizedBox(height: 18),
                    GestureDetector(
                      onTap: onBack,
                      child: Text(
                        '← Kembali',
                        style: tt.bodySmall?.copyWith(
                          color: GaraColors.textMuted,
                          decoration: TextDecoration.underline,
                          decorationColor: GaraColors.textMuted,
                        ),
                      ),
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

// ── Input Field ────────────────────────────────────────────────
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
      style: tt.bodyMedium?.copyWith(color: Colors.white, fontSize: 15),
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
        fillColor: GaraColors.inputBg,
        hintText: placeholder,
        hintStyle: tt.bodyMedium?.copyWith(
            color: Colors.white.withOpacity(0.4), fontSize: 14),
        prefixIcon: Icon(icon, color: GaraColors.textMuted, size: 18),
        suffixIcon: isPassword
            ? IconButton(
                icon: Icon(
                  isPasswordVisible
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: GaraColors.textMuted,
                  size: 18,
                ),
                onPressed: onTogglePassword,
              )
            : null,
        contentPadding: const EdgeInsets.symmetric(vertical: 15),
        border: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.glassBorder)),
        enabledBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.glassBorder)),
        focusedBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: GaraColors.primaryLight, width: 1.5)),
        errorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: Colors.redAccent)),
        focusedErrorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: Colors.redAccent, width: 1.5)),
        errorStyle: tt.bodySmall?.copyWith(
            color: Colors.orangeAccent, fontSize: 11),
      ),
    );
  }
}

// ── Particle Overlay — static CustomPainter, shouldRepaint:false ──
// Gratis di GPU karena tidak pernah repaint setelah first draw
class _ParticleOverlay extends StatelessWidget {
  const _ParticleOverlay();

  @override
  Widget build(BuildContext context) {
    return RepaintBoundary(
      child: CustomPaint(
        size: MediaQuery.of(context).size,
        painter: _ParticlePainter(),
      ),
    );
  }
}

class _ParticlePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.04)
      ..style = PaintingStyle.fill;

    canvas.drawCircle(Offset(size.width * 0.05, size.height * 0.8),
        size.width * 0.55, paint);

    paint.color = Colors.white.withOpacity(0.03);
    canvas.drawCircle(Offset(size.width * 0.95, size.height * 0.12),
        size.width * 0.38, paint);

    final linePaint = Paint()
      ..color = Colors.white.withOpacity(0.05)
      ..strokeWidth = 1;
    for (int i = 0; i < 5; i++) {
      final y = size.height * 0.1 * (i + 1);
      canvas.drawLine(
          Offset(0, y), Offset(size.width * 0.4, y + size.width * 0.15), linePaint);
    }
  }

  // shouldRepaint: false = painter tidak pernah repaint = 0 GPU cost setelah render pertama
  @override
  bool shouldRepaint(_ParticlePainter _) => false;
}
