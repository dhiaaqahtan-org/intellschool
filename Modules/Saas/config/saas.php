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
    | Tenancy
    |--------------------------------------------------------------------------
    */
    'database' => [
        // Control plane. Must be a DIFFERENT database from any tenant.
        'landlord_connection' => env('SAAS_LANDLORD_CONNECTION', 'landlord'),

        // Name of the dynamically reconfigured connection that tenant requests
        // run through. It is made the DEFAULT connection during a request so
        // unmodified ERP models land on it without edits.
        'tenant_connection' => env('SAAS_TENANT_CONNECTION', 'tenant'),

        // Connection whose host/port/driver settings are used as the template
        // for tenant connections. Credentials are always overridden.
        'tenant_template' => env('SAAS_TENANT_TEMPLATE', 'mysql'),

        // Prefix for generated tenant database names. Names are DERIVED from
        // the tenant UUID and never accepted from input.
        'tenant_prefix' => env('SAAS_TENANT_DB_PREFIX', 'tnt_'),
    ],

    /*
     * Tenant database clusters.
     *
     * Development only — these are read by EnvTenantCredentialResolver, which
     * refuses to run in production. In production, `secret_ref` on
     * saas_tenant_databases points at a secret manager entry and a different
     * resolver implementation fetches it.
     */
    'clusters' => [
        'default' => [
            'host' => env('SAAS_CLUSTER_DEFAULT_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('SAAS_CLUSTER_DEFAULT_PORT', env('DB_PORT', 3306)),
            'username' => env('SAAS_CLUSTER_DEFAULT_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('SAAS_CLUSTER_DEFAULT_PASSWORD', env('DB_PASSWORD', '')),
        ],
    ],

    'storage' => [
        // Disks that get re-rooted under tenants/{uuid}/ for the duration of a
        // tenant request or job. Add every disk that holds school content.
        // Do NOT add a disk used for control-plane or shared assets.
        'tenant_disks' => ['local', 'public'],
    ],

    'tenancy' => [
        // How long a resolved host→tenant mapping is cached. Kept short: a
        // suspended or deleted tenant must stop serving quickly. The cache is
        // flushed explicitly on tenant and domain lifecycle events.
        'resolution_cache_ttl' => (int) env('SAAS_RESOLUTION_TTL', 60),

        // Hosts that are always treated as control-plane, never as a tenant.
        // The marketing and platform hosts are added automatically.
        'reserved_hosts' => array_filter(explode(',', (string) env('SAAS_RESERVED_HOSTS', ''))),

        // Slugs that may never be issued as a tenant subdomain, because they
        // would shadow a platform host or be mistaken for one.
        'reserved_slugs' => [
            'www', 'app', 'api', 'admin', 'platform', 'status', 'help', 'docs',
            'mail', 'smtp', 'ftp', 'cdn', 'assets', 'static', 'blog', 'support',
            'billing', 'account', 'accounts', 'login', 'signup', 'register',
            'test', 'staging', 'dev', 'demo', 'localhost', 'internal',
        ],

        // In local/testing, allow resolving a tenant even when no marketing
        // host is configured. MUST stay false in production.
        'allow_unresolved_in_local' => (bool) env('SAAS_ALLOW_UNRESOLVED_LOCAL', true),
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
