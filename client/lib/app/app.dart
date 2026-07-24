import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/config/env.dart';
import 'l10n/app_localizations.dart';
import 'router.dart';
import 'theme/app_theme.dart';

/// Locale provider — Arabic-first, but user can switch.
final localeProvider = StateProvider<Locale>((ref) => const Locale('ar'));

/// Theme mode provider.
final themeModeProvider = StateProvider<ThemeMode>((ref) => ThemeMode.system);

/// Root widget for the InstiKit Flutter client.
class InstiKitApp extends ConsumerWidget {
  const InstiKitApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final locale = ref.watch(localeProvider);
    final themeMode = ref.watch(themeModeProvider);
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'InstiKit',
      debugShowCheckedModeBanner: false,

      // Localization
      locale: locale,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('ar'),
        Locale('en'),
      ],

      // RTL: driven by locale (Arabic = RTL, English = LTR)
      builder: (context, child) {
        // Print resolved base URL at startup (Done-when: §5.1)
        return Directionality(
          textDirection: locale.languageCode == 'ar'
              ? TextDirection.rtl
              : TextDirection.ltr,
          child: child!,
        );
      },

      // Theme
      theme: lightTheme,
      darkTheme: darkTheme,
      themeMode: themeMode,

      // Router
      routerConfig: router,
    );
  }
}
