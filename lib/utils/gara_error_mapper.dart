import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';

class GaraUserError {
  final String title;
  final String userMessage;
  final String errorCode;
  final IconData icon;

  const GaraUserError({
    required this.title,
    required this.userMessage,
    required this.errorCode,
    this.icon = Icons.error_outline_rounded,
  });
}

class GaraErrorMapper {
  GaraErrorMapper._();

  static GaraUserError fromException(Object error, [StackTrace? stackTrace]) {
    // Technical logging strictly restricted to debugPrint (disabled in release)
    debugPrint('[GaraErrorMapper] Raw Exception: $error');
    if (stackTrace != null) {
      debugPrint('[GaraErrorMapper] StackTrace: $stackTrace');
    }

    final str = error.toString().toLowerCase();

    // 1. Network / Disconnected
    if (error is SocketException ||
        str.contains('socketexception') ||
        str.contains('err_internet_disconnected') ||
        str.contains('err_name_not_resolved') ||
        str.contains('failed host lookup')) {
      return const GaraUserError(
        title: 'Koneksi Terputus',
        userMessage: 'Periksa jaringan Wi-Fi atau data seluler Anda, lalu coba kembali.',
        errorCode: 'ERR-NET-001',
        icon: Icons.wifi_off_rounded,
      );
    }

    // 2. Timeout / InfinityFree Challenge Expired
    if (error is TimeoutException ||
        str.contains('timeoutexception') ||
        str.contains('infinityfreebypassexception') ||
        str.contains('timeout_expired') ||
        str.contains('warm-up timeout') ||
        str.contains('timed out')) {
      return const GaraUserError(
        title: 'Server Sedang Sibuk',
        userMessage: 'Waktu tunggu koneksi ke server habis. Silakan tekan Coba Lagi beberapa saat lagi.',
        errorCode: 'ERR-CONN-504',
        icon: Icons.hourglass_empty_rounded,
      );
    }

    // 3. Server Maintenance / Refused
    if (str.contains('500') ||
        str.contains('502') ||
        str.contains('503') ||
        str.contains('err_connection_refused') ||
        str.contains('connection refused') ||
        str.contains('maintenance')) {
      return const GaraUserError(
        title: 'Layanan Sedang Pemeliharaan',
        userMessage: 'Server sekolah sedang dalam penyesuaian teknis. Mohon tunggu sebentar dan coba kembali.',
        errorCode: 'ERR-SRV-500',
        icon: Icons.construction_rounded,
      );
    }

    // 4. Session / Auth
    if (str.contains('401') ||
        str.contains('403') ||
        str.contains('unauthenticated') ||
        str.contains('sesi berakhir') ||
        str.contains('unauthorized')) {
      return const GaraUserError(
        title: 'Sesi Berakhir',
        userMessage: 'Sesi masuk Anda telah berakhir demi keamanan. Silakan masuk kembali.',
        errorCode: 'ERR-AUTH-401',
        icon: Icons.lock_clock_rounded,
      );
    }

    // 5. Download Failures
    if (str.contains('download') || str.contains('err-dl')) {
      return const GaraUserError(
        title: 'Gagal Mengunduh Berkas',
        userMessage: 'Berkas belum dapat disimpan ke perangkat. Pastikan ruang penyimpanan cukup dan coba lagi.',
        errorCode: 'ERR-DL-002',
        icon: Icons.file_download_off_rounded,
      );
    }

    // 6. Generic / Unknown Fallback
    return const GaraUserError(
      title: 'Gangguan Sementara',
      userMessage: 'Terjadi kendala saat memuat halaman ini. Silakan tekan Coba Lagi.',
      errorCode: 'ERR-SYS-999',
      icon: Icons.cloud_off_rounded,
    );
  }
}
