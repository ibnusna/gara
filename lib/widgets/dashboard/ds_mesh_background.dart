














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
  
  late final AnimationController _ctrl1;
  late final AnimationController _ctrl2;
  late final AnimationController _ctrl3;

  late final Animation<double> _anim1;
  late final Animation<double> _anim2;
  late final Animation<double> _anim3;

  @override
  void initState() {
    super.initState();

    
    const curve = Curves.easeInOut;

    
    _ctrl1 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim1 = CurvedAnimation(parent: _ctrl1, curve: curve);

    
    _ctrl2 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim2 = CurvedAnimation(parent: _ctrl2, curve: curve);
    
    _ctrl2.value = 0.2;

    
    _ctrl3 = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 25000),
    )..repeat(reverse: true);
    _anim3 = CurvedAnimation(parent: _ctrl3, curve: curve);
    
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
        
        widget.child,
      ],
    );
  }
}



class _MeshPainter extends CustomPainter {
  final double t1; 
  final double t2;
  final double t3;

  const _MeshPainter({required this.t1, required this.t2, required this.t3});

  @override
  void paint(Canvas canvas, Size size) {
    
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = GaraColors.dsMeshBase,
    );

    
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob1.withOpacity(0.8),
      blobRadius: size.width * 0.2,    
      baseX: size.width * -0.1,        
      baseY: size.height * -0.1,       
      t: t1,
      
      driftX: 30,
      driftY: -50,
      blurRadius: 60,
    );

    
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob2.withOpacity(0.8),
      blobRadius: size.width * 0.25,   
      baseX: size.width * 1.2,         
      baseY: size.height * 0.4,        
      t: t2,
      driftX: -20,
      driftY: 20,
      blurRadius: 70,
    );

    
    _drawBlob(
      canvas: canvas,
      size: size,
      color: GaraColors.dsBlob3.withOpacity(0.8),
      blobRadius: size.width * 0.175,  
      baseX: size.width * 0.1,         
      baseY: size.height * 1.1,        
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
    
    final progress = math.sin(t * math.pi); 
    final x = baseX + driftX * progress;
    final y = baseY + driftY * progress;
    final scale = 1.0 + 0.1 * progress; 

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
