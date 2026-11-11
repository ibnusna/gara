import 'package:flutter/material.dart';
import 'login_mesh_painter.dart';

class LoginAnimatedBackground extends StatefulWidget {
  final Widget child;

  const LoginAnimatedBackground({super.key, required this.child});

  @override
  State<LoginAnimatedBackground> createState() => _LoginAnimatedBackgroundState();
}

class _LoginAnimatedBackgroundState extends State<LoginAnimatedBackground>
    with TickerProviderStateMixin {
  late final AnimationController _blob1;
  late final AnimationController _blob2;
  late final AnimationController _blob3;

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
  static const _floatX = [0.06, 0.78, 0.12, 0.82, 0.04, 0.70];
  static const _floatY0 = [0.10, 0.06, 0.42, 0.38, 0.68, 0.65];
  static const _floatSz = [30.0, 24.0, 22.0, 28.0, 26.0, 20.0];
  static const _floatMs = [3200, 2800, 3600, 2500, 3100, 2900];

  @override
  void initState() {
    super.initState();
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
    _blob1.dispose(); _blob2.dispose(); _blob3.dispose();
    for (final c in _floatCtrls) { c.dispose(); }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return Stack(
      fit: StackFit.expand,
      children: [
        AnimatedBuilder(
          animation: Listenable.merge([_blob1, _blob2, _blob3]),
          builder: (_, __) => CustomPaint(
            painter: LoginMeshPainter(t1: _blob1.value, t2: _blob2.value, t3: _blob3.value),
          ),
        ),
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
        widget.child,
      ],
    );
  }
}
