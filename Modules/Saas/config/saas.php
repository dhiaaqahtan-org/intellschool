<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    | PLACEHOLDER. The product name below is not an approved brand. Replace it,
    | and register the trademark, before anything ships publicly.
    */
    'brand' => [
        'name'     => env('SAAS_BRAND_NAME', 'SchoolOS'),
        'legal'    => env('SAAS_LEGAL_NAME', ''),
        'reg_no'   => env('SAAS_COMPANY_REG', ''),
        'address'  => env('SAAS_COMPANY_ADDRESS', ''),
        'email'    => env('SAAS_CONTACT_EMAIL', ''),
        'phone'    => env('SAAS_CONTACT_PHONE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hosts
    |--------------------------------------------------------------------------
    | Marketing routes are registered ONLY for the marketing host so that `/`
    | never collides with the tenant school website served by routes/site.php.
    | Leave `marketing` null in local development to serve marketing on any host.
    */
    'hosts' => [
        'marketing' => env('SAAS_MARKETING_HOST'),   // www.product.example
        'platform'  => env('SAAS_PLATFORM_HOST'),    // app.product.example
        'tenant_suffix' => env('SAAS_TENANT_SUFFIX'), // .product.example
    ],

    /*
    |--------------------------------------------------------------------------
    | Verified product facts
    |--------------------------------------------------------------------------
    | Every number rendered on the marketing site comes from here so that a
    | claim can be traced to its source and re-verified after a release.
    | Source: docs/instikit-modules-endpoints.md and docs/instikit-role-capabilities.md
    | Re-run those generators after any release that adds routes or permissions.
    */
    'facts' => [
        'verified_at'      => '2026-07-26',
        'modules'          => 34,
        'api_endpoints'    => 1701,
        'roles'            => 17,
        'admin_permissions'=> 644,
        'locales'          => ['en', 'ar'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketing claim gates
    |--------------------------------------------------------------------------
    | Fail closed. Each flag stays false until the underlying evidence exists;
    | the Blade views hide the corresponding claim while the flag is false.
    | See SAAS_MULTITENANT_NWIDART_IMPLEMENTATION_PLAN.md §10.4 and §20.
    */
    'claims' => [
        // Requires: approved commercial pricing sign-off.
        'publish_pricing'      => env('SAAS_CLAIM_PRICING', false),
        // Requires: signed customer references. Never fabricate logos.
        'publish_customers'    => env('SAAS_CLAIM_CUSTOMERS', false),
        // Requires: monitoring history + published SLA.
        'publish_uptime'       => env('SAAS_CLAIM_UPTIME', false),
        // Requires: completed external security assessment.
        'publish_certifications' => env('SAAS_CLAIM_CERTS', false),
        // Requires: green flutter analyze/test/build + two-tenant device test.
        'publish_mobile_ga'    => env('SAAS_CLAIM_MOBILE_GA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo request handling
    |--------------------------------------------------------------------------
    */
    'leads' => [
        'notify'     => env('SAAS_LEAD_NOTIFY_EMAIL'),
        'rate_limit' => '5,60', // attempts, minutes
    ],
];
