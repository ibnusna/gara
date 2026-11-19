import 'dart:async';
import 'package:flutter/foundation.dart';
import 'notification_service.dart';

class NotificationPollingManager {
  static const int minIntervalSeconds = 180;
  static const int maxIntervalSeconds = 600;

  Timer? _timer;
  int _currentInterval = minIntervalSeconds;
  int _prevUnreadCount = 0;
  bool _isPaused = false;

  final void Function(int count)? onNewNotification;

  NotificationPollingManager({this.onNewNotification});

  int get currentInterval => _currentInterval;

  void start() {
    _isPaused = false;
    _scheduleNext(initial: true);
  }

  void pause() {
    _isPaused = true;
    _timer?.cancel();
    _timer = null;
  }

  void resume() {
    if (!_isPaused) return;
    _isPaused = false;
    // Trigger immediately on resume
    checkNow();
  }

  Future<void> checkNow() async {
    _timer?.cancel();
    _timer = null;

    try {
      final count = await NotificationService.getUnreadCount();
      if (count > _prevUnreadCount) {
        onNewNotification?.call(count);
      }
      _prevUnreadCount = count;
      // Success: reset interval
      _currentInterval = minIntervalSeconds;
    } catch (e) {
      debugPrint('[NotificationPollingManager] Polling error: $e');
      // Exponential backoff
      _currentInterval = (_currentInterval * 2).clamp(
        minIntervalSeconds,
        maxIntervalSeconds,
      );
    }

    if (!_isPaused) {
      _scheduleNext(initial: false);
    }
  }

  void _scheduleNext({required bool initial}) {
    _timer?.cancel();
    _timer = Timer(
      Duration(seconds: initial ? 5 : _currentInterval),
      () => checkNow(),
    );
  }

  void dispose() {
    _timer?.cancel();
    _timer = null;
    _isPaused = true;
  }
}
