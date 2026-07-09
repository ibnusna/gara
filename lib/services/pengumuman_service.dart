import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';
import '../models/pengumuman_model.dart';
import 'auth_service.dart';

class PengumumanService {
  PengumumanService._();

  
  static Future<PengumumanResponse> getPengumumanList() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return PengumumanResponse.error('Sesi berakhir.');

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/pengumuman'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'X-App':         'GARA_MOBILE',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          final List listJson = data['data'] ?? [];
          final list = listJson.map((e) => PengumumanModel.fromJson(e)).toList();
          return PengumumanResponse(success: true, data: list);
        }
        return PengumumanResponse.error(data['message'] ?? 'Gagal memuat pengumuman.');
      } else {
         return PengumumanResponse.error('Server error (HTTP ${response.statusCode})');
      }
    } catch (e) {
      debugPrint('[PengumumanService] Error: $e');
      return PengumumanResponse.error('Terjadi kesalahan jaringan.');
    }
  }
}
