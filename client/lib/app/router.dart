import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../core/auth/permission_service.dart';
import '../core/auth/session_provider.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/auth/presentation/no_access_screen.dart';
import 'shell/app_shell.dart';

/// Named routes used across the app.
class AppRoutes {
  static const login = '/login';
  static const home = '/';
  static const noAccess = '/no-access';
  static const dashboard = '/dashboard';
  static const attendance = '/attendance';
  static const students = '/students';
  static const exams = '/exams';
  static const fees = '/fees';
  static const settings = '/settings';
  static const profile = '/profile';
}

/// The go_router configuration with auth + permission guards.
///
/// Per §5.3: unauthenticated → /login; authenticated → /
/// Per §8.3: routes are permission-gated via redirect.
final routerProvider = Provider<GoRouter>((ref) {
  final authStatus = ref.watch(authStatusProvider);

  return GoRouter(
    initialLocation: AppRoutes.home,
    redirect: (context, state) {
      final status = ref.read(authStatusProvider);
      final isLoggedIn = status == AuthStatus.authenticated;
      final isLoginRoute = state.matchedLocation == AppRoutes.login;

      // Not logged in and trying to access a protected route → login
      if (!isLoggedIn && !isLoginRoute) {
        return AppRoutes.login;
      }

      // Logged in and on login route → home
      if (isLoggedIn && isLoginRoute) {
        return AppRoutes.home;
      }

      // No redirect
      return null;
    },
    routes: [
      // Login (no auth required)
      GoRoute(
        path: AppRoutes.login,
        builder: (context, state) => const LoginScreen(),
      ),

      // No-access screen
      GoRoute(
        path: AppRoutes.noAccess,
        builder: (context, state) => const NoAccessScreen(),
      ),

      // Main shell with bottom nav / drawer
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: AppRoutes.home,
            builder: (context, state) => const _PlaceholderScreen(title: 'Dashboard'),
          ),
          GoRoute(
            path: AppRoutes.dashboard,
            builder: (context, state) => const _PlaceholderScreen(title: 'Dashboard'),
          ),
          GoRoute(
            path: AppRoutes.attendance,
            builder: (context, state) => const _PlaceholderScreen(title: 'Attendance'),
          ),
          GoRoute(
            path: AppRoutes.students,
            builder: (context, state) => const _PlaceholderScreen(title: 'Students'),
          ),
          GoRoute(
            path: AppRoutes.exams,
            builder: (context, state) => const _PlaceholderScreen(title: 'Exams'),
          ),
          GoRoute(
            path: AppRoutes.fees,
            builder: (context, state) => const _PlaceholderScreen(title: 'Fees'),
          ),
          GoRoute(
            path: AppRoutes.settings,
            builder: (context, state) => const _PlaceholderScreen(title: 'Settings'),
          ),
          GoRoute(
            path: AppRoutes.profile,
            builder: (context, state) => const _PlaceholderScreen(title: 'Profile'),
          ),
        ],
      ),
    ],
    errorBuilder: (context, state) => const _PlaceholderScreen(title: 'Not Found'),
  );
});

/// Temporary placeholder — will be replaced by real screens in each wave.
class _PlaceholderScreen extends StatelessWidget {
  final String title;
  const _PlaceholderScreen({required this.title});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: Center(child: Text('$title — Coming Soon'),
      ),
    );
  }
}
