<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Api\EntitlementController;
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
            ->name('discover');

        // Public plan listing for pricing pages.
        Route::get('/plans', [SubscriptionController::class, 'publicPlans'])
            ->name('plans');

        /*
        |----------------------------------------------------------------------
        | Tenant endpoints (Sanctum auth, tenant context required)
        |----------------------------------------------------------------------
        */

        Route::middleware(['auth:sanctum', 'saas.tenant-host', 'saas.tenant-active'])
            ->group(function () {

                // Current tenant info and configuration.
                Route::get('/tenant', [TenantApiController::class, 'show'])
                    ->name('tenant.show');

                // Entitlement snapshot for the mobile client.
                Route::get('/tenant/entitlements', [EntitlementController::class, 'index'])
                    ->name('tenant.entitlements');

                // Check a specific feature entitlement.
                Route::get('/tenant/entitlements/{featureCode}', [EntitlementController::class, 'check'])
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
            ->middleware(['auth:platform', 'saas.landlord-host'])
            ->name('platform.')
            ->group(function () {

                // Tenant management.
                Route::get('/tenants', [TenantApiController::class, 'platformIndex'])
                    ->name('tenants.index');

                Route::get('/tenants/{tenant}', [TenantApiController::class, 'platformShow'])
                    ->name('tenants.show');

                Route::post('/tenants', [TenantApiController::class, 'platformStore'])
                    ->name('tenants.store');

                Route::patch('/tenants/{tenant}/status', [TenantApiController::class, 'platformUpdateStatus'])
                    ->name('tenants.status');

                // Subscription management.
                Route::get('/subscriptions', [SubscriptionController::class, 'platformIndex'])
                    ->name('subscriptions.index');

                Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'platformShow'])
                    ->name('subscriptions.show');

                // Plan management.
                Route::get('/plans', [SubscriptionController::class, 'platformPlans'])
                    ->name('plans.index');

                Route::post('/plans', [SubscriptionController::class, 'platformStorePlan'])
                    ->name('plans.store');
            });
    });
