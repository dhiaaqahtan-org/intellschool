# Modules/Saas

`Modules/Saas` is the landlord control plane and public website for the InstiKit SaaS conversion. It uses `nwidart/laravel-modules`, server-rendered Blade, Vue 3 islands, and module-owned Vite assets. It does not use Livewire at runtime.

This module is a substantial implementation, not a production-launch certificate. The authoritative launch gates remain in `SAAS_MULTITENANT_NWIDART_IMPLEMENTATION_PLAN.md`.

## Verified implementation status — 2026-07-28

| Area | Current repository status |
|---|---|
| Module and build boundary | Implemented with module providers, config, routes, migrations, translations, views, Vue assets, ESLint, Vitest, and Vite |
| Landlord control plane | Tenant/domain/database/owner/platform-user/plan/subscription/entitlement/audit/support/invitation/provisioning schemas and platform UI are implemented |
| Tenant isolation | Host resolution, fail-closed tenant middleware, database switching, cache/storage bootstrappers, queue propagation, and teardown tests are implemented |
| Provisioning and migrations | Idempotent queued provisioning, scheduler fallback, tenant migration runner, migration repository setup, version/health tracking, and failure states are implemented |
| Public website | Home, demo, legal placeholders, signup, signup result, and owner activation are implemented with meaningful Blade fallbacks |
| Localization | `niels-numbers/laravel-localizer` detects browser/mobile language and serves canonical English `/en` and Arabic `/ar` routes with RTL metadata and a manual switcher |
| Demo leads | Consent, landlord persistence, HMAC request metadata, retention/pruning, rate limiting, and optional queued email notification are implemented |
| Signup and owner activation | Fail-closed plan selection, landlord transaction, queued provisioning, tenant-local owner linkage, hashed expiring invitation, queued email, one-time password setup, and replay rejection are implemented |
| Entitlements and billing state | Server-side feature enforcement, versioned plans, verified/idempotent webhook inbox, lifecycle mapping, grace behavior, and scheduled reconciliation are implemented |
| Real subscription charging | Not implemented: `NullBillingGateway` remains bound until a provider, merchant account, tax/currency policy, and checkout contract are approved |
| Legal/commercial launch | Blocked: license provenance, operative legal documents, company identity, brand, approved pricing, and evidence-backed claims are external decisions |
| Production infrastructure | Blocked: secret-manager credential resolver, DNS/TLS, Redis/Horizon policy, object storage, backups/restore drills, monitoring, WAF, and deployment runbooks must be supplied and verified |
| Flutter and integrations | Not complete: tenant discovery, device storage partitioning, sync/outbox isolation, push routing, logout clearing, and two-tenant device tests remain |

Public signup remains unavailable unless all three gates are true:

```dotenv
SAAS_PUBLIC_SIGNUP_ENABLED=true
SAAS_OWNER_INVITATIONS_ENABLED=true
SAAS_CLAIM_PRICING=true
```

Do not enable them while the legal pages are placeholders or pricing/email delivery is unapproved.

## Installation

The repository currently requires the compatible Laravel 12 packages:

```bash
composer require nwidart/laravel-modules:^12.0 niels-numbers/laravel-localizer:^1.4
composer dump-autoload
php artisan module:enable Saas
```

Build the module assets separately from the legacy application bundle:

```bash
cd Modules/Saas
npm install
npm run lint
npm test
npm run build
```

The production manifest is written to `public/build-saas/`.

## Required configuration

Start from the documented keys in the root `.env.example`. At minimum, separate the three host classes and configure a dedicated landlord database:

```dotenv
SAAS_TENANCY_ENABLED=false
SAAS_MARKETING_HOST=www.product.example
SAAS_PLATFORM_HOST=app.product.example
SAAS_TENANT_SUFFIX=.product.example
SAAS_LANDLORD_DB_DATABASE=instikit_landlord
```

`SAAS_TENANCY_ENABLED` must stay false until landlord migrations, tenant credentials, wildcard DNS/TLS, and at least one verified tenant are ready. In production, `EnvTenantCredentialResolver` deliberately refuses to run; bind a real secret-manager-backed implementation of `TenantCredentialResolver`.

Run landlord migrations and workers with explicit operational ownership:

```bash
php artisan migrate --database=landlord --path=Modules/Saas/database/migrations/landlord
php artisan queue:work --queue=provisioning,notifications,default
php artisan schedule:work
```

The scheduler provides provisioning recovery, demo-lead pruning, and billing reconciliation. A queue broker outage does not erase committed provisioning runs; pending runs remain available to the scheduler/operator command.

## English and Arabic routing

The public website uses `niels-numbers/laravel-localizer`:

