<?php

namespace App\Http\Middleware;

use App\Support\SetConfig;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Init
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // ResolveTenant marks marketing/platform requests before the web
        // middleware group runs. The legacy ERP initializer reads school
        // tables and must never run on the landlord-only control plane.
        if ($request->attributes->getBoolean('saas.control_plane')) {
            return $next($request);
        }

        (new SetConfig)->set();

        return $next($request);
    }
}
