<?php

namespace Modules\Saas\Contracts;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Tenant-aware storage abstraction (plan §3.3, §7).
 *
 * Core ERP code depends on this contract for any file operation that must be
 * scoped to the active tenant. It guarantees:
 *
 *  - Paths are always relative to the tenant's storage prefix.
 *  - A tenant can never read, write, or delete files outside its prefix.
 *  - The underlying disk is re-rooted by the FilesystemBootstrapper during a
 *    tenant request/job, so this contract adds a validation layer on top.
 *
 * Do NOT use this contract for control-plane/landlord assets (templates,
 * shared branding, platform exports). Those use a dedicated non-tenant disk.
 */
interface TenantStorage
{
    /**
     * Get the filesystem disk rooted at the current tenant's prefix.
     *
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function disk(?string $diskName = null): Filesystem;

    /**
     * Build a tenant-scoped path. Guarantees the result starts with the
     * tenant prefix and contains no traversal sequences.
     *
     * @throws \InvalidArgumentException on path traversal attempts.
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function path(string $relativePath): string;

    /**
     * Get the storage prefix for the active tenant (e.g. "tenants/{uuid}").
     *
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function prefix(): string;

    /**
     * Generate a temporary signed URL for a tenant file.
     *
     * @param  int  $expiresInMinutes  URL validity in minutes.
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function temporaryUrl(string $relativePath, int $expiresInMinutes = 60): string;

    /**
     * Assert that a path belongs to the active tenant. Used by download
     * controllers and export services before streaming a file.
     *
     * @throws \Modules\Saas\Exceptions\EntitlementDenied when path escapes tenant prefix.
     */
    public function assertPathBelongsToTenant(string $path): void;

    /**
     * Get the total storage usage in bytes for the active tenant.
     */
    public function usageBytes(): int;

    /**
     * Delete all files for a tenant (offboarding). Irreversible.
     * Called only by the deletion workflow after confirmation and delay.
     */
    public function purgeAll(): void;
}
