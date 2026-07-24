# Blockers Log

> Per flutter-implementation-plan.md §0.8: "Ask nothing; if blocked, record the blocker
> in `docs/BLOCKERS.md` and continue with the next independent task."

---

## Active Blockers

### B-001: MySQL connection not available in WSL
- **Date:** 2026-07-24
- **Phase:** Server (migrations)
- **Description:** WAMP MySQL is not running/accessible from WSL at port 3306. Cannot run
  `php artisan migrate` to apply the sync_cursors and add_sync_columns migrations.
- **Impact:** Migrations are written but not yet applied. They will run on first deploy
  to a server with MySQL running.
- **Workaround:** Migrations are idempotent and safe to run later. Proceeding with
  Flutter client development which does not require the DB to be running.
- **Status:** Open

### B-002: Flutter SDK not installed in WSL
- **Date:** 2026-07-24
- **Phase:** Phase 0.0 (bootstrap)
- **Description:** Flutter SDK is not installed in WSL or on the Windows host.
  Download was attempted but the tarball is very large (~1GB).
- **Impact:** Cannot run `flutter create` or `flutter pub add` to bootstrap.
- **Workaround:** All Flutter client code is being written manually (pubspec.yaml,
  lib/**, etc.) without running the Flutter toolchain. The project will be built
  via Codemagic CI or locally once Flutter SDK is installed.
- **Status:** Accepted — user will install Flutter later or build via Codemagic.

---

## Resolved Blockers

### B-000: sync.php routes not registered (resolved)
- **Date:** 2026-07-24
- **Description:** routes/sync.php existed but was never loaded by RouteServiceProvider,
  making the sync endpoints unreachable.
- **Resolution:** Added `Route::middleware(['api', 'user.config'])->group(base_path('routes/sync.php'))`
  to RouteServiceProvider.php inside the `/api/v1` prefix group. Also added `user.config`
  middleware to sync.php's own middleware stack.
