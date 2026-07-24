import 'package:freezed_annotation/freezed_annotation.dart';

part 'auth_dto.freezed.dart';
part 'auth_dto.g.dart';

/// Login request body for `POST /api/v1/auth/login`.
@freezed
class LoginRequest with _$LoginRequest {
  const factory LoginRequest({
    @JsonKey(name: 'username_or_email') required String usernameOrEmail,
    @JsonKey(name: 'password') required String password,
    @JsonKey(name: 'device_name') @Default('flutter_client') String deviceName,
    @JsonKey(name: 'otp') String? otp,
  }) = _LoginRequest;

  factory LoginRequest.fromJson(Map<String, dynamic> json) => _$LoginRequestFromJson(json);
}

/// Login response from `POST /api/v1/auth/login`.
///
/// Per flutter-implementation-plan.md §6.4: capture real response first.
/// This is a shape template — verify against actual InstiKit JSON.
@freezed
class LoginResponse with _$LoginResponse {
  const factory LoginResponse({
    @JsonKey(name: 'token') String? token,
    @JsonKey(name: 'user') UserDto? user,
    @JsonKey(name: 'requires_otp') @Default(false) bool requiresOtp,
    @JsonKey(name: 'requires_2fa') @Default(false) bool requires2fa,
    @JsonKey(name: 'force_change_password') @Default(false) bool forceChangePassword,
    @JsonKey(name: 'maintenance') @Default(false) bool maintenance,
    @JsonKey(name: 'message') String? message,
  }) = _LoginResponse;

  factory LoginResponse.fromJson(Map<String, dynamic> json) => _$LoginResponseFromJson(json);
}

/// User data returned in login/config responses.
@freezed
class UserDto with _$UserDto {
  const factory UserDto({
    @JsonKey(name: 'id') required String id,
    @JsonKey(name: 'uuid') String? uuid,
    @JsonKey(name: 'name') required String name,
    @JsonKey(name: 'email') String? email,
    @JsonKey(name: 'phone') String? phone,
    @JsonKey(name: 'avatar') String? avatar,
    @JsonKey(name: 'role_id') String? roleId,
    @JsonKey(name: 'role_name') String? roleName,
    @JsonKey(name: 'permissions') @Default([]) List<String> permissions,
  }) = _UserDto;

  factory UserDto.fromJson(Map<String, dynamic> json) => _$UserDtoFromJson(json);
}

/// OTP request body for `POST /api/v1/auth/login/otp/request`.
@freezed
class OtpRequest with _$OtpRequest {
  const factory OtpRequest({
    @JsonKey(name: 'username_or_email') required String usernameOrEmail,
  @JsonKey(name: 'password') required String password,
  @JsonKey(name: 'device_name') @Default('flutter_client') String deviceName,
  }) = _OtpRequest;

  factory OtpRequest.fromJson(Map<String, dynamic> json) => _$OtpRequestFromJson(json);
}

/// OTP confirm body for `POST /api/v1/auth/login/otp/confirm`.
@freezed
class OtpConfirmRequest with _$OtpConfirmRequest {
  const factory OtpConfirmRequest({
    @JsonKey(name: 'username_or_email') required String usernameOrEmail,
    @JsonKey(name: 'password') required String password,
    @JsonKey(name: 'otp') required String otp,
    @JsonKey(name: 'device_name') @Default('flutter_client') String deviceName,
  }) = _OtpConfirmRequest;

  factory OtpConfirmRequest.fromJson(Map<String, dynamic> json) => _$OtpConfirmRequestFromJson(json);
}

/// Password reset request body for `POST /api/v1/auth/password/request`.
@freezed
class PasswordResetRequest with _$PasswordResetRequest {
  const factory PasswordResetRequest({
    @JsonKey(name: 'email') required String email,
  }) = _PasswordResetRequest;

  factory PasswordResetRequest.fromJson(Map<String, dynamic> json) => _$PasswordResetRequestFromJson(json);
}

/// Force-change-password request body.
@freezed
class ChangePasswordRequest with _$ChangePasswordRequest {
  const factory ChangePasswordRequest({
    @JsonKey(name: 'current_password') required String currentPassword,
    @JsonKey(name: 'new_password') required String newPassword,
    @JsonKey(name: 'new_password_confirmation') required String newPasswordConfirmation,
  }) = _ChangePasswordRequest;

  factory ChangePasswordRequest.fromJson(Map<String, dynamic> json) => _$ChangePasswordRequestFromJson(json);
}
