import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:http/http.dart' as http;
import 'package:open_filex/open_filex.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';
import '../utils/app_config.dart';

class DownloadResult {
  final bool success;
  final String? filePath;
  final String? fileName;
  final String? errorCode;
  final String? errorMessage;

  const DownloadResult({
    required this.success,
    this.filePath,
    this.fileName,
    this.errorCode,
    this.errorMessage,
  });
}

class AuthenticatedDownloadService {
  AuthenticatedDownloadService._();

  static Future<DownloadResult> downloadFromWebView({
    required Uri url,
    String? suggestedFilename,
    String? userAgent,
  }) async {
    try {
      final cookieHeader = await _extractCookies(url);
      final client = http.Client();
      http.Response response;
      Uri currentUrl = url;

      for (int i = 0; i < 5; i++) {
        final req = http.Request('GET', currentUrl);
        req.headers['Cookie'] = cookieHeader;
        req.headers['User-Agent'] = userAgent ?? 'GARA_OFFICIAL_APP';
        req.headers['Referer'] = AppConfig.baseUrl;
        req.followRedirects = false;

        final streamed = await client.send(req).timeout(const Duration(seconds: 25));
        response = await http.Response.fromStream(streamed);

        if ([301, 302, 303, 307, 308].contains(response.statusCode)) {
          final loc = response.headers['location'];
          if (loc != null && loc.isNotEmpty) {
            currentUrl = currentUrl.resolve(loc);
            continue;
          }
        }

        if (response.statusCode == 401 || response.statusCode == 403) {
          return const DownloadResult(
            success: false,
            errorCode: 'ERR-DL-401',
            errorMessage: 'Sesi unduhan berakhir. Silakan muat ulang halaman.',
          );
        }

        final contentType = response.headers['content-type'] ?? '';
        if (contentType.contains('text/html') && !currentUrl.path.endsWith('.html')) {
          return const DownloadResult(
            success: false,
            errorCode: 'ERR-DL-401',
            errorMessage: 'Otentikasi unduhan gagal.',
          );
        }

        final fileName = _resolveFileName(
          response.headers['content-disposition'],
          suggestedFilename,
          currentUrl,
        );

        final targetDir = await _getDownloadDir();
        final finalPath = _ensureUniquePath(targetDir, fileName);

        final file = File(finalPath);
        await file.writeAsBytes(response.bodyBytes);

        return DownloadResult(
          success: true,
          filePath: finalPath,
          fileName: p.basename(finalPath),
        );
      }

      return const DownloadResult(
        success: false,
        errorCode: 'ERR-DL-500',
        errorMessage: 'Terlalu banyak pengalihan unduhan.',
      );
    } catch (e) {
      debugPrint('[AuthenticatedDownloadService] Error: $e');
      return const DownloadResult(
        success: false,
        errorCode: 'ERR-DL-002',
        errorMessage: 'Gagal mengunduh berkas ke penyimpanan perangkat.',
      );
    }
  }

  static Future<String> _extractCookies(Uri targetUrl) async {
    try {
      final mgr = CookieManager.instance();
      final targetCookies = await mgr.getCookies(url: WebUri.uri(targetUrl));
      final baseCookies = await mgr.getCookies(url: WebUri(AppConfig.baseUrl));

      final allMap = <String, String>{};
      for (final c in baseCookies) {
        allMap[c.name] = c.value.toString();
      }
      for (final c in targetCookies) {
        allMap[c.name] = c.value.toString();
      }
      return allMap.entries.map((e) => '${e.key}=${e.value}').join('; ');
    } catch (_) {
      return '';
    }
  }

  static String _resolveFileName(String? disposition, String? suggested, Uri url) {
    if (disposition != null && disposition.isNotEmpty) {
      final utfMatch = RegExp(r"filename\*=UTF-8''([^;\r\n]+)").firstMatch(disposition);
      if (utfMatch != null) return Uri.decodeComponent(utfMatch.group(1)!);

      final simpleMatch = RegExp(r'filename="?([^";\r\n]+)"?').firstMatch(disposition);
      if (simpleMatch != null) return simpleMatch.group(1)!.trim();
    }

    if (suggested != null && suggested.trim().isNotEmpty) {
      return suggested.trim();
    }

    final lastSegment = url.pathSegments.isNotEmpty ? url.pathSegments.last : 'downloaded_file';
    return lastSegment.contains('.') ? lastSegment : '$lastSegment.bin';
  }

  static Future<Directory> _getDownloadDir() async {
    if (Platform.isAndroid) {
      final ext = await getExternalStorageDirectories(type: StorageDirectory.downloads);
      if (ext != null && ext.isNotEmpty) return ext.first;
    }
    try {
      final d = await getDownloadsDirectory();
      if (d != null) return d;
    } catch (_) {}
    return await getApplicationDocumentsDirectory();
  }

  static String _ensureUniquePath(Directory dir, String fileName) {
    String base = p.basenameWithoutExtension(fileName);
    String ext = p.extension(fileName);
    String target = p.join(dir.path, fileName);
    int count = 1;

    while (File(target).existsSync()) {
      target = p.join(dir.path, '$base($count)$ext');
      count++;
    }
    return target;
  }

  static Future<void> openFile(String filePath) async {
    try {
      await OpenFilex.open(filePath);
    } catch (e) {
      debugPrint('[AuthenticatedDownloadService.openFile] Error: $e');
    }
  }
}
