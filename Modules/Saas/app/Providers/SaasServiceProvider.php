<?php

namespace Modules\Saas\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\Saas\Bootstrappers\CacheBootstrapper;
use Modules\Saas\Bootstrappers\FilesystemBootstrapper;
use Modules\Saas\Bootstrappers\SessionBootstrapper;
use Modules\Saas\Console\Commands\MigrateTenants;
use Modules\Saas\Console\Commands\ProvisionTenant;
use Modules\Saas\Console\Commands\ProvisionTenantDomain;
use Modules\Saas\Console\Commands\PruneDemoRequests;
use Modules\Saas\Console\Commands\ReconcileSubscriptions;
use Modules\Saas\Console\Commands\VerifyTenantIsolation;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Contracts\TenantConnectionManager;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Contracts\TenantStorage;
use Modules\Saas\Contracts\TenantUrlGenerator;
use Modules\Saas\Domain\Tenancy\TenantContextManager;
use Modules\Saas\Http\Middleware\EnsureTenantActive;
use Modules\Saas\Http\Middleware\RequireEntitlement;
use Modules\Saas\Http\Middleware\RequireLandlordHost;
use Modules\Saas\Http\Middleware\RequireTenantHost;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Policies\PlatformPolicy;
use Modules\Saas\Queue\QueueTenancy;
use Modules\Saas\Services\Billing\NullBillingGateway;
use Modules\Saas\Services\ClusterTenantCredentialResolver;
use Modules\Saas\Services\DatabaseTenantConnectionManager;
use Modules\Saas\Services\FeatureEntitlementService;
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
        $this->registerPolicies();
        $this->registerQueueTenancy();

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

        // Credential resolution. One resolver for every environment on purpose:
        // a binding that differs between dev and production is how a tenant
        // isolation path ends up untested in the only environment that matters.
        // It follows the `secret_ref` pointer, so moving a cluster onto a secret
        // manager later is a new scheme in that class, not a change here.
        $this->app->singleton(TenantCredentialResolver::class, ClusterTenantCredentialResolver::class);

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
        // MUST be singletons. Each bootstrapper holds the original value it
        // replaced (cache prefix, disk roots) and restores it in revert().
        // Without a singleton binding the container hands out a fresh instance
        // every time the tag is iterated, so revert() runs against an object
        // that never captured anything, silently does nothing, and the tenant's
        // cache prefix and storage root stay applied to the next tenant served
        // by that worker.
        $this->app->singleton(CacheBootstrapper::class);
        $this->app->singleton(SessionBootstrapper::class);
        $this->app->singleton(FilesystemBootstrapper::class);

        // Session after cache on purpose: the redis/cache session drivers read
        // through the cache store, so the tenant prefix has to be in place
        // before a session handler is built against it.
        $this->app->tag([
            CacheBootstrapper::class,
            SessionBootstrapper::class,
            FilesystemBootstrapper::class,
        ], 'saas.bootstrappers');
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
            RequireLandlordHost::class
        );

        $router->aliasMiddleware(
            'saas.tenant-host',
            RequireTenantHost::class
        );

        $router->aliasMiddleware(
            'saas.tenant-active',
            EnsureTenantActive::class
        );

        $router->aliasMiddleware(
            'saas.entitlement',
            RequireEntitlement::class
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
                ProvisionTenant::class,
                ProvisionTenantDomain::class,
                MigrateTenants::class,
                VerifyTenantIsolation::class,
                ReconcileSubscriptions::class,
                PruneDemoRequests::class,
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

    /**
     * Automatic tenant propagation through the queue (plan §7, Phase 8).
     *
     * This is what actually guarantees queue isolation. The TenantAwareJob
     * middleware remains available for jobs that need explicit control, but
     * correctness must not depend on 70 job classes each remembering to
     * opt in.
     */
    protected function registerQueueTenancy(): void
    {
        if (! config('saas.tenancy.enabled', false)) {
            return;
        }

        $this->app->make(QueueTenancy::class)
            ->register($this->app['events']);
    }

    /**
     * Register platform authorization policies (plan §5.4).
     * Deny-by-default: platform operators have no implicit tenant data access.
     */
    protected function registerPolicies(): void
    {
        // Abilities that take a Tenant model resolve through the policy.
        Gate::policy(
            Tenant::class,
            PlatformPolicy::class
        );

        /*
         * Abilities with NO model argument must be defined as gates.
         *
         * Gate::policy() only maps abilities that are called with an instance
         * or class name. `Gate::authorize('manageBilling')` has neither, so it
         * finds no policy, falls through to Spatie's permission gate, and dies
         * with "Call to undefined method PlatformUser::hasRole()" — because
         * platform operators are a separate guard with no Spatie roles at all.
         */
        foreach (['manageUsers', 'createTenant', 'viewAnyTenant', 'manageBilling', 'accessSupport', 'viewAuditLog'] as $ability) {
            Gate::define($ability, [PlatformPolicy::class, $ability]);
        }
    }
}
