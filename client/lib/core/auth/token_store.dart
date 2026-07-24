import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:logger/logger.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'config/env.dart';

/// Centralized secure storage instance.
final secureStorageProvider = Provider<FlutterSecureStorage>((ref) {
  return const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );
});

/// SharedPreferences for non-sensitive key-value data (locale, theme, last-sync, etc.).
final sharedPreferencesProvider = FutureProvider<SharedPreferences>((ref) async {
  return SharedPreferences.getInstance();
});

/// Stores the Sanctum bearer token securely.
class TokenStore {
  static const _keyToken = 'auth_token';
  static const _keyUserId = 'auth_user_id';
  static const _keyExpiry = 'auth_token_expiry';

  final FlutterSecureStorage _storage;

  TokenStore(this._storage);

  Future<void> saveToken(String token, {String? userId}) async {
    await _storage.write(key: _keyToken, value: token);
    if (userId != null) {
      await _storage.write(key: _keyUserId, value: userId);
    }
  }

  Future<String?> getToken() async {
    return _storage.read(key: _keyToken);
  }

  Future<String?> getUserId() async {
    return _storage.read(key: _keyUserId);
  }

  Future<bool> hasToken() async {
    final token = await _storage.read(key: _keyToken);
    return token != null && token.isNotEmpty;
  }

  Future<void> clearAll() async {
    await _storage.deleteAll();
  }
}

final tokenStoreProvider = Provider<TokenStore>((ref) {
  return TokenStore(ref.read(secureStorageProvider));
});

/// Logger instance used across the app.
final loggerProvider = Provider((ref) {
  // Configure in main.dart or here as needed
  return Logger();
});
