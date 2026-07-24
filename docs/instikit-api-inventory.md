# InstiKit → Flutter — Master API & Role Inventory

> Reverse-engineered from **InstiKit School v5.5.0** (Laravel 12, API-first, Sanctum).
> This is the blueprint for building the Flutter client. All figures auto-extracted from the source.

## Headline numbers

| Metric | Value |
|---|---|
| App-module API endpoints | **1,701** — authenticated `/api/v1/app/*` CRUD (the main Flutter surface) |
| Additional endpoints *not* inventoried here | **~452** (exports/reports ~366, guest 44, gateway 16, web/custom ~26) → **~2,153 gross** |
| Route groups (modules) inventoried | **34** |
| Distinct permissions | **644** |
| Roles | **17** |
| Auth | Laravel Sanctum (token) + OTP + 2FA + screen-lock |
| API prefix | `/api/v1/` (auth under `/auth`, app under `/app`) |

## The generated files (this folder)

| File | What it is |
|---|---|
| [instikit-api-inventory.md](instikit-api-inventory.md) | This master doc |
| [instikit-endpoints.csv](instikit-endpoints.csv) | All 1,701 endpoints: module, method, URI, route name, permission |
| [instikit-modules-endpoints.md](instikit-modules-endpoints.md) | Same, grouped & readable per module |
| [instikit-permission-matrix.csv](instikit-permission-matrix.csv) | 644 permissions × 17 roles (1/0 grid) |
| [instikit-role-capabilities.md](instikit-role-capabilities.md) | Per-role list of granted permissions |

---

## 1. The 17 roles (one adaptive app, permission-gated)

`admin` implicitly holds all 644 permissions. Everyone else is a subset returned in `/config` at login — the Flutter UI renders menus/actions from that set.

| Role | Perms | Tier | Primary use in the app |
|---|---|---|---|
| admin | 644 | Leadership | Everything |
| manager | 567 | Leadership | Near-admin operational control |
| principal | 468 | Leadership | Academics + oversight + approvals |
| observer | 104 | Leadership | Read-only across modules |
| staff (teacher) | 80 | Staff | Attendance, marks, homework, timetable, notices |
| accountant | 73 | Staff | Finance, fees, transactions, payroll |
| receptionist | 68 | Staff | Reception, visitors, admission/registration, contacts |
| transport-incharge | 71 | Staff | Transport routes, vehicles, fuel |
| inventory-incharge | 58 | Staff | Stock, items, issue/receive |
| exam-incharge | 38 | Staff | Exams, schedules, marks |
| librarian | 35 | Staff | Library books, members, issue/return |
| mess-incharge | 16 | Staff | Mess/meal management |
| hostel-incharge | 15 | Staff | Hostel rooms & allocation |
| attendance-assistant | 2 | Staff | Mark attendance only |
| student | 29 | End user | Own profile, attendance, results, fees, homework, chat |
| guardian (parent) | 29 | End user | Ward's attendance, fees, results, homework, chat |
| user | 0 | Base | Authenticated fallback, no module access |

**Implication:** build **one** app. Gate every screen and button on the permission strings from `/config`. Do not build separate apps per role.

---

## 2. Modules by size, owner role, and Flutter delivery wave

| Module | Endpoints | Primary roles | Wave |
|---|---|---|---|
| student | 265 | staff, attendance-assistant, admin, student, guardian | **1** |
| academic | 167 | staff, principal, admin | **1** |
| core (dashboard/config/users/notifications) | 132 | all | **1** (Phase 0) |
| communication | 26 | all | **1** |
| calendar | 18 | all | **1** |
| exam | 116 | exam-incharge, staff, student, guardian | **2** |
| resource (homework/lessons/live) | 53 | staff, student, guardian | **2** |
| finance | 115 | accountant, admin (student: view) | **2** |
| chat | 8 | all (Pusher realtime) | **2** |
| discipline | 6 | staff, principal | **2** |
| employee | 200 | admin, manager, principal | **3** |
| transport | 95 | transport-incharge | **3** |
| inventory | 76 | inventory-incharge | **3** |
| reception | 73 | receptionist | **3** |
| library | 39 | librarian | **3** |
| hostel | 30 | hostel-incharge | **3** |
| mess | 18 | mess-incharge | **3** |
| approval | 19 | manager, principal | **3** |
| helpdesk | 21 | all | **3** |
| recruitment | 12 | admin, manager | **3** |
| form | 12 | admin, staff | **3** |
| guardian | 13 | admin, staff | **3** |
| contact | 17 | receptionist, admin | **3** |
| asset / activity / task | 18/15/30 | staff, admin | **3** |
| blog / news / gallery / post / site | 16/16/9/10/31 | admin (CMS) | **3** |
| reports + exports | (cross-module) | leadership, accountant | **4** |
| admin (users, roles, teams, custom_field, device) | ~30 | admin | **4** |

