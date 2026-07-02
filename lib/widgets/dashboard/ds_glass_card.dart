// ============================================================
//  GARA Flutter — Design System: Glass Card Widget
//  Sumber: dashborad.html (.glass-card & .glass-card-strong)
//
//  PENTING: BackdropFilter DIHAPUS dari implementasi kartu scroll.
//  BackdropFilter di dalam CustomScrollView/SliverList menyebabkan
//  seluruh konten scroll menjadi invisible (Flutter rendering bug).
//
//  Solusi: Efek kaca disimulasikan menggunakan:
//  - Background putih semi-transparan (opacity 0.55–0.80)
//  - Border putih tegas (pantulan cahaya)
//  - Box shadow lembut
//  Hasil visual identik dengan DS di layar nyata karena mesh background
//  sudah cukup memberikan konteks "kaca" di belakang kartu.
//
//  BackdropFilter HANYA dipakai di header & bottom nav
//  (widget yang TIDAK berada di dalam scroll view).
// ============================================================

import 'dart:ui';
import 'package:flutter/material.dart';

/// Varian glass card dari Design System GARA.
enum GaraGlassVariant {
  /// Kartu transparan — untuk konten dalam scroll view (TANPA BackdropFilter)
  glass,

  /// Kartu kuat — untuk header & bottom nav (DENGAN BackdropFilter)
  strong,
}

/// Widget reusable glassmorphism dari Design System GARA.
///
/// [GaraGlassVariant.glass]  → dipakai dalam scroll view (no BackdropFilter)
/// [GaraGlassVariant.strong] → dipakai di header/bottom nav (with BackdropFilter)
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

    // ── Varian STRONG: pakai BackdropFilter (aman — tidak di scroll view) ──
    if (isStrong) {
      return _buildStrong();
    }

    // ── Varian GLASS: TANPA BackdropFilter (aman di scroll view) ──
    // Efek kaca disimulasikan: bg putih 55% opasitas + border putih + shadow
    return _buildGlass();
  }

  /// Card kuat untuk header & bottom nav — dengan BackdropFilter
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
            // DS: rgba(255,255,255,0.70)
            color: const Color(0xB3FFFFFF),
            borderRadius: borderRadius,
            // DS: border rgba(255,255,255,0.90)
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

  /// Card kaca untuk konten scroll — TANPA BackdropFilter
  /// Warna putih dengan opasitas lebih tinggi (0.78) agar konten tetap terbaca
  /// dan terlihat jelas di atas mesh background.
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

/// Shortcut untuk varian strong (header, bottom nav).
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
