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
     * Marketing routes own `/` on the marketing host, and only there.
     *
     * A DOMAIN CONSTRAINT IS MANDATORY, not a nicety. Laravel's RouteCollection
     * is keyed by domain+uri, so two routes claiming `/` with no domain are the
     * same key and the later registration silently overwrites the earlier one.
     * The core app registers routes/site.php (the tenant's own school website)
     * at `/` too, so an unconstrained marketing route does not "win" or "lose"
     * predictably — it depends on provider boot order, which is not something
     * to build tenant routing on.
     *
     * With a domain set, the two routes have different keys and coexist: the
     * host decides which one answers. That is the only arrangement that is
     * correct regardless of boot order.
     *
     * If no host is configured we fall back to `localhost` in local/testing so
     * a developer still has a working URL, and register nothing at all in
     * production — better to 404 the marketing site than to shadow every
     * tenant's school website.
     */
    protected function mapMarketingRoutes(): void
    {
        $host = config('saas.hosts.marketing');

        if (empty($host)) {
            if ($this->app->environment('production')) {
                return; // fail closed
            }

            $host = 'localhost';
        }

        $route = Route::middleware(['web', 'saas.landlord-host'])->domain($host);

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
