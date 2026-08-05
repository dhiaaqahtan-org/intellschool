<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'app/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * A literal '*' here is INVALID together with supports_credentials below:
     * a browser refuses a credentialed response whose Access-Control-Allow-Origin
     * is the wildcard, so cookie-authenticated cross-origin calls fail outright.
     * It survives in local development only because most dev setups end up
     * same-origin, where CORS never engages.
     *
     * Set CORS_ALLOWED_ORIGINS in production to the exact origins that may send
     * credentials. Entries may contain '*' as a subdomain wildcard — php-cors
     * converts those to patterns — so one entry covers every tenant:
     *
     *   CORS_ALLOWED_ORIGINS=https://intellschool.com,https://*.intellschool.com
     */
    'allowed_origins' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*')))
    )),

    'allowed_origins_patterns' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', '')))
    )),

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
