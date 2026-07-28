import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'login_page.dart';

// ─────────────────────────────────────────────────────────────────────────────
// GaraWelcomePage — WhatsApp-style welcome dengan floating education icons
// ─────────────────────────────────────────────────────────────────────────────
class GaraWelcomePage extends StatefulWidget {
  const GaraWelcomePage({super.key});

  @override
  State<GaraWelcomePage> createState() => _GaraWelcomePageState();
}

class _GaraWelcomePageState extends State<GaraWelcomePage>
    with TickerProviderStateMixin {

  // ── Logo cross-fade loop (GARA 3D ↔ Kucing) ──
  late final AnimationController _logoCtrl;
  late final Animation<double> _garaOpacity;
  late final Animation<double> _kucingOpacity;

  // ── Floating icon animators (7 icon particles) ──
  late final List<AnimationController> _floatCtrls;
  late final List<Animation<double>> _floatY;
  late final List<Animation<double>> _floatOpacity;
  late final List<Animation<double>> _floatRotate;

  // ── Entrance animation ──
  late final AnimationController _entranceCtrl;
  late final Animation<double> _entranceFade;
  late final Animation<Offset> _entranceSlide;

  // ── Button press scale ──
  late final AnimationController _btnCtrl;
  late final Animation<double> _btnScale;

  static const _icons = [
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
  static const _iconY  = [0.10, 0.07, 0.43, 0.40, 0.70, 0.68, 0.83];
  static const _iconSz = [30.0, 24.0, 22.0, 28.0, 26.0, 20.0, 24.0];
  static const _floatMs = [3200, 2800, 3500, 2600, 3100, 2900, 3300];

  @override
  void initState() {
    super.initState();

    // Floating animators
    _floatCtrls = List.generate(_icons.length, (i) {
      final c = AnimationController(
        vsync: this,
        duration: Duration(milliseconds: _floatMs[i]),
      )..repeat(reverse: true);
      c.value = i / _icons.length; // stagger
      return c;
    });

    _floatY = _floatCtrls.map((c) =>
      Tween<double>(begin: -10, end: 10).animate(
        CurvedAnimation(parent: c, curve: Curves.easeInOutSine),
      )
    ).toList();

    _floatOpacity = _floatCtrls.map((c) =>
      Tween<double>(begin: 0.15, end: 0.50).animate(
        CurvedAnimation(parent: c, curve: Curves.easeInOutSine),
      )
    ).toList();

    _floatRotate = _floatCtrls.map((c) =>
      Tween<double>(begin: -0.08, end: 0.08).animate(
        CurvedAnimation(parent: c, curve: Curves.easeInOutSine),
      )
    ).toList();

    // Logo cross-fade loop (5s cycle)
    _logoCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 5000),
    )..repeat();

    _garaOpacity = TweenSequence<double>([
      TweenSequenceItem(
        tween: Tween(begin: 0.0, end: 1.0).chain(CurveTween(curve: Curves.easeInOut)),
        weight: 12,
      ),
      TweenSequenceItem(tween: ConstantTween(1.0), weight: 30),
      TweenSequenceItem(
        tween: Tween(begin: 1.0, end: 0.0).chain(CurveTween(curve: Curves.easeInOut)),
        weight: 20,
      ),
      TweenSequenceItem(tween: ConstantTween(0.0), weight: 38),
    ]).animate(_logoCtrl);

    _kucingOpacity = TweenSequence<double>([
      TweenSequenceItem(tween: ConstantTween(0.0), weight: 42),
      TweenSequenceItem(
        tween: Tween(begin: 0.0, end: 1.0).chain(CurveTween(curve: Curves.easeInOut)),
        weight: 20,
      ),
      TweenSequenceItem(tween: ConstantTween(1.0), weight: 30),
      TweenSequenceItem(
        tween: Tween(begin: 1.0, end: 0.0).chain(CurveTween(curve: Curves.easeInOut)),
        weight: 8,
      ),
    ]).animate(_logoCtrl);

    // Entrance
    _entranceCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    )..forward();

    _entranceFade = CurvedAnimation(
      parent: _entranceCtrl,
      curve: Curves.easeOut,
    );
    _entranceSlide = Tween<Offset>(
      begin: const Offset(0, 0.05),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _entranceCtrl,
      curve: Curves.easeOutCubic,
    ));

    // Button scale
    _btnCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 100),
      lowerBound: 0.0,
      upperBound: 1.0,
    );
    _btnScale = Tween<double>(begin: 1.0, end: 0.95)
        .animate(CurvedAnimation(parent: _btnCtrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _logoCtrl.dispose();
    _entranceCtrl.dispose();
    _btnCtrl.dispose();
    for (final c in _floatCtrls) {
      c.dispose();
    }
    super.dispose();
  }

  void _onLanjutkan() {
    _btnCtrl.forward().then((_) {
      _btnCtrl.reverse();
      if (!mounted) return;
      Navigator.of(context).push(_slideUpRoute(const LoginPage()));
    });
  }

  // WhatsApp-style: slide up dari bawah + fade
  PageRoute<dynamic> _slideUpRoute(Widget page) {
    return PageRouteBuilder(
      pageBuilder: (_, __, ___) => page,
      transitionDuration: const Duration(milliseconds: 420),
      reverseTransitionDuration: const Duration(milliseconds: 300),
      transitionsBuilder: (_, animation, __, child) {
        final curved = CurvedAnimation(
          parent: animation,
          curve: Curves.easeOutCubic,
        );
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0, 1),
            end: Offset.zero,
          ).animate(curved),
          child: FadeTransition(
            opacity: Tween<double>(begin: 0.0, end: 1.0).animate(
              CurvedAnimation(
                parent: animation,
                curve: const Interval(0.0, 0.4),
              ),
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
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          // ── Clean gradient background (bersih, tanpa blob) ──
          const Positioned.fill(
            child: DecoratedBox(
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
          ),

          // ── Floating animated education icons ──
          for (int i = 0; i < _icons.length; i++)
            AnimatedBuilder(
              animation: _floatCtrls[i],
              builder: (context, _) {
                return Positioned(
                  left: _iconX[i] * size.width,
                  top: _iconY[i] * size.height + _floatY[i].value,
                  child: Opacity(
                    opacity: _floatOpacity[i].value,
                    child: Transform.rotate(
                      angle: _floatRotate[i].value,
                      child: Icon(
                        _icons[i],
                        size: _iconSz[i],
                        color: _iconColors[i],
                      ),
                    ),
                  ),
                );
              },
            ),

          // ── Main content ──
          SafeArea(
            child: FadeTransition(
              opacity: _entranceFade,
              child: SlideTransition(
                position: _entranceSlide,
                child: Column(
                  children: [
                    const Spacer(flex: 2),

                    // ── Logo cross-fade (BESAR, no box, transparent) ──
                    SizedBox(
                      width: 260,
                      height: 260,
                      child: AnimatedBuilder(
                        animation: _logoCtrl,
                        builder: (context, _) {
                          return Stack(
                            alignment: Alignment.center,
                            children: [
                              // Kucing baru (transparent BG)
                              Opacity(
                                opacity: _kucingOpacity.value,
                                child: Image.asset(
                                  'assets/images/kucing_baru.png',
                                  width: 260,
                                  height: 260,
                                  fit: BoxFit.contain,
                                ),
                              ),
                              // GARA 3D logo (transparent BG)
                              Opacity(
                                opacity: _garaOpacity.value,
                                child: Image.asset(
                                  'assets/images/GARA_3D.png',
                                  width: 250,
                                  height: 250,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),

                    const Spacer(flex: 1),

                    // ── Heading & Tagline ──
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
                              color: const Color(0xFF1E293B),
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
                              color: const Color(0xFF64748B),
                              height: 1.5,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const Spacer(flex: 3),

                    // ── Tombol Lanjutkan ──
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
                                  color: const Color(0xFF1D4ED8).withOpacity(0.4),
                                  blurRadius: 24,
                                  offset: const Offset(0, 10),
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
                        color: const Color(0xFF94A3B8),
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
