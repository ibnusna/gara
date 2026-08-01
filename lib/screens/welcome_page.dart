import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import 'login_page.dart';

// ─────────────────────────────────────────────────────────────────────────────
// GaraWelcomePage — Animated mesh background + floating education icons
// ─────────────────────────────────────────────────────────────────────────────
class GaraWelcomePage extends StatefulWidget {
  const GaraWelcomePage({super.key});
  @override
  State<GaraWelcomePage> createState() => _GaraWelcomePageState();
}

class _GaraWelcomePageState extends State<GaraWelcomePage>
    with TickerProviderStateMixin {

  // ── Mesh blob animators ──────────────────────────────────────────────────
  late final AnimationController _blob1;
  late final AnimationController _blob2;
  late final AnimationController _blob3;

  // ── Logo cross-fade loop (GARA 3D ↔ Kucing) ─────────────────────────────
  late final AnimationController _logoCtrl;
  late final Animation<double> _garaOpacity;
  late final Animation<double> _kucingOpacity;

  // ── Floating icon animators ──────────────────────────────────────────────
  late final List<AnimationController> _floatCtrls;
  late final List<Animation<double>> _floatY;
  late final List<Animation<double>> _floatOpacity;
  late final List<Animation<double>> _floatRotate;

  // ── Entrance animation ───────────────────────────────────────────────────
  late final AnimationController _entranceCtrl;
  late final Animation<double> _entranceFade;
  late final Animation<Offset> _entranceSlide;

  // ── Button scale ─────────────────────────────────────────────────────────
  late final AnimationController _btnCtrl;
  late final Animation<double> _btnScale;

  static const _floatingIcons = [
    Icons.menu_book_rounded,
    Icons.star_rounded,
    Icons.school_rounded,
    Icons.edit_rounded,
    Icons.lightbulb_rounded,
    Icons.psychology_rounded,
    Icons.emoji_events_rounded,
  ];
  static const _iconColors = [
    Color(0xFF3B82F6),
    Color(0xFFFBBD05),
    Color(0xFF8B5CF6),
    Color(0xFF10B981),
    Color(0xFFF97316),
    Color(0xFF06B6D4),
    Color(0xFFF43F5E),
  ];
  static const _iconX  = [0.07, 0.77, 0.14, 0.81, 0.04, 0.71, 0.48];
  static const _iconY  = [0.08, 0.06, 0.40, 0.38, 0.68, 0.66, 0.81];
  static const _iconSz = [32.0, 26.0, 24.0, 30.0, 28.0, 22.0, 26.0];
  static const _floatMs = [3200, 2800, 3600, 2500, 3100, 2900, 3400];

  @override
  void initState() {
    super.initState();

    // Mesh blob controllers — slow loop
    _blob1 = AnimationController(vsync: this, duration: const Duration(milliseconds: 18000))..repeat(reverse: true);
    _blob2 = AnimationController(vsync: this, duration: const Duration(milliseconds: 22000))..repeat(reverse: true);
    _blob3 = AnimationController(vsync: this, duration: const Duration(milliseconds: 15000))..repeat(reverse: true);
    _blob2.value = 0.33;
    _blob3.value = 0.66;

    // Floating icons
    _floatCtrls = List.generate(_floatingIcons.length, (i) {
      final c = AnimationController(vsync: this, duration: Duration(milliseconds: _floatMs[i]))
        ..repeat(reverse: true);
      c.value = i / _floatingIcons.length;
      return c;
    });

    _floatY = _floatCtrls.map((c) =>
      Tween<double>(begin: -12, end: 12)
          .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))
    ).toList();

    _floatOpacity = _floatCtrls.map((c) =>
      Tween<double>(begin: 0.25, end: 0.70)
          .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))
    ).toList();

    _floatRotate = _floatCtrls.map((c) =>
      Tween<double>(begin: -0.12, end: 0.12)
          .animate(CurvedAnimation(parent: c, curve: Curves.easeInOutSine))
    ).toList();

    // Logo cross-fade (5s loop: GARA → kucing → GARA)
    _logoCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 5000))..repeat();

    _garaOpacity = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: 0.0, end: 1.0).chain(CurveTween(curve: Curves.easeInOut)), weight: 12),
      TweenSequenceItem(tween: ConstantTween(1.0), weight: 30),
      TweenSequenceItem(tween: Tween(begin: 1.0, end: 0.0).chain(CurveTween(curve: Curves.easeInOut)), weight: 20),
      TweenSequenceItem(tween: ConstantTween(0.0), weight: 38),
    ]).animate(_logoCtrl);

    _kucingOpacity = TweenSequence<double>([
      TweenSequenceItem(tween: ConstantTween(0.0), weight: 42),
      TweenSequenceItem(tween: Tween(begin: 0.0, end: 1.0).chain(CurveTween(curve: Curves.easeInOut)), weight: 20),
      TweenSequenceItem(tween: ConstantTween(1.0), weight: 30),
      TweenSequenceItem(tween: Tween(begin: 1.0, end: 0.0).chain(CurveTween(curve: Curves.easeInOut)), weight: 8),
    ]).animate(_logoCtrl);

    // Entrance
    _entranceCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 700))..forward();
    _entranceFade = CurvedAnimation(parent: _entranceCtrl, curve: Curves.easeOut);
    _entranceSlide = Tween<Offset>(begin: const Offset(0, 0.06), end: Offset.zero)
        .animate(CurvedAnimation(parent: _entranceCtrl, curve: Curves.easeOutCubic));

    // Button
    _btnCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 100), lowerBound: 0.0, upperBound: 1.0);
    _btnScale = Tween<double>(begin: 1.0, end: 0.94)
        .animate(CurvedAnimation(parent: _btnCtrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _blob1.dispose(); _blob2.dispose(); _blob3.dispose();
    _logoCtrl.dispose();
    _entranceCtrl.dispose();
    _btnCtrl.dispose();
    for (final c in _floatCtrls) { c.dispose(); }
    super.dispose();
  }

  void _onLanjutkan() {
    _btnCtrl.forward().then((_) {
      _btnCtrl.reverse();
      if (!mounted) return;
      Navigator.of(context).push(_slideUpRoute(const LoginPage()));
    });
  }

  PageRoute<dynamic> _slideUpRoute(Widget page) {
    return PageRouteBuilder(
      pageBuilder: (_, __, ___) => page,
      transitionDuration: const Duration(milliseconds: 450),
      reverseTransitionDuration: const Duration(milliseconds: 300),
      transitionsBuilder: (_, animation, __, child) {
        final curved = CurvedAnimation(parent: animation, curve: Curves.easeOutCubic);
        return SlideTransition(
          position: Tween<Offset>(begin: const Offset(0, 1), end: Offset.zero).animate(curved),
          child: FadeTransition(
            opacity: Tween<double>(begin: 0.0, end: 1.0).animate(
              CurvedAnimation(parent: animation, curve: const Interval(0.0, 0.5)),
            ),
            child: child,
          ),
        );
      },
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
          // ── Layer 1: Animated mesh blob background ──────────────────────
          AnimatedBuilder(
            animation: Listenable.merge([_blob1, _blob2, _blob3]),
            builder: (_, __) => CustomPaint(
              painter: _GaraMeshPainter(
                t1: _blob1.value,
                t2: _blob2.value,
                t3: _blob3.value,
              ),
            ),
          ),

          // ── Layer 2: Floating animated education icons ──────────────────
          for (int i = 0; i < _floatingIcons.length; i++)
            AnimatedBuilder(
              animation: _floatCtrls[i],
              builder: (_, __) => Positioned(
                left: _iconX[i] * size.width,
                top: _iconY[i] * size.height + _floatY[i].value,
                child: Opacity(
                  opacity: _floatOpacity[i].value,
                  child: Transform.rotate(
                    angle: _floatRotate[i].value,
                    child: Icon(_floatingIcons[i], size: _iconSz[i], color: _iconColors[i]),
                  ),
                ),
              ),
            ),

          // ── Layer 3: Main content ───────────────────────────────────────
          SafeArea(
            child: FadeTransition(
              opacity: _entranceFade,
              child: SlideTransition(
                position: _entranceSlide,
                child: Column(
                  children: [
                    const Spacer(flex: 2),

                    // Logo cross-fade — besar, transparan, no box
                    SizedBox(
                      width: 260,
                      height: 260,
                      child: AnimatedBuilder(
                        animation: _logoCtrl,
                        builder: (_, __) => Stack(
                          alignment: Alignment.center,
                          children: [
                            Opacity(
                              opacity: _kucingOpacity.value,
                              child: Image.asset(
                                'assets/images/kucing_baru.png',
                                width: 260, height: 260,
                                fit: BoxFit.contain,
                              ),
                            ),
                            Opacity(
                              opacity: _garaOpacity.value,
                              child: Image.asset(
                                'assets/images/GARA_3D.png',
                                width: 250, height: 250,
                                fit: BoxFit.contain,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const Spacer(flex: 1),

                    // Teks
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 32),
                      child: Column(
                        children: [
                          Text(
                            'Selamat Datang di GARA',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 26,
                              fontWeight: FontWeight.w800,
                              color: GaraColors.dsSlate800,
                              height: 1.25,
                            ),
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Gapai Prestasi Setinggi Mungkin',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: GaraColors.dsSlate500,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const Spacer(flex: 3),

                    // Tombol Lanjutkan
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 28),
                      child: GestureDetector(
                        onTapDown: (_) => _btnCtrl.forward(),
                        onTapUp: (_) => _onLanjutkan(),
                        onTapCancel: () => _btnCtrl.reverse(),
                        child: ScaleTransition(
                          scale: _btnScale,
                          child: Container(
                            height: 56,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ),
                              borderRadius: BorderRadius.circular(28),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF1D4ED8).withOpacity(0.45),
                                  blurRadius: 28,
                                  offset: const Offset(0, 12),
                                ),
                              ],
                            ),
                            child: Center(
                              child: Text(
                                'Lanjutkan',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                  letterSpacing: 0.3,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    Text(
                      'GARA — Garuda Akademi',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: GaraColors.dsSlate400,
                        letterSpacing: 0.8,
                      ),
                    ),

                    const SizedBox(height: 36),
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
// Mesh Painter — animated gradient blobs
// ─────────────────────────────────────────────────────────────────────────────
class _GaraMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _GaraMeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    // Base
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    // Blob 1 — biru (kiri atas)
    _drawBlob(canvas,
      color: GaraColors.dsBlob1.withOpacity(0.80),
      radius: size.width * 0.52,
      baseX: size.width * 0.05,
      baseY: size.height * 0.02,
      t: t1, dx: 40, dy: 30, blur: 70,
    );

    // Blob 2 — ungu (kanan tengah)
    _drawBlob(canvas,
      color: GaraColors.dsBlob2.withOpacity(0.72),
      radius: size.width * 0.48,
      baseX: size.width * 0.88,
      baseY: size.height * 0.40,
      t: t2, dx: -35, dy: 40, blur: 80,
    );

    // Blob 3 — cyan (kiri bawah)
    _drawBlob(canvas,
      color: GaraColors.dsBlob3.withOpacity(0.68),
      radius: size.width * 0.42,
      baseX: size.width * 0.10,
      baseY: size.height * 0.85,
      t: t3, dx: 30, dy: -35, blur: 65,
    );
  }

  void _drawBlob(Canvas canvas, {
    required Color color,
    required double radius,
    required double baseX, required double baseY,
    required double t, required double dx, required double dy,
    required double blur,
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
  bool shouldRepaint(_GaraMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
