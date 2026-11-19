import 'package:flutter_test/flutter_test.dart';
import 'package:gara_flutter/utils/app_config.dart';

void main() {
  group('AppConfig Security & Network Hardening Tests', () {
    test('Default compile-time config has Infinity bypass disabled', () {
      expect(AppConfig.enableLegacyInfinityBypass, isFalse);
    });

    test('Allowed domains whitelist verification', () {
      expect(AppConfig.allowedDomains, contains('garaedu.gt.tc'));
      expect(AppConfig.allowedDomains, contains('api.sekolah.sch.id'));
      // Ensure no dangerous wildcards
      expect(AppConfig.allowedDomains.contains('*'), isFalse);
      expect(AppConfig.allowedDomains.contains(''), isFalse);
    });

    test('isAllowedDomain validates trusted hosts and subdomains', () {
      expect(AppConfig.isAllowedDomain('https://garaedu.gt.tc/student/dashboard'), isTrue);
      expect(AppConfig.isAllowedDomain('https://cbt.garaedu.gt.tc/exam'), isTrue);
      expect(AppConfig.isAllowedDomain('https://api.sekolah.sch.id/api'), isTrue);
      expect(AppConfig.isAllowedDomain('http://localhost:8000'), isTrue);
      expect(AppConfig.isAllowedDomain('http://127.0.0.1:8000'), isTrue);
      expect(AppConfig.isAllowedDomain('http://10.0.2.2:8000'), isTrue);
      expect(AppConfig.isAllowedDomain('http://192.168.1.10:8000'), isTrue);
    });

    test('isAllowedDomain rejects unauthorized and spoofed hosts', () {
      expect(AppConfig.isAllowedDomain('https://evil.com'), isFalse);
      expect(AppConfig.isAllowedDomain('https://garaedu.gt.tc.attacker.com'), isFalse);
      expect(AppConfig.isAllowedDomain('https://fakegaraedu.gt.tc.malicious.net'), isFalse);
      expect(AppConfig.isAllowedDomain('https://phishing-lms.com/login'), isFalse);
    });

    test('isExamArenaUrl correctly identifies exam arena URLs', () {
      expect(AppConfig.isExamArenaUrl('https://garaedu.gt.tc/ruang-ujian/arena/123'), isTrue);
      expect(AppConfig.isExamArenaUrl('https://garaedu.gt.tc/student/ruang-kompetensi/ujian'), isTrue);
      expect(AppConfig.isExamArenaUrl('https://garaedu.gt.tc/student/dashboard'), isFalse);
      expect(AppConfig.isExamArenaUrl('https://garaedu.gt.tc/student/ruang-belajar'), isFalse);
    });
  });
}
