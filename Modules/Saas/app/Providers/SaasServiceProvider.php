<?php

namespace Modules\Saas\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\Saas\Bootstrappers\CacheBootstrapper;
use Modules\Saas\Bootstrappers\FilesystemBootstrapper;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Contracts\TenantConnectionManager;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Contracts\TenantStorage;
use Modules\Saas\Contracts\TenantUrlGenerator;
use Modules\Saas\Domain\Tenancy\TenantContextManager;
use Modules\Saas\Services\DatabaseTenantConnectionManager;
use Modules\Saas\Services\EnvTenantCredentialResolver;
use Modules\Saas\Services\FeatureEntitlementService;
use Modules\Saas\Services\Billing\NullBillingGateway;
use Modules\Saas\Services\Storage\FilesystemTenantStorage;
use Modules\Saas\Services\Support\SupportAccessService;
use Modules\Saas\Services\TenantContextGuard;
use Modules\Saas\Services\TenantMigrationRunner;
use Modules\Saas\Services\Url\DomainTenantUrlGenerator;

class SaasServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Saas';

    protected string $moduleNameLower = 'saas';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/saas.php'), 'saas');

        $this->registerContracts();
        $this->registerBootstrappers();

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerRateLimiters();
        $this->registerMiddlewareAliases();
        $this->registerCommands();

        $this->publishes([
            module_path($this->moduleName, 'config/saas.php') => config_path('saas.php'),
        ], 'saas-config');
    }

    /**
     * Bind module contracts to their implementations.
     *
     * Core ERP code depends on these interfaces, never on concrete classes
     * (plan §3.3). Swapping the credential resolver for a secret-manager
     * backed one, or the billing gateway for a different provider, is a
     * one-line change here rather than a grep across 693 controllers.
     */
    protected function registerContracts(): void
    {
        // The singleton that answers "which tenant am I in?"
        $this->app->singleton(CurrentTenant::class, function (Application $app) {
            return new TenantContextManager(
                $app->make(TenantConnectionManager::class),
                $app->tagged('saas.bootstrappers'),
            );
        });

        // Database connection swap.
        $this->app->singleton(TenantConnectionManager::class, DatabaseTenantConnectionManager::class);

        // Credential resolution. Development uses env vars; production MUST
        // bind a secret-manager-backed resolver instead.
        $this->app->singleton(TenantCredentialResolver::class, EnvTenantCredentialResolver::class);

        // Entitlement checking.
        $this->app->singleton(EntitlementChecker::class, FeatureEntitlementService::class);

        // Billing gateway. Null gateway until a provider is approved (plan §9).
        $this->app->singleton(BillingGateway::class, NullBillingGateway::class);

        // Tenant-aware storage (plan §3.3, §7).
        $this->app->singleton(TenantStorage::class, FilesystemTenantStorage::class);

        // Tenant-aware URL generation (plan §3.3).
        $this->app->singleton(TenantUrlGenerator::class, DomainTenantUrlGenerator::class);

        // Support access management (plan §7, §12).
        $this->app->singleton(SupportAccessService::class);

        // Tenant migration runner (plan §4).
        $this->app->singleton(TenantMigrationRunner::class);

        // Tenant context guard — assertions and drift detection (plan §4).
        $this->app->singleton(TenantContextGuard::class);
    }

    /**
     * Register per-tenant bootstrappers. Order matters: cache first so that
     * subsequent bootstrappers (which may cache) already have the tenant
     * prefix active.
     */
    protected function registerBootstrappers(): void
    {
        $this->app->tag([CacheBootstrapper::class], 'saas.bootstrappers');
        $this->app->tag([FilesystemBootstrapper::class], 'saas.bootstrappers');
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);

            return;
        }

        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
        $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'resources/lang'));
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        // Published overrides win; the module's own views are the fallback.
        $this->loadViewsFrom([$viewPath, $sourcePath], $this->moduleNameLower);
    }

    protected function registerMiddlewareAliases(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware(
            'saas.landlord-host',
            \Modules\Saas\Http\Middleware\RequireLandlordHost::class
        );

        $router->aliasMiddleware(
            'saas.tenant-host',
            \Modules\Saas\Http\Middleware\RequireTenantHost::class
        );

        $router->aliasMiddleware(
            'saas.tenant-active',
            \Modules\Saas\Http\Middleware\EnsureTenantActive::class
        );

        $router->aliasMiddleware(
            'saas.entitlement',
            \Modules\Saas\Http\Middleware\RequireEntitlement::class
        );
    }

    protected function registerMigrations(): void
    {
        // Landlord migrations run on the landlord connection.
        $this->loadMigrationsFrom(
            module_path($this->moduleName, 'database/migrations/landlord')
        );
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Saas\Console\Commands\ProvisionTenant::class,
                \Modules\Saas\Console\Commands\MigrateTenants::class,
                \Modules\Saas\Console\Commands\VerifyTenantIsolation::class,
                \Modules\Saas\Console\Commands\ReconcileSubscriptions::class,
            ]);
        }
    }

    protected function registerRateLimiters(): void
    {
        [$attempts, $minutes] = array_pad(
            explode(',', (string) config('saas.leads.rate_limit', '5,60')),
            2,
            60
        );

        RateLimiter::for('saas-leads', function (Request $request) use ($attempts, $minutes) {
            return Limit::perMinutes((int) $minutes, (int) $attempts)->by($request->ip());
        });
    }
}
