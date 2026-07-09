// ============================================================
//  GARA Flutter — Responsive Utilities
//  Menyediakan breakpoint dan helper untuk layout adaptif
//  di tablet, iPad, dan mode split-screen Android.
//
//  Breakpoint:
//    Phone        : width < 600dp
//    Tablet/Wide  : width >= 600dp
//
//  Penggunaan:
//    if (GaraResponsive.isTablet(context)) { ... }
//    padding: EdgeInsets.symmetric(
//      horizontal: GaraResponsive.hPad(context),
//    )
// ============================================================

import 'package:flutter/material.dart';

class GaraResponsive {
  GaraResponsive._();

  // ── Breakpoint ────────────────────────────────────────────
  static const double _tabletBreakpoint = 600.0;

  /// Apakah layar saat ini dianggap tablet/wide (>= 600dp)
  static bool isTablet(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= _tabletBreakpoint;

  // ── Padding Horizontal ────────────────────────────────────
  /// Padding horizontal konten utama
  /// Phone: 16  |  Tablet: 28
  static double hPad(BuildContext context) =>
      isTablet(context) ? 28.0 : 16.0;

  /// Padding horizontal untuk halaman full-width (PilihMapel, Login)
  /// Phone: 28  |  Tablet: 40
  static double hPadPage(BuildContext context) =>
      isTablet(context) ? 40.0 : 28.0;

  // ── Max Width Constraints ─────────────────────────────────
  /// Lebar maksimum konten dashboard (HomeTab, AkunTab, dll)
  /// Phone: tidak dibatasi  |  Tablet: 720dp
  static double contentMaxWidth(BuildContext context) =>
      isTablet(context) ? 720.0 : double.infinity;

  /// Lebar maksimum halaman penuh (PilihMapel)
  /// Phone: tidak dibatasi  |  Tablet: 640dp
  static double pageMaxWidth(BuildContext context) =>
      isTablet(context) ? 640.0 : double.infinity;

  /// Lebar maksimum bottom navigation pill
  /// Phone: tidak dibatasi  |  Tablet: 460dp
  static double navMaxWidth(BuildContext context) =>
      isTablet(context) ? 460.0 : double.infinity;

  // ── Font Scale ────────────────────────────────────────────
  /// Skala font untuk elemen besar (judul hero card)
  /// Phone: 1.0  |  Tablet: 1.15
  static double fontScale(BuildContext context) =>
      isTablet(context) ? 1.15 : 1.0;

  // ── Ukuran Komponen ───────────────────────────────────────
  /// Tinggi kartu besar di MainMenuGrid
  /// Phone: 112  |  Tablet: 128
  static double menuCardHeight(BuildContext context) =>
      isTablet(context) ? 128.0 : 112.0;

  /// Ukuran ikon di kartu besar MainMenuGrid
  /// Phone: 42  |  Tablet: 48
  static double menuIconSize(BuildContext context) =>
      isTablet(context) ? 48.0 : 42.0;

  /// Avatar size di AppHeader
  /// Phone: 44  |  Tablet: 50
  static double avatarSize(BuildContext context) =>
      isTablet(context) ? 50.0 : 44.0;

  // ── Grid Column Count ─────────────────────────────────────
  /// Jumlah kolom untuk kartu kecil di MainMenuGrid
  /// Phone: 4 dalam satu baris  |  Tablet: tetap 4 tapi dengan sizing lebih baik
  /// (Implementasi: tetap Row tapi spacing lebih proporsional)
  static double smallMenuIconSize(BuildContext context) =>
      isTablet(context) ? 44.0 : 40.0;

  // ── Helper Widget Wrapper ─────────────────────────────────
  /// Membungkus [child] dengan Center + ConstrainedBox maxWidth pada tablet
  static Widget constrained(BuildContext context, Widget child, {double? maxWidth}) {
    final mw = maxWidth ?? contentMaxWidth(context);
    if (!isTablet(context)) return child;
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: mw),
        child: child,
      ),
    );
  }
}
