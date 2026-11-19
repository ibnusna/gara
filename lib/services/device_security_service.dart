import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

class DeviceSecurityReport {
  final bool isRooted;
  final bool isAdbEnabled;
  final bool canDrawOverlays;
  final bool isScreenCaptured;

  const DeviceSecurityReport({
    this.isRooted = false,
    this.isAdbEnabled = false,
    this.canDrawOverlays = false,
    this.isScreenCaptured = false,
  });

  bool get hasViolation {
    if (kReleaseMode && isRooted) return true;
    if (isScreenCaptured) return true;
    return false;
  }
}

class DeviceSecurityService {
  static const MethodChannel _channel = MethodChannel('com.lms.gara/security');
  static void Function(String reason)? onViolationCallback;

  static void initialize() {
    _channel.setMethodCallHandler((call) async {
      if (call.method == 'onSecurityViolation') {
        final args = call.arguments as Map?;
        final reason = args?['reason']?.toString() ?? 'unknown_violation';
        debugPrint('[DeviceSecurityService] Violation event: $reason');
        onViolationCallback?.call(reason);
      }
    });
  }

  static Future<DeviceSecurityReport> checkDeviceIntegrity() async {
    try {
      final res = await _channel.invokeMethod<Map>('checkDeviceIntegrity');
      if (res != null) {
        return DeviceSecurityReport(
          isRooted: res['isRooted'] == true,
          isAdbEnabled: res['isAdbEnabled'] == true,
          canDrawOverlays: res['canDrawOverlays'] == true,
          isScreenCaptured: res['isScreenCaptured'] == true,
        );
      }
    } catch (e) {
      debugPrint('[DeviceSecurityService.checkDeviceIntegrity] $e');
    }
    return const DeviceSecurityReport();
  }

  static Future<void> enableExamMode() async {
    try {
      await _channel.invokeMethod('enableExamMode');
    } catch (e) {
      debugPrint('[DeviceSecurityService.enableExamMode] $e');
    }
  }

  static Future<void> disableExamMode() async {
    try {
      await _channel.invokeMethod('disableExamMode');
    } catch (e) {
      debugPrint('[DeviceSecurityService.disableExamMode] $e');
    }
  }
}
