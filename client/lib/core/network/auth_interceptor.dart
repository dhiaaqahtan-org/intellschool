import 'package:dio/dio.dart';

import '../auth/token_store.dart';

/// Injects `Authorization: Bearer <token>` and `Accept-Language` headers.
class AuthInterceptor extends Interceptor {
  final TokenStore _tokenStore;

  AuthInterceptor(this._tokenStore);

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    final token = await _tokenStore.getToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    // Accept-Language from stored locale (default 'ar' since Arabic-first)
    // TODO: read from locale provider once wired
    options.headers['Accept-Language'] = 'ar';

    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    // On 401: clear session (InstiKit uses opaque Sanctum tokens, no refresh).
    if (err.response?.statusCode == 401) {
      await _tokenStore.clearAll();
    }
    handler.next(err);
  }
}
