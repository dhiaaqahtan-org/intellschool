<?php

namespace Modules\Saas\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Saas';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapMarketingRoutes();
        $this->mapPlatformRoutes();
        $this->mapWebhookRoutes();
        $this->mapTenantRoutes();
        $this->mapApiRoutes();
    }

    /**
     * Marketing routes own `/` on the marketing host only.
     *
     * ORDERING MATTERS. The core application registers routes/site.php, which
     * also claims `/` for the tenant's public school website. Because the
     * marketing routes carry a domain constraint they only match the marketing
     * host, but they must still be registered BEFORE the unconstrained
     * site.php route or that route will win on every host.
     *
     * nwidart's provider is package-discovered and therefore boots before
     * App\Providers\RouteServiceProvider in a default Laravel 12 application.
     * Verify with `php artisan route:list --path=/` after installing: the
     * domain-constrained marketing route must appear above the site route.
     */
    protected function mapMarketingRoutes(): void
    {
        $host = config('saas.hosts.marketing');

        $route = Route::middleware(['web', 'saas.landlord-host']);

        // No host configured (local development): serve marketing everywhere.
        // Never leave this unset in production — `/` would shadow every
        // tenant's own school website.
        if (! empty($host)) {
            $route = $route->domain($host);
        }

        $route->group(module_path($this->moduleName, 'routes/marketing.php'));
    }

    /**
     * Platform administration routes run on the platform host only.
     * They manage the control plane and never touch tenant databases.
     */
    protected function mapPlatformRoutes(): void
    {
        $host = config('saas.hosts.platform');

        $route = Route::middleware(['web', 'saas.landlord-host']);

        if (! empty($host)) {
            $route = $route->domain($host);
        }

        $route->group(module_path($this->moduleName, 'routes/platform.php'));
    }

    /**
     * Webhook routes are NOT behind CSRF or session auth.
     * They use provider signature verification instead.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware([])
            ->group(module_path($this->moduleName, 'routes/webhooks.php'));
    }

    /**
     * Tenant routes run on tenant hosts only.
     * They provide SaaS-specific endpoints (info, entitlements, assets)
     * that complement the existing ERP routes.
     */
    protected function mapTenantRoutes(): void
    {
        Route::middleware(['web'])
            ->group(module_path($this->moduleName, 'routes/tenant.php'));
    }

    /**
     * API routes for the Flutter/mobile client and platform integrations.
     * These run on all hosts but enforce their own host/tenant requirements.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware(['api'])
            ->group(module_path($this->moduleName, 'routes/api.php'));
    }
}
