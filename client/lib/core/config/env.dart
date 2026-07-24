import 'package:flutter/foundation.dart';

/// Environment configuration read from --dart-define values.
///
/// Usage:
///   flutter run --dart-define=API_BASE_URL=https://demo.instikit.com/api/v1 \
///               --dart-define=PUSHER_KEY=xxx --dart-define=PUSHER_CLUSTER=mt1
class Env {
  const Env._();

  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://localhost:8000/api/v1',
  );

  static const String pusherKey = String.fromEnvironment(
    'PUSHER_KEY',
    defaultValue: '',
  );

  static const String pusherCluster = String.fromEnvironment(
    'PUSHER_CLUSTER',
    defaultValue: 'mt1',
  );

  static const bool isDebug = kDebugMode;

  /// Logs the resolved environment at startup (Done-when: app logs base URL).
  static void logEnvironment() {
    debugPrint('══════════════════════════════════════');
    debugPrint('  InstiKit Client Environment');
    debugPrint('  API Base URL : $apiBaseUrl');
    debugPrint('  Pusher Key   : ${pusherKey.isEmpty ? "(not set)" : "***"}');
    debugPrint('  Pusher Cluster: $pusherCluster');
    debugPrint('  Debug Mode   : $isDebug');
    debugPrint('══════════════════════════════════════');
  }
}
