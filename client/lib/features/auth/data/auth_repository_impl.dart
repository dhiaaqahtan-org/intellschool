import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/auth/token_store.dart';
import '../../../core/config/config_model.dart';
import '../../../core/config/config_provider.dart';
import '../../../core/error/failures.dart';
import '../../../core/error/result.dart';
import '../../../core/network/dio_provider.dart';
import '../data/auth_api_client.dart';
import '../data/auth_dto.dart';
import 'auth_repository.dart';

/// Implementation of [AuthRepository] using Retrofit + Dio.
class AuthRepositoryImpl implements AuthRepository {
  final AuthApiClient _apiClient;
  final TokenStore _tokenStore;

  AuthRepositoryImpl(this._apiClient, this._tokenStore);

  @override
  Future<Result<LoginResponse>> login({
    required String usernameOrEmail,
    required String password,
    String? otp,
  }) async {
    try {
      final response = await _apiClient.login(LoginRequest(
        usernameOrEmail: usernameOrEmail,
        password: password,
        otp: otp,
      ));

      // If token was returned, persist it
      if (response.token != null && response.token!.isNotEmpty) {
        await _tokenStore.saveToken(response.token!, userId: response.user?.id);
      }

      return Results.ok(response);
    } on DioException catch (e) {
      return Results.err(e.toFailure());
    } catch (e) {
      return Results.err(Failure.unknown(message: e.toString()));
    }
  }

  @override
  Future<Result<void>> requestOtp({
    required String usernameOrEmail,
    required String password,
  }) async {
    try {
      await _apiClient.requestOtp(OtpRequest(
        usernameOrEmail: usernameOrEmail,
        password: password,
      ));
      return Results.ok(null);
    } on DioException catch (e) {
      return Results.err(e.toFailure());
    } catch (e) {
      return Results.err(Failure.unknown(message: e.toString()));
    }
  }

  @override
  Future<Result<LoginResponse>> confirmOtp({
    required String usernameOrEmail,
    required String password,
    required String otp,
  }) async {
    try {
      final response = await _apiClient.confirmOtp(OtpConfirmRequest(
        usernameOrEmail: usernameOrEmail,
        password: password,
        otp: otp,
      ));

      if (response.token != null && response.token!.isNotEmpty) {
        await _tokenStore.saveToken(response.token!, userId: response.user?.id);
      }

      return Results.ok(response);
    } on DioException catch (e) {
      return Results.err(e.toFailure());
    } catch (e) {
      return Results.err(Failure.unknown(message: e.toString()));
    }
  }

  @override
  Future<Result<void>> logout() async {
    try {
      await _apiClient.logout();
    } catch (_) {
      // Even if the API call fails, we must clear local state
    }
    await _tokenStore.clearAll();
    return Results.ok(null);
  }

  @override
  Future<Result<AppConfig>> fetchConfig() async {
    try {
      final json = await _apiClient.getConfig();
      final config = AppConfig.fromJson(json);
      return Results.ok(config);
    } on DioException catch (e) {
      return Results.err(e.toFailure());
    } catch (e) {
      return Results.err(Failure.unknown(message: e.toString()));
    }
  }

  @override
  Future<Result<void>> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    try {
      // TODO: wire to the correct endpoint once verified from routes/auth.php
      await _apiClient.confirmPasswordReset({
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPassword,
      });
      return Results.ok(null);
    } on DioException catch (e) {
      return Results.err(e.toFailure());
    } catch (e) {
      return Results.err(Failure.unknown(message: e.toString()));
    }
  }
}

/// Provider for the auth repository.
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final dio = ref.read(dioProvider);
  final tokenStore = ref.read(tokenStoreProvider);
  return AuthRepositoryImpl(AuthApiClient(dio), tokenStore);
});
