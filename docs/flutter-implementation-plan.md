# Flutter Implementation Plan — InstiKit School ERP Client

> A deterministic, implementation-ready build spec. An AI coding agent (or developer)
> executes it **top to bottom, phase by phase**. Each task states files to create and a
> **Done-when** check. Do not skip ahead: a phase starts only when the previous phase's
> Done-when checks all pass.

---

## 0. Agent operating rules (read first, obey always)

1. **Work in order.** Phases are sequential. Within a phase, tasks are ordered.
2. **Never invent API response shapes.** InstiKit's exact JSON is authoritative. For any
   endpoint you consume, first **capture the real response** (see §6.4), then generate the
   model from it. Treat every data contract in this doc as *shape guidance to verify*, not truth.
3. **Two separate repos.** `server/` = the InstiKit Laravel app (you only ADD sync endpoints).
   `client/` = the new Flutter app (everything else). Never edit InstiKit business logic.
4. **Every screen is permission-gated and RTL-correct.** No exceptions (§4.6, §8).
5. **Follow the conventions in §4 exactly** so all generated code is uniform.
6. **Verify by running** (§14), not by reading code. A task is Done only when its check is observed.
7. **Idempotent edits.** Re-running a task must not duplicate code. Check before you write.
8. **Ask nothing; if blocked, record the blocker in `docs/BLOCKERS.md` and continue** with the
   next independent task.

## 1. System context (source of truth)

| Fact | Value |
|---|---|
| Backend | InstiKit School v5.5.0 — Laravel 12, API-first, MySQL |
| API base | `https://<host>/api/v1` |
| Auth | Laravel Sanctum bearer token; flow includes OTP, 2FA, screen-lock, force-password, maintenance |
| Bootstrap | `GET /api/v1/config` → user, permissions, menu, enums, currency, locale, enabled modules |
| Forms | every create/edit has `.../pre-requisite` GET returning dropdown data |
| AuthZ | spatie permissions `module:action`; **17 roles**; gate UI from `/config` |
| Realtime | Pusher (chat/notifications); Push = FCM |
| Endpoint inventory | `docs/instikit-endpoints.csv`, `docs/instikit-modules-endpoints.md` (1,701 app endpoints) |
| Role matrix | `docs/instikit-permission-matrix.csv`, `docs/instikit-role-capabilities.md` |
| Caveats | endpoint `permission` column = route-middleware only; reports/exports (~366) not in the 1,701 |

**Targets:** Android (primary) + Windows desktop. iOS optional/later. Arabic (RTL) is a
first-class locale from day one.

## 2. Tech stack (add via `dart pub add`, which pins current stable)

| Concern | Package |
|---|---|
| State + DI | `flutter_riverpod`, `riverpod_annotation`, `riverpod_generator` |
| Models/immutability | `freezed`, `freezed_annotation`, `json_serializable`, `json_annotation` |
| Networking | `dio`, `retrofit`, `retrofit_generator` |
| Local DB | `drift`, `drift_flutter`, `sqlite3_flutter_libs` |
| Routing | `go_router` |
| Secure storage | `flutter_secure_storage` |
| Key-value | `shared_preferences` |
| i18n | Flutter `gen-l10n` (`flutter_localizations`, `intl`) — ARB files |
| Connectivity | `connectivity_plus` |
| Realtime | `pusher_channels_flutter` |
| Push | `firebase_core`, `firebase_messaging` |
| Errors (functional) | `fpdart` (or a hand-rolled `Result<T>`) |
| Utils | `logger`, `equatable` (if not using freezed for a type) |
| Dev | `build_runner`, `custom_lint`, `riverpod_lint` |

## 3. Bootstrap the project (Phase 0.0)

