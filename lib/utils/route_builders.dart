// ============================================================
//  GARA Flutter — Route Builders (Premium, konsisten semua device)
//
//  Semua route menggunakan durasi dan kurva yang sama.
//  Perbedaan dari PageRouteBuilder manual: RepaintBoundary
//  otomatis di-wrap agar GPU tidak repaint halaman lama saat transisi.
// ============================================================

import 'package:flutter/material.dart';
import 'performance_config.dart';

/// Fade transition — paling ringan, digunakan untuk overlay/dialog-style push
Route<T> garaFadeRoute<T>(Widget page, {RouteSettings? settings}) {
  return PageRouteBuilder<T>(
    settings: settings,
    pageBuilder: (_, __, ___) => page,
    transitionDuration: PerformanceConfig.pageTransitionDuration,
    reverseTransitionDuration: PerformanceConfig.pageTransitionDuration,
    transitionsBuilder: (_, animation, __, child) => FadeTransition(
      opacity: CurvedAnimation(
        parent: animation,
        curve: PerformanceConfig.pageCurve,
      ),
      child: RepaintBoundary(child: child),
    ),
  );
}

/// Fade + slide vertikal mikro (4%) — transisi login ke halaman utama
Route<T> garaFadeSlideRoute<T>(Widget page, {RouteSettings? settings}) {
  return PageRouteBuilder<T>(
    settings: settings,
    pageBuilder: (_, __, ___) => page,
    transitionDuration: PerformanceConfig.pageTransitionDuration,
    reverseTransitionDuration: PerformanceConfig.pageTransitionDuration,
    transitionsBuilder: (_, animation, __, child) {
      final curved = CurvedAnimation(
        parent: animation,
        curve: PerformanceConfig.pageCurve,
      );
      return FadeTransition(
        opacity: curved,
        child: SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0, 0.04),
            end: Offset.zero,
          ).animate(curved),
          child: RepaintBoundary(child: child),
        ),
      );
    },
  );
}

/// Slide kanan ke kiri — untuk navigasi masuk lebih dalam (PilihMapel → Dashboard)
Route<T> garaSlideRightRoute<T>(Widget page, {RouteSettings? settings}) {
  return PageRouteBuilder<T>(
    settings: settings,
    pageBuilder: (_, __, ___) => page,
    transitionDuration: PerformanceConfig.pageTransitionDuration,
    reverseTransitionDuration: PerformanceConfig.pageTransitionDuration,
    transitionsBuilder: (_, animation, __, child) => SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(1.0, 0),
        end: Offset.zero,
      ).animate(CurvedAnimation(
        parent: animation,
        curve: PerformanceConfig.pageCurve,
      )),
      child: RepaintBoundary(child: child),
    ),
  );
}

/// Tanpa animasi — untuk pushReplacement setelah logout
Route<T> garaInstantRoute<T>(Widget page, {RouteSettings? settings}) {
  return PageRouteBuilder<T>(
    settings: settings,
    pageBuilder: (_, __, ___) => page,
    transitionDuration: Duration.zero,
    reverseTransitionDuration: Duration.zero,
    transitionsBuilder: (_, __, ___, child) => child,
  );
}
