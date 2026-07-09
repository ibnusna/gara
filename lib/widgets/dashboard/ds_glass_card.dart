


















import 'dart:ui';
import 'package:flutter/material.dart';


enum GaraGlassVariant {
  
  glass,

  
  strong,
}





class GaraGlassCard extends StatelessWidget {
  final Widget child;
  final BorderRadius borderRadius;
  final GaraGlassVariant variant;
  final EdgeInsetsGeometry? padding;
  final double? width;
  final double? height;

  const GaraGlassCard({
    super.key,
    required this.child,
    this.borderRadius = const BorderRadius.all(Radius.circular(24)),
    this.variant = GaraGlassVariant.glass,
    this.padding,
    this.width,
    this.height,
  });

  @override
  Widget build(BuildContext context) {
    final isStrong = variant == GaraGlassVariant.strong;

    
    if (isStrong) {
      return _buildStrong();
    }

    
    
    return _buildGlass();
  }

  
  Widget _buildStrong() {
    return ClipRRect(
      borderRadius: borderRadius,
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24),
        child: Container(
          width: width,
          height: height,
          padding: padding,
          decoration: BoxDecoration(
            
            color: const Color(0xB3FFFFFF),
            borderRadius: borderRadius,
            
            border: Border.all(color: const Color(0xE6FFFFFF), width: 1.0),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 40,
                spreadRadius: -10,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: child,
        ),
      ),
    );
  }

  
  
  
  Widget _buildGlass() {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.78),
        borderRadius: borderRadius,
        border: Border.all(
          color: Colors.white.withOpacity(0.90),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 20,
            spreadRadius: -2,
            offset: const Offset(0, 4),
          ),
          BoxShadow(
            color: Colors.white.withOpacity(0.60),
            blurRadius: 1,
            spreadRadius: 0,
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: borderRadius,
        child: Padding(
          padding: padding ?? EdgeInsets.zero,
          child: child,
        ),
      ),
    );
  }
}


class GaraGlassCardStrong extends StatelessWidget {
  final Widget child;
  final BorderRadius borderRadius;
  final EdgeInsetsGeometry? padding;
  final double? width;
  final double? height;

  const GaraGlassCardStrong({
    super.key,
    required this.child,
    this.borderRadius = const BorderRadius.all(Radius.circular(24)),
    this.padding,
    this.width,
    this.height,
  });

  @override
  Widget build(BuildContext context) {
    return GaraGlassCard(
      borderRadius: borderRadius,
      variant: GaraGlassVariant.strong,
      padding: padding,
      width: width,
      height: height,
      child: child,
    );
  }
}
