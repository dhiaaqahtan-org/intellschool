<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Platform\AuditController;
use Modules\Saas\Http\Controllers\Platform\AuthController;
use Modules\Saas\Http\Controllers\Platform\DashboardController;
use Modules\Saas\Http\Controllers\Platform\EntitlementController;
use Modules\Saas\Http\Controllers\Platform\ChatConfigController;
use Modules\Saas\Http\Controllers\Platform\FeatureConfigController;
use Modules\Saas\Http\Controllers\Platform\MailConfigController;
use Modules\Saas\Http\Controllers\Platform\ModuleController;
use Modules\Saas\Http\Controllers\Platform\PlanController;
use Modules\Saas\Http\Controllers\Platform\SMSConfigController;
use Modules\Saas\Http\Controllers\Platform\SystemConfigController;
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
        Route::delete('/tenants/{tenant:uuid}', [TenantController::class, 'destroy'])->name('tenants.destroy');

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

        // Tenant module management.
        Route::get('/tenants/{tenant:uuid}/modules', [ModuleController::class, 'showTenantModules'])
            ->name('tenants.modules.index');
        Route::post('/tenants/{tenant:uuid}/modules', [ModuleController::class, 'updateTenantModules'])
            ->name('tenants.modules.update');

        // Global and bulk module management.
        Route::get('/modules', [ModuleController::class, 'index'])
            ->name('modules.index');
        Route::post('/modules/bulk', [ModuleController::class, 'bulkUpdateModules'])
            ->name('modules.bulk');

        // Tenant system configuration.
        Route::get('/tenants/{tenant:uuid}/system-config', [SystemConfigController::class, 'showTenantSystemConfig'])
            ->name('tenants.system-config.index');
        Route::post('/tenants/{tenant:uuid}/system-config', [SystemConfigController::class, 'updateTenantSystemConfig'])
            ->name('tenants.system-config.update');

        // Global and bulk system configuration.
        Route::get('/system-config', [SystemConfigController::class, 'index'])
            ->name('system-config.index');
        Route::post('/system-config/bulk', [SystemConfigController::class, 'bulkUpdateSystemConfig'])
            ->name('system-config.bulk');

        // Tenant mail configuration.
        Route::get('/tenants/{tenant:uuid}/mail-config', [MailConfigController::class, 'showTenantMailConfig'])
            ->name('tenants.mail-config.index');
        Route::post('/tenants/{tenant:uuid}/mail-config', [MailConfigController::class, 'updateTenantMailConfig'])
            ->name('tenants.mail-config.update');
        Route::post('/tenants/{tenant:uuid}/mail-config/test', [MailConfigController::class, 'testTenantMailConfig'])
            ->name('tenants.mail-config.test');

        // Global and bulk mail configuration.
        Route::get('/mail-config', [MailConfigController::class, 'index'])
            ->name('mail-config.index');
        Route::post('/mail-config/bulk', [MailConfigController::class, 'bulkUpdateMailConfig'])
            ->name('mail-config.bulk');

        // Tenant SMS configuration.
        Route::get('/tenants/{tenant:uuid}/sms-config', [SMSConfigController::class, 'showTenantSMSConfig'])
            ->name('tenants.sms-config.index');
        Route::post('/tenants/{tenant:uuid}/sms-config', [SMSConfigController::class, 'updateTenantSMSConfig'])
            ->name('tenants.sms-config.update');
        Route::post('/tenants/{tenant:uuid}/sms-config/test', [SMSConfigController::class, 'testTenantSMSConfig'])
            ->name('tenants.sms-config.test');

        // Global and bulk SMS configuration.
        Route::get('/sms-config', [SMSConfigController::class, 'index'])
            ->name('sms-config.index');
        Route::post('/sms-config/bulk', [SMSConfigController::class, 'bulkUpdateSMSConfig'])
            ->name('sms-config.bulk');

        // Tenant Chat & Pusher configuration.
        Route::get('/tenants/{tenant:uuid}/chat-config', [ChatConfigController::class, 'showTenantChatConfig'])
            ->name('tenants.chat-config.index');
        Route::post('/tenants/{tenant:uuid}/chat-config', [ChatConfigController::class, 'updateTenantChatConfig'])
            ->name('tenants.chat-config.update');
        Route::post('/tenants/{tenant:uuid}/chat-config/test', [ChatConfigController::class, 'testTenantChatConfig'])
            ->name('tenants.chat-config.test');

        // Global and bulk Chat configuration.
        Route::get('/chat-config', [ChatConfigController::class, 'index'])
            ->name('chat-config.index');
        Route::post('/chat-config/bulk', [ChatConfigController::class, 'bulkUpdateChatConfig'])
            ->name('chat-config.bulk');

        // Tenant Feature configuration.
        Route::get('/tenants/{tenant:uuid}/feature-config', [FeatureConfigController::class, 'showTenantFeatureConfig'])
            ->name('tenants.feature-config.index');
        Route::post('/tenants/{tenant:uuid}/feature-config', [FeatureConfigController::class, 'updateTenantFeatureConfig'])
            ->name('tenants.feature-config.update');

        // Global and bulk Feature configuration.
        Route::get('/feature-config', [FeatureConfigController::class, 'index'])
            ->name('feature-config.index');
        Route::post('/feature-config/bulk', [FeatureConfigController::class, 'bulkUpdateFeatureConfig'])
            ->name('feature-config.bulk');

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
