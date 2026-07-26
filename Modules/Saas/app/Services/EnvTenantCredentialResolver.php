<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Domain\Tenancy\TenantContext;
use RuntimeException;

/**
 * Development credential resolver.
 *
 * Reads cluster credentials from config/env. Every tenant on a cluster shares
 * one database *user*; isolation comes from the separate database, not from
 * per-tenant users.
 *
 * ============================ NOT FOR PRODUCTION ============================
 * Before launch, replace the binding in SaasServiceProvider with a resolver
 * backed by a real secret manager (plan §5.1, ADR-006) and ideally issue
 * per-tenant database users so a leaked credential cannot reach every school.
 * This class refuses to run in production for exactly that reason.
 * ===========================================================================
 */
class EnvTenantCredentialResolver implements TenantCredentialResolver
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function resolveFor(TenantContext $context): array
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'EnvTenantCredentialResolver must not be used in production. '
                .'Bind a secret-manager-backed TenantCredentialResolver in SaasServiceProvider.'
            );
        }

        $cluster = $context->cluster ?: 'default';
        $credentials = $this->config->get("saas.clusters.{$cluster}");

        if (! is_array($credentials)) {
            // Fail rather than fall back to the app's own credentials — a
            // silent fallback would connect the tenant to the wrong database.
            throw new RuntimeException(
                "No credentials configured for tenant database cluster [{$cluster}]."
            );
        }

        foreach (['host', 'port', 'username'] as $key) {
            if (! isset($credentials[$key]) || $credentials[$key] === '') {
                throw new RuntimeException(
                    "Cluster [{$cluster}] credential [{$key}] is missing."
                );
            }
        }

        return [
            'host' => (string) $credentials['host'],
            'port' => (int) $credentials['port'],
            'username' => (string) $credentials['username'],
            'password' => (string) ($credentials['password'] ?? ''),
        ];
    }
}