```bash
# from D:\project 2026\school 2
flutter create --org com.<yourorg> --platforms=android,windows client
cd client
dart pub add flutter_riverpod riverpod_annotation dio retrofit go_router \
  drift drift_flutter sqlite3_flutter_libs flutter_secure_storage shared_preferences \
  connectivity_plus fpdart intl pusher_channels_flutter firebase_core firebase_messaging logger
dart pub add dev:build_runner dev:riverpod_generator dev:freezed dev:json_serializable \
  dev:retrofit_generator dev:custom_lint dev:riverpod_lint dev:drift_dev
flutter gen-l10n   # after l10n.yaml + ARB files exist (§5.2)
dart run build_runner build --delete-conflicting-outputs
```

**Done-when:** `flutter run -d windows` shows the default counter app; `build_runner` completes with no errors.

## 4. Architecture & conventions (enforced)

### 4.1 Feature-first clean architecture — folder tree
```
client/lib/
├── main.dart
├── app/                     # App widget, router, theme, localization wiring
│   ├── app.dart
│   ├── router.dart          # go_router + permission guards
│   ├── theme/
│   └── l10n/                # generated
├── core/
│   ├── network/             # Dio, interceptors, api result, error mapping
│   ├── db/                  # Drift database, DAOs, base tables
│   ├── sync/                # SyncService, outbox, conflict policy
│   ├── auth/                # token store, session, permission service
│   ├── config/              # /config model + provider (bootstrap)
│   ├── error/               # Failure types, Result
│   ├── widgets/             # shared UI (paginated list, form scaffold, gates)
│   └── utils/
└── features/
    └── <feature>/           # e.g. attendance, students, exams, fees...
        ├── data/            # dtos (freezed), api client (retrofit), repository impl, drift table
        ├── domain/          # entities, repository interface, usecases (thin)
        └── presentation/    # riverpod controllers + screens + widgets
```

### 4.2 Layer rules
- **presentation** depends on **domain** only. **data** implements **domain** interfaces.
- No Dio/Drift types leak into presentation. No Flutter imports in data/domain.
- One repository interface per feature in `domain/`, one impl in `data/`.

### 4.3 Naming
- Files `snake_case`; classes `PascalCase`; providers `camelCaseProvider`.
- DTO ⇢ `XxxDto` (data), Entity ⇢ `Xxx` (domain), Drift table ⇢ `XxxTable`.

### 4.4 Errors
- All repo methods return `Future<Result<T>>` where `Result = Either<Failure, T>` (fpdart).
- `Failure` union: `NetworkFailure`, `AuthFailure`, `ValidationFailure(fields)`, `ServerFailure(code,msg)`, `OfflineFailure`, `UnknownFailure`.
- Map Dio errors → `Failure` in one place (§6.3). Presentation renders `Failure` via a shared error widget.

### 4.5 State
- Riverpod **codegen** (`@riverpod`). Async UI state via `AsyncNotifier`/`AsyncValue`.
- No business logic in widgets; controllers orchestrate usecases/repositories.

### 4.6 Non-negotiable guardrails
- **Permission gate every route and action** (§8.3). Blank permission in the CSV ≠ open — confirm via `/config` and controller policies.
- **RTL:** never hardcode `EdgeInsets.only(left:)`; use `.directional`. Test every screen in Arabic.
- **Offline:** obey the data classification (§10.2). Money/admin screens are online-only.
- **No secrets in code.** Base URL via env (§5.1). Token only in `flutter_secure_storage`.

## 5. Cross-cutting foundations (Phase 0.1)

### 5.1 Environment/config
- `--dart-define` flavors: `API_BASE_URL`, `PUSHER_KEY`, `PUSHER_CLUSTER`.
- `core/config/env.dart` reads them. **Done-when:** app logs the resolved base URL at startup.

### 5.2 Localization + RTL
- `l10n.yaml`; `lib/app/l10n/app_en.arb`, `app_ar.arb`.
- `MaterialApp.router(localizationsDelegates, supportedLocales: [Locale('en'), Locale('ar')], locale: from settings)`.
- Locale-driven `TextDirection`. **Done-when:** toggling to `ar` flips the whole UI to RTL and shows Arabic strings.

