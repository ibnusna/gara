// ============================================================
//  GARA Flutter — Widget: Dot Indicator (Welcome Screen)
//  Mereplikasi: .slide-indicators .dot dari login.css
// ============================================================

import 'package:flutter/material.dart';
import '../utils/app_constants.dart';

class DotIndicator extends StatelessWidget {
  final bool isActive;

  const DotIndicator({super.key, required this.isActive});

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
      // Active dot: w=20px (pill shape), inactive: w=6px (circle)
      width: isActive ? 20.0 : 6.0,
      height: 6.0,
      decoration: BoxDecoration(
        color: isActive
            ? GaraColors.primaryLight
            : Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(isActive ? 10.0 : 3.0),
      ),
    );
  }
}

/// Row of 3 dots — dot tengah aktif seperti di web (dot.active adalah index 1)
class SlideIndicators extends StatelessWidget {
  const SlideIndicators({super.key});

  @override
  Widget build(BuildContext context) {
    return const Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        DotIndicator(isActive: false),
        SizedBox(width: 8),
        DotIndicator(isActive: true),
        SizedBox(width: 8),
        DotIndicator(isActive: false),
      ],
    );
  }
}
