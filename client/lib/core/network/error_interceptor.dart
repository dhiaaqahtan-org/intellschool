import 'package:dio/dio.dart';
import 'package:logger/logger.dart';

import '../error/failures.dart';

/// Maps DioException → Failure (the single place where HTTP errors become app errors).
///
/// Also detects InstiKit-specific response shapes:
/// - Maintenance mode (503 with `{maintenance: true}`)
/// - 2FA / screen-lock challenges (200/403 with `{security: ...}`)
/// - Force-change-password (200 with `{force_change_password: true}`)
class ErrorInterceptor extends Interceptor {
  final Logger _logger;

  ErrorInterceptor(this._logger);

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final failure = _mapDioError(err);
    _logger.w('Dio error → ${failure.runtimeType}: ${failure.displayMessage}');

    // Attach the Failure to the exception for upstream extraction
    handler.next(
      DioException(
        requestOptions: err.requestOptions,
        type: err.type,
        response: err.response,
        error: failure,
        message: err.message,
        stackTrace: err.stackTrace,
      ),
    );
  }

  @override
  void onResponse(Response response, ResponseHandler handler) {
    // Intercept 200 responses that carry special instructions
    final data = response.data;
    if (data is Map<String, dynamic>) {
      // Force-change-password flag
      if (data['force_change_password'] == true) {
        handler.reject(
          DioException(
            requestOptions: response.requestOptions,
            response: response,
            error: const ForceChangePasswordFailure(),
            type: DioExceptionType.badResponse,
          ),
        );
        return;
      }

      // Security challenge (2FA, screen-lock)
      if (data['security'] != null) {
        final secData = data['security'] as Map<String, dynamic>;
        handler.reject(
          DioException(
            requestOptions: response.requestOptions,
            response: response,
            error: SecurityChallengeFailure(
              type: secData['type'] as String? ?? 'unknown',
              data: secData,
            ),
            type: DioExceptionType.badResponse,
          ),
        );
        return;
      }

      // Maintenance mode
      if (data['maintenance'] == true || response.statusCode == 503) {
        handler.reject(
          DioException(
            requestOptions: response.requestOptions,
            response: response,
            error: MaintenanceFailure(
              message: data['message'] as String?,
            ),
            type: DioExceptionType.badResponse,
          ),
        );
        return;
      }
    }

    handler.next(response);
  }

  /// The central mapping function.
  Failure _mapDioError(DioException err) {
    // If a Failure was already attached (by onResponse above), use it.
    if (err.error is Failure) {
      return err.error as Failure;
    }

    switch (err.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return const Failure.network(message: 'Connection timed out.');

      case DioExceptionType.connectionError:
        return const Failure.network(message: 'Cannot connect to the server.');

      case DioExceptionType.badResponse:
        return _mapStatusCode(err);

      case DioExceptionType.cancel:
        return const Failure.unknown(message: 'Request was cancelled.');

      case DioExceptionType.unknown:
        if (err.error.toString().contains('SocketException')) {
          return const Failure.network();
        }
        return Failure.unknown(message: err.message);
    }
  }

  Failure _mapStatusCode(DioException err) {
    final statusCode = err.response?.statusCode ?? 0;
    final data = err.response?.data;

    String? message;
    Map<String, List<String>> fieldErrors = {};

    if (data is Map<String, dynamic>) {
      message = data['message'] as String?;

      // Laravel validation format: { "message": "...", "errors": { "field": ["msg"] } }
      final errors = data['errors'];
      if (errors is Map<String, dynamic>) {
        fieldErrors = errors.map((key, value) {
          final list = (value as List).map((e) => e.toString()).toList();
          return MapEntry(key, list);
        });
      }
    }

    return switch (statusCode) {
      401 => Failure.auth(message: message),
      403 => Failure.forbidden(message: message),
      404 => Failure.notFound(message: message),
      422 => Failure.validation(message: message, fieldErrors: fieldErrors),
      503 => MaintenanceFailure(message: message),
      >= 500 => Failure.server(statusCode: statusCode, message: message),
      _ => Failure.unknown(message: message, statusCode: statusCode),
    };
  }
}

/// Extension to extract a Failure from a DioException.
extension FailureExtraction on DioException {
  /// Returns the Failure if one was attached by ErrorInterceptor,
  /// otherwise maps the error to a generic Failure.
  Failure toFailure() {
    if (error is Failure) return error as Failure;
    return Failure.unknown(message: message);
  }
}
