// ============================================================
//  GARA Flutter — RuangTugasService
//  Stateless API service for the native Ruang Tugas screens.
//
//  Upload progress is tracked via http.StreamedResponse
//  for multipart file submissions, mirroring the web form's
//  progress feedback expectation.
// ============================================================

import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/ruang_tugas_model.dart';

class RuangTugasService {
  RuangTugasService._();

  // ── Internal helpers ──────────────────────────────────────

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  static Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'X-App': 'GARA_MOBILE',
      };

  // ── GET: Assignment List ──────────────────────────────────

  /// GET /api/mobile/ruang-tugas/list?mapel_id=X
  ///
  /// Returns all assignments pre-categorized into aktif/selesai/terlewat.
  /// Business logic (categorization) is done server-side, matching
  /// TugasController::index() exactly.
  static Future<TugasListResponse> getList(String mapelId) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return TugasListResponse.error('Sesi berakhir. Silakan login ulang.');
      }

      final uri = Uri.parse(
              '${AppConfig.apiMobileUrl}${AppConfig.ruangTugasListPath}')
          .replace(queryParameters: {'mapel_id': mapelId});

      final response = await http
          .get(uri, headers: _headers(token))
          .timeout(const Duration(seconds: 15));

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['status'] == 'success') {
        final data      = body['data'] as Map<String, dynamic>;
        final aktifList = (data['aktif'] as List? ?? [])
            .map((j) => TugasModel.fromJson(j as Map<String, dynamic>))
            .toList();
        final selesaiList = (data['selesai'] as List? ?? [])
            .map((j) => TugasModel.fromJson(j as Map<String, dynamic>))
            .toList();
        final terlewatList = (data['terlewat'] as List? ?? [])
            .map((j) => TugasModel.fromJson(j as Map<String, dynamic>))
            .toList();

        return TugasListResponse(
          success: true,
          aktif: aktifList,
          selesai: selesaiList,
          terlewat: terlewatList,
        );
      }

      return TugasListResponse.error(
          body['message'] as String? ?? 'Gagal mengambil daftar tugas.');
    } on SocketException {
      return TugasListResponse.error('Tidak ada koneksi internet.');
    } on http.ClientException {
      return TugasListResponse.error('Tidak dapat terhubung ke server.');
    } catch (e) {
      debugPrint('[RuangTugasService.getList] Error: $e');
      return TugasListResponse.error('Terjadi kesalahan. Coba lagi.');
    }
  }

  // ── POST: Submit via Link ─────────────────────────────────

  /// POST /api/mobile/ruang-tugas/submit (metode=link)
  static Future<TugasApiResponse> submitLink({
    required int tugasId,
    required String mapelId,
    required String link,
    String catatan = '',
  }) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return const TugasApiResponse(success: false, message: 'Sesi berakhir.');
      }

      final uri = Uri.parse(
          '${AppConfig.apiMobileUrl}${AppConfig.ruangTugasSubmitPath}');

      final response = await http.post(
        uri,
        headers: {
          ..._headers(token),
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {
          'tugas_id': '$tugasId',
          'mapel_id': mapelId,
          'metode': 'link',
          'link_pengumpulan': link,
          'catatan': catatan,
        },
      ).timeout(const Duration(seconds: 20));

      final data = jsonDecode(response.body) as Map<String, dynamic>;
      return TugasApiResponse(
        success: data['status'] == 'success',
        message: data['message'] as String? ?? '',
        data: data['data'] as Map<String, dynamic>?,
      );
    } on SocketException {
      return const TugasApiResponse(success: false, message: 'Tidak ada koneksi internet.');
    } catch (e) {
      debugPrint('[RuangTugasService.submitLink] Error: $e');
      return const TugasApiResponse(success: false, message: 'Gagal terhubung ke server.');
    }
  }

  // ── POST: Submit via File Upload ──────────────────────────

  /// POST /api/mobile/ruang-tugas/submit (metode=file)
  ///
  /// Uses http.MultipartRequest with StreamedResponse so we can
  /// track upload progress and report it via [onProgress].
  ///
  /// Validation (mirrors web):
  ///   - Max 5MB
  ///   - Accepted: pdf, doc, docx, ppt, pptx, jpg, jpeg, png, webp
  static Future<TugasApiResponse> submitFile({
    required int tugasId,
    required String mapelId,
    required File file,
    String catatan = '',
    void Function(double progress)? onProgress,
  }) async {
    // Client-side size check (5MB) — mirrors JS check in Blade
    const maxBytes = 5 * 1024 * 1024;
    final fileSize = await file.length();
    if (fileSize > maxBytes) {
      return const TugasApiResponse(success: false, message: 'File maksimal 5MB.');
    }

    // Accepted types check — mirrors Blade accept attribute
    final ext = file.path.split('.').last.toLowerCase();
    const allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp'];
    if (!allowed.contains(ext)) {
      return const TugasApiResponse(
          success: false, message: 'Tipe file tidak diizinkan. Gunakan PDF, Word, PPT, atau Gambar.');
    }

    try {
      final token = await _getToken();
      if (token == null) {
        return const TugasApiResponse(success: false, message: 'Sesi berakhir.');
      }

      final uri = Uri.parse(
          '${AppConfig.apiMobileUrl}${AppConfig.ruangTugasSubmitPath}');

      final request = http.MultipartRequest('POST', uri)
        ..headers.addAll(_headers(token))
        ..fields['tugas_id'] = '$tugasId'
        ..fields['mapel_id'] = mapelId
        ..fields['metode']   = 'file'
        ..fields['catatan']  = catatan
        ..files.add(await http.MultipartFile.fromPath(
          'file_upload',
          file.path,
          filename: file.path.split('/').last,
        ));

      // Stream response to track progress
      final stream   = await request.send().timeout(const Duration(seconds: 60));
      final total    = stream.contentLength ?? 0;
      var   received = 0;
      final chunks   = <int>[];

      await for (final chunk in stream.stream) {
        chunks.addAll(chunk);
        received += chunk.length;
        if (total > 0 && onProgress != null) {
          onProgress(received / total);
        }
      }

      final body = jsonDecode(utf8.decode(chunks)) as Map<String, dynamic>;
      return TugasApiResponse(
        success: body['status'] == 'success',
        message: body['message'] as String? ?? '',
        data: body['data'] as Map<String, dynamic>?,
      );
    } on SocketException {
      return const TugasApiResponse(success: false, message: 'Tidak ada koneksi internet.');
    } catch (e) {
      debugPrint('[RuangTugasService.submitFile] Error: $e');
      return TugasApiResponse(success: false, message: 'Upload gagal: ${e.toString()}');
    }
  }

  // ── POST: Mark as Read (Manual mode) ─────────────────────

  /// POST /api/mobile/ruang-tugas/submit (metode=manual)
  /// Used for assignments with allow_upload=false.
  static Future<TugasApiResponse> markAsRead({
    required int tugasId,
    required String mapelId,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return const TugasApiResponse(success: false, message: 'Sesi berakhir.');
      }

      final uri = Uri.parse(
          '${AppConfig.apiMobileUrl}${AppConfig.ruangTugasSubmitPath}');

      final response = await http.post(
        uri,
        headers: {
          ..._headers(token),
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {
          'tugas_id': '$tugasId',
          'mapel_id': mapelId,
          'metode': 'manual',
          'catatan': '',
        },
      ).timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;
      return TugasApiResponse(
        success: data['status'] == 'success',
        message: data['message'] as String? ?? '',
      );
    } on SocketException {
      return const TugasApiResponse(success: false, message: 'Tidak ada koneksi internet.');
    } catch (e) {
      debugPrint('[RuangTugasService.markAsRead] Error: $e');
      return const TugasApiResponse(success: false, message: 'Gagal terhubung ke server.');
    }
  }

  // ── POST: Delete/Cancel Submission ───────────────────────

  /// POST /api/mobile/ruang-tugas/delete
  /// Blocked server-side if nilai !== null.
  static Future<TugasApiResponse> deleteSubmission({
    required int tugasId,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) {
        return const TugasApiResponse(success: false, message: 'Sesi berakhir.');
      }

      final uri = Uri.parse(
          '${AppConfig.apiMobileUrl}${AppConfig.ruangTugasDeletePath}');

      final response = await http.post(
        uri,
        headers: {
          ..._headers(token),
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {'tugas_id': '$tugasId'},
      ).timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;
      return TugasApiResponse(
        success: data['status'] == 'success',
        message: data['message'] as String? ?? '',
      );
    } on SocketException {
      return const TugasApiResponse(success: false, message: 'Tidak ada koneksi internet.');
    } catch (e) {
      debugPrint('[RuangTugasService.deleteSubmission] Error: $e');
      return const TugasApiResponse(success: false, message: 'Gagal terhubung ke server.');
    }
  }

  // ── Helper: Build public URL for file submissions ─────────

  /// Resolves the public URL for a file-type submission.
  /// Storage path: public_path('lms/uploads/tugas') → served at BASE_URL/lms/uploads/tugas/
  static String fileSubmissionUrl(String filename) {
    return '${AppConfig.baseUrl}/lms/uploads/tugas/$filename';
  }
}
