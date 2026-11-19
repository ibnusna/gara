import 'package:flutter/foundation.dart';
import '../../utils/app_config.dart';

/// Interceptor cerdas untuk mendeteksi apakah navigasi URL dalam WebView
/// harus dicegat dan dialihkan kembali ke halaman native Flutter (misal: PilihMapelPage).
class HybridNavigationInterceptor {
  HybridNavigationInterceptor._();

  /// Mengembalikan `true` jika URI mengindikasikan intent untuk keluar dari WebView
  /// dan kembali ke aplikasi native Flutter.
  static bool shouldPopToNative(Uri? uri) {
    if (uri == null) return false;

    final scheme = uri.scheme.toLowerCase();
    // 1. Skema custom internal mobile app
    if (scheme == 'garamobile' || scheme == 'gara') {
      return true;
    }

    final path = uri.path.toLowerCase();

    // 2. Navigasi web ke halaman Pilih Mapel (harus selalu native di mobile)
    if (path == AppConfig.studentPilihMapelPath ||
        path.endsWith(AppConfig.studentPilihMapelPath) ||
        path == AppConfig.pilihMapelPath ||
        path.endsWith(AppConfig.pilihMapelPath) ||
        path == '/student/pilih-mapel' ||
        path.endsWith('/student/pilih-mapel') ||
        path == '/pilih-mapel' ||
        path.endsWith('/pilih-mapel')) {
      debugPrint('[HybridNavigationInterceptor] Pop to native on pilih-mapel route: $path');
      return true;
    }

    // 3. Navigasi web ke dashboard siswa (user keluar dari sesi/fitur web)
    if (path == AppConfig.studentDashboardPath ||
        path.endsWith(AppConfig.studentDashboardPath) ||
        path == '/student' ||
        path.endsWith('/student') ||
        path == '/student/' ||
        path.endsWith('/student/')) {
      debugPrint('[HybridNavigationInterceptor] Pop to native on student dashboard route: $path');
      return true;
    }

    // 4. Navigasi web ke halaman login (pop ke native jika user logout dari web LMS)
    if (path == '/login' || path.endsWith('/login')) {
      debugPrint('[HybridNavigationInterceptor] Pop to native on login route: $path');
      return true;
    }

    return false;
  }
}
