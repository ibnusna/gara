import 'dart:async';
import 'dart:io';
import 'package:flutter_test/flutter_test.dart';
import 'package:gara_flutter/services/infinity_bypass_exception.dart';
import 'package:gara_flutter/utils/gara_error_mapper.dart';

void main() {
  group('GaraErrorMapper Taxonomy Tests', () {
    test('SocketException maps to ERR-NET-001 with user-friendly Indonesian copy', () {
      final err = GaraErrorMapper.fromException(const SocketException('Failed host lookup'));
      expect(err.errorCode, 'ERR-NET-001');
      expect(err.title, 'Koneksi Terputus');
      expect(err.userMessage.contains('Periksa jaringan'), isTrue);
    });

    test('TimeoutException maps to ERR-CONN-504', () {
      final err = GaraErrorMapper.fromException(TimeoutException('Connection timed out'));
      expect(err.errorCode, 'ERR-CONN-504');
      expect(err.title, 'Server Sedang Sibuk');
    });

    test('Auth 401 string or exception maps to ERR-AUTH-401', () {
      final err = GaraErrorMapper.fromException('HTTP 401 Unauthorized');
      expect(err.errorCode, 'ERR-AUTH-401');
      expect(err.title, 'Sesi Berakhir');
    });

    test('Forbidden 403 maps to ERR-AUTH-401', () {
      final err = GaraErrorMapper.fromException('Status 403 Forbidden');
      expect(err.errorCode, 'ERR-AUTH-401');
      expect(err.title, 'Sesi Berakhir');
    });

    test('Server 500 error maps to ERR-SRV-500', () {
      final err = GaraErrorMapper.fromException('HTTP 500 Internal Server Error');
      expect(err.errorCode, 'ERR-SRV-500');
      expect(err.title, 'Layanan Sedang Pemeliharaan');
    });

    test('InfinityFreeBypassException maps to ERR-CONN-504 and masks rawHtmlSnippet in toString', () {
      const ex = InfinityFreeBypassException(
        message: 'Security challenge failed',
        rawHtmlSnippet: '<html><body>Security Challenge Blocked</body></html>',
      );
      final err = GaraErrorMapper.fromException(ex);
      expect(err.errorCode, 'ERR-CONN-504');
      expect(ex.toString().contains('<html>'), isFalse);
    });

    test('Unknown arbitrary exceptions map to ERR-SYS-999 without exposing internals', () {
      final err = GaraErrorMapper.fromException(Exception('Fatal kernel panic sql syntax error'));
      expect(err.errorCode, 'ERR-SYS-999');
      expect(err.title, 'Gangguan Sementara');
      expect(err.userMessage.contains('sql syntax'), isFalse);
    });
  });
}
