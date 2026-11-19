import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:gara_flutter/services/secure_token_storage.dart';
import 'package:gara_flutter/utils/app_constants.dart';

class FakeFlutterSecureStorage extends Fake implements FlutterSecureStorage {
  final Map<String, String> _data = {};

  @override
  Future<void> write({
    required String key,
    required String? value,
    IOSOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    MacOsOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    if (value != null) {
      _data[key] = value;
    } else {
      _data.remove(key);
    }
  }

  @override
  Future<String?> read({
    required String key,
    IOSOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    MacOsOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    return _data[key];
  }

  @override
  Future<void> delete({
    required String key,
    IOSOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    MacOsOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    _data.remove(key);
  }

  @override
  Future<bool> containsKey({
    required String key,
    IOSOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    MacOsOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    return _data.containsKey(key);
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('SecureTokenStorage & Legacy Auth Migration Tests', () {
    late FakeFlutterSecureStorage fakeSecureStorage;

    setUp(() {
      fakeSecureStorage = FakeFlutterSecureStorage();
    });

    test('Migrates legacy plaintext token from SharedPreferences to SecureStorage and deletes plaintext', () async {
      SharedPreferences.setMockInitialValues({
        GaraPrefKeys.authToken: 'legacy_plain_secret_jwt_token',
      });
      final prefs = await SharedPreferences.getInstance();

      final storage = SecureTokenStorage(storage: fakeSecureStorage);

      final migrated = await storage.migrateLegacyToken(sharedPreferences: prefs);
      expect(migrated, isTrue);

      final tokenInSecure = await storage.getToken();
      expect(tokenInSecure, 'legacy_plain_secret_jwt_token');

      // Verify plaintext token is removed from SharedPreferences
      expect(prefs.containsKey(GaraPrefKeys.authToken), isFalse);
    });

    test('Idempotency: Re-running migration when SharedPreferences has no token returns false and preserves secure token', () async {
      SharedPreferences.setMockInitialValues({});
      final prefs = await SharedPreferences.getInstance();

      final storage = SecureTokenStorage(storage: fakeSecureStorage);
      await storage.saveToken('already_secure_token');

      final migrated = await storage.migrateLegacyToken(sharedPreferences: prefs);
      expect(migrated, isFalse);

      final token = await storage.getToken();
      expect(token, 'already_secure_token');
    });

    test('saveToken, getToken, clearToken cycle works correctly', () async {
      final storage = SecureTokenStorage(storage: fakeSecureStorage);

      expect(await storage.getToken(), isNull);
      await storage.saveToken('token_abc_123');
      expect(await storage.getToken(), 'token_abc_123');
      await storage.clearToken();
      expect(await storage.getToken(), isNull);
    });
  });
}
