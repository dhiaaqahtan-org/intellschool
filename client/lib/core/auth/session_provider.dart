import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../auth/token_store.dart';

/// Enum representing the authentication state of the app.
enum AuthStatus {
  /// Haven't checked yet (splash).
  initial,

  /// No token stored — show login.
  unauthenticated,

  /// Token exists, but special handling required (2FA, OTP, force-change-password, lock).
  challenged,

  /// Fully authenticated — config has been fetched.
  authenticated,

  /// Server is in maintenance mode.
  maintenance,

  /// Screen is locked (biometric/PIN required after timeout).
  locked,
}

/// The current auth status of the app.
final authStatusProvider = StateProvider<AuthStatus>((ref) => AuthStatus.initial);

/// Checks stored token on startup and sets the initial auth status.
final authInitializerProvider = FutureProvider<AuthStatus>((ref) async {
  final tokenStore = ref.read(tokenStoreProvider);
  final hasToken = await tokenStore.hasToken();

  final status = hasToken ? AuthStatus.authenticated : AuthStatus.unauthenticated;
  ref.read(authStatusProvider.notifier).state = status;
  return status;
});
