










import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';
import '../utils/app_constants.dart';
import '../models/mapel_model.dart';


class AuthResult {
  final bool success;
  final String? token;
  final String? role;
  final String? nama;
  final String? username;
  final String? profilePhotoUrl;
  final String? errorMessage;
  final bool isMaintenance;

  const AuthResult({
    required this.success,
    this.token,
    this.role,
    this.nama,
    this.username,
    this.profilePhotoUrl,
    this.errorMessage,
    this.isMaintenance = false,
  });
}




class AuthService {
  AuthService._();

  

  
  
  
  
  static Future<AuthResult> login({
    required String identifier,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.apiMobileUrl}/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-App':        'GARA_MOBILE',
        },
        body: jsonEncode({
          'identifier': identifier.trim(),
          'password':   password,
        }),
      ).timeout(const Duration(seconds: 15));

      
      if (response.statusCode == 503) {
        return const AuthResult(
          success:      false,
          isMaintenance: true,
          errorMessage: 'Sistem sedang dalam pemeliharaan. Coba lagi nanti.',
        );
      }

      if (response.statusCode >= 500) {
        return const AuthResult(
          success:      false,
          errorMessage: 'Server sedang mengalami gangguan (500). Silakan hubungi admin.',
        );
      }

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        
        await _saveSession(data);

        return AuthResult(
          success:         true,
          token:           data['token'] as String?,
          role:            data['role']  as String?,
          nama:            data['nama']  as String?,
          username:        data['username'] as String?,
          profilePhotoUrl: data['profile_photo'] as String?,
        );
      }

      return AuthResult(
        success:      false,
        errorMessage: data['message'] as String? ?? 'Login gagal.',
      );
    } on SocketException {
      return const AuthResult(
        success:      false,
        errorMessage: 'Tidak ada koneksi internet. Periksa jaringan Anda.',
      );
    } on http.ClientException {
      return const AuthResult(
        success:      false,
        errorMessage: 'Tidak dapat terhubung ke server GARA.',
      );
    } catch (e) {
      debugPrint('[AuthService.login] Error: $e');
      return AuthResult(
        success:      false,
        errorMessage: 'Debug Error: $e',
      );
    }
  }

  

  
  
  
  
  static Future<void> logout() async {
    try {
      final token = await getToken();
      if (token != null && token.isNotEmpty) {
        final prefs = await SharedPreferences.getInstance();
        final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';
        
        await http.post(
          Uri.parse('${AppConfig.apiMobileUrl}/logout'),
          headers: {
            'Authorization': 'Bearer $token',
            'Accept':        'application/json',
            'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            'Cookie': '__test=$testCookie',
          },
        ).timeout(const Duration(seconds: 8));
      }
    } catch (e) {
      debugPrint('[AuthService.logout] Server revoke gagal (OK — hapus lokal): $e');
    } finally {
      
      await clearSession();
    }
  }

  

  
  static Future<MapelResponse> getMapelList() async {
    try {
      final token = await getToken();
      if (token == null) return MapelResponse.error('Sesi berakhir. Silakan login ulang.');

      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/mapel'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(const Duration(seconds: 15));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        // Cookie bypass expired — coba load dari cache terlebih dahulu
        final cached = await getCachedMapelList();
        if (cached != null) return cached;
        return MapelResponse.error('Sesi pengaman expired. Silakan login ulang.');
      }

      final data = jsonDecode(rawText) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == 'success') {
        final List mapelJson = data['mapel_list'] as List;
        final mapelList = mapelJson.map((j) => MapelModel.fromJson(j)).toList();

        // Simpan ke cache SharedPreferences
        await prefs.setString(GaraPrefKeys.namaSiswa,  data['nama_siswa']  as String? ?? '');
        await prefs.setString(GaraPrefKeys.kelasSiswa, data['kelas_siswa'] as String? ?? '');
        await prefs.setString(GaraPrefKeys.cachedNamaSiswa,   data['nama_siswa']   as String? ?? '');
        await prefs.setString(GaraPrefKeys.cachedKelasSiswa,  data['kelas_siswa']  as String? ?? '');
        await prefs.setString(GaraPrefKeys.cachedSekolahNama, data['sekolah_nama'] as String? ?? 'Garuda Akademi');
        await prefs.setBool(GaraPrefKeys.cachedGateUjian,     data['gate_ujian_open'] as bool? ?? false);
        await prefs.setString(GaraPrefKeys.cachedMapelList,   jsonEncode(mapelJson));
        await prefs.setInt(GaraPrefKeys.cachedMapelTimestamp, DateTime.now().millisecondsSinceEpoch);

        return MapelResponse(
          success:       true,
          namaSiswa:     data['nama_siswa']  as String? ?? '',
          kelasSiswa:    data['kelas_siswa'] as String? ?? '',
          sekolahNama:   data['sekolah_nama'] as String? ?? 'Garuda Akademi',
          gateUjianOpen: data['gate_ujian_open'] as bool? ?? false,
          mapelList:     mapelList,
        );
      }

      return MapelResponse.error(data['message'] as String? ?? 'Gagal mengambil data mapel.');
    } catch (e) {
      debugPrint('[AuthService.getMapelList] Error: $e');
      // Fallback ke cache jika ada
      final cached = await getCachedMapelList();
      if (cached != null) {
        debugPrint('[AuthService.getMapelList] Menggunakan data cache offline.');
        return cached.copyWith(fromCache: true);
      }
      return MapelResponse.error('Terjadi kesalahan koneksi.');
    }
  }

  /// Membaca data mapel dari cache SharedPreferences.
  /// Mengembalikan null jika tidak ada cache.
  static Future<MapelResponse?> getCachedMapelList() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(GaraPrefKeys.cachedMapelList);
      if (cachedJson == null || cachedJson.isEmpty) return null;

      final List mapelJson = jsonDecode(cachedJson) as List;
      final mapelList = mapelJson.map((j) => MapelModel.fromJson(j)).toList();

      return MapelResponse(
        success:       true,
        namaSiswa:     prefs.getString(GaraPrefKeys.cachedNamaSiswa)  ?? '',
        kelasSiswa:    prefs.getString(GaraPrefKeys.cachedKelasSiswa) ?? '',
        sekolahNama:   prefs.getString(GaraPrefKeys.cachedSekolahNama) ?? 'Garuda Akademi',
        gateUjianOpen: prefs.getBool(GaraPrefKeys.cachedGateUjian)    ?? false,
        mapelList:     mapelList,
        fromCache:     true,
      );
    } catch (e) {
      debugPrint('[AuthService.getCachedMapelList] Error: $e');
      return null;
    }
  }

  

  
  
  
  
  
  
  
  
  
  static Future<bool?> getExamStatus() async {
    try {
      final token = await getToken();
      if (token == null || token.isEmpty) return false;

      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      final response = await http.get(
        Uri.parse('${AppConfig.baseUrl}${AppConfig.examStatusPath}'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'X-App':         'GARA_MOBILE',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(const Duration(seconds: 8));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) {
        return false;
      }

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['is_active'] as bool? ?? false;
        }
      }
      return false;
    } catch (e) {
      debugPrint('[AuthService.getExamStatus] Error: $e');
      return null; 
    }
  }

  

  
  
  static Future<bool> refreshUserData() async {
    try {
      final token = await getToken();
      if (token == null) return false;

      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(const Duration(seconds: 10));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) return false;

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(GaraPrefKeys.namaLengkap,    data['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.namaSiswa,      data['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
          return true;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.refreshUserData] $e');
    }
    return false;
  }

  

  
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(GaraPrefKeys.authToken);
  }

  
  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final loggedIn = prefs.getBool(GaraPrefKeys.isLoggedIn) ?? false;
    final token    = prefs.getString(GaraPrefKeys.authToken) ?? '';
    return loggedIn && token.isNotEmpty;
  }

  

  
  static Future<void> _saveSession(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool  (GaraPrefKeys.isLoggedIn,      true);
    await prefs.setString(GaraPrefKeys.authToken,       data['token']         as String? ?? '');
    await prefs.setString(GaraPrefKeys.userRole,        data['role']          as String? ?? 'siswa');
    await prefs.setString(GaraPrefKeys.namaLengkap,     data['nama']          as String? ?? '');
    await prefs.setString(GaraPrefKeys.namaSiswa,       data['nama']          as String? ?? '');
    await prefs.setString(GaraPrefKeys.profilePhotoUrl, data['profile_photo'] as String? ?? '');
  }

  
  static Future<bool> getProfile() async {
    try {
      final token = await getToken();
      if (token == null) return false;

      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/profile'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(const Duration(seconds: 10));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) return false;

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          final profile = data['data'] as Map<String, dynamic>;
          final prefs = await SharedPreferences.getInstance();
          
          await prefs.setString(GaraPrefKeys.namaSiswa,   profile['nama']  as String? ?? '');
          await prefs.setString(GaraPrefKeys.kelasSiswa,  profile['kelas'] as String? ?? '');
          
          
          await prefs.setInt('student_points',            profile['poin'] as int? ?? 0);
          await prefs.setInt('student_attendance',        profile['absensi_persen'] as int? ?? 0);
          await prefs.setInt('student_pending_tasks',     profile['tugas_pending'] as int? ?? 0);
          
          return true;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.getProfile] Error: $e');
    }
    return false;
  }

  
  
  
  
  
  
  
  
  
  static Future<Map<String, dynamic>?> getStudentAccountDetail() async {
    try {
      final token = await getToken();
      if (token == null) return null;

      final prefs = await SharedPreferences.getInstance();
      final testCookie = prefs.getString(GaraPrefKeys.bypassTestCookie) ?? '';

      final response = await http.get(
        Uri.parse('${AppConfig.apiMobileUrl}/account-detail'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':        'application/json',
          'X-App':         'GARA_MOBILE',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; GARA_APP) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Cookie': '__test=$testCookie',
        },
      ).timeout(const Duration(seconds: 10));

      final rawText = response.body.trim();
      if (rawText.contains('javascript') && rawText.contains('document.cookie')) return null;

      if (response.statusCode == 200) {
        final data = jsonDecode(rawText) as Map<String, dynamic>;
        if (data['status'] == 'success') {
          return data['data'] as Map<String, dynamic>?;
        }
      }
    } catch (e) {
      debugPrint('[AuthService.getStudentAccountDetail] Error: $e');
    }
    return null;
  }

  
  
  static Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    final keysToRemove = [
      GaraPrefKeys.isLoggedIn,
      GaraPrefKeys.userRole,
      GaraPrefKeys.namaSiswa,
      GaraPrefKeys.kelasSiswa,
      GaraPrefKeys.selectedMapel,
      GaraPrefKeys.selectedMapelId,
      GaraPrefKeys.authToken,
      GaraPrefKeys.namaLengkap,
      GaraPrefKeys.profilePhotoUrl,
      GaraPrefKeys.bypassTestCookie,
      GaraPrefKeys.bypassSessionCookie,
      GaraPrefKeys.bypassCookieTimestamp,
      GaraPrefKeys.cachedMapelList,
      GaraPrefKeys.cachedMapelTimestamp,
      GaraPrefKeys.cachedGateUjian,
      GaraPrefKeys.cachedNamaSiswa,
      GaraPrefKeys.cachedKelasSiswa,
      GaraPrefKeys.cachedSekolahNama,
      'student_points',
      'student_attendance',
      'student_pending_tasks',
    ];
    for (final key in keysToRemove) {
      await prefs.remove(key);
    }
  }
}
