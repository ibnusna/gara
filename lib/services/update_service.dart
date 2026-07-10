import 'dart:io';
import 'package:dio/dio.dart';
import 'package:firebase_remote_config/firebase_remote_config.dart';
import 'package:open_filex/open_filex.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:path_provider/path_provider.dart';

class UpdateService {
  final FirebaseRemoteConfig _remoteConfig = FirebaseRemoteConfig.instance;

  Future<void> initialize() async {
    await _remoteConfig.setConfigSettings(RemoteConfigSettings(
      fetchTimeout: const Duration(seconds: 10),
      minimumFetchInterval: const Duration(minutes: 5), // Change to 0 for testing if needed
    ));
    await _remoteConfig.setDefaults(const {
      "build_number": 1,
      "latest_version": "1.0.0",
      "repo_path": "ibnusna/gara",
      "force_update": false,
      "release_notes": "Versi awal GARA.",
    });
    await _remoteConfig.fetchAndActivate();
  }

  Future<bool> isUpdateAvailable() async {
    final packageInfo = await PackageInfo.fromPlatform();
    final localBuildNumber = int.tryParse(packageInfo.buildNumber) ?? 1;
    final remoteBuildNumber = _remoteConfig.getInt('build_number');
    return remoteBuildNumber > localBuildNumber;
  }

  bool isForceUpdate() {
    return _remoteConfig.getBool('force_update');
  }

  String getLatestVersion() => _remoteConfig.getString('latest_version');
  String getReleaseNotes() => _remoteConfig.getString('release_notes');

  Future<String?> fetchLatestApkUrl() async {
    final repoPath = _remoteConfig.getString('repo_path');
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
      print('Failed to fetch github release: $e');
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