- supported locales and direction are in `config/localizer.php`;
- translations are in `resources/lang/{en,ar}/marketing.php`;
- the first unprefixed visit uses `Accept-Language` and persists the choice;
- explicit `/en/...` or `/ar/...` URLs override browser preference;
- canonical, `hreflang`, Open Graph locale, `lang`, and `dir` metadata are rendered in Blade;
- ERP/admin/API routes outside `Route::localize()` keep their legacy behavior.

After locale or route changes:

```bash
php artisan route:clear
php artisan route:cache
```

Marketing and platform routes are domain constrained and protected by `RequireLandlordHost`. Tenant ERP/public-site routes require a resolved tenant. The marketing and platform hosts may differ; both are recognized as control-plane hosts, while a tenant host is rejected.

## Verification evidence

The following checks passed on 2026-07-28:

```text
PHP module suite:       236 tests, 761 assertions
Vue component tests:   4 files, 7 tests
PHP syntax:             213 files passed
ESLint:                 0 errors, 0 warnings
Vite production build: passed
Laravel config cache:  passed
Public browser QA:      English/Arabic, LTR/RTL, 375 px reflow, validation/focus passed
Laravel route cache:   passed
PHP syntax/diff check: passed
```

Composer schema validation passes. `composer audit` is not green: the legacy ERP dependency `firebase/php-jwt` has one low-severity advisory (CVE-2025-45769) affecting versions below 7.0; the installed Microsoft Socialite provider constrains it to 6.x. Remediation requires a coordinated provider/JWT major upgrade plus Microsoft-login and Billdesk regression testing.

The full PHP suite was verified with `php -d xdebug.mode=off -d memory_limit=512M vendor/bin/pest Modules/Saas/tests --compact` in this checkout. The memory override is a test-runner allowance for the legacy Laravel route graph, not evidence that production memory should be raised without profiling.

Key regression suites cover two physical tenant databases, host normalization, control-plane separation, queue context cleanup, cache/storage isolation, entitlements, provisioning idempotency, billing webhooks/reconciliation, localized routing, demo retention, signup, and one-time owner activation.

## Production blockers

The repository must not be described as launch-complete until all of these have evidence:

- legitimate license/source ownership and SaaS redistribution rights;
- counsel-approved terms, privacy notice, DPA, subprocessors, retention/deletion language, and consent-version storage;
- approved brand, legal company details, currencies, tax behavior, prices, trials, renewal, cancellation, and refunds;
- a real billing gateway adapter and provider contract tests;
- a production secret-manager credential resolver and database allocation adapter;
- Redis queue/cache/session, object storage, wildcard/custom-domain automation, TLS, monitoring, alerting, backup and restore drills;
- authenticated platform browser tests at narrow/wide sizes in LTR/RTL, keyboard/screen-reader checks, and measured Core Web Vitals;
- the remaining sitemap pages and the complete owner setup wizard;
- remediation and integration regression evidence for the open `firebase/php-jwt` security advisory;
- Flutter tenant partitioning and two-tenant offline/sync/device verification;
- CI/CD security scans, load/noisy-tenant tests, external penetration testing, offboarding drills, and a controlled pilot.

The legal routes intentionally remain `noindex` placeholders. Claim flags in `config/saas.php` must remain false until their supporting evidence exists.

## Main structure (summary)

```text
Modules/Saas/
├── app/
│   ├── Contracts/                 tenant, billing, storage and entitlement ports
│   ├── Http/                      marketing, platform, webhook controllers and middleware
│   ├── Jobs/                      provisioning, demo notification, owner invitation
│   ├── Models/Landlord/           explicit landlord-connection models
│   ├── Providers/                 module, routes and events
│   └── Services/                  tenancy, migrations, billing, support and storage
├── config/saas.php
├── database/migrations/{landlord,tenant}/
├── resources/
│   ├── assets/{css,js}/
│   ├── lang/{en,ar}/
│   └── views/{marketing,platform,mail}/
├── routes/{marketing,platform,webhooks}.php
├── tests/{Feature,Unit,Js}/
└── module.json, composer.json, package.json, vite.config.js
```

The executable Section 4 architecture extends that compact tree with:

- `app/Domain/{Billing,Entitlements,Identity,Provisioning,Support,Tenancy,Usage,Website}`;
- dedicated Marketing, Platform, Tenant, Webhook, and API controllers plus Form Requests and Resources;
- `Models/Concerns`, explicit `Models/Landlord`, and fail-closed `Models/Tenant` boundaries;
- `database/{factories,migrations,seeders}`;
- `resources/views/{marketing,onboarding,platform,billing,mail}` and shared Vue component/service layers;
- `routes/{marketing,platform,tenant,api,webhooks}.php`;
- `tests/{Architecture,Feature,Integration,Unit,Js}`.
