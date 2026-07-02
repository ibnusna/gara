

import 'dart:io';
import 'package:flutter/material.dart';

class PerformanceConfig {
  PerformanceConfig._();

  static bool _initialized = false;
  static bool _isLowEnd = false;

  /// Deteksi RAM untuk logging saja — tidak digunakan untuk downgrade visual
  static Future<void> initialize() async {
    if (_initialized) return;
    _initialized = true;
    if (!Platform.isAndroid) return;
    try {
      final memInfo = File('/proc/meminfo');
      if (await memInfo.exists()) {
        for (final line in await memInfo.readAsLines()) {
          if (line.startsWith('MemTotal:')) {
            final parts = line.split(RegExp(r'\s+'));
            if (parts.length >= 2) {
              final gb = (int.tryParse(parts[1]) ?? 0) / (1024 * 1024);
              _isLowEnd = gb < 3.5;
              debugPrint('[GARA Perf] RAM: ${gb.toStringAsFixed(2)} GB');
            }
            break;
          }
        }
      }
    } catch (_) {}
  }

  static bool get isLowEnd => _isLowEnd;

  // ── Durasi animasi — SAMA di semua perangkat (premium tetap premium) ────
  static const Duration pageTransitionDuration = Duration(milliseconds: 400);
  static const Duration loginSwitcherDuration  = Duration(milliseconds: 500);
  static const Duration loginFadeDuration      = Duration(milliseconds: 600);
  static const Duration cardSlideDuration      = Duration(milliseconds: 500);

  // ── Kurva animasi — easeOut lebih ringan CPU vs easeInOutCubic ──────────
  // easeOut: decelerasi sederhana → GPU-friendly, tetap terasa premium
  static const Curve pageCurve      = Curves.easeInOutCubic;
  static const Curve loginFadeCurve = Curves.easeOutCubic;
  static const Curve cardCurve      = Curves.easeOutCubic;
}
