import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../auth/token_store.dart';
import 'config_model.dart';

/// Provider that holds the bootstrap config (from GET /api/v1/config).
///
/// Set after successful auth by AuthController.
final configProvider = StateProvider<AppConfig?>((ref) => null);

/// Convenience: current user, or null if not bootstrapped.
final currentUserProvider = Provider<UserConfig?>((ref) {
  return ref.watch(configProvider)?.user;
});
