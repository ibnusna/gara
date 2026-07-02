import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';

class NotificationService {
  // Pastikan baseUrl menunjuk ke localhost:8000 sesuai permintaan
  static const String baseUrl = 'http://localhost:8000';

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  /// Mengambil semua notifikasi untuk siswa dari backend.
  static Future<List<dynamic>> fetchNotifications() async {
    try {
      final token = await _getToken();
      if (token == null) return [];

      final response = await http.get(
        Uri.parse('$baseUrl/api/student/notifications'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['status'] == 'success') {
          return data['data'] ?? [];
        }
      }
      return [];
    } catch (e) {
      print('Error fetchNotifications: $e');
      return [];
    }
  }

  /// Menandai notifikasi sebagai telah dibaca berdasarkan ID.
  static Future<bool> markAsRead(int id) async {
    try {
      final token = await _getToken();
      if (token == null) return false;

      final response = await http.post(
        Uri.parse('$baseUrl/api/student/notifications/$id/read'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['status'] == 'success';
      }
      return false;
    } catch (e) {
      print('Error markAsRead: $e');
      return false;
    }
  }

  /// Mengambil jumlah notifikasi yang belum dibaca.
  static Future<int> getUnreadCount() async {
    try {
      final token = await _getToken();
      if (token == null) return 0;

      final response = await http.get(
        Uri.parse('$baseUrl/api/student/notifications/unread-count'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['status'] == 'success') {
          return data['count'] ?? 0;
        }
      }
      return 0;
    } catch (e) {
      print('Error getUnreadCount: $e');
      return 0;
    }
  }
}
