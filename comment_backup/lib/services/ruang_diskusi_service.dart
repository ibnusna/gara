import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/ruang_diskusi_model.dart';
import 'dart:io';

class RuangDiskusiService {

  Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(GaraPrefKeys.authToken);
    return {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Base method for the RPC-style `api` endpoint
  Future<Map<String, dynamic>> _callApi(Map<String, String> bodyMap, {File? mediaFile}) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(GaraPrefKeys.authToken);
    if (token == null || token.isEmpty) throw Exception('Sesi tidak valid.');

    final mapelId = prefs.getString(GaraPrefKeys.selectedMapelId);
    final kelasId = prefs.getString(GaraPrefKeys.kelasSiswa);
    if (mapelId == null || kelasId == null) throw Exception('Pilih mata pelajaran terlebih dahulu.');

    bodyMap['mapel_id'] = mapelId;
    bodyMap['kelas_id'] = kelasId;

    final uri = Uri.parse('${AppConfig.baseUrl}/api/mobile/ruang-diskusi/api');

    if (mediaFile != null) {
      var request = http.MultipartRequest('POST', uri);
      request.headers.addAll({
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      });
      
      bodyMap.forEach((key, value) {
        request.fields[key] = value;
      });

      String filename = mediaFile.path.split('/').last;
      String ext = filename.split('.').last.toLowerCase();
      var contentType = MediaType('image', ext == 'jpg' ? 'jpeg' : ext);

      request.files.add(await http.MultipartFile.fromPath(
        'media',
        mediaFile.path,
        contentType: contentType,
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      return json.decode(response.body);
    } else {
      final response = await http.post(
        uri,
        headers: await _getHeaders(),
        body: bodyMap,
      );
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        return json.decode(response.body);
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    }
  }

  // 1. GET THREADS
  Future<Map<String, dynamic>> getThreads(int page) async {
    final res = await _callApi({
      'action': 'get_threads',
      'page': page.toString(),
    });
    if (res['status'] == 'success') {
      List<DiskusiThread> threads = (res['data'] as List).map((x) => DiskusiThread.fromJson(x)).toList();
      return {
        'threads': threads,
        'has_next': res['meta']['has_next'] ?? false,
      };
    }
    throw Exception(res['message'] ?? 'Gagal memuat diskusi');
  }

  // 2. GET THREAD DETAIL
  Future<DiskusiThread> getThreadDetail(int threadId) async {
    final res = await _callApi({
      'action': 'get_thread_detail',
      'thread_id': threadId.toString(),
    });
    if (res['status'] == 'success') {
      return DiskusiThread.fromJson(res['data']);
    }
    throw Exception(res['message'] ?? 'Gagal memuat detail diskusi');
  }

  // 3. GET REPLIES
  Future<List<DiskusiReply>> getReplies(int threadId) async {
    final res = await _callApi({
      'action': 'get_replies',
      'thread_id': threadId.toString(),
    });
    if (res['status'] == 'success') {
      return (res['data'] as List).map((x) => DiskusiReply.fromJson(x)).toList();
    }
    throw Exception(res['message'] ?? 'Gagal memuat balasan');
  }

  // 4. CREATE THREAD
  Future<DiskusiThread?> createThread(String judul, String isiKonten, {File? mediaFile}) async {
    final res = await _callApi({
      'action': 'create_thread',
      'judul': judul,
      'isi_konten': isiKonten,
    }, mediaFile: mediaFile);
    
    if (res['status'] == 'success') {
      if (res['data'] != null) {
        return DiskusiThread.fromJson(res['data']);
      }
      return null;
    }
    throw Exception(res['message'] ?? 'Gagal membuat postingan');
  }

  // 5. POST REPLY
  Future<DiskusiReply?> postReply(int threadId, String isiBalasan, {File? mediaFile}) async {
    final res = await _callApi({
      'action': 'post_reply',
      'thread_id': threadId.toString(),
      'isi_balasan': isiBalasan,
    }, mediaFile: mediaFile);

    if (res['status'] == 'success') {
      if (res['data'] != null) {
        return DiskusiReply.fromJson(res['data']);
      }
      return null;
    }
    throw Exception(res['message'] ?? 'Gagal mengirim balasan');
  }

  // 6. EDIT THREAD
  Future<bool> editThread(int id, String isiKonten) async {
    final res = await _callApi({
      'action': 'edit_thread',
      'id': id.toString(),
      'isi_konten': isiKonten,
    });
    if (res['status'] == 'success') return true;
    throw Exception(res['message'] ?? 'Gagal mengedit');
  }

  // 7. EDIT REPLY
  Future<bool> editReply(int id, String isiBalasan) async {
    final res = await _callApi({
      'action': 'edit_reply',
      'id': id.toString(),
      'isi_balasan': isiBalasan,
    });
    if (res['status'] == 'success') return true;
    throw Exception(res['message'] ?? 'Gagal mengedit');
  }

  // 8. DELETE THREAD
  Future<bool> deleteThread(int id) async {
    final res = await _callApi({
      'action': 'delete_thread',
      'id': id.toString(),
    });
    if (res['status'] == 'success') return true;
    throw Exception(res['message'] ?? 'Gagal menghapus');
  }

  // 9. DELETE REPLY
  Future<bool> deleteReply(int id) async {
    final res = await _callApi({
      'action': 'delete_reply',
      'id': id.toString(),
    });
    if (res['status'] == 'success') return true;
    throw Exception(res['message'] ?? 'Gagal menghapus');
  }

  // 10. CHECK UPDATES
  Future<Map<String, dynamic>> checkUpdates(int lastThreadId, int activeThreadId, int lastReplyId) async {
    final res = await _callApi({
      'action': 'check_updates',
      'last_thread_id': lastThreadId.toString(),
      'active_thread_id': activeThreadId.toString(),
      'last_reply_id': lastReplyId.toString(),
    });
    if (res['status'] == 'success') {
      List<DiskusiThread> newThreads = (res['new_threads'] as List).map((x) => DiskusiThread.fromJson(x)).toList();
      List<DiskusiReply> newReplies = (res['new_replies'] as List).map((x) => DiskusiReply.fromJson(x)).toList();
      return {
        'new_threads': newThreads,
        'new_replies': newReplies,
      };
    }
    return {'new_threads': [], 'new_replies': []};
  }
}
