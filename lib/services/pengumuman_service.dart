import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';
import '../models/pengumuman_model.dart';
import 'auth_service.dart';

import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';

class PengumumanService {
  PengumumanService._();

  
  static Future<PengumumanResponse> getPengumumanList({String? mapelId}) async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return PengumumanResponse.error('Sesi berakhir.');

      final prefs = await SharedPreferences.getInstance();
      final savedBaseUrl = prefs.getString('base_url');
      final baseUrlToUse = (savedBaseUrl != null && savedBaseUrl.isNotEmpty) 
          ? savedBaseUrl 
          : AppConfig.baseUrl;

      final selectedMapelId = mapelId ?? prefs.getString(GaraPrefKeys.selectedMapelId);

      final uri = Uri.parse('$baseUrlToUse/api/mobile/pengumuman').replace(
        queryParameters: selectedMapelId != null && selectedMapelId.isNotEmpty
            ? {'mapel_id': selectedMapelId}
            : null,
      );

      final response = await http.get(
        uri,
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
