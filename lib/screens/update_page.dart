import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/update_service.dart';
import '../utils/app_constants.dart';

class UpdatePage extends StatefulWidget {
  final bool isForceUpdate;
  final String releaseNotes;
  final String latestVersion;

  const UpdatePage({
    super.key,
    required this.isForceUpdate,
    required this.releaseNotes,
    required this.latestVersion,
  });

  @override
  State<UpdatePage> createState() => _UpdatePageState();
}

class _UpdatePageState extends State<UpdatePage> with TickerProviderStateMixin {
  final UpdateService _updateService = UpdateService();
  bool _isDownloading = false;
  double _progress = 0.0;
  String _statusMessage = "Siap memperbarui";

  // Mesh background animators
  late final AnimationController _blobCtrl1;
  late final AnimationController _blobCtrl2;
  late final AnimationController _blobCtrl3;

  // Content entry animation
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
    _blobCtrl2.value = 0.2;
    _blobCtrl3.value = 0.5;

    _contentCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 700));
    _contentFade =
        CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut);
    _contentSlide = Tween<Offset>(
      begin: const Offset(0, 0.10),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _contentCtrl, curve: Curves.easeOut));
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

  Future<void> _startUpdate() async {
    setState(() {
      _isDownloading = true;
      _statusMessage = "Mencari tautan unduhan...";
    });

    final apkUrl = await _updateService.fetchLatestApkUrl();
    if (apkUrl == null) {
      setState(() {
        _isDownloading = false;
        _statusMessage = "Gagal menemukan tautan update.";
      });
      return;
    }

    setState(() {
      _statusMessage = "Mengunduh pembaruan...";
    });

    try {
      await _updateService.downloadAndInstallApk(apkUrl, (received, total) {
        if (total != -1) {
          setState(() {
            _progress = received / total;
          });
        }
      });
      setState(() {
        _statusMessage = "Unduhan selesai. Memulai pemasangan...";
      });
    } catch (e) {
      setState(() {
        _isDownloading = false;
        _statusMessage = "Gagal mengunduh: $e";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      body: PopScope(
        canPop: !widget.isForceUpdate && !_isDownloading,
        child: Stack(
          children: [
            // ── Animated Mesh Background ──
            Positioned.fill(
              child: AnimatedBuilder(
                animation:
                    Listenable.merge([_blobCtrl1, _blobCtrl2, _blobCtrl3]),
                builder: (context, _) => CustomPaint(
                  painter: _UpdateMeshPainter(
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
                  padding: const EdgeInsets.symmetric(
                      horizontal: 28, vertical: 40),
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
                              width: 88,
                              height: 88,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(22),
                                boxShadow: [
                                  BoxShadow(
                                    color: GaraColors.dsPrimaryDeep
                                        .withOpacity(0.16),
                                    blurRadius: 28,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              padding: const EdgeInsets.all(13),
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
                            'Pembaruan Tersedia',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 24,
                              fontWeight: FontWeight.w800,
                              color: GaraColors.dsSlate800,
                            ),
                          ),
                          const SizedBox(height: 8),

                          // Version badge
                          Center(
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 6),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(colors: [
                                  GaraColors.dsPrimaryDeep,
                                  GaraColors.dsPrimaryBright,
                                ]),
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: Text(
                                'Versi ${widget.latestVersion}',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 24),

                          // ── Release Notes Glass Card ──
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
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    const Icon(
                                      Icons.new_releases_rounded,
                                      color: GaraColors.dsPrimaryDeep,
                                      size: 18,
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      'Yang Baru',
                                      style: GoogleFonts.plusJakartaSans(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w700,
                                        color: GaraColors.dsSlate800,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  widget.releaseNotes,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 14,
                                    color: GaraColors.dsSlate500,
                                    height: 1.6,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 24),

                          // ── Progress / Action Area ──
                          if (_isDownloading) ...[
                            // Progress card
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: GaraColors.dsGlassBgStrong,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(
                                  color: GaraColors.dsGlassBorderStrong,
                                  width: 1.5,
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment:
                                    CrossAxisAlignment.stretch,
                                children: [
                                  // Progress bar
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(999),
                                    child: LinearProgressIndicator(
                                      value: _progress > 0 ? _progress : null,
                                      minHeight: 8,
                                      backgroundColor:
                                          GaraColors.dsSlate200,
                                      valueColor:
                                          const AlwaysStoppedAnimation<Color>(
                                        GaraColors.dsPrimaryDeep,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        _statusMessage,
                                        style: GoogleFonts.plusJakartaSans(
                                          fontSize: 13,
                                          color: GaraColors.dsSlate500,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                      if (_progress > 0)
                                        Text(
                                          '${(_progress * 100).toStringAsFixed(1)}%',
                                          style: GoogleFonts.plusJakartaSans(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w700,
                                            color: GaraColors.dsPrimaryDeep,
                                          ),
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ] else ...[
                            // ── Update Button ──
                            SizedBox(
                              height: 52,
                              child: DecoratedBox(
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      GaraColors.dsPrimaryDeep,
                                      GaraColors.dsPrimaryBright,
                                    ],
                                  ),
                                  borderRadius: BorderRadius.circular(14),
                                  boxShadow: [
                                    BoxShadow(
                                      color: GaraColors.dsPrimaryDeep
                                          .withOpacity(0.30),
                                      blurRadius: 16,
                                      offset: const Offset(0, 6),
                                    ),
                                  ],
                                ),
                                child: ElevatedButton.icon(
                                  onPressed: _startUpdate,
                                  icon: const Icon(
                                    Icons.system_update_alt_rounded,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                  label: Text(
                                    'Perbarui Sekarang',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.white,
                                    ),
                                  ),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    shape: RoundedRectangleBorder(
                                      borderRadius:
                                          BorderRadius.circular(14),
                                    ),
                                  ),
                                ),
                              ),
                            ),

                            // "Later" option for non-force update
                            if (!widget.isForceUpdate) ...[
                              const SizedBox(height: 12),
                              TextButton(
                                onPressed: () =>
                                    Navigator.of(context).pop(),
                                child: Text(
                                  'Nanti Saja',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 14,
                                    color: GaraColors.dsSlate400,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ],

                          // Force update notice
                          if (widget.isForceUpdate) ...[
                            const SizedBox(height: 16),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: GaraColors.dsRose500.withOpacity(0.08),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                    color: GaraColors.dsRose500
                                        .withOpacity(0.25)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(
                                      Icons.warning_amber_rounded,
                                      color: GaraColors.dsRose500,
                                      size: 16),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Pembaruan ini wajib dipasang sebelum melanjutkan.',
                                      style: GoogleFonts.plusJakartaSans(
                                        fontSize: 12,
                                        color: GaraColors.dsRose500,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Mesh Painter
// ─────────────────────────────────────────────────────────────────────────────
class _UpdateMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const _UpdateMeshPainter(
      {required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );
    _blob(canvas, size,
        color: GaraColors.dsBlob1.withOpacity(0.70),
        radius: size.width * 0.38,
        baseX: size.width * 0.0,
        baseY: size.height * 0.0,
        t: t1,
        dx: 25,
        dy: -40,
        blur: 55);
    _blob(canvas, size,
        color: GaraColors.dsBlob2.withOpacity(0.65),
        radius: size.width * 0.42,
        baseX: size.width * 0.85,
        baseY: size.height * 0.35,
        t: t2,
        dx: -20,
        dy: 25,
        blur: 65);
    _blob(canvas, size,
        color: GaraColors.dsBlob3.withOpacity(0.60),
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
  bool shouldRepaint(_UpdateMeshPainter old) =>
      old.t1 != t1 || old.t2 != t2 || old.t3 != t3;
}
