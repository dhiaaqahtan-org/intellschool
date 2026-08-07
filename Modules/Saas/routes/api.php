<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Api\AccountLookupController;
use Modules\Saas\Http\Controllers\Api\EntitlementController;
use Modules\Saas\Http\Controllers\Api\PlatformPlanController;
use Modules\Saas\Http\Controllers\Api\PlatformSubscriptionController;
use Modules\Saas\Http\Controllers\Api\PlatformTenantController;
use Modules\Saas\Http\Controllers\Api\SubscriptionController;
use Modules\Saas\Http\Controllers\Api\TenantApiController;

/*
|--------------------------------------------------------------------------
| SaaS API Routes
|--------------------------------------------------------------------------
| These routes provide JSON API endpoints for:
|  - The Flutter/mobile client (tenant discovery, entitlements, subscription)
|  - Platform integrations (tenant management, billing webhooks)
|
| Authentication:
|  - Tenant endpoints use Sanctum tokens issued within tenant context
|  - Platform endpoints use the 'platform' guard
|
| These routes are registered on ALL hosts but enforce their own
| host/tenant requirements through middleware.
*/

Route::prefix('api/saas')
    ->name('saas.api.')
    ->group(function () {

        /*
        |----------------------------------------------------------------------
        | Public endpoints (no authentication required)
        |----------------------------------------------------------------------
        */

        // Tenant discovery: validate a slug/subdomain and get basic info.
        Route::get('/discover/{slug}', [TenantApiController::class, 'discover'])
            ->middleware(['api', 'saas.landlord-host', 'throttle:30,1'])
            ->where('slug', '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?')
            ->name('discover');

        // Public plan listing for pricing pages.
        Route::get('/plans', [SubscriptionController::class, 'publicPlans'])
            ->middleware(['api', 'saas.landlord-host', 'throttle:60,1'])
            ->name('plans');

        /*
        |----------------------------------------------------------------------
        | Tenant endpoints (Sanctum auth, tenant context required)
        |----------------------------------------------------------------------
        */

        Route::middleware(['api', 'auth:sanctum', 'saas.tenant-host', 'saas.tenant-active'])
            ->group(function () {

                // Current tenant info and configuration.
                Route::get('/tenant', [TenantApiController::class, 'show'])
                    ->name('tenant.show');

                // Entitlement snapshot for the mobile client.
                Route::get('/tenant/entitlements', [EntitlementController::class, 'index'])
                    ->name('tenant.entitlements');

                // Check a specific feature entitlement.
                Route::get('/tenant/entitlements/{featureCode}', [EntitlementController::class, 'check'])
                    ->where('featureCode', '[a-z0-9][a-z0-9._-]{0,79}')
                    ->name('tenant.entitlements.check');

                // Current subscription status.
                Route::get('/tenant/subscription', [SubscriptionController::class, 'show'])
                    ->name('tenant.subscription');

                // Usage statistics.
                Route::get('/tenant/usage', [TenantApiController::class, 'usage'])
                    ->name('tenant.usage');
            });

        /*
        |----------------------------------------------------------------------
        | Platform endpoints (platform guard, landlord host)
        |----------------------------------------------------------------------
        */

        Route::prefix('platform')
            ->middleware(['web', 'auth:platform', 'saas.landlord-host'])
            ->name('platform.')
            ->group(function () {

                // Tenant management.
                Route::get('/tenants', [PlatformTenantController::class, 'index'])
                    ->name('tenants.index');

                Route::get('/tenants/{tenant:uuid}', [PlatformTenantController::class, 'show'])
                    ->name('tenants.show');

                Route::get('/tenants/{tenant:uuid}/provisioning', [PlatformTenantController::class, 'provisioning'])
                    ->name('tenants.provisioning');

                Route::post('/tenants', [PlatformTenantController::class, 'store'])
                    ->name('tenants.store');

                Route::patch('/tenants/{tenant:uuid}/status', [PlatformTenantController::class, 'updateStatus'])
                    ->name('tenants.status');

                // Subscription management.
                Route::get('/subscriptions', [PlatformSubscriptionController::class, 'index'])
                    ->name('subscriptions.index');

                Route::get('/subscriptions/{subscription:uuid}', [PlatformSubscriptionController::class, 'show'])
                    ->name('subscriptions.show');

                // Plan management.
                Route::get('/plans', [PlatformPlanController::class, 'index'])
                    ->name('plans.index');

                Route::post('/plans', [PlatformPlanController::class, 'store'])
                    ->name('plans.store');
            });
    });

/*
|--------------------------------------------------------------------------
| Control-plane auth: pre-login account discovery
|--------------------------------------------------------------------------
| Lives at /api/v1/auth/lookup (NOT under the api/saas prefix) so the Flutter
| client's control-plane Dio — baseUrl https://app.<domain>/api/v1 — reaches it
| as `/auth/lookup`. Landlord host only; the tenant ERP owns /api/v1/auth/* on
| its own subdomains. Unauthenticated by design (it precedes any tenant token)
| and rate limited against enumeration.
*/
Route::prefix('api/v1/auth')
    ->middleware(['api', 'saas.landlord-host', 'throttle:10,1'])
    ->name('saas.auth.')
    ->group(function () {
        Route::post('/lookup', AccountLookupController::class)->name('lookup');
    });
