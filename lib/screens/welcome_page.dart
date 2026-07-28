import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../utils/route_builders.dart';
import 'login_page.dart';

// ─────────────────────────────────────────────────────────────────────────────
// GaraWelcomePage — Halaman selamat datang ala WhatsApp dengan brand GARA
// ─────────────────────────────────────────────────────────────────────────────
class GaraWelcomePage extends StatefulWidget {
  const GaraWelcomePage({super.key});

  @override
  State<GaraWelcomePage> createState() => _GaraWelcomePageState();
}

class _GaraWelcomePageState extends State<GaraWelcomePage>
    with TickerProviderStateMixin {
  // ── Mesh background blob controllers ──
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  // ── Logo cross-fade loop controller ──
  // Siklus: 4000ms total
  //   0.0–0.25 : GARA 3D logo fade-in (kucing fade-out)
  //   0.25–0.5 : GARA 3D logo ditampilkan penuh
  //   0.5–0.75 : cross-fade GARA → kucing
  //   0.75–1.0 : kucing ditampilkan penuh, lalu loop balik
  late final AnimationController _logoCtrl;

  // Opacity GARA logo: max di [0.0–0.5], min di [0.5–1.0]
  late final Animation<double> _garaOpacity;

  // Opacity kucing: min di [0.0–0.5], max di [0.5–1.0]
  late final Animation<double> _kucingOpacity;

  // ── Entrance animation ──
  late final AnimationController _entranceCtrl;
  late final Animation<double> _entranceFade;
  late final Animation<Offset> _entranceSlide;

  @override
  void initState() {
    super.initState();

    // Mesh blob animators — identik dengan SplashLogicPage
    _blobCtrl1 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 25000))
      ..repeat(reverse: true);
    _blobCtrl2 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 22000))
      ..repeat(reverse: true);
    _blobCtrl3 = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 28000))
      ..repeat(reverse: true);
    _blobCtrl2.value = 0.3;
    _blobCtrl3.value = 0.6;

    // Logo cross-fade loop controller (4 detik per cycle)
    _logoCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 4000),
    )..repeat();

    // GARA logo: full opacity di 0.0→0.5, fade keluar di 0.5→0.75, tetap 0 di 0.75→1.0
    _garaOpacity = TweenSequence<double>([
      TweenSequenceItem(
        tween: Tween(begin: 0.0, end: 1.0)
            .chain(CurveTween(curve: Curves.easeInOut)),
        weight: 15, // 0.0 → 0.15: fade-in GARA
      ),
      TweenSequenceItem(
        tween: ConstantTween(1.0),
        weight: 35, // 0.15 → 0.50: GARA penuh
      ),
      TweenSequenceItem(
        tween: Tween(begin: 1.0, end: 0.0)
            .chain(CurveTween(curve: Curves.easeInOut)),
        weight: 25, // 0.50 → 0.75: fade-out GARA
      ),
      TweenSequenceItem(
        tween: ConstantTween(0.0),
        weight: 25, // 0.75 → 1.0: kucing penuh (GARA tidak kelihatan)
      ),
    ]).animate(_logoCtrl);

    // Kucing: tidak terlihat di 0.0→0.5, fade-in di 0.5→0.75, penuh di 0.75→1.0
    _kucingOpacity = TweenSequence<double>([
      TweenSequenceItem(
        tween: ConstantTween(0.0),
        weight: 50, // 0.0 → 0.50: kucing belum terlihat
      ),
      TweenSequenceItem(
        tween: Tween(begin: 0.0, end: 1.0)
            .chain(CurveTween(curve: Curves.easeInOut)),
        weight: 25, // 0.50 → 0.75: fade-in kucing
      ),
      TweenSequenceItem(
        tween: ConstantTween(1.0),
        weight: 25, // 0.75 → 1.0: kucing penuh
      ),
    ]).animate(_logoCtrl);

    // Entrance animation
    _entranceCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    )..forward();

    _entranceFade = CurvedAnimation(
      parent: _entranceCtrl,
      curve: Curves.easeOut,
    );

    _entranceSlide = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _entranceCtrl,
      curve: Curves.easeOutCubic,
    ));
  }

  @override
  void dispose() {
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    _logoCtrl.dispose();
    _entranceCtrl.dispose();
    super.dispose();
  }

  void _onLanjutkan() {
    Navigator.of(context).pushReplacement(garaFadeSlideRoute(const LoginPage()));
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      body: Stack(
        children: [
          // ── Animated Mesh Background ──
          Positioned.fill(
            child: AnimatedBuilder(
              animation:
                  Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
              builder: (context, _) => CustomPaint(
                painter: _WelcomeMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                ),
              ),
            ),
          ),

          // ── Content ──
          SafeArea(
            child: FadeTransition(
              opacity: _entranceFade,
              child: SlideTransition(
                position: _entranceSlide,
                child: Column(
                  children: [
                    // Spacer atas — mendorong logo ke tengah atas
                    SizedBox(height: size.height * 0.10),

                    // ── Logo Area (cross-fade GARA ↔ kucing) ──
                    SizedBox(
                      width: 200,
                      height: 200,
                      child: AnimatedBuilder(
                        animation: _logoCtrl,
                        builder: (context, _) {
                          return Stack(
                            alignment: Alignment.center,
                            children: [
                              // Kucing (layer bawah)
                              Opacity(
                                opacity: _kucingOpacity.value,
                                child: Image.asset(
                                  'assets/images/kucing.png',
                                  width: 200,
                                  height: 200,
                                  fit: BoxFit.contain,
                                ),
                              ),
                              // GARA 3D logo (layer atas)
                              Opacity(
                                opacity: _garaOpacity.value,
                                child: Container(
                                  width: 160,
                                  height: 160,
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(36),
                                    boxShadow: [
                                      BoxShadow(
                                        color: GaraColors.dsPrimaryDeep
                                            .withOpacity(0.18),
                                        blurRadius: 40,
                                        spreadRadius: 4,
                                        offset: const Offset(0, 12),
                                      ),
                                    ],
                                  ),
                                  padding: const EdgeInsets.all(18),
                                  child: Image.asset(
                                    'assets/images/3dlogo.png',
                                    fit: BoxFit.contain,
                                  ),
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),

                    SizedBox(height: size.height * 0.06),

                    // ── Heading ──
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 32),
                      child: Text(
                        'Selamat Datang di GARA',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 26,
                          fontWeight: FontWeight.w800,
                          color: GaraColors.dsSlate800,
                          height: 1.25,
                        ),
                      ),
                    ),

                    const SizedBox(height: 12),

                    // ── Tagline ──
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 40),
                      child: Text(
                        'Gapai Prestasi Setinggi Mungkin',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: GaraColors.dsSlate500,
                          height: 1.5,
                        ),
                      ),
                    ),

                    const Spacer(),

                    // ── Tombol Lanjutkan ──
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 32),
                      child: _WelcomeButton(onPressed: _onLanjutkan),
                    ),

                    const SizedBox(height: 16),

                    // ── Brand footer ──
                    Text(
                      'GARA — Garuda Akademi',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: GaraColors.dsSlate400,
                        letterSpacing: 0.8,
                      ),
                    ),

                    const SizedBox(height: 32),
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
// Tombol "Lanjutkan" — bergaya WhatsApp dengan warna brand GARA
// ─────────────────────────────────────────────────────────────────────────────
class _WelcomeButton extends StatefulWidget {
  final VoidCallback onPressed;
  const _WelcomeButton({required this.onPressed});

  @override
  State<_WelcomeButton> createState() => _WelcomeButtonState();
}

class _WelcomeButtonState extends State<_WelcomeButton>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 90),
      lowerBound: 0.0,
      upperBound: 1.0,
    );
    _scale = Tween<double>(begin: 1.0, end: 0.96).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeOut),
    );
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _ctrl.forward(),
      onTapUp: (_) {
        _ctrl.reverse();
        widget.onPressed();
      },
      onTapCancel: () => _ctrl.reverse(),
      child: ScaleTransition(
        scale: _scale,
        child: Container(
          height: 54,
          width: double.infinity,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [
                Color(0xFF1D4ED8), // dsPrimaryDeep
                Color(0xFF2563EB), // dsPrimaryBright
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(27),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF1D4ED8).withOpacity(0.35),
                blurRadius: 20,
                offset: const Offset(0, 8),
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
                letterSpacing: 0.5,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Mesh Painter — identik dengan _SplashMeshPainter di splash_logic_page.dart
// ─────────────────────────────────────────────────────────────────────────────
class _WelcomeMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _WelcomeMeshPainter(
      {required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );
    _blob(canvas, size,
        color: GaraColors.dsBlob1.withOpacity(0.75),
        radius: size.width * 0.38,
        baseX: size.width * 0.0,
        baseY: size.height * 0.0,
        t: t1,
        dx: 25,
        dy: -40,
        blur: 55);
    _blob(canvas, size,
        color: GaraColors.dsBlob2.withOpacity(0.70),
        radius: size.width * 0.42,
        baseX: size.width * 0.85,
        baseY: size.height * 0.35,
        t: t2,
        dx: -20,
        dy: 25,
        blur: 65);
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.65),
        radius: size.width * 0.32,
        baseX: size.width * 0.15,
        baseY: size.height * 0.88,
        t: t3,
        dx: 18,
        dy: -25,
        blur: 50);
  }

  void _blob(Canvas canvas, Size size,
      {required Color color,
      required double radius,
      required double baseX,
      required double baseY,
      required double t,
      required double dx,
      required double dy,
      required double blur}) {
    final progress = math.sin(t * math.pi);
    canvas.drawCircle(
      Offset(baseX + dx * progress, baseY + dy * progress),
      radius * (1.0 + 0.08 * progress),
      Paint()
        ..color = color
        ..maskFilter = MaskFilter.blur(BlurStyle.normal, blur),
    );
  }

  @override
  bool shouldRepaint(_WelcomeMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
