<?php

declare(strict_types=1);

use NielsNumbers\LaravelLocalizer\Detectors\BrowserDetector;
use NielsNumbers\LaravelLocalizer\Detectors\UserDetector;

return [
    /*
    |--------------------------------------------------------------------------
    | Public website locales
    |--------------------------------------------------------------------------
    |
    | Keep this list static: the package registers one set of cacheable routes
    | from it during application boot. A tenant may narrow the active subset at
    | request time, but the union of routes must not change per customer.
    |
    */
    'supported_locales' => ['en', 'ar'],

    // Both languages get explicit, indexable URLs: /en/... and /ar/....
    // The unprefixed route remains the first-visit language negotiator.
    'hide_default_locale' => false,

    'redirect_enabled' => true,

    'persist_locale' => [
        'session' => true,
        'cookie' => true,
    ],

    'detectors' => [
        UserDetector::class,
        BrowserDetector::class,
    ],

    // Per-locale override for writing direction. Keys must match the
    // locale codes used in `supported_locales`. Values: 'rtl' or 'ltr'.
    // Wins over the script-based detection in `LocaleDirection`.
    'locale_directions' => [
        'en' => 'ltr',
        'ar' => 'rtl',
    ],
];
