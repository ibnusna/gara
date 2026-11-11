import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';

class BrowserFileDownloader {
  BrowserFileDownloader._();

  static Future<void> saveAndOpenDataUri(
    String dataUri,
    String suggestedFilename,
  ) async {
    try {
      final parts = dataUri.split(',');
      if (parts.length < 2) return;
      final String base64Str = parts[1];
      final List<int> bytes = base64.decode(base64Str);

      final Directory? tempDir = Platform.isAndroid
          ? await getExternalStorageDirectory()
          : await getTemporaryDirectory();

      if (tempDir == null) return;

      final String filePath = '${tempDir.path}/$suggestedFilename';
      final File file = File(filePath);
      await file.writeAsBytes(bytes);

      debugPrint('[GARA Download] File saved to: $filePath');
      await OpenFilex.open(filePath);
    } catch (e) {
      debugPrint('[GARA Download] Error saving/opening base64 file: $e');
    }
  }
}
