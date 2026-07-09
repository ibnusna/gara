







import 'package:flutter/material.dart';
import 'performance_config.dart';


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


Route<T> garaInstantRoute<T>(Widget page, {RouteSettings? settings}) {
  return PageRouteBuilder<T>(
    settings: settings,
    pageBuilder: (_, __, ___) => page,
    transitionDuration: Duration.zero,
    reverseTransitionDuration: Duration.zero,
    transitionsBuilder: (_, __, ___, child) => child,
  );
}
