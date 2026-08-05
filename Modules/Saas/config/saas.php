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
        'name' => env('SAAS_BRAND_NAME', 'SchoolOS'),
        'legal' => env('SAAS_LEGAL_NAME', ''),
        'reg_no' => env('SAAS_COMPANY_REG', ''),
        'address' => env('SAAS_COMPANY_ADDRESS', ''),
        'email' => env('SAAS_CONTACT_EMAIL', ''),
        'phone' => env('SAAS_CONTACT_PHONE', ''),
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
        'platform' => env('SAAS_PLATFORM_HOST'),    // app.product.example
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
     * Read by ClusterTenantCredentialResolver, which follows the `secret_ref`
     * pointer on saas_tenant_databases: `env:SAAS_CLUSTER_<NAME>` and
     * `cluster:<name>` both land on an entry here.
     *
     * Values are resolved through config rather than env() at request time,
     * because `config:cache` stops Laravel reading .env — an env() call from a
     * service would return null on a cached-config deploy and only there.
     *
     * One database user per cluster, one database per tenant: isolation comes
     * from the separate database, not from per-tenant credentials. To move a
     * cluster onto a secret manager, add a scheme to that resolver and rewrite
     * the affected `secret_ref` values; nothing else changes.
     */
    'clusters' => [
        'default' => [
            'host' => env('SAAS_CLUSTER_DEFAULT_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('SAAS_CLUSTER_DEFAULT_PORT', env('DB_PORT', 3306)),
            'username' => env('SAAS_CLUSTER_DEFAULT_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('SAAS_CLUSTER_DEFAULT_PASSWORD', env('DB_PASSWORD', '')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom domains
    |--------------------------------------------------------------------------
    | A school may be reached at a subdomain we issue (tamjeed.intellschool.com)
    | or at its own domain (tamjeed.com). The second kind routes only after the
    | school proves it controls the DNS, by publishing a TXT record at
    | <prefix>.<their domain> containing the token we generated.
    |
    | Changing the prefix invalidates instructions already given to schools
    | mid-verification, so treat it as fixed once the first custom domain is
    | live.
    */
    'domains' => [
        'verification_prefix' => env('SAAS_DOMAIN_VERIFY_PREFIX', '_saas-verify'),
    ],

    'storage' => [
        // Disks that get re-rooted under tenants/{uuid}/ for the duration of a
        // tenant request or job. Add every disk that holds school content.
        // Do NOT add a disk used for control-plane or shared assets.
        'tenant_disks' => ['local', 'public'],
    ],

    'tenancy' => [
        /*
         * Master switch for multi-tenant request handling.
         *
         * When false, ResolveTenant passes every request straight through and
         * the application behaves exactly as the original single-tenant ERP.
         * This exists so the middleware can stay registered in the kernel
         * during the migration instead of being commented in and out — a
         * commented-out isolation control is one merge away from being lost.
         *
         * Flip to true only once the landlord database is migrated and at
         * least one tenant is provisioned, or every request will 404.
         */
        'enabled' => (bool) env('SAAS_TENANCY_ENABLED', false),

        // How long a resolved host→tenant mapping is cached. Kept short: a
        // suspended or deleted tenant must stop serving quickly. The cache is
        // flushed explicitly on tenant and domain lifecycle events.
        'resolution_cache_ttl' => (int) env('SAAS_RESOLUTION_TTL', 60),

        // School data is retained after cancellation so export and recovery
        // remain possible. Irreversible purging is a separate reviewed job.
        'cancellation_retention_days' => (int) env('SAAS_CANCELLATION_RETENTION_DAYS', 90),

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
        'verified_at' => '2026-07-26',
        'modules' => 34,
        'api_endpoints' => 1701,
        'roles' => 17,
        'admin_permissions' => 644,
        'locales' => ['en', 'ar'],
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
        'publish_pricing' => env('SAAS_CLAIM_PRICING', false),
        // Requires: signed customer references. Never fabricate logos.
        'publish_customers' => env('SAAS_CLAIM_CUSTOMERS', false),
        // Requires: monitoring history + published SLA.
        'publish_uptime' => env('SAAS_CLAIM_UPTIME', false),
        // Requires: completed external security assessment.
        'publish_certifications' => env('SAAS_CLAIM_CERTS', false),
        // Requires: green flutter analyze/test/build + two-tenant device test.
        'publish_mobile_ga' => env('SAAS_CLAIM_MOBILE_GA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo request handling
    |--------------------------------------------------------------------------
    */
    'leads' => [
        'notify' => env('SAAS_LEAD_NOTIFY_EMAIL'),
        'rate_limit' => '5,60', // attempts, minutes
        'queue' => env('SAAS_LEAD_QUEUE', 'notifications'),
        'retention_days' => (int) env('SAAS_LEAD_RETENTION_DAYS', 90),
        'size_options' => ['up_to_300', '300_to_800', '800_to_2000', 'over_2000'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public signup
    |--------------------------------------------------------------------------
    | Fail closed until commercial pricing, legal documents, email delivery,
    | queue workers, and provisioning infrastructure have been approved.
    */
    'signup' => [
        'enabled' => (bool) env('SAAS_PUBLIC_SIGNUP_ENABLED', false),
        'owner_invitations_enabled' => (bool) env('SAAS_OWNER_INVITATIONS_ENABLED', false),
        'invitation_expiry_days' => (int) env('SAAS_INVITATION_EXPIRY_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    | SaaS subscription billing is a SEPARATE domain from school fee payments.
    | The ERP's Stripe/Razorpay integration for student fees must never be the
    | source of truth for platform subscriptions (plan §9).
    |
    | The NullBillingGateway is bound by default. Switch to a real provider
    | adapter in SaasServiceProvider once a billing provider is approved.
    */
    'billing' => [
        // Provider identifier: 'null', 'stripe', 'razorpay', 'paddle', etc.
        'provider' => env('SAAS_BILLING_PROVIDER', 'null'),

        // Webhook signature verification secret. MUST be set before enabling
        // a real billing provider. Never commit the real value.
        'webhook_secret' => env('SAAS_BILLING_WEBHOOK_SECRET'),

        // Optional absolute customer-facing URL exposed on entitlement denial.
        // Invalid or missing URLs remain null in API responses.
        'upgrade_url' => env('SAAS_BILLING_UPGRADE_URL'),

        // Provider API keys. Kept separate from the ERP's payment gateway keys.
        'api_key' => env('SAAS_BILLING_API_KEY'),
        'api_secret' => env('SAAS_BILLING_API_SECRET'),

        // Grace period (days) after a failed payment before access is restricted.
        // School data is NEVER deleted due to billing failure (plan §12).
        'grace_days' => (int) env('SAAS_BILLING_GRACE_DAYS', 7),

        // Trial configuration.
        'trial_days' => (int) env('SAAS_BILLING_TRIAL_DAYS', 14),
        'trial_requires_card' => (bool) env('SAAS_BILLING_TRIAL_CARD', false),

        // Reconciliation schedule (cron expression).
        'reconcile_schedule' => env('SAAS_BILLING_RECONCILE_CRON', '0 */6 * * *'),

        // Maximum webhook processing retries before manual review.
        'max_webhook_retries' => (int) env('SAAS_BILLING_MAX_RETRIES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entitlements
    |--------------------------------------------------------------------------
    | Feature codes and plan enforcement configuration (plan §8).
    */
    'entitlements' => [
        // Cache TTL for the effective entitlement snapshot per tenant.
        'cache_ttl' => (int) env('SAAS_ENTITLEMENT_CACHE_TTL', 300),

        // In development, grant all features regardless of plan.
        // MUST be false in production.
        'development_mode' => (bool) env('SAAS_ENTITLEMENT_DEV_MODE', env('APP_ENV') === 'local'),

        // Stable feature codes. These are referenced by RequireEntitlement
        // middleware and EntitlementChecker. Adding a code here does NOT
        // automatically grant it — that is controlled by plan features.
        'feature_codes' => [
            'students.core',
            'students.admissions',
            'students.attendance',
            'students.promotion',
            'academics.core',
            'academics.exams',
            'academics.timetable',
            'finance.fees',
            'finance.payroll',
            'finance.accounting',
            'hr.core',
            'hr.leave',
            'transport.routes',
            'library.core',
            'inventory.core',
            'communication.sms',
            'communication.email',
            'communication.push',
            'website.cms',
            'mobile.offline',
            'api.access',
            'reports.advanced',
            'storage.gb',
            'campuses.max',
            'users.max',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisioning
    |--------------------------------------------------------------------------
    */
    'provisioning' => [
        // Maximum concurrent provisioning runs.
        'max_concurrent' => (int) env('SAAS_PROVISION_MAX_CONCURRENT', 3),

        // Dedicated queue so database creation never blocks web requests.
        'queue' => env('SAAS_PROVISION_QUEUE', 'provisioning'),

        // Delay (seconds) between batch provisioning steps.
        'step_delay' => (int) env('SAAS_PROVISION_STEP_DELAY', 2),

        // Whether to run health checks after provisioning.
        'verify_after_provision' => (bool) env('SAAS_PROVISION_VERIFY', true),

        // Seeder class used for tenant database initialization.
        'tenant_seeder' => env('SAAS_TENANT_SEEDER', 'Database\\Seeders\\TenantSeeder'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Support access
    |--------------------------------------------------------------------------
    | Platform operators may access a tenant only through an approved,
    | time-limited, fully audited support session (plan §7, §12).
    */
    'support' => [
        // Maximum session duration in minutes.
        'max_duration' => (int) env('SAAS_SUPPORT_MAX_DURATION', 60),

        // Default access scope: 'read' or 'write'.
        'default_scope' => env('SAAS_SUPPORT_DEFAULT_SCOPE', 'read'),

        // Whether tenant owner approval is required before access.
        'requires_approval' => (bool) env('SAAS_SUPPORT_REQUIRES_APPROVAL', true),

        // Show a visible banner to tenant users during support access.
        'show_banner' => (bool) env('SAAS_SUPPORT_SHOW_BANNER', true),
    ],
];
