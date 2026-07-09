
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class TimerRing extends StatelessWidget {
  final int secondsRemaining;
  final int totalSeconds;
  final String modeLabel; 

  const TimerRing({
    super.key,
    required this.secondsRemaining,
    required this.totalSeconds,
    required this.modeLabel,
  });

  String get _display {
    final m = secondsRemaining ~/ 60;
    final s = secondsRemaining % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    final progress = totalSeconds > 0
        ? (totalSeconds - secondsRemaining) / totalSeconds
        : 0.0;

    return SizedBox(
      width: 240,
      height: 240,
      child: Stack(alignment: Alignment.center, children: [
        
        CustomPaint(
          size: const Size(240, 240),
          painter: _RingPainter(progress: progress.clamp(0.0, 1.0)),
        ),
        
        Column(mainAxisSize: MainAxisSize.min, children: [
          Text(_display,
              style: GoogleFonts.poppins(
                  fontSize: 48,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF1E3A8A))),
          Text(modeLabel,
              style: GoogleFonts.poppins(
                  fontSize: 14, color: const Color(0xFF3B82F6))),
        ]),
      ]),
    );
  }
}

class _RingPainter extends CustomPainter {
  final double progress; 

  const _RingPainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    final cx = size.width / 2;
    final cy = size.height / 2;
    final radius = cx - 10;

    
    canvas.drawCircle(
      Offset(cx, cy),
      radius,
      Paint()
        ..color = const Color(0xFFBFDBFE)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 12,
    );

    if (progress <= 0) return;

    
    canvas.drawArc(
      Rect.fromCircle(center: Offset(cx, cy), radius: radius),
      -3.14159 / 2, 
      2 * 3.14159 * progress,
      false,
      Paint()
        ..color = const Color(0xFF2563EB)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 12
        ..strokeCap = StrokeCap.round,
    );
  }

  @override
  bool shouldRepaint(_RingPainter old) => old.progress != progress;
}
