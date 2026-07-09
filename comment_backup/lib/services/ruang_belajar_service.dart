// ============================================================
//  GARA Flutter — RuangBelajarService
//  Stateless API service for the native Ruang Belajar screens.
//
//  All methods follow the exact same pattern as AuthService.
//  Endpoints:
//    GET  /api/mobile/ruang-belajar/bab-list?mapel_id=X
//    GET  /api/mobile/ruang-belajar/topic-list?mapel_id=X&bab=Y
//    GET  /api/mobile/ruang-belajar/detail/{id}?mapel_id=X
//    POST /api/mobile/ruang-belajar/mark-selesai
// ============================================================

import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/ruang_belajar_model.dart';

/// Key used to persist locally which materi IDs the student has completed.
/// Stored as JSON list: '["IPA-IX-S1-B1-P1", "IPA-IX-S1-B1-P2", ...]'
const _kCompletedMateriKey = 'completed_materi_ids';

class RuangBelajarService {
  RuangBelajarService._();

  // ── Internal helper ───────────────────────────────────────

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  static Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'X-App': 'GARA_MOBILE',
      };

  // ── Completed Materi — Local SharedPreferences ────────────

  /// Returns the set of id_materi strings the student has marked as complete.
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

  /// Marks a materi as completed locally (SharedPreferences).
  /// Also calls the server endpoint as a lightweight hook (fire-and-forget).
  static Future<void> markSelesai({
    required int materId,
    required String idMateri,
    required int mapelId,
  }) async {
    // 1. Local persistence (primary — works offline)
    final prefs = await SharedPreferences.getInstance();
    final completed = await getCompletedMateri();
    if (!completed.contains(idMateri)) {
      completed.add(idMateri);
      await prefs.setString(_kCompletedMateriKey, jsonEncode(completed.toList()));
    }

    // 2. Server hook (secondary — fire-and-forget, failure is silent)
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
      // Intentionally silenced — completion is already persisted locally
    }
  }

  // ── API Calls ─────────────────────────────────────────────

  /// GET /api/mobile/ruang-belajar/bab-list?mapel_id=X
  ///
  /// Returns the grouped list of BABs (chapters) for the selected subject.
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

  /// GET /api/mobile/ruang-belajar/topic-list?mapel_id=X&bab=Y
  ///
  /// Returns all topics within a specific chapter.
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

  /// GET /api/mobile/ruang-belajar/detail/{id}?mapel_id=X
  ///
  /// Returns full material detail including resource URLs and YouTube embed ID.
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
