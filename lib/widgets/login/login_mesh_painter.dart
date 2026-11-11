import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../../utils/app_constants.dart';

class LoginMeshPainter extends CustomPainter {
  final double t1, t2, t3;
  const LoginMeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    _drawBlob(
      canvas,
      color: GaraColors.dsBlob1.withOpacity(0.75),
      radius: size.width * 0.50,
      baseX: size.width * 0.05,
      baseY: size.height * 0.02,
      t: t1,
      dx: 35,
      dy: 30,
      blur: 70,
    );

    _drawBlob(
      canvas,
      color: GaraColors.dsBlob2.withOpacity(0.68),
      radius: size.width * 0.46,
      baseX: size.width * 0.88,
      baseY: size.height * 0.45,
      t: t2,
      dx: -30,
      dy: 35,
      blur: 78,
    );

    _drawBlob(
      canvas,
      color: GaraColors.dsBlob3.withOpacity(0.65),
      radius: size.width * 0.40,
      baseX: size.width * 0.10,
      baseY: size.height * 0.88,
      t: t3,
      dx: 28,
      dy: -30,
      blur: 65,
    );
  }

  void _drawBlob(
    Canvas canvas, {
    required Color color,
    required double radius,
    required double baseX,
    required double baseY,
    required double t,
    required double dx,
    required double dy,
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
  bool shouldRepaint(LoginMeshPainter oldDelegate) =>
      oldDelegate.t1 != t1 || oldDelegate.t2 != t2 || oldDelegate.t3 != t3;
}
