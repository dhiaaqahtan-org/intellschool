import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../auth/token_store.dart';
import '../config/env.dart';
import 'auth_interceptor.dart';
import 'error_interceptor.dart';
import 'offline_queue_interceptor.dart';

/// Provides the singleton Dio instance with all interceptors configured.
///
/// Interceptor order (matters):
/// 1. AuthInterceptor — inject Bearer token + Accept-Language
/// 2. ErrorInterceptor — map DioException → Failure, detect maintenance/2FA/lock
/// 3. OfflineQueueInterceptor — queue mutations when offline
final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(BaseOptions(
    baseUrl: Env.apiBaseUrl,
    connectTimeout: const Duration(seconds: 15),
    receiveTimeout: const Duration(seconds: 30),
    sendTimeout: const Duration(seconds: 30),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    responseType: ResponseType.json,
  ));

  // Add interceptors in order
  dio.interceptors.addAll([
    AuthInterceptor(ref.read(tokenStoreProvider)),
    ErrorInterceptor(ref.read(loggerProvider)),
    OfflineQueueInterceptor(ref),
  ]);

  if (Env.isDebug) {
    dio.interceptors.add(LogInterceptor(
      requestHeader: false,
      responseHeader: false,
      requestBody: true,
      responseBody: true,
      error: true,
      logPrint: (obj) => ref.read(loggerProvider).d(obj.toString()),
    ));
  }

  return dio;
});
