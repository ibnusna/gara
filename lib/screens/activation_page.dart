import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/activation_service.dart';
import '../utils/app_constants.dart';
import 'splash_logic_page.dart';

class ActivationPage extends StatefulWidget {
  const ActivationPage({super.key});

  @override
  State<ActivationPage> createState() => _ActivationPageState();
}

class _ActivationPageState extends State<ActivationPage>
    with TickerProviderStateMixin {
  final TextEditingController _keyController = TextEditingController();
  final ActivationService _activationService = ActivationService();
  bool _isLoading = false;
  String? _errorMessage;

  // Mesh background animators
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  // Content fade-in animator
  late final AnimationController _contentCtrl;
  late final Animation<double> _contentFade;
  late final Animation<Offset> _contentSlide;

  @override
  void initState() {
    super.initState();

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
    _blobCtrl3.value = 0.5;

    _contentCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 700));
    _contentFade = CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut);
    _contentSlide = Tween<Offset>(
      begin: const Offset(0, 0.12),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut));

    _contentCtrl.forward();
  }

  @override
  void dispose() {
    _keyController.dispose();
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    _contentCtrl.dispose();
    super.dispose();
  }

  Future<void> _activate() async {
    final key = _keyController.text.trim();
    if (key.isEmpty) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _activationService.activateSchool(key);
      if (result != null) {
        if (!mounted) return;
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            pageBuilder: (_, __, ___) => const SplashLogicPage(),
            transitionsBuilder: (_, anim, __, child) =>
                FadeTransition(opacity: anim, child: child),
            transitionDuration: const Duration(milliseconds: 300),
          ),
        );
      } else {
        setState(() {
          _errorMessage = "School Key tidak valid atau tidak ditemukan.";
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
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
                painter: _ActivationMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                ),
              ),
            ),
          ),

          // ── Content ──
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 40),
                child: FadeTransition(
                  opacity: _contentFade,
                  child: SlideTransition(
                    position: _contentSlide,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // ── GARA Blue Logo ──
                        Center(
                          child: Container(
                            width: 96,
                            height: 96,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(24),
                              boxShadow: [
                                BoxShadow(
                                  color: GaraColors.dsPrimaryDeep
                                      .withOpacity(0.16),
                                  blurRadius: 28,
                                  offset: const Offset(0, 8),
                                ),
                              ],
                            ),
                            padding: const EdgeInsets.all(14),
                            child: ColorFiltered(
                              colorFilter: const ColorFilter.mode(
                                GaraColors.dsPrimaryDeep,
                                BlendMode.srcIn,
                              ),
                              child: Image.asset(
                                'assets/images/FARA_BLACK.png',
                                fit: BoxFit.contain,
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(height: 28),

                        // ── Heading ──
                        Text(
                          'Aktivasi Sekolah',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                            color: GaraColors.dsSlate800,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Masukkan School Key untuk menghubungkan\naplikasi dengan sekolah Anda.',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 14,
                            color: GaraColors.dsSlate500,
                            height: 1.5,
                          ),
                        ),

                        const SizedBox(height: 36),

                        // ── Glass Card Form ──
                        Container(
                          decoration: BoxDecoration(
                            color: GaraColors.dsGlassBgStrong,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: GaraColors.dsGlassBorderStrong,
                              width: 1.5,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.04),
                                blurRadius: 20,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              // School Key field
                              Text(
                                'School Key',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: GaraColors.dsSlate500,
                                  letterSpacing: 0.4,
                                ),
                              ),
                              const SizedBox(height: 8),
                              TextField(
                                controller: _keyController,
                                textCapitalization:
                                    TextCapitalization.characters,
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w600,
                                  color: GaraColors.dsSlate800,
                                  letterSpacing: 2,
                                ),
                                onSubmitted: (_) => _isLoading ? null : _activate(),
                                decoration: InputDecoration(
                                  hintText: 'Contoh: GARA2024',
                                  hintStyle: GoogleFonts.plusJakartaSans(
                                    fontSize: 14,
                                    color: GaraColors.dsSlate400,
                                    letterSpacing: 1,
                                    fontWeight: FontWeight.w500,
                                  ),
                                  prefixIcon: const Icon(
                                    Icons.vpn_key_rounded,
                                    color: GaraColors.dsPrimaryDeep,
                                    size: 20,
                                  ),
                                  filled: true,
                                  fillColor: Colors.white.withOpacity(0.6),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(
                                        color: GaraColors.dsSlate200,
                                        width: 1.5),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(
                                        color: GaraColors.dsSlate200,
                                        width: 1.5),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(
                                        color: GaraColors.dsPrimaryDeep,
                                        width: 2),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(
                                      horizontal: 16, vertical: 14),
                                ),
                              ),

                              // Error message
                              if (_errorMessage != null) ...[
                                const SizedBox(height: 12),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 14, vertical: 10),
                                  decoration: BoxDecoration(
                                    color: GaraColors.dsRose500.withOpacity(0.08),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                      color:
                                          GaraColors.dsRose500.withOpacity(0.25),
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.error_outline_rounded,
                                          color: GaraColors.dsRose500, size: 16),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _errorMessage!,
                                          style: GoogleFonts.plusJakartaSans(
                                            fontSize: 13,
                                            color: GaraColors.dsRose500,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              const SizedBox(height: 20),

                              // Activate button
                              SizedBox(
                                height: 52,
                                child: _isLoading
                                    ? Center(
                                        child: SizedBox(
                                          width: 26,
                                          height: 26,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2.5,
                                            valueColor:
                                                const AlwaysStoppedAnimation<
                                                    Color>(
                                              GaraColors.dsPrimaryDeep,
                                            ),
                                          ),
                                        ),
                                      )
                                    : DecoratedBox(
                                        decoration: BoxDecoration(
                                          gradient: const LinearGradient(
                                            colors: [
                                              GaraColors.dsPrimaryDeep,
                                              GaraColors.dsPrimaryBright,
                                            ],
                                          ),
                                          borderRadius:
                                              BorderRadius.circular(14),
                                          boxShadow: [
                                            BoxShadow(
                                              color: GaraColors.dsPrimaryDeep
                                                  .withOpacity(0.30),
                                              blurRadius: 16,
                                              offset: const Offset(0, 6),
                                            ),
                                          ],
                                        ),
                                        child: ElevatedButton(
                                          onPressed: _activate,
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.transparent,
                                            shadowColor: Colors.transparent,
                                            shape: RoundedRectangleBorder(
                                                borderRadius:
                                                    BorderRadius.circular(14)),
                                          ),
                                          child: Text(
                                            'Aktivasi',
                                            style: GoogleFonts.plusJakartaSans(
                                              fontSize: 16,
                                              fontWeight: FontWeight.w700,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
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
// Mesh Painter
// ─────────────────────────────────────────────────────────────────────────────
class _ActivationMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _ActivationMeshPainter(
      {required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );
    _blob(canvas, size,
        color: GaraColors.dsBlob1.withOpacity(0.75),
        radius: size.width * 0.40,
        baseX: size.width * 0.0,
        baseY: size.height * 0.15,
        t: t1,
        dx: 20,
        dy: -30,
        blur: 55);
    _blob(canvas, size,
        color: GaraColors.dsBlob2.withOpacity(0.65),
        radius: size.width * 0.38,
        baseX: size.width * 0.9,
        baseY: size.height * 0.6,
        t: t2,
        dx: -18,
        dy: 20,
        blur: 60);
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.60),
        radius: size.width * 0.30,
        baseX: size.width * 0.2,
        baseY: size.height * 0.9,
        t: t3,
        dx: 15,
        dy: -20,
        blur: 48);
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
  bool shouldRepaint(_ActivationMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
