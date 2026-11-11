import 'dart:io';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:open_filex/open_filex.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:path_provider/path_provider.dart';

class UpdateService {
  Map<String, dynamic> _config = {};

  Future<void> initialize() async {
    try {
      final doc = await FirebaseFirestore.instance
          .collection('app_config')
          .doc('remote_config')
          .get()
          .timeout(const Duration(seconds: 3));

      if (doc.exists && doc.data() != null) {
        _config = doc.data()!;
        debugPrint('[UpdateService] Firestore RemoteConfig terisi: $_config');
      }
    } catch (e) {
      debugPrint('[UpdateService] Error fetching remote_config from Firestore: $e');
    }
  }

  Future<bool> isUpdateAvailable() async {
    final packageInfo = await PackageInfo.fromPlatform();
    final localBuildNumber = int.tryParse(packageInfo.buildNumber) ?? 1;
    
    final remoteBuildRaw = _config['build_number'];
    final remoteBuildNumber = remoteBuildRaw is int
        ? remoteBuildRaw
        : int.tryParse(remoteBuildRaw?.toString() ?? '1') ?? 1;

    debugPrint('[UpdateService] Local Build: $localBuildNumber, Remote Build: $remoteBuildNumber');
    return remoteBuildNumber > localBuildNumber;
  }

  bool isForceUpdate() {
    return _config['force_update'] as bool? ?? false;
  }

  String getLatestVersion() => _config['latest_version'] as String? ?? '1.0.0';
  String getReleaseNotes() => _config['release_notes'] as String? ?? 'Pembaruan aplikasi GARA.';

  Future<String?> fetchLatestApkUrl() async {
    final repoPath = _config['repo_path'] as String? ?? 'ibnusna/appsgara';
    final apiUrl = 'https://api.github.com/repos/$repoPath/releases/latest';
    try {
      final dio = Dio();
      final response = await dio.get(apiUrl);
      if (response.statusCode == 200) {
        final data = response.data;
        final assets = data['assets'] as List<dynamic>;
        for (var asset in assets) {
          final name = asset['name'] as String;
          if (name.endsWith('.apk')) {
            return asset['browser_download_url'] as String;
          }
        }
      }
    } catch (e) {
      debugPrint('Failed to fetch github release: $e');
    }
    return null;
  }

  Future<void> downloadAndInstallApk(String url, Function(int, int) onProgress) async {
    final tempDir = await getExternalStorageDirectory();
    if (tempDir == null) throw Exception("Cannot get external storage directory");
    final filePath = "${tempDir.path}/update_lms_sekolah.apk";

    // Remove old file if exists
    final oldFile = File(filePath);
    if (await oldFile.exists()) {
      await oldFile.delete();
    }

    final dio = Dio();
    await dio.download(url, filePath, onReceiveProgress: onProgress);

    final result = await OpenFilex.open(filePath);
    print("OpenFilex result: ${result.message}");
  }

  Future<void> cleanOldApk() async {
    try {
      final tempDir = await getExternalStorageDirectory();
      if (tempDir != null) {
        final filePath = "${tempDir.path}/update_lms_sekolah.apk";
        final oldFile = File(filePath);
        if (await oldFile.exists()) {
          await oldFile.delete();
        }
      }
    } catch (e) {
      print("Clean old apk failed: $e");
    }
  }
}
