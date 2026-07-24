import '../../../core/config/config_model.dart';
import '../../../core/error/result.dart';
import '../data/auth_dto.dart';

/// Auth repository interface (domain layer).
///
/// Per §4.2: presentation depends on domain only.
/// The data layer implements this interface.
abstract interface class AuthRepository {
  /// Login with username/email + password. Optionally include OTP.
  Future<Result<LoginResponse>> login({
    required String usernameOrEmail,
    required String password,
    String? otp,
  });

  /// Request OTP for login.
  Future<Result<void>> requestOtp({
    required String usernameOrEmail,
    required String password,
  });

  /// Confirm OTP to complete login.
  Future<Result<LoginResponse>> confirmOtp({
    required String usernameOrEmail,
    required String password,
    required String otp,
  });

  /// Logout — clear token + call server logout.
  Future<Result<void>> logout();

  /// Fetch bootstrap config after auth (GET /api/v1/config).
  Future<Result<AppConfig>> fetchConfig();

  /// Force-change-password.
  Future<Result<void>> changePassword({
    required String currentPassword,
    required String newPassword,
  });
}