---

## 3. API conventions the Flutter app must implement

1. **Auth flow** — `POST /api/v1/auth/login` → may require OTP (`login/otp/request` + `login/otp/confirm`) and/or 2FA (`security`). Handle `force-change-password`, `screen.lock`, and `under.maintenance` responses. Store the Sanctum token in `flutter_secure_storage`; send `Authorization: Bearer`.
2. **Bootstrap** — after login call `GET /api/v1/config`. It returns the user, permissions, enabled modules, menu, enums, currency, locale, regional settings. **Drive the whole UI from this.** Cache it (offline).
3. **Form data** — every create/edit screen has a sibling `.../pre-requisite` GET returning dropdown options. Build one reusable "form loader" around this pattern.
4. **Permission gating** — check `module:action` strings before rendering nav items, screens, and action buttons.
5. **Lists** — expect pagination + filters/sorting query params (standard Laravel API resource collections). Build a generic paginated-list widget.
6. **Realtime** — chat/notifications broadcast over **Pusher**; use `pusher_channels_flutter`. Push via **FCM** (`firebase_messaging`).

---

## 4. Offline-sync design (server additions + client engine)

InstiKit's API is per-resource CRUD, not a sync feed. On the **Laravel server** (add, reusing existing UUIDs + spatie scoping):

- `POST /api/v1/app/sync/pull` — changes since a cursor (per module, scoped to the user's permissions).
- `POST /api/v1/app/sync/push` — apply a batch of offline mutations from the client outbox.

**Data classes for offline:**

| Class | Modules | Offline behavior |
|---|---|---|
| Reference (pull-only cache) | config, academic, timetable, student/staff directory | Read offline; server overwrites |
| Field data (full offline CRUD) | attendance, exam marks, homework, discipline notes | Create/edit offline → outbox → push |
| Money/admin (online-only) | finance, payroll, approvals, user/role/config | Blocked offline; server-authoritative |

Client (Drift/SQLite): mirror only the synced slice; UUID keys, `updated_at`, tombstones, outbox queue, per-device cursor; conflict = last-write-wins for field data, server-wins for money.

---

## 5. Recommended Flutter stack

Feature-first Clean Architecture · **Riverpod 2** (state+DI) · **freezed + json_serializable** · **Dio + Retrofit** (Sanctum/refresh/offline-queue interceptors) · **Drift (SQLite)** · **go_router** (permission guards) · **flutter_secure_storage** · **gen-l10n / slang** with first-class **Arabic/RTL** · **pusher_channels_flutter** · **firebase_messaging**. Base structure on the [ssoad riverpod clean-arch template](https://github.com/ssoad/flutter_riverpod_clean_architecture), swapping Hive → Drift and hardening RTL.

---

## 6. Delivery waves (role-first)

- **Phase 0 — Foundation:** scaffold, Arabic/RTL, theme; full auth (login+OTP+2FA+screen-lock+force-password+maintenance); `/config` bootstrap + permission gating; Dio/Sanctum; Drift + SyncService core; FCM + Pusher; error/loading system.
- **Wave 1 — MVP (offline-critical):** dashboard, timetable, **student attendance (offline)**, student list/profile, communication/notices, calendar → makes teacher/student/guardian/attendance-assistant useful offline.
- **Wave 2:** exam (offline marks + results), homework/resources, fees (view+record), chat, discipline.
- **Wave 3:** employee/HR/payroll, library, transport, hostel, mess, inventory, reception, admission, approval, helpdesk, recruitment, forms, CMS (blog/news/gallery), asset, activity, contact.
- **Wave 4:** reports & PDF export, admin (users/roles/teams/config/custom fields), biometric lock, deep links, store release.

---

## 7. Caveats

- Figures are **auto-generated** by static parsing of route files; verify a few against the live API (`php artisan route:list`) once hosted.
- The `permission` column in `instikit-endpoints.csv` reflects **route-level middleware only — 492 of 1,701 rows.** The other **1,209 rows are gated by Laravel policies inside controllers** (e.g. `$this->authorize('viewAny', Student::class)`), which a route parser cannot see. **A blank permission does NOT mean the endpoint is unprotected.** For authoritative role→capability data, use `instikit-permission-matrix.csv` (from the ACL), never the endpoints permission column.
- **Reports & exports (~366 endpoints)** live in separate route files (`routes/export.php`, `routes/exports/*.php`, `routes/report.php`) not counted in the 1,701. They power the Wave 4 reporting screens and must be inventoried before that wave.
- This copy is **nulled** — passed a clean tamper scan, but **buy the $59 CodeCanyon license** before any production/school use (legal + updates + support).
- Scale is real: ~2,153 endpoints gross. Full parity is a multi-wave build; the uniform API (config + pre-requisite + Sanctum) lets us template a reusable "module kit" to move fast.
