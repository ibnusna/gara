import 'dart:math';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_config.dart';

class ServerFallbackService {
  static const String _keyFallbackUntil = 'fallback_until_timestamp';
  static const String _keyAltServer = 'fallback_active_server';

  /// Cek apakah kita masih berada dalam masa fallback (2 hari sejak terdeteksi suspend)
  /// Panggil fungsi ini saat inisialisasi aplikasi (di main.dart)
  static Future<void> checkAndApplyFallbackOnStartup() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final fallbackUntilMs = prefs.getInt(_keyFallbackUntil);
      
      if (fallbackUntilMs != null) {
        final fallbackUntil = DateTime.fromMillisecondsSinceEpoch(fallbackUntilMs);
        if (DateTime.now().isBefore(fallbackUntil)) {
          // Masih dalam masa fallback
          final altServer = prefs.getString(_keyAltServer);
          if (altServer != null && altServer.isNotEmpty) {
            AppConfig.overrideBaseUrl = altServer;
            debugPrint('[ServerFallback] Melanjutkan sesi Fallback Server ke: $altServer (berlaku s.d. $fallbackUntil)');
          }
        } else {
          // Masa fallback habis (sudah 2 hari), hapus preferensi agar mencoba server utama lagi
          debugPrint('[ServerFallback] Masa fallback sudah habis. Kembali mencoba server utama.');
          await prefs.remove(_keyFallbackUntil);
          await prefs.remove(_keyAltServer);
          AppConfig.overrideBaseUrl = null;
        }
      }
    } catch (e) {
      debugPrint('[ServerFallback] Error checking fallback status: $e');
    }
  }

  /// Panggil fungsi ini saat InfinityApiClient mendeteksi bahwa domain di-suspend atau maintenance.
  /// Ini akan menembak Firestore, mengambil daftar alternative server, lalu apply fallback.
  static Future<bool> activateFallback() async {
    try {
      debugPrint('[ServerFallback] Mengaktifkan Fallback Mechanism...');
      final doc = await FirebaseFirestore.instance
          .collection('app_config')
          .doc('remote_config')
          .get()
          .timeout(const Duration(seconds: 5));

      if (doc.exists && doc.data() != null) {
        final data = doc.data()!;
        final String? altServersStr = data['alternative_servers'] as String?;
        final bool isMaintenance = data['maintenance_mode'] as bool? ?? false;

        debugPrint('[ServerFallback] Simulasi Maintenance: $isMaintenance');

        if (altServersStr != null && altServersStr.isNotEmpty) {
          // Pisahkan dengan koma jika ada banyak server alternatif
          final servers = altServersStr.split(',')
              .map((s) => s.trim())
              .where((s) => s.isNotEmpty && s.startsWith('http'))
              .toList();

          if (servers.isNotEmpty) {
            // Pilih satu secara acak jika ada lebih dari 1
            final random = Random();
            final selectedServer = servers[random.nextInt(servers.length)];
            
            AppConfig.overrideBaseUrl = selectedServer;
            
            // Simpan status fallback selama 2 hari ke depan
            final prefs = await SharedPreferences.getInstance();
            final fallbackUntil = DateTime.now().add(const Duration(days: 2));
            
            await prefs.setInt(_keyFallbackUntil, fallbackUntil.millisecondsSinceEpoch);
            await prefs.setString(_keyAltServer, selectedServer);

            debugPrint('[ServerFallback] SUKSES! Berpindah otomatis ke server alternatif: $selectedServer');
            return true;
          }
        }
      }
      
      debugPrint('[ServerFallback] Gagal mengaktifkan fallback: Tidak ada URL alternative_servers valid di Firestore.');
      return false;
    } catch (e) {
      debugPrint('[ServerFallback] Error saat mengambil daftar Alternative Server dari Firestore: $e');
      return false;
    }
  }

  /// Secara manual menghapus masa fallback dan kembali ke server InfinityFree (jika ingin di UI dsb)
  static Future<void> clearFallback() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyFallbackUntil);
    await prefs.remove(_keyAltServer);
    AppConfig.overrideBaseUrl = null;
    debugPrint('[ServerFallback] Fallback dihapus secara manual.');
  }
}
