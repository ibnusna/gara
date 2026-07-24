import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';

class StatusPage extends StatefulWidget {
  final String status;
  final String schoolName;

  const StatusPage(
      {super.key, required this.status, required this.schoolName});

  @override
  State<StatusPage> createState() => _StatusPageState();
}

class _StatusPageState extends State<StatusPage> with TickerProviderStateMixin {
  // Mesh background animators
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  // Content fade-in
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
    _blobCtrl3.value = 0.6;

    _contentCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 700));
    _contentFade =
        CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut);
    _contentSlide = Tween<Offset>(
      begin: const Offset(0, 0.10),
      end: Offset.zero,
    ).animate(
        CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut));
    _contentCtrl.forward();
  }

  @override
  void dispose() {
    _blobCtrl1.dispose();
    _blobCtrl2.dispose();
    _blobCtrl3.dispose();
    _contentCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isMaintenance = widget.status == 'maintenance';

    // Design tokens per status
    final Color accentColor =
        isMaintenance ? const Color(0xFFF59E0B) : GaraColors.dsRose500;
    final Color accentLight = isMaintenance
        ? const Color(0xFFFEF3C7)
        : GaraColors.dsRose500.withOpacity(0.08);
    final Color accentBorder = isMaintenance
        ? const Color(0xFFF59E0B).withOpacity(0.30)
        : GaraColors.dsRose500.withOpacity(0.25);
    final IconData iconData = isMaintenance
        ? Icons.construction_rounded
        : Icons.block_rounded;
    final String title =
        isMaintenance ? 'Sedang Pemeliharaan' : 'Akses Ditangguhkan';
    final String message = isMaintenance
        ? 'Sistem ${widget.schoolName} sedang dalam pemeliharaan.\nSilakan coba beberapa saat lagi.'
        : 'Akses untuk ${widget.schoolName} telah ditangguhkan.\nSilakan hubungi administrator.';

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
                painter: _StatusMeshPainter(
                  t1: _blobCtrl1.value,
                  t2: _blobCtrl2.value,
                  t3: _blobCtrl3.value,
                  isMaintenance: isMaintenance,
                ),
              ),
            ),
          ),

          // ── Centered Content ──
          SafeArea(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 28),
                child: FadeTransition(
                  opacity: _contentFade,
                  child: SlideTransition(
                    position: _contentSlide,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // GARA Logo (small branding)
                        Container(
                          width: 56,
                          height: 56,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color:
                                    GaraColors.dsPrimaryDeep.withOpacity(0.10),
                                blurRadius: 16,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(10),
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

                        const SizedBox(height: 32),

                        // Status Icon Container
                        Container(
                          width: 88,
                          height: 88,
                          decoration: BoxDecoration(
                            color: accentLight,
                            shape: BoxShape.circle,
                            border: Border.all(color: accentBorder, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: accentColor.withOpacity(0.15),
                                blurRadius: 24,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Icon(iconData, color: accentColor, size: 40),
                        ),

                        const SizedBox(height: 28),

                        // Title
                        Text(
                          title,
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 22,
                            fontWeight: FontWeight.w800,
                            color: GaraColors.dsSlate800,
                          ),
                        ),

                        const SizedBox(height: 12),

                        // Glass info card
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: GaraColors.dsGlassBgStrong,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(
                              color: GaraColors.dsGlassBorderStrong,
                              width: 1.5,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.04),
                                blurRadius: 16,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Column(
                            children: [
                              // School name badge
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 14, vertical: 6),
                                decoration: BoxDecoration(
                                  color: accentColor.withOpacity(0.10),
                                  borderRadius: BorderRadius.circular(999),
                                  border: Border.all(
                                    color: accentColor.withOpacity(0.25),
                                  ),
                                ),
                                child: Text(
                                  widget.schoolName,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    color: accentColor,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              Text(
                                message,
                                textAlign: TextAlign.center,
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 14,
                                  color: GaraColors.dsSlate500,
                                  height: 1.6,
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 28),

                        // Subtle footer note
                        Text(
                          isMaintenance
                              ? 'Sistem akan kembali normal sebentar lagi.'
                              : 'Untuk informasi lebih lanjut, hubungi admin sekolah.',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 12,
                            color: GaraColors.dsSlate400,
                            fontStyle: FontStyle.italic,
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
// Mesh Painter — tinted for maintenance (amber) or suspend (rose)
// ─────────────────────────────────────────────────────────────────────────────
class _StatusMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  final bool isMaintenance;
  const _StatusMeshPainter(
      {required this.t1,
      required this.t2,
      required this.t3,
      required this.isMaintenance});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    // Blob 1: primary blue (brand)
    _blob(canvas, size,
        color: GaraColors.dsBlob1.withOpacity(0.55),
        radius: size.width * 0.38,
        baseX: size.width * 0.0,
        baseY: size.height * 0.0,
        t: t1,
        dx: 25,
        dy: -40,
        blur: 55);

    // Blob 2: status-colored tint
    final Color blob2 = isMaintenance
        ? const Color(0xFFFBD38D).withOpacity(0.65)
        : const Color(0xFFFDA4AF).withOpacity(0.60);
    _blob(canvas, size,
        color: blob2,
        radius: size.width * 0.42,
        baseX: size.width * 0.85,
        baseY: size.height * 0.30,
        t: t2,
        dx: -20,
        dy: 25,
        blur: 65);

    // Blob 3: subtle cyan
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.50),
        radius: size.width * 0.30,
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
  bool shouldRepaint(_StatusMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
