// ============================================================
//  GARA Flutter — Color Palette
//  Garuda Akademi Mobile | Design System
// ============================================================

import 'package:flutter/material.dart';

/// Palet warna terpusat untuk aplikasi GARA.
/// Menggunakan Color Semantics dari Material Design 3.
class GaraColors {
  // ========== Student Theme Colors ==========

  /// Warna primer untuk tema siswa — biru gelap yang elegan
  static const Color studentPrimary = Color(0xFF1B5E8F);

  /// Warna background utama untuk siswa
  static const Color studentBgBody = Color(0xFFFAFBFC);

  /// Warna surface untuk kartu dan kontainer siswa
  static const Color studentSurface = Color(0xFFFFFFFF);

  /// Warna teks utama untuk siswa
  static const Color studentTextMain = Color(0xFF1F2937);

  /// Warna teks buram/secondary untuk siswa
  static const Color studentTextMuted = Color(0xFF6B7280);

  /// Warna border/divider untuk siswa
  static const Color studentBorder = Color(0xFFE5E7EB);

  // ========== General Theme Colors ==========

  /// Warna primer terang
  static const Color primaryLight = Color(0xFF3B82F6);

  /// Warna teks putih untuk kontras tinggi
  static const Color textWhite = Color(0xFFFFFFFF);

  /// Warna success/hijau
  static const Color success = Color(0xFF10B981);

  /// Warna warning/kuning
  static const Color warning = Color(0xFFF59E0B);

  /// Warna error/merah
  static const Color error = Color(0xFFEF4444);

  /// Warna info/blue
  static const Color info = Color(0xFF0EA5E9);

  // ========== Exam Mode Colors (Proctoring) ==========

  /// Warna indikator exam mode aktif
  static const Color examModeIndicator = Color(0xFFDC2626);

  /// Warna background exam mode warning
  static const Color examModeWarning = Color(0xFFFEE2E2);
}
