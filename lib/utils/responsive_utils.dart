















import 'package:flutter/material.dart';

class GaraResponsive {
  GaraResponsive._();

  
  static const double _tabletBreakpoint = 600.0;

  
  static bool isTablet(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= _tabletBreakpoint;

  
  
  
  static double hPad(BuildContext context) =>
      isTablet(context) ? 28.0 : 16.0;

  
  
  static double hPadPage(BuildContext context) =>
      isTablet(context) ? 40.0 : 28.0;

  
  
  
  static double contentMaxWidth(BuildContext context) =>
      isTablet(context) ? 720.0 : double.infinity;

  
  
  static double pageMaxWidth(BuildContext context) =>
      isTablet(context) ? 640.0 : double.infinity;

  
  
  static double navMaxWidth(BuildContext context) =>
      isTablet(context) ? 460.0 : double.infinity;

  
  
  
  static double fontScale(BuildContext context) =>
      isTablet(context) ? 1.15 : 1.0;

  
  
  
  static double menuCardHeight(BuildContext context) =>
      isTablet(context) ? 128.0 : 112.0;

  
  
  static double menuIconSize(BuildContext context) =>
      isTablet(context) ? 48.0 : 42.0;

  
  
  static double avatarSize(BuildContext context) =>
      isTablet(context) ? 50.0 : 44.0;

  
  
  
  
  static double smallMenuIconSize(BuildContext context) =>
      isTablet(context) ? 44.0 : 40.0;

  
  
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
