<?php

namespace Modules\Saas\Services\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\URL;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantStorage;
use Modules\Saas\Exceptions\EntitlementDenied;

/**
 * Default TenantStorage implementation backed by Laravel's filesystem manager.
 *
 * The FilesystemBootstrapper already re-roots configured disks under the
 * tenant prefix during a request/job. This service adds:
 *
 *  - Path traversal validation (defense in depth).
 *  - A stable contract for ERP code that should not know about bootstrappers.
 *  - Temporary URL generation with tenant identity in the signature.
 *  - Usage calculation and purge for offboarding.
 */
class FilesystemTenantStorage implements TenantStorage
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly FilesystemManager $filesystemManager,
    ) {
    }

    public function disk(?string $diskName = null): Filesystem
    {
        // Ensure a tenant is active before handing out a disk.
        $this->currentTenant->getOrFail();

        return $this->filesystemManager->disk($diskName);
    }

    public function path(string $relativePath): string
    {
        $context = $this->currentTenant->getOrFail();
        $this->rejectTraversal($relativePath);

        return $context->storagePrefix().'/'.ltrim($relativePath, '/');
    }

    public function prefix(): string
    {
        return $this->currentTenant->getOrFail()->storagePrefix();
    }

    public function temporaryUrl(string $relativePath, int $expiresInMinutes = 60): string
    {
        $context = $this->currentTenant->getOrFail();
        $this->rejectTraversal($relativePath);

        $disk = $this->filesystemManager->disk();

        // If the disk driver supports temporary URLs (S3, GCS), use native.
        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl(
                    $this->path($relativePath),
                    now()->addMinutes($expiresInMinutes)
                );
            } catch (\RuntimeException) {
                // Fall through to signed route URL for local disks.
            }
        }

        // Local disk fallback: generate a signed route URL.
        return URL::temporarySignedRoute(
            'saas.tenant.download',
            now()->addMinutes($expiresInMinutes),
            [
                'tenant' => $context->uuid,
                'path' => encrypt($relativePath),
            ]
        );
    }

    public function assertPathBelongsToTenant(string $path): void
    {
        $context = $this->currentTenant->getOrFail();
        $prefix = $context->storagePrefix();

        $normalized = str_replace('\\', '/', $path);

        if (! str_starts_with($normalized, $prefix.'/') && $normalized !== $prefix) {
            throw EntitlementDenied::withMessage(
                'storage.access',
                "Path [{$path}] does not belong to the active tenant."
            );
        }

        // Reject any traversal that could escape after prefix stripping.
        if (str_contains($normalized, '..')) {
            throw EntitlementDenied::withMessage(
                'storage.access',
                'Path traversal detected.'
            );
        }
    }

    public function usageBytes(): int
    {
        $disk = $this->disk();
        $prefix = $this->prefix();

        $total = 0;

        foreach ($disk->allFiles($prefix) as $file) {
            $total += $disk->size($file);
        }

        return $total;
    }

    public function purgeAll(): void
    {
        $context = $this->currentTenant->getOrFail();
        $prefix = $context->storagePrefix();

        $disks = (array) config('saas.storage.tenant_disks', ['local', 'public']);

        foreach ($disks as $diskName) {
            $disk = $this->filesystemManager->disk($diskName);
            $disk->deleteDirectory($prefix);
        }

        logger()->warning('Tenant storage purged', [
            'tenant_uuid' => $context->uuid,
            'prefix' => $prefix,
            'disks' => $disks,
        ]);
    }

    /**
     * Reject path traversal sequences that could escape the tenant prefix.
     */
    private function rejectTraversal(string $path): void
    {
        $normalized = str_replace('\\', '/', $path);

        if (str_contains($normalized, '..') || str_starts_with($normalized, '/')) {
            throw new \InvalidArgumentException(
                "Invalid storage path [{$path}]: traversal or absolute paths are not allowed."
            );
        }
    }
}
