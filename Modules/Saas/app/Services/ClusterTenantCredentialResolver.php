<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Models\Landlord\TenantDatabase;
use RuntimeException;

/**
 * Resolves tenant database credentials by following the `secret_ref` pointer
 * recorded on saas_tenant_databases.
 *
 * The single implementation for every environment. It supports the pointer
 * scheme the provisioner already writes (`env:SAAS_CLUSTER_DEFAULT`) and leaves
 * room for a secret-manager scheme to be added without touching call sites.
 *
 * TOPOLOGY AND ITS LIMIT — one database *user* per cluster, one *database* per
 * tenant. Isolation comes from the separate database and from
 * DatabaseTenantConnectionManager purging the PDO handle between tenants, not
 * from per-tenant credentials. That is a deliberate, common trade-off, but it
 * has a real consequence: a leaked cluster credential reaches every school on
 * that cluster. Issuing per-tenant MySQL users (and pointing `secret_ref` at a
 * secret manager entry per tenant) is the upgrade path, and it needs no change
 * outside this class.
 *
 * WHY NO env() CALLS AT RUNTIME — `php artisan config:cache` is standard in
 * production, and when the config cache exists Laravel skips loading the .env
 * file entirely. An `env()` call from a service at request time would return
 * null on a cached-config deploy and only there, which is the worst kind of
 * bug: invisible in dev, total in production. So an `env:PREFIX` pointer is
 * resolved through config/saas.php's `clusters` array, which materialises those
 * same variables at config-build time and therefore survives caching.
 */
class ClusterTenantCredentialResolver implements TenantCredentialResolver
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function resolveFor(TenantContext $context): array
    {
        // Per-tenant credentials take precedence, because a host that issues one
        // MySQL user per database gives the cluster user no rights on it — the
        // cluster credential would not just be wrong, it would fail to connect.
        if ($this->pointsAtOwnRow($context)) {
            return $this->fromTenantRow($context);
        }

        $cluster = $this->clusterFor($context);
        $credentials = $this->config->get("saas.clusters.{$cluster}");

        if (! is_array($credentials)) {
            // Fail rather than fall back to the app's own credentials. A silent
            // fallback would connect the tenant to the landlord database, which
            // is a cross-tenant read, not an outage.
            throw new RuntimeException(
                "No credentials configured for tenant database cluster [{$cluster}]. "
                .'Add it to config/saas.php under "clusters".'
            );
        }

        foreach (['host', 'port', 'username'] as $key) {
            if (! isset($credentials[$key]) || $credentials[$key] === '') {
                throw new RuntimeException(
                    "Cluster [{$cluster}] credential [{$key}] is missing."
                );
            }
        }

        $password = (string) ($credentials['password'] ?? '');

        // An empty password on a production MySQL user reachable over TCP is an
        // open door to every tenant database on the cluster. Fail closed here
        // rather than serve traffic from one.
        if ($password === '' && app()->environment('production')) {
            throw new RuntimeException(
                "Cluster [{$cluster}] has an empty database password, which is refused in production. "
                .'Set SAAS_CLUSTER_DEFAULT_PASSWORD (or the matching cluster password) in the environment.'
            );
        }

        return [
            'host' => (string) $credentials['host'],
            'port' => (int) $credentials['port'],
            'username' => (string) $credentials['username'],
            'password' => $password,
        ];
    }

    private function pointsAtOwnRow(TenantContext $context): bool
    {
        return trim($context->secretRef) === TenantDatabase::SECRET_REF_ROW;
    }

    /**
     * Read the credentials stored on the tenant's own saas_tenant_databases row.
     *
     * Deliberately queried here rather than carried on TenantContext. The
     * context is an immutable snapshot that gets attached to logs, traces and
     * queue payloads; a password on it would eventually be written to one of
     * them. One extra landlord query per request is the cheaper mistake.
     *
     * Not cached, for the same reason.
     *
     * @return array{host: string, port: int, username: string, password: string}
     */
    private function fromTenantRow(TenantContext $context): array
    {
        $row = TenantDatabase::query()
            ->where('tenant_uuid', $context->uuid)
            ->first();

        if ($row === null || ! $row->hasOwnCredentials()) {
            throw new RuntimeException(
                "Tenant [{$context->uuid}] points at per-tenant database credentials, but none are stored. "
                .'Re-enter them on the tenant in the platform panel.'
            );
        }

        // Endpoint still comes from the cluster: the host and port are
        // infrastructure, identical for every database on that server, and
        // nothing is gained by copying them onto each tenant row.
        $cluster = $this->config->get('saas.clusters.'.($context->cluster ?: 'default'));

        $password = (string) $row->db_password;

        if ($password === '' && app()->environment('production')) {
            throw new RuntimeException(
                "Tenant [{$context->uuid}] has an empty database password, which is refused in production."
            );
        }

        return [
            'host' => (string) ($cluster['host'] ?? '127.0.0.1'),
            'port' => (int) ($cluster['port'] ?? 3306),
            'username' => (string) $row->db_username,
            'password' => $password,
        ];
    }

    /**
     * Map a `secret_ref` pointer onto a key in config('saas.clusters').
     *
     * Supported pointers:
     *   ''                        -> the context's own cluster
     *   'cluster:eu1'             -> clusters.eu1
     *   'env:SAAS_CLUSTER_EU1'    -> clusters.eu1  (prefix convention)
     *
     * An unrecognised scheme throws instead of degrading to a default. A
     * pointer such as `vault://...` means the operator intended a secret
     * manager; quietly serving cluster credentials instead would hand back
     * working-but-wrong credentials, which is worse than a clear failure.
     */
    private function clusterFor(TenantContext $context): string
    {
        $ref = trim($context->secretRef);
        $fallback = $context->cluster ?: 'default';

        if ($ref === '') {
            return $fallback;
        }

        [$scheme, $value] = array_pad(explode(':', $ref, 2), 2, '');
        $value = trim($value);

        return match (strtolower($scheme)) {
            'cluster' => $value !== '' ? strtolower($value) : $fallback,
            'env' => $this->clusterFromEnvPrefix($value, $fallback),
            default => throw new RuntimeException(
                "Unsupported tenant credential pointer scheme [{$scheme}]. "
                .'Bind a TenantCredentialResolver that understands it in SaasServiceProvider.'
            ),
        };
    }

    /**
     * `env:SAAS_CLUSTER_DEFAULT` is the pointer TenantProvisioner writes. The
     * trailing segment after the SAAS_CLUSTER_ prefix is the cluster name, so
     * the pointer stays meaningful without this class reading the environment.
     */
    private function clusterFromEnvPrefix(string $prefix, string $fallback): string
    {
        $prefix = strtoupper(trim($prefix));

        if ($prefix === '') {
            return $fallback;
        }

        if (str_starts_with($prefix, 'SAAS_CLUSTER_')) {
            $name = strtolower(substr($prefix, strlen('SAAS_CLUSTER_')));

            if ($name !== '' && is_array($this->config->get("saas.clusters.{$name}"))) {
                return $name;
            }
        }

        return $fallback;
    }
}
