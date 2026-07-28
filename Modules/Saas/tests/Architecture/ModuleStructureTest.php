<?php

function saasModulePath(string $path = ''): string
{
    $root = dirname(__DIR__, 2);

    return $path === '' ? $root : $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
}

it('contains the executable layers promised by the module architecture', function () {
    $required = [
        'app/Console/Commands/ProvisionTenant.php',
        'app/Console/Commands/MigrateTenants.php',
        'app/Console/Commands/VerifyTenantIsolation.php',
        'app/Console/Commands/ReconcileSubscriptions.php',
        'app/Contracts/CurrentTenant.php',
        'app/Contracts/TenantConnectionManager.php',
        'app/Contracts/TenantStorage.php',
        'app/Domain/Billing/SubscriptionStatus.php',
        'app/Domain/Entitlements/EntitlementSnapshot.php',
        'app/Domain/Identity/InvitationToken.php',
        'app/Domain/Provisioning/ProvisioningStep.php',
        'app/Domain/Support/SupportScope.php',
        'app/Domain/Tenancy/TenantContextManager.php',
        'app/Domain/Usage/UsageMetric.php',
        'app/Domain/Website/ClaimGate.php',
        'app/Http/Middleware/ResolveTenant.php',
        'app/Http/Middleware/InitializeTenant.php',
        'app/Http/Middleware/RequireLandlordHost.php',
        'app/Http/Middleware/RequireTenantHost.php',
        'app/Http/Middleware/EnsureTenantActive.php',
        'app/Http/Middleware/RequireEntitlement.php',
        'app/Models/Concerns/UsesLandlordConnection.php',
        'app/Models/Landlord/LandlordModel.php',
        'app/Models/Tenant/TenantModel.php',
        'app/Models/Tenant/TenantInstallation.php',
        'app/Providers/SaasServiceProvider.php',
        'app/Providers/RouteServiceProvider.php',
        'app/Providers/EventServiceProvider.php',
        'database/factories/TenantFactory.php',
        'database/factories/PlanFactory.php',
        'database/seeders/PlanSeeder.php',
        'routes/marketing.php',
        'routes/platform.php',
        'routes/tenant.php',
        'routes/api.php',
        'routes/webhooks.php',
        'composer.json',
        'module.json',
        'package.json',
        'vite.config.js',
    ];

    foreach ($required as $relativePath) {
        expect(saasModulePath($relativePath))
            ->toBeFile("Missing required SaaS module layer: {$relativePath}");
    }
});

it('keeps the SaaS runtime free of Livewire dependencies and directives', function () {
    $roots = ['app', 'routes', 'resources'];
    $violations = [];

    foreach ($roots as $root) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                saasModulePath($root),
                FilesystemIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            if (str_contains($path, DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            if (! preg_match('/\.(?:php|blade\.php|js|vue)$/', $path)) {
                continue;
            }

            $contents = file_get_contents($path);

            if (preg_match('/(?:\bLivewire\b|wire:)/i', $contents)) {
                $violations[] = str_replace(saasModulePath().DIRECTORY_SEPARATOR, '', $path);
            }
        }
    }

    expect($violations)->toBe([]);
});

it('declares one module-local Vue and Vite toolchain', function () {
    $package = json_decode(file_get_contents(saasModulePath('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $vite = file_get_contents(saasModulePath('vite.config.js'));

    expect($package['dependencies']['vue'] ?? null)->toStartWith('^3.')
        ->and($package['devDependencies']['@vitejs/plugin-vue'] ?? null)->not->toBeNull()
        ->and($package['scripts'])->toHaveKeys(['build', 'lint', 'test'])
        ->and($vite)->toContain('build-saas');
});
