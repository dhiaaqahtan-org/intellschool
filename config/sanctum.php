<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    | MULTI-TENANCY: every school runs on its own subdomain
    | (tamjeed.intellschool.com, ...), and each one serves the same Vue SPA via
    | cookie/session auth. A tenant host that is NOT listed here still logs in
    | successfully — Auth::attempt passes and the session is written — but
    | EnsureFrontendRequestsAreStateful then declines to use the session guard,
    | so the SPA's next call (GET /auth/user) returns 401 "session expired" and
    | the app bounces back to the login screen instead of reaching the
    | dashboard. Because tenants are created at runtime we cannot enumerate
    | them, so the tenant suffix is appended as a wildcard: any current or
    | future tenant subdomain is stateful without another .env edit.
    |
    | SESSION_DOMAIN must stay unset (host-only cookies). Widening it to
    | `.intellschool.com` would make ONE session cookie valid on every tenant
    | host — a cross-tenant isolation breach. Wildcarding here is safe because
    | it only marks which hosts may use cookie auth; each host still has its
    | own cookie.
    |
    */

    'stateful' => collect(explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))))
        ->push(($suffix = trim((string) env('SAAS_TENANT_SUFFIX'), " \t\n\r\0\x0B."))
            ? '*.'.$suffix
            : null)
        ->map(fn ($domain) => trim((string) $domain))
        ->filter()
        ->unique()
        ->values()
        ->all(),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. This will override any values set in the token's
    | "expires_at" attribute, but first-party sessions are not affected.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
