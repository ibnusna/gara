// ============================================================
//  GARA Flutter — Connectivity Service
//  Masalah 4: Deteksi koneksi internet real-time
//
//  Cara pakai:
//    final connected = await ConnectivityService.isConnected();
//    ConnectivityService.stream.listen((isConnected) { ... });
// ============================================================

import 'dart:async';
import 'dart:io';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';

class ConnectivityService {
  ConnectivityService._();

  static final Connectivity _connectivity = Connectivity();

  // Stream yang emit true/false saat status koneksi berubah
  static Stream<bool> get stream {
    return _connectivity.onConnectivityChanged.asyncMap(
      (results) => _isActuallyConnected(results),
    );
  }

  /// Cek apakah saat ini ada koneksi internet aktif.
  ///
  /// PENTING: Ini melakukan HTTP ping ke server GARA, bukan hanya cek WiFi/data.
  /// WiFi bisa aktif tapi internet tidak tersedia (captive portal, dll).
  static Future<bool> isConnected() async {
    try {
      final results = await _connectivity.checkConnectivity();
      if (results.contains(ConnectivityResult.none)) return false;

      // Ping ke server untuk konfirmasi internet aktif
      final result = await InternetAddress.lookup('garudakademi.ct.ws')
          .timeout(const Duration(seconds: 5));
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } on SocketException {
      return false;
    } catch (e) {
      debugPrint('[ConnectivityService] Error: $e');
      return false;
    }
  }

  /// Versi cepat: hanya cek status network adapter (tanpa ping).
  /// Lebih cepat tapi tidak 100% akurat (false positive jika WiFi connected tapi no internet).
  static Future<bool> isNetworkAvailable() async {
    try {
      final results = await _connectivity.checkConnectivity();
      return !results.contains(ConnectivityResult.none);
    } catch (_) {
      return false;
    }
  }

  static Future<bool> _isActuallyConnected(List<ConnectivityResult> results) async {
    if (results.contains(ConnectivityResult.none)) return false;
    try {
      final result = await InternetAddress.lookup('garudakademi.ct.ws')
          .timeout(const Duration(seconds: 3));
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } catch (_) {
      return false;
    }
  }
}
