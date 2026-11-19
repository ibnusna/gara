import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Progress bar section for student HeroCard dashboard.
/// Conforms to <= 200 lines per file policy.
class HeroProgressBar extends StatelessWidget {
  final double progress;
  final String label;

  const HeroProgressBar({
    super.key,
    this.progress = 0.65,
    this.label = 'Progres Modul',
  });

  @override
  Widget build(BuildContext context) {
    final percentText = '${(progress * 100).toInt()}%';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              label,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: const Color(0xFFEFF6FF),
              ),
            ),
            Text(
              percentText,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: const Color(0xFFEFF6FF),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Container(
          height: 8,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.20),
            borderRadius: BorderRadius.circular(999),
          ),
          child: FractionallySizedBox(
            alignment: Alignment.centerLeft,
            widthFactor: progress.clamp(0.0, 1.0),
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Colors.white.withOpacity(0.90),
                    Colors.white,
                  ],
                ),
                borderRadius: BorderRadius.circular(999),
                boxShadow: [
                  BoxShadow(
                    color: Colors.white.withOpacity(0.50),
                    blurRadius: 6,
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}
