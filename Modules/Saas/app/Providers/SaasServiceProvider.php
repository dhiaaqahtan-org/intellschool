<?php

namespace Modules\Saas\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class SaasServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Saas';

    protected string $moduleNameLower = 'saas';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/saas.php'), 'saas');

        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerViews();
        $this->registerRateLimiters();
        $this->registerMiddlewareAliases();

        $this->publishes([
            module_path($this->moduleName, 'config/saas.php') => config_path('saas.php'),
        ], 'saas-config');
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
        $this->app['router']->aliasMiddleware(
            'saas.landlord-host',
            \Modules\Saas\Http\Middleware\RequireLandlordHost::class
        );
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
