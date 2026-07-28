<?php

namespace Modules\Saas\Services\Url;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantUrlGenerator;
use Modules\Saas\Models\Landlord\TenantDomain;

/**
 * Generates URLs scoped to the active tenant's primary domain.
 *
 * The tenant's primary hostname is resolved from the landlord database and
 * cached briefly. This ensures emails, exports, API responses, and Flutter
 * client configuration always point at the correct tenant host — never at
 * the platform host or another tenant.
 */
class DomainTenantUrlGenerator implements TenantUrlGenerator
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(
        private readonly CurrentTenant $currentTenant,
    ) {
    }

    public function to(string $path = '', array $parameters = [], ?bool $secure = null): string
    {
        $context = $this->currentTenant->getOrFail();
        $base = $this->resolvePrimaryHost($context->uuid);
        $secure ??= app()->environment('production');

        $scheme = $secure ? 'https' : 'http';
        $url = "{$scheme}://{$base}/".ltrim($path, '/');

        if (! empty($parameters)) {
            $url .= '?'.http_build_query($parameters);
        }

        return $url;
    }

    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return $this->onTenantOrigin(
            static fn (): string => route($name, $parameters, $absolute),
        );
    }

    public function baseUrl(): string
    {
        $context = $this->currentTenant->getOrFail();
        $host = $this->resolvePrimaryHost($context->uuid);
        $scheme = app()->environment('production') ? 'https' : 'http';

        return "{$scheme}://{$host}";
    }

    public function signedAsset(string $path, int $expiresInMinutes = 60): string
    {
        $context = $this->currentTenant->getOrFail();

        return $this->onTenantOrigin(
            static fn (): string => URL::temporarySignedRoute(
                'saas.tenant.asset',
                now()->addMinutes($expiresInMinutes),
                [
                    'tenant' => $context->uuid,
                    'path' => encrypt($path),
                ],
            ),
        );
    }

    public function apiBaseUrl(): string
    {
        return $this->baseUrl().'/api';
    }

    /**
     * Run URL generation against the tenant origin without leaking that
     * process-wide setting into the next request or queued notification.
     *
     * @template T
     * @param  \Closure(): T  $callback
     * @return T
     */
    private function onTenantOrigin(\Closure $callback): mixed
    {
        $context = $this->currentTenant->getOrFail();
        $host = $this->resolvePrimaryHost($context->uuid);
        $scheme = app()->environment('production') ? 'https' : 'http';
        $previousRoot = URL::to('/');

        URL::useOrigin("{$scheme}://{$host}");

        try {
            return $callback();
        } finally {
            URL::useOrigin($previousRoot);
        }
    }

    /**
     * Resolve the primary hostname for a tenant from the landlord database.
     * Cached per-tenant to avoid a query on every URL generation.
     */
    private function resolvePrimaryHost(string $tenantUuid): string
    {
        return Cache::remember(
            "saas:tenant_host:{$tenantUuid}",
            self::CACHE_TTL_SECONDS,
            function () use ($tenantUuid) {
                $domain = TenantDomain::query()
                    ->where('tenant_uuid', $tenantUuid)
                    ->where('is_primary', true)
                    ->routable()
                    ->first();

                if ($domain) {
                    return $domain->hostname;
                }

                // Fallback: construct subdomain from slug + configured suffix.
                $tenant = \Modules\Saas\Models\Landlord\Tenant::where('uuid', $tenantUuid)->first();
                $suffix = config('saas.hosts.tenant_suffix', '.localhost');

                if ($tenant) {
                    return $tenant->slug.$suffix;
                }

                // Last resort: use the context host directly.
                $context = $this->currentTenant->get();

                return $context?->host ?? 'localhost';
            }
        );
    }
}
