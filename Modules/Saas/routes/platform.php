<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Platform\AuditController;
use Modules\Saas\Http\Controllers\Platform\AuthController;
use Modules\Saas\Http\Controllers\Platform\DashboardController;
use Modules\Saas\Http\Controllers\Platform\EntitlementController;
use Modules\Saas\Http\Controllers\Platform\PlanController;
use Modules\Saas\Http\Controllers\Platform\SubscriptionController;
use Modules\Saas\Http\Controllers\Platform\SupportSessionController;
use Modules\Saas\Http\Controllers\Platform\TenantController;

/*
|--------------------------------------------------------------------------
| Platform administration routes
|--------------------------------------------------------------------------
| Served on the platform host (app.product.example) only. These routes
| manage tenants, domains, provisioning, and platform health.
|
| Authorization: platform guard (saas_platform_users). Platform operators
| have NO implicit tenant data access — they manage the control plane only.
| Support access to tenant data requires an explicit, time-limited,
| approved support session (plan §5.4, §Phase 5).
*/

Route::name('saas.platform.')->prefix('platform')->group(function () {
    // Authentication (public).
    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:platform')
        ->name('logout');

    // Authenticated platform routes.
    Route::middleware('auth:platform')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Tenant CRUD and lifecycle.
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant:uuid}', [TenantController::class, 'show'])->name('tenants.show');
        Route::patch('/tenants/{tenant:uuid}', [TenantController::class, 'update'])->name('tenants.update');

        // Tenant lifecycle actions.
        Route::post('/tenants/{tenant:uuid}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenants/{tenant:uuid}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');
        Route::post('/tenants/{tenant:uuid}/cancel', [TenantController::class, 'cancel'])->name('tenants.cancel');

        // Provisioning.
        Route::post('/tenants/{tenant:uuid}/provision', [TenantController::class, 'provision'])->name('tenants.provision');

        // Domains.
        Route::post('/tenants/{tenant:uuid}/domains', [TenantController::class, 'addDomain'])->name('tenants.domains.store');
        Route::post('/tenants/{tenant:uuid}/domains/{domain}/verify', [TenantController::class, 'verifyDomain'])->name('tenants.domains.verify');
        Route::post('/tenants/{tenant:uuid}/domains/{domain}/primary', [TenantController::class, 'setPrimaryDomain'])->name('tenants.domains.primary');
        Route::delete('/tenants/{tenant:uuid}/domains/{domain}', [TenantController::class, 'removeDomain'])->name('tenants.domains.destroy');

        // Per-tenant entitlement overrides (plan §8). Sit above plan features.
        Route::post('/tenants/{tenant:uuid}/entitlements', [EntitlementController::class, 'store'])
            ->name('tenants.entitlements.store');
        Route::delete('/tenants/{tenant:uuid}/entitlements/{entitlement}', [EntitlementController::class, 'destroy'])
            ->name('tenants.entitlements.destroy');

        // Plans. Versioned and effectively immutable once subscribed to —
        // changes create a new version rather than rewriting a live contract.
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
        Route::post('/plans/{plan}/versions', [PlanController::class, 'newVersion'])->name('plans.versions.store');
        Route::post('/plans/{plan}/features', [PlanController::class, 'updateFeature'])->name('plans.features.update');
        Route::post('/plans/{plan}/toggle-public', [PlanController::class, 'togglePublic'])->name('plans.toggle-public');

        // Subscriptions. Operator actions here are overrides, not billing
        // events — provider state arrives through verified webhooks.
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])
            ->name('subscriptions.status');

        // Support access — the only sanctioned route to tenant data.
        Route::get('/support', [SupportSessionController::class, 'index'])->name('support.index');
        Route::post('/support', [SupportSessionController::class, 'store'])->name('support.store');
        Route::post('/support/{session}/approve', [SupportSessionController::class, 'approve'])->name('support.approve');
        Route::post('/support/{session}/revoke', [SupportSessionController::class, 'revoke'])->name('support.revoke');

        // Audit log. Read-only by construction.
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });
});
