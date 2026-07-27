<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/app';

    public const VERSION_PATH = '/v1';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api'.self::VERSION_PATH)->group(function () {
                Route::middleware(['api', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/api.php'));

                Route::middleware(['api', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/integration.php'));

                Route::middleware(['api', 'saas.tenant-host', 'guest'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/guest.php'));

                Route::prefix('auth')
                    ->middleware(['api', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/auth.php'));

                Route::prefix('app')
                    ->middleware(['api', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/app.php'));

                Route::prefix('app/chat')
                    ->middleware(['api', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config', 'permission:chat:access'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/chat.php'));

                $modules = glob(base_path('routes/modules/*.php'));
                foreach ($modules as $module) {
                    Route::prefix('app')
                        ->middleware(['api', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config'])
                        // ->namespace($this->namespace)
                        ->group($module);
                }

                Route::prefix('app')
                    ->middleware(['api', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/module.php'));

                // Offline sync endpoints for Flutter client
                Route::middleware(['api', 'saas.tenant-host', 'user.config'])
                    // ->namespace($this->namespace)
                    ->group(base_path('routes/sync.php'));
            });

            $modules = glob(base_path('routes/exports/*.php'));
            foreach ($modules as $module) {
                Route::prefix('app')
                    ->middleware(['web', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config', 'export'])
                    // ->namespace($this->namespace)
                    ->group($module);
            }

            Route::prefix('app')
                ->middleware(['web', 'auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'saas.tenant-host', 'user.config', 'export'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/export.php'));

            Route::middleware(['web', 'saas.tenant-host'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/gateway.php'));

            // The school's own public website is tenant content: it must not
            // render on the marketing or platform host.
            Route::middleware(['web', 'saas.tenant-host', 'site.enabled'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/site.php'));

            Route::middleware(['web', 'auth:sanctum', 'saas.tenant-host', 'user.config', 'permission:access:reports'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/report.php'));

            // Custom routes

            // These controllers read and write school records (fee mismatches,
            // guardians, password resets) but the group carries no middleware
            // at all. Adding the tenant guard at minimum; the missing auth on
            // this group is pre-existing and worth a separate look.
            Route::middleware(['saas.tenant-host'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/custom.php'));

            // Custom routes for site

            // The school's own public website is tenant content: it must not
            // render on the marketing or platform host.
            Route::middleware(['web', 'saas.tenant-host', 'site.enabled'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/site/custom.php'));

            Route::middleware(['web', 'saas.tenant-host', 'user.config'])
                // ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                // ->namespace($this->namespace)
                ->group(base_path('routes/asset.php'));

            Route::middleware(['web', 'saas.tenant-host', 'user.config', 'role:admin'])
                ->prefix('cmd')
                // ->namespace($this->namespace)
                ->group(base_path('routes/command.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5);
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3);
        });

        RateLimiter::for('timesheet', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('biometric', function (Request $request) {
            return Limit::perMinute(10);
        });
    }
}
