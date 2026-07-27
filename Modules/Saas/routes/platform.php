<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Platform\AuthController;
use Modules\Saas\Http\Controllers\Platform\TenantController;
use Modules\Saas\Http\Controllers\Platform\DashboardController;

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
        Route::post('/login', [AuthController::class, 'login']);
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
        Route::delete('/tenants/{tenant:uuid}/domains/{domain}', [TenantController::class, 'removeDomain'])->name('tenants.domains.destroy');
    });
});
