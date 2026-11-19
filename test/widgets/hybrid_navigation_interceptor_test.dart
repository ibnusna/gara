import 'package:flutter_test/flutter_test.dart';
import 'package:gara_flutter/widgets/browser/hybrid_navigation_interceptor.dart';

void main() {
  group('HybridNavigationInterceptor Tests', () {
    test('should return false for null URI', () {
      expect(HybridNavigationInterceptor.shouldPopToNative(null), isFalse);
    });

    test('should intercept custom schemes (garamobile, gara)', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(Uri.parse('garamobile://pilih-mapel')),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(Uri.parse('garamobile://back')),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(Uri.parse('gara://close')),
        isTrue,
      );
    });

    test('should intercept web pilih-mapel paths to keep student on native UI', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student/pilih-mapel'),
        ),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('http://localhost:8000/student/pilih-mapel'),
        ),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://garaedu.gt.tc/pilih-mapel'),
        ),
        isTrue,
      );
    });

    test('should intercept web student dashboard paths to pop back to native app', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student/dashboard'),
        ),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student'),
        ),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student/'),
        ),
        isTrue,
      );
    });

    test('should intercept web login paths to pop back to native app', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/login'),
        ),
        isTrue,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('http://localhost:8000/login'),
        ),
        isTrue,
      );
    });

    test('should NOT intercept active exam paths', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/ruang-ujian'),
        ),
        isFalse,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/ruang-ujian/arena'),
        ),
        isFalse,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/ruang-ujian/konfirmasi'),
        ),
        isFalse,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/ruang-ujian/hasil'),
        ),
        isFalse,
      );
    });

    test('should NOT intercept student content or handoff routes', () {
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student/materi'),
        ),
        isFalse,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/student/tugas'),
        ),
        isFalse,
      );
      expect(
        HybridNavigationInterceptor.shouldPopToNative(
          Uri.parse('https://gara.my.id/auth/webview-handoff?ticket=abc123xyz'),
        ),
        isFalse,
      );
    });
  });
}