### 5.3 Theme + router skeleton
- Light/dark `ThemeData`. `go_router` with a `redirect` hook wired to auth+permission (fleshed out in §7–§8).
- **Done-when:** unauthenticated launch lands on `/login`; authenticated lands on `/`.

## 6. Networking layer (Phase 0.2)

### 6.1 Dio
- Single `Dio` in `core/network/dio_provider.dart`; `baseUrl = env.apiBaseUrl`, JSON headers, timeouts.

### 6.2 Interceptors (order matters)
1. **AuthInterceptor** — inject `Authorization: Bearer <token>` from secure storage; add `Accept-Language` from locale.
2. **RefreshInterceptor** — on 401: clear session, route to `/login` (InstiKit uses opaque Sanctum tokens; there is no refresh token — treat 401 as logout).
3. **ErrorInterceptor** — map to `Failure` (§4.4). Detect `under.maintenance` and 2FA/screen-lock responses and surface typed states.
4. **OfflineQueueInterceptor** — if offline and request is a queued mutation, hand to Outbox (§10.3) instead of failing.

### 6.3 Error mapping
- Centralize Dio→Failure. Parse Laravel validation `{message, errors:{field:[...]}}` into `ValidationFailure`.

### 6.4 Contract-capture procedure (do this before modeling any endpoint)
1. Log in against the live/dev InstiKit with a seeded user.
2. Call the endpoint; save the raw JSON to `client/tool/contracts/<name>.json`.
3. Generate the freezed DTO from that JSON. Keep the sample as a fixture for tests.
- **Done-when:** `tool/contracts/config.json` and `login.json` exist and DTOs parse them in a test.

## 7. Auth module (Phase 0.3) — full flow

**Endpoints** (`/api/v1/auth/...`, from `routes/auth.php`):
`login`, `login/otp/request`, `login/otp/confirm`, `password/request`, `password/confirm`,
`password/reset`, `register`, `register/email`, `register/verify`, `logout`, `security` (2FA).
Plus session middlewares to handle: **force-change-password**, **screen-lock**, **under-maintenance**.

**Flow (implement as a state machine):**
```
login(credentials)
  ├─ 200 + token .......................→ session established → §8 bootstrap
  ├─ requires OTP ......................→ otp/request → otp/confirm → token
  ├─ requires 2FA ......................→ security challenge → token
  ├─ force_change_password .............→ change-password screen → retry
  ├─ maintenance .......................→ maintenance screen (blocking)
  └─ 422/401 ...........................→ ValidationFailure/AuthFailure on form
screen-lock: on resume after timeout → lock screen (PIN/biometric) before app content
logout → clear token + Drift session tables → /login
```

**Files:** `features/auth/{data,domain,presentation}` + `core/auth/token_store.dart` (secure storage) + `core/auth/session_provider.dart`.

**Done-when:** with a real InstiKit dev server you can log in (incl. an OTP user and a
force-password user), token is persisted, app relaunch stays logged in, logout clears it.

## 8. Config bootstrap + permission gating (Phase 0.4)

1. After auth, `GET /api/v1/config`; store in Drift (`config` table) + expose `configProvider`.
2. `PermissionService.can('module:action')` reads the permission set from config.
3. `PermissionGate` widget + `go_router` `redirect` deny routes the user lacks.
4. Build the **nav/menu from config** (not hardcoded), filtered by permissions.
- **Done-when:** logging in as `student` vs `accountant` shows different menus; navigating to a
  denied route redirects to a "no access" screen. (Cross-check against `instikit-role-capabilities.md`.)

## 9. Local database — Drift (Phase 0.5)

- `core/db/app_database.dart`. Every syncable table includes:
  `uuid TEXT PK`, `updatedAt DATETIME`, `deletedAt DATETIME NULL`, `syncStatus TEXT` (`synced|dirty|pending`).
