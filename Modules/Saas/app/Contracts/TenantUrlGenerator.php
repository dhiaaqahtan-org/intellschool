<?php

namespace Modules\Saas\Contracts;

/**
 * Tenant-aware URL generation (plan §3.3, §7).
 *
 * Generates URLs that are scoped to the current tenant's domain. Core ERP
 * code uses this contract instead of raw url()/route() calls when the URL
 * must point at the tenant's own host (e.g. download links in emails,
 * public file URLs, API base URLs sent to the Flutter client).
 *
 * The tenant's primary domain is resolved from the landlord database and
 * cached. This contract never constructs a URL from request input.
 */
interface TenantUrlGenerator
{
    /**
     * Generate an absolute URL on the tenant's primary domain.
     *
     * @param  string  $path  Path relative to the tenant root (no leading slash required).
     * @param  array  $parameters  Query string parameters.
     * @param  bool  $secure  Force HTTPS. Defaults to true in production.
     *
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function to(string $path = '', array $parameters = [], ?bool $secure = null): string;

    /**
     * Generate a URL to a named route on the tenant's domain.
     *
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string;

    /**
     * Get the tenant's primary base URL (scheme + host, no trailing slash).
     *
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function baseUrl(): string;

    /**
     * Generate a signed/temporary URL for a tenant asset (file download,
     * export, media). Includes tenant identity in the signature so a URL
     * from Tenant A cannot be replayed on Tenant B.
     *
     * @param  int  $expiresInMinutes  Validity window.
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function signedAsset(string $path, int $expiresInMinutes = 60): string;

    /**
     * Get the API base URL for the Flutter/mobile client.
     * Includes tenant identity so the client can validate it matches
     * its stored tenant context.
     */
    public function apiBaseUrl(): string;
}
