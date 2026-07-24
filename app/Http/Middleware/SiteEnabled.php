<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiteEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! config('config.site.enable_site')) {
            return redirect()->route('app');
        }

        if (! auth()->check() && ! config('config.site.show_public_view')) {
            return redirect()->route('app');
        }

        config([
            'config.site.view' => 'site.'.config('config.site.theme').'.',
        ]);

        $locale = config('config.site.locale', 'ar');
        if (in_array(session('site_locale'), ['en', 'ar'])) {
            $locale = session('site_locale');
        }
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
