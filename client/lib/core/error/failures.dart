import 'package:freezed_annotation/freezed_annotation.dart';

part 'failures.freezed.dart';

/// Union of all failure types the app can encounter.
/// Mapped from Dio exceptions in one place (ErrorInterceptor).
@freezed
sealed class Failure with _$Failure {
  const Failure._();

  /// Network unreachable, timeout, DNS failure, etc.
  const factory Failure.network({String? message}) = NetworkFailure;

  /// 401 — token expired/invalid. Triggers logout + redirect to /login.
  const factory Failure.auth({String? message}) = AuthFailure;

  /// 422 — Laravel validation errors with field-level messages.
  const factory Failure.validation({
    String? message,
    @Default({}) Map<String, List<String>> fieldErrors,
  }) = ValidationFailure;

  /// 403 — user lacks permission for this action.
  const factory Failure.forbidden({String? message}) = ForbiddenFailure;

  /// 404 — resource not found.
  const factory Failure.notFound({String? message}) = NotFoundFailure;

  /// 5xx — server error.
  const factory Failure.server({int? statusCode, String? message}) = ServerFailure;

  /// Server is in maintenance mode (503 with specific body).
  const factory Failure.maintenance({String? message}) = MaintenanceFailure;

  /// 2FA / screen-lock challenge required.
  const factory Failure.securityChallenge({
    required String type,
    Map<String, dynamic>? data,
  }) = SecurityChallengeFailure;

  /// Force-change-password required (200 with specific flag).
  const factory Failure.forceChangePassword() = ForceChangePasswordFailure;

  /// Operation attempted while offline and not queueable.
  const factory Failure.offline({String? message}) = OfflineFailure;

  /// Anything unexpected.
  const factory Failure.unknown({String? message, int? statusCode}) = UnknownFailure;

  /// Human-readable message for display.
  String get displayMessage {
    return switch (this) {
      NetworkFailure(:final message) => message ?? 'Network error. Check your connection.',
      AuthFailure(:final message) => message ?? 'Session expired. Please log in again.',
      ValidationFailure(:final message, :final fieldErrors) =>
        message ?? fieldErrors.values.firstOrNull?.firstOrNull ?? 'Validation error.',
      ForbiddenFailure(:final message) => message ?? 'You do not have permission for this action.',
      NotFoundFailure(:final message) => message ?? 'Resource not found.',
      ServerFailure(:final message) => message ?? 'Server error. Please try again later.',
      MaintenanceFailure(:final message) => message ?? 'System under maintenance.',
      SecurityChallengeFailure(:final type) => 'Security challenge required: $type',
      ForceChangePasswordFailure() => 'Password change required.',
      OfflineFailure(:final message) => message ?? 'You are offline.',
      UnknownFailure(:final message) => message ?? 'An unexpected error occurred.',
    };
  }
}
