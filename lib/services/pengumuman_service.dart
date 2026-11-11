import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/pengumuman_model.dart';
import '../utils/app_constants.dart';
import 'InfinityApiClient.dart';

class PengumumanService {
  PengumumanService._();

  static Future<PengumumanResponse> getPengumumanList({String? mapelId}) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final selectedMapelId = mapelId ?? prefs.getString(GaraPrefKeys.selectedMapelId);

      String endpoint = '/api/mobile/pengumuman';
      if (selectedMapelId != null && selectedMapelId.isNotEmpty) {
        endpoint += '?mapel_id=$selectedMapelId';
      }

      final client = InfinityApiClient();
      final data = await client.getJson(endpoint);

      if (data['status'] == 'success') {
        final List listJson = data['data'] ?? [];
        final list = listJson.map((e) => PengumumanModel.fromJson(e)).toList();
        return PengumumanResponse(success: true, data: list);
      }
      
      return PengumumanResponse.error(data['message'] ?? 'Gagal memuat pengumuman.');
    } catch (e) {
      debugPrint('[PengumumanService] Error: $e');
      return PengumumanResponse.error('Terjadi kesalahan jaringan.');
    }
  }
}
