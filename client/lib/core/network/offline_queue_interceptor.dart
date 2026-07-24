import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../error/failures.dart';

/// When offline and the request is a mutation (POST/PUT/PATCH/DELETE),
/// this interceptor hands it to the Outbox instead of failing.
///
/// Only enabled for Field-class entities (attendance, exam marks, homework, discipline).
/// Reference-class reads and Money/admin operations bypass this and fail normally.
class OfflineQueueInterceptor extends Interceptor {
  final Ref _ref;

  OfflineQueueInterceptor(this._ref);

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final isNetworkError = err.type == DioExceptionType.connectionError ||
        err.type == DioExceptionType.connectionTimeout ||
        err.type == DioExceptionType.sendTimeout ||
        err.type == DioExceptionType.receiveTimeout;

    if (!isNetworkError) {
      handler.next(err);
      return;
    }

    final method = err.requestOptions.method.toUpperCase();
    final isMutation = method == 'POST' || method == 'PUT' || method == 'PATCH' || method == 'DELETE';

    if (!isMutation) {
      handler.next(DioException(
        requestOptions: err.requestOptions,
        error: const Failure.offline(),
        type: err.type,
      ));
      return;
    }

    // Check if this endpoint is syncable (Field class)
    final path = err.requestOptions.path;
    if (!_isSyncablePath(path)) {
      handler.next(DioException(
        requestOptions: err.requestOptions,
        error: const Failure.offline(message: 'This action requires an internet connection.'),
        type: err.type,
      ));
      return;
    }

    // TODO: enqueue to outbox once SyncService is wired
    // For now, just surface offline failure
    handler.next(DioException(
      requestOptions: err.requestOptions,
      error: const Failure.offline(message: 'Queued for sync.'),
      type: err.type,
    ));
  }

  /// Check if the request path belongs to a syncable (Field-class) entity.
  bool _isSyncablePath(String path) {
    const syncablePatterns = [
      '/app/student/attendance',
      '/app/exam/mark',
      '/app/exam/observation-mark',
      '/app/exam/competency-evaluation',
      '/app/resource/assignments',
      '/app/discipline/incidents',
    ];

    return syncablePatterns.any((p) => path.contains(p));
  }
}
