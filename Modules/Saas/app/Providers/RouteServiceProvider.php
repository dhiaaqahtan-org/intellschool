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
}
