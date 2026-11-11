import 'package:flutter/foundation.dart';
import 'InfinityApiClient.dart';

class NotificationService {
  
  static Future<List<dynamic>> fetchNotifications() async {
    try {
      final client = InfinityApiClient();
      final data = await client.getJson('/api/student/notifications');

      if (data['status'] == 'success') {
        return data['data'] ?? [];
      }
      return [];
    } catch (e) {
      debugPrint('Error fetchNotifications: $e');
      return [];
    }
  }

  static Future<bool> markAsRead(int id) async {
    try {
      final client = InfinityApiClient();
      final data = await client.postJson('/api/student/notifications/$id/read', {});

      return data['status'] == 'success';
    } catch (e) {
      debugPrint('Error markAsRead: $e');
      return false;
    }
  }

  static Future<int> getUnreadCount() async {
    try {
      final client = InfinityApiClient();
      final data = await client.getJson('/api/student/notifications/unread-count');

      if (data['status'] == 'success') {
        return data['count'] ?? 0;
      }
      return 0;
    } catch (e) {
      debugPrint('Error getUnreadCount: $e');
      return 0;
    }
  }
}
