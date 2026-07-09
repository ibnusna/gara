// ============================================================
//  GARA Flutter — Design System: Mesh Background Widget
//  Sumber: dashborad.html (.mesh-bg + .mesh-blob + @keyframes drift)
//
//  Mengimplementasikan 3 blob berwarna yang melayang secara halus
//  di background dashboard menggunakan AnimationController + CustomPainter.
//
//  Blob:
//    blob-blue   : #93c5fd — top-left, 40vw, drift normal
//    blob-purple : #d8b4fe — top-right, 50vw, delay -5s
//    blob-cyan   : #67e8f9 — bottom-left, 35vw, delay -10s
//
//  Animasi: drift 25s infinite alternate cubic-bezier(0.4, 0, 0.2, 1)
// ============================================================

import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../../utils/app_constants.dart';

class GaraMeshBackground extends StatefulWidget {
  final Widget child;

  const GaraMeshBackground({super.key, required this.child});

  @override
  State<GaraMeshBackground> createState() => _GaraMeshBackgroundState();
}

class _GaraMeshBackgroundState extends State<GaraMeshBackground>
    with TickerProviderStateMixin {
  // 3 controller untuk setiap blob, offset berbeda (simulate animation-delay)
  late final AnimationController _ctrl1;
  late final AnimationController _ctrl2;
  late final AnimationController _ctrl3;

  late final Animation<double> _anim1;
  late final Animation<double> _anim2;
  late final Animation<double> _anim3;

  @override
  void initState() {
    super.initState();

    // Cubic-bezier(0.4, 0, 0.2, 1) → mendekati Curves.easeInOut
    const curve = Curves.easeInOut;

    // Blob 1: biru — 25s, mulai dari awal
    _ctrl1 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim1 = CurvedAnimation(parent: _ctrl1, curve: curve);

    // Blob 2: ungu — 25s, simulate delay -5s (mulai dari posisi tengah)
    _ctrl2 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim2 = CurvedAnimation(parent: _ctrl2, curve: curve);
    // Offset simulasi delay -5s → maju 5/25 = 0.2 progress
    _ctrl2.value = 0.2;

    // Blob 3: cyan — 25s, simulate delay -10s
    _ctrl3 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim3 = CurvedAnimation(parent: _ctrl3, curve: curve);
    // Offset simulasi delay -10s → maju 10/25 = 0.4 progress
    _ctrl3.value = 0.4;
  }

  @override
  void dispose() {
    _ctrl1.dispose();
    _ctrl2.dispose();
    _ctrl3.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // Mesh background layer — fixed position, di belakang segalanya
        Positioned.fill(
          child: AnimatedBuilder(
            animation: Listenable.merge([_anim1, _anim2, _anim3]),
            builder: (context, _) {
              return CustomPaint(
                painter: _MeshPainter(
                  t1: _anim1.value,
                  t2: _anim2.value,
                  t3: _anim3.value,
                ),
              );
            },
          ),
        ),
        // Konten aplikasi di atas mesh
        widget.child,
      ],
    );
  }
}

/// Painter yang menggambar 3 blob berwarna dengan MaskFilter.blur.
/// Setiap blob bergerak mengikuti fungsi drift yang berbeda.
class _MeshPainter extends CustomPainter {
  final double t1; // 0.0 → 1.0
  final double t2;
  final double t3;

  const _MeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    // Background base: #f1f5f9
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    // Blob 1: Biru — top-left area, 40vw
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob1.withOpacity(0.8),
      blobRadius: size.width * 0.2,    // radius = 40vw/2
      baseX: size.width * -0.1,        // left: -10%
      baseY: size.height * -0.1,       // top: -10%
      t: t1,
      // Drift: translate(0→30px, 0→-50px), scale(1→1.1)
      driftX: 30,
      driftY: -50,
      blurRadius: 60,
    );

    // Blob 2: Ungu — top-right area, 50vw
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob2.withOpacity(0.8),
      blobRadius: size.width * 0.25,   // radius = 50vw/2
      baseX: size.width * 1.2,         // right: -20% (kanan layar)
      baseY: size.height * 0.4,        // top: 40%
      t: t2,
      driftX: -20,
      driftY: 20,
      blurRadius: 70,
    );

    // Blob 3: Cyan — bottom-left area, 35vw
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob3.withOpacity(0.8),
      blobRadius: size.width * 0.175,  // radius = 35vw/2
      baseX: size.width * 0.1,         // left: 10%
      baseY: size.height * 1.1,        // bottom: -10%
      t: t3,
      driftX: 15,
      driftY: -30,
      blurRadius: 55,
    );
  }

  void _drawBlob({
    required Canvas canvas,
    required Size size,
    required Color color,
    required double blobRadius,
    required double baseX,
    required double baseY,
    required double t,
    required double driftX,
    required double driftY,
    required double blurRadius,
  }) {
    // Interpolasi drift menggunakan sin untuk gerakan yang lebih organik
    final progress = math.sin(t * math.pi); // 0→1→0 sepanjang cycle
    final x = baseX + driftX * progress;
    final y = baseY + driftY * progress;
    final scale = 1.0 + 0.1 * progress; // scale 1.0 → 1.1

    final paint = Paint()
      ..color = color
      ..maskFilter = MaskFilter.blur(BlurStyle.normal, blurRadius);

    canvas.drawCircle(
      Offset(x, y),
      blobRadius * scale,
      paint,
    );
  }

  @override
  bool shouldRepaint(_MeshPainter oldDelegate) =>
      oldDelegate.t1 != t1 || oldDelegate.t2 != t2 || oldDelegate.t3 != t3;
}
