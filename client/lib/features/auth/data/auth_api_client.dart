import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';

import 'auth_dto.dart';

part 'auth_api_client.g.dart';

/// Retrofit API client for auth endpoints.
///
/// Based on routes/auth.php:
/// - POST /login
/// - POST /login/otp/request
/// - POST /login/otp/confirm
/// - POST /password/request
/// - POST /password/confirm
/// - POST /password/reset
/// - POST /register
/// - POST /register/email
/// - POST /register/verify
/// - POST /logout
/// - POST /security (2FA)
/// - POST /lock (screen-lock)
/// - POST /unlock
/// - GET /user
/// - POST /confirm-password
/// - GET /config
///
/// Note: InstiKit auth routes are under `/api/v1/auth/` NOT `/api/v1/app/auth/`.
@RestApi()
abstract class AuthApiClient {
  factory AuthApiClient(Dio dio, {String baseUrl}) = _AuthApiClient;

  @POST('/auth/login')
  Future<LoginResponse> login(@Body() LoginRequest request);

  @POST('/auth/login/otp/request')
  Future<void> requestOtp(@Body() OtpRequest request);

  @POST('/auth/login/otp/confirm')
  Future<LoginResponse> confirmOtp(@Body() OtpConfirmRequest request);

  @POST('/auth/password/request')
  Future<void> requestPasswordReset(@Body() PasswordResetRequest request);

  @POST('/auth/password/confirm')
  Future<void> confirmPasswordReset(@Body() Map<String, dynamic> body);

  @POST('/auth/password/reset')
  Future<void> resetPassword(@Body() Map<String, dynamic> body);

  @POST('/auth/logout')
  Future<void> logout();

  @POST('/auth/security')
  Future<Map<String, dynamic>> verify2FA(@Body() Map<String, dynamic> body);

  @POST('/auth/lock')
  Future<void> lockScreen();

  @POST('/auth/unlock')
  Future<void> unlockScreen(@Body() Map<String, dynamic> body);

  @GET('/auth/user')
  Future<UserDto> getCurrentUser();

  @GET('/auth/config')
  Future<Map<String, dynamic>> getConfig();
}
