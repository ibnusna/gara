import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../../utils/app_constants.dart';
import '../../utils/responsive_utils.dart';

/// Rotated floating play action button for student HeroCard (< 200 lines).
class HeroPlayButton extends StatelessWidget {
  const HeroPlayButton({super.key});

  @override
  Widget build(BuildContext context) {
    final fabSize = GaraResponsive.isTablet(context) ? 58.0 : 50.0;
    return Transform.rotate(
      angle: 3 * math.pi / 180,
      child: Container(
        width: fabSize,
        height: fabSize,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.20),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: const Icon(
          Icons.play_arrow_rounded,
          color: GaraColors.dsPrimaryDeep,
          size: 28,
        ),
      ),
    );
  }
}
