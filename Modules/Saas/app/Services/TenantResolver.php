<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Saas\Domain\Tenancy\HostClassification;
use Modules\Saas\Domain\Tenancy\HostNormalizer;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDomain;

/**
 * Turns a Host header into a tenant, or into nothing.
 *
 * Rules that must not be relaxed (plan §6):
 *
 *  - EXACT hostname match only. No suffix matching, no LIKE, no wildcards.
 *  - A custom domain routes only after DNS verification.
 *  - A tenant that is not `Ready` and servable does not resolve.
 *  - Unknown hosts resolve to nothing, and the caller fails closed. There is
 *    never a fallback to "the default tenant".
 */
class TenantResolver
{
    public function __construct(
        private readonly Cache $cache,
    ) {
    }

    /**
     * Classify a host without touching the database.
     */
    public function classify(?string $rawHost): HostClassification
    {
        $host = HostNormalizer::normalize($rawHost);

        if ($host === null) {
            return HostClassification::invalid($rawHost);
        }

        $marketing = HostNormalizer::normalize(config('saas.hosts.marketing'));
        $platform = HostNormalizer::normalize(config('saas.hosts.platform'));

        if ($marketing !== null && $host === $marketing) {
            return HostClassification::marketing($host);
        }

        if ($platform !== null && $host === $platform) {
            return HostClassification::platform($host);
        }

        foreach (config('saas.tenancy.reserved_hosts', []) as $reserved) {
            if ($host === HostNormalizer::normalize($reserved)) {
                return HostClassification::reserved($host);
            }
        }

        return HostClassification::tenant($host);
    }

    /**
     * @return TenantContext|null null means "no tenant here" — the caller
     *         decides whether that is acceptable for the route.
     */
    public function resolve(?string $rawHost): ?TenantContext
    {
        $classification = $this->classify($rawHost);

        if (! $classification->isTenantCandidate()) {
            return null;
        }

        return $this->resolveHost($classification->host);
    }

    private function resolveHost(string $host): ?TenantContext
    {
        $ttl = (int) config('saas.tenancy.resolution_cache_ttl', 60);

        // Cache the tenant UUID only, then load the record. Caching the whole
        // context would mean a suspended or re-pointed tenant keeps serving
        // from a stale snapshot for the life of the TTL.
        $uuid = $this->cache->remember(
            self::cacheKey($host),
            $ttl,
            fn () => $this->lookupUuid($host) ?? false
        );

        if ($uuid === false || $uuid === null) {
            return null;
        }

        $tenant = Tenant::query()
            ->with('database')
            ->where('uuid', $uuid)
            ->first();

        // Tenant vanished or is no longer servable since the mapping was
        // cached — drop the mapping and refuse.
        if ($tenant === null || ! $tenant->isServable()) {
            $this->cache->forget(self::cacheKey($host));

            return null;
        }

        return $tenant->toContext($host);
    }

    private function lookupUuid(string $host): ?string
    {
        $domain = TenantDomain::query()
            ->routable()
            ->where('hostname', $host)   // exact match, single index hit
            ->first();

        return $domain?->tenant_uuid;
    }

    public static function cacheKey(string $host): string
    {
        // Landlord-scoped, not tenant-scoped: this cache exists to FIND the
        // tenant, so it cannot itself live under a tenant prefix.
        return 'saas:host:'.hash('sha256', $host);
    }

    /**
     * Call whenever a domain is added, verified, unverified or removed, or a
     * tenant changes status. Without this, a suspended tenant keeps serving
     * until the TTL expires.
     */
    public function forget(string $host): void
    {
        $normalized = HostNormalizer::normalize($host);

        if ($normalized !== null) {
            $this->cache->forget(self::cacheKey($normalized));
        }
    }
}