- `outbox` table: `id, entity, op(create|update|delete), uuid, payload(JSON), createdAt, tries, lastError`.
- `sync_cursor` table: `entity TEXT PK, cursor TEXT` (ISO-8601 or server token).
- Migrations: bump `schemaVersion`; write `MigrationStrategy`. Never destructive-drop on upgrade.
- **Done-when:** DB opens on Android + Windows; a smoke test inserts/reads a row on both.

## 10. Offline sync engine (Phase 0.6) — the core of the whole app

### 10.1 Server additions (in `server/`, Laravel — the ONLY server work)
Add two authenticated endpoints under `/api/v1/app` (Sanctum + `user.config`):
- `POST sync/pull`
  - **Request:** `{ "cursors": { "<entity>": "<iso8601|token>" }, "entities": ["attendance", ...] }`
  - **Response:** `{ "changes": { "<entity>": [ {record...} ] }, "deletions": { "<entity>": ["uuid"] }, "cursors": { "<entity>": "<new>" } }`
  - Scope every query by the user's spatie permissions + team. Include `deleted_at` rows as deletions (tombstones).
- `POST sync/push`
  - **Request:** `{ "mutations": [ { "uuid","entity","op","payload","updatedAt" } ] }`
  - **Response:** `{ "results": [ { "uuid","status":"applied|conflict|rejected","server": {record} } ] }`
  - Enforce authorization per mutation (reuse existing policies). Money/admin entities → reject if attempted.
- Add `uuid`/`updated_at`/`deleted_at` columns to synced tables that lack them (most already have `uuid`).
- **Done-when:** `curl` pull returns scoped data for a seeded teacher; push of an offline attendance row persists it.

### 10.2 Data classification (client must honor)
| Class | Entities | Offline behavior | Conflict |
|---|---|---|---|
| Reference | config, academic structure, timetable, student/staff directory | pull-only cache | server overwrites |
| Field | attendance, exam marks, homework, discipline notes | full offline CRUD → outbox | last-write-wins by `updatedAt` |
| Money/admin | finance, payroll, approvals, users/roles/config | ONLINE ONLY | server-authoritative (no offline write) |

### 10.3 Client `SyncService` (`core/sync/`)
- `pull()` → call `sync/pull` with stored cursors → upsert into Drift → advance cursors.
- `push()` → drain `outbox` FIFO → `sync/push` → on `applied` mark synced, on `conflict` apply policy, on `rejected` surface + drop.
- **Triggers:** on login, on connectivity-regained (`connectivity_plus`), every N minutes (timer), and manual pull-to-refresh.
- Writes while offline: repository writes to Drift + enqueues an outbox row (never blocks UI).
- **Done-when (acceptance test):** airplane-mode → mark attendance for a class → it persists and shows locally → re-enable network → within one sync cycle the server reflects it; editing the same record on two devices resolves per the policy.

## 11. The "module kit" (reusable pattern — build once, reuse for every module)

Each feature module is implemented by filling this template (create a generator script or checklist):
1. **DTO(s)** (freezed) generated from captured contract (§6.4).
2. **Retrofit API client** for its endpoints (from `instikit-modules-endpoints.md`).
3. **Drift table** (only if the module is Reference/Field class).
4. **Repository** interface (domain) + impl (data) returning `Result<T>`; offline-aware for Field class.
5. **Riverpod controllers**: `listController` (paginated+filter), `detailController`, `formController` (loads `pre-requisite`).
6. **Screens** from shared widgets: `PaginatedListScreen`, `DetailScreen`, `FormScreen` — all wrapped in `PermissionGate`.
7. **Routes** added to `go_router` with permission guards.
8. **Strings** added to both ARB files (en + ar).
- **Done-when:** the module's list/detail/create/edit works against the live API, is permission-gated, and renders correctly in Arabic.

