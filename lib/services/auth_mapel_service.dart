import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/mapel_model.dart';
import '../utils/app_constants.dart';
import 'InfinityAuthService.dart';

class AuthMapelService {
  static Future<MapelResponse> getMapelList() async {
    try {
      final res = await InfinityAuthService.getMapelList();
      if (res.requiresPasswordChange) return res;
      if (res.success) return res;
      final cached = await getCachedMapelList();
      if (cached != null) {
        debugPrint('[AuthMapelService.getMapelList] Menggunakan cache offline.');
        return cached.copyWith(fromCache: true);
      }
      return res;
    } catch (e) {
      debugPrint('[AuthMapelService.getMapelList] Error: $e');
      final cached = await getCachedMapelList();
      if (cached != null) return cached.copyWith(fromCache: true);
      return MapelResponse.error('Terjadi kesalahan koneksi.');
    }
  }

  static Future<MapelResponse?> getCachedMapelList() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(GaraPrefKeys.cachedMapelList);
      if (cachedJson == null || cachedJson.isEmpty) return null;

      final List mapelJson = jsonDecode(cachedJson) as List;
      final mapelList = mapelJson.map((j) => MapelModel.fromJson(j)).toList();

      return MapelResponse(
        success: true,
        namaSiswa: prefs.getString(GaraPrefKeys.cachedNamaSiswa) ?? '',
        kelasSiswa: prefs.getString(GaraPrefKeys.cachedKelasSiswa) ?? '',
        sekolahNama: prefs.getString(GaraPrefKeys.cachedSekolahNama) ?? 'Garuda Akademi',
        gateUjianOpen: prefs.getBool(GaraPrefKeys.cachedGateUjian) ?? false,
        mapelList: mapelList,
        fromCache: true,
      );
    } catch (e) {
      debugPrint('[AuthMapelService.getCachedMapelList] Error: $e');
      return null;
    }
  }
}
