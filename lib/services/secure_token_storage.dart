import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/app_constants.dart';

/// Pengelola token tersimpan menggunakan enkripsi hardware-backed keystore/keychain.
class SecureTokenStorage {
  static const String _kSecureTokenKey = 'gara_secure_auth_token';

  static const _androidOptions = AndroidOptions(
    encryptedSharedPreferences: true,
  );

  static const _iosOptions = IOSOptions(
    accessibility: KeychainAccessibility.first_unlock_this_device,
  );

  final FlutterSecureStorage _storage;

  SecureTokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ??
            const FlutterSecureStorage(
              aOptions: _androidOptions,
              iOptions: _iosOptions,
            );

  static final SecureTokenStorage instance = SecureTokenStorage();

  static Future<bool> migrateLegacyTokenIfNeeded({
    SharedPreferences? sharedPreferences,
  }) =>
      instance.migrateLegacyToken(sharedPreferences: sharedPreferences);

  /// Menyimpan token autentikasi ke dalam penyimpanan terenkripsi.
  Future<void> saveToken(String token) async {
    await _storage.write(
      key: _kSecureTokenKey,
      value: token,
      aOptions: _androidOptions,
      iOptions: _iosOptions,
    );
  }

  /// Mengambil token autentikasi dari penyimpanan terenkripsi.
  Future<String?> getToken() async {
    return await _storage.read(
      key: _kSecureTokenKey,
      aOptions: _androidOptions,
      iOptions: _iosOptions,
    );
  }

  /// Menghapus token dari penyimpanan terenkripsi.
  Future<void> clearToken() async {
    await _storage.delete(
      key: _kSecureTokenKey,
      aOptions: _androidOptions,
      iOptions: _iosOptions,
    );
  }

  /// Migrasi atomik token legacy plaintext dari SharedPreferences ke FlutterSecureStorage.
  Future<bool> migrateLegacyToken({
    SharedPreferences? sharedPreferences,
  }) async {
    try {
      final prefs = sharedPreferences ?? await SharedPreferences.getInstance();
      final legacyToken = prefs.getString(GaraPrefKeys.authToken);

      if (legacyToken != null && legacyToken.trim().isNotEmpty) {
        debugPrint('[SecureTokenStorage] Migrating legacy token from SharedPreferences...');
        await saveToken(legacyToken.trim());
        await prefs.remove(GaraPrefKeys.authToken);
        debugPrint('[SecureTokenStorage] Legacy token migration completed.');
        return true;
      }
      return false;
    } catch (e) {
      debugPrint('[SecureTokenStorage] Migration error: $e');
      return false;
    }
  }
}