Shared widgets to build first (in `core/widgets/`): `PaginatedListView`, `FormScaffold` (drives
`pre-requisite`), `PermissionGate`, `AsyncValueView` (loading/error/empty), `OfflineBanner`.

## 12. Delivery waves (module task list)

> For each module: consult `instikit-modules-endpoints.md` for endpoints and
> `instikit-role-capabilities.md` for which roles get it. Apply the §11 kit.

**Phase 0 — Foundations** (§3–§10): project, conventions, i18n/RTL, networking, auth,
config+permissions, Drift, SyncService, shared widgets. **Gate:** all Phase-0 Done-when pass.

**Wave 1 — MVP (offline-critical):**
- `communication` (notices), `calendar` (events) — Reference/pull.
- `academic` (timetable/subjects view) — Reference.
- `student` subset: list/profile (Reference) + **attendance (Field, offline)**.
- `dashboard` (from `/config` + core stats).
- Roles unlocked: staff, attendance-assistant, student, guardian.

**Wave 2:**
- `exam` (offline marks entry + results view — Field), `resource` (homework/lessons),
  `finance` (fees: view + record payment — Money=online), `chat` (Pusher), `discipline`.

**Wave 3 (breadth):**
- `employee`(HR/payroll), `library`, `transport`, `hostel`, `mess`, `inventory`,
  `reception`(visitor/gate-pass), `admission/registration`, `approval`, `helpdesk`,
  `recruitment`, `form`, `guardian`, `contact`, `asset`, `activity`, CMS (`blog`,`news`,`gallery`,`post`,`site`).

**Wave 4 (admin + reports):**
- Reports & exports (inventory the `routes/export*.php` first — see §1 caveat),
  admin (`user`/roles/teams/`custom_field`/config), biometric app-lock, deep links, store release.

## 13. Realtime + push (during Wave 2)
- **Pusher:** subscribe to the user's channels for chat + notifications; auth endpoint via the API.
- **FCM:** `firebase_messaging` on Android; register the device token with the InstiKit device endpoint
  (`routes/modules/device.php`); handle foreground/background/tapped notifications.
- **Done-when:** a server-sent notification appears on the device; a chat message arrives live.

## 14. Verification per phase (how to prove Done)
- **Run the app on Windows and an Android emulator**, drive the exact flow the phase added, capture a screenshot/log.
- For sync: exercise the airplane-mode acceptance test (§10.3).
- Do **not** substitute unit tests for running the app; tests are additional, not the proof.
- Record evidence under `client/docs/verification/<phase>.md`.

## 15. Build & release
- Android: `flutter build appbundle --dart-define=...` → Play Console.
- Windows: `flutter build windows` → package installer (MSIX optional).
- Keep `--dart-define` flavors for dev/staging/prod. iOS later (needs Mac/Codemagic).

## 16. Definition of Done (global)
- One codebase → Android + Windows.
- One adaptive, permission-gated UI serving all 17 roles from `/config`.
- Field-class modules work fully offline and sync bidirectionally; money/admin online-only.
- Full Arabic/RTL parity with English.
- Each shipped module passes its §11 Done-when + a §14 verification record.

## 17. Do-not list
- Do not hardcode menus, roles, or permissions — derive from `/config`.
- Do not put money/admin writes offline.
- Do not fabricate API models — capture first (§6.4).
- Do not edit InstiKit business logic; only add the `sync/*` endpoints + sync columns.
- Do not ship a screen untested in Arabic.
- Do not store the token anywhere but secure storage.

---
### Reference files (already in this repo)
- `docs/instikit-api-inventory.md` — master overview
- `docs/instikit-modules-endpoints.md` — all endpoints per module
- `docs/instikit-endpoints.csv` — endpoints (spreadsheet)
- `docs/instikit-permission-matrix.csv` — 644 permissions × 17 roles
- `docs/instikit-role-capabilities.md` — per-role capabilities
