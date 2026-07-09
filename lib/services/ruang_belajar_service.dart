











import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/ruang_belajar_model.dart';



const _kCompletedMateriKey = 'completed_materi_ids';

class RuangBelajarService {
  RuangBelajarService._();

  

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  static Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'X-App': 'GARA_MOBILE',
      };

  

  
  static Future<Set<String>> getCompletedMateri() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kCompletedMateriKey) ?? '[]';
    try {
      final List decoded = jsonDecode(raw);
      return decoded.cast<String>().toSet();
    } catch (_) {
      return {};
    }
  }

  
  
  static Future<void> markSelesai({
    required int materId,
    required String idMateri,
    required int mapelId,
  }) async {
    
    final prefs = await SharedPreferences.getInstance();
    final completed = await getCompletedMateri();
    if (!completed.contains(idMateri)) {
      completed.add(idMateri);
      await prefs.setString(_kCompletedMateriKey, jsonEncode(completed.toList()));
    }

    
    try {
      final token = await _getToken();
      if (token == null) return;
      await http.post(
        Uri.parse('${AppConfig.apiMobileUrl}${AppConfig.ruangBelajarMarkSelesaiPath}'),
        headers: {
          ..._headers(token),
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'materi_id': materId, 'mapel_id': mapelId}),
      ).timeout(const Duration(seconds: 5));
    } catch (_) {
      
    }
  }

  

  
  
  
  static Future<BabListResponse> getBabList(String mapelId) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return BabListResponse.error('Sesi berakhir. Silakan login ulang.');
      }

      final uri = Uri.parse(
              '${AppConfig.apiMobileUrl}${AppConfig.ruangBelajarBabListPath}')
          .replace(queryParameters: {'mapel_id': mapelId});

      final response = await http.get(uri, headers: _headers(token))
          .timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        final List rawList = data['data'] as List? ?? [];
        final babs = rawList.map((j) => BabModel.fromJson(j as Map<String, dynamic>)).toList();

        final guruJson = data['guru'] as Map<String, dynamic>?;
        final guru = guruJson != null ? GuruInfoModel.fromJson(guruJson) : null;

        return BabListResponse(
          success: true,
          mapelId: (data['mapel_id'] as num?)?.toInt() ?? 0,
          kelasId: (data['kelas_id'] as num?)?.toInt() ?? 0,
          guru: guru,
          data: babs,
        );
      }

      return BabListResponse.error(
          data['message'] as String? ?? 'Gagal mengambil daftar bab.');
    } on SocketException {
      return BabListResponse.error('Tidak ada koneksi internet.');
    } on http.ClientException {
      return BabListResponse.error('Tidak dapat terhubung ke server.');
    } catch (e) {
      debugPrint('[RuangBelajarService.getBabList] Error: $e');
      return BabListResponse.error('Terjadi kesalahan. Coba lagi.');
    }
  }

  
  
  
  static Future<TopicListResponse> getTopicList(String mapelId, int bab) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return TopicListResponse.error('Sesi berakhir. Silakan login ulang.');
      }

      final uri = Uri.parse(
              '${AppConfig.apiMobileUrl}${AppConfig.ruangBelajarTopicListPath}')
          .replace(queryParameters: {'mapel_id': mapelId, 'bab': '$bab'});

      final response = await http.get(uri, headers: _headers(token))
          .timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        final List rawList = data['data'] as List? ?? [];
        final topics = rawList
            .map((j) => TopicModel.fromJson(j as Map<String, dynamic>))
            .toList();

        return TopicListResponse(
          success: true,
          bab: (data['bab'] as num?)?.toInt() ?? bab,
          judulBab: data['judul_bab'] as String? ?? 'Bab $bab',
          data: topics,
        );
      }

      return TopicListResponse.error(
          data['message'] as String? ?? 'Gagal mengambil daftar topik.');
    } on SocketException {
      return TopicListResponse.error('Tidak ada koneksi internet.');
    } on http.ClientException {
      return TopicListResponse.error('Tidak dapat terhubung ke server.');
    } catch (e) {
      debugPrint('[RuangBelajarService.getTopicList] Error: $e');
      return TopicListResponse.error('Terjadi kesalahan. Coba lagi.');
    }
  }

  
  
  
  static Future<MateriDetailResponse> getDetail(int materId, String mapelId) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return MateriDetailResponse.error('Sesi berakhir. Silakan login ulang.');
      }

      final uri = Uri.parse(
              '${AppConfig.apiMobileUrl}${AppConfig.ruangBelajarDetailPath}/$materId')
          .replace(queryParameters: {'mapel_id': mapelId});

      final response = await http.get(uri, headers: _headers(token))
          .timeout(const Duration(seconds: 15));

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['status'] == 'success') {
        final detail =
            MateriDetailModel.fromJson(body['data'] as Map<String, dynamic>);
        return MateriDetailResponse(success: true, data: detail);
      }

      if (response.statusCode == 404) {
        return MateriDetailResponse.error('Materi tidak ditemukan.');
      }

      return MateriDetailResponse.error(
          body['message'] as String? ?? 'Gagal mengambil detail materi.');
    } on SocketException {
      return MateriDetailResponse.error('Tidak ada koneksi internet.');
    } on http.ClientException {
      return MateriDetailResponse.error('Tidak dapat terhubung ke server.');
    } catch (e) {
      debugPrint('[RuangBelajarService.getDetail] Error: $e');
      return MateriDetailResponse.error('Terjadi kesalahan. Coba lagi.');
    }
  }
}
