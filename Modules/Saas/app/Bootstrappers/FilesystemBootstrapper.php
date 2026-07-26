<?php

namespace Modules\Saas\Bootstrappers;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\FilesystemManager;
use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Roots every configured disk inside tenants/{uuid}/.
 *
 * Two properties this buys (plan §7):
 *
 *  - A path traversal or a guessed filename inside tenant A cannot reach
 *    tenant B, because A's disk root does not contain B's files.
 *  - Offboarding deletes one prefix rather than hunting for rows across
 *    shared storage.
 *
 * Downloads must still resolve the tenant *before* touching a path. Rooting
 * the disk protects the storage layer; it does not excuse a controller that
 * takes a path from the request.
 */
class FilesystemBootstrapper implements TenantBootstrapper
{
    /** @var array<string, array> Original disk config, keyed by disk name. */
    private array $originalDisks = [];

    private bool $active = false;

    public function __construct(
        private readonly FilesystemManager $filesystem,
        private readonly Config $config,
    ) {
    }

    public function bootstrap(TenantContext $context): void
    {
        $disks = (array) $this->config->get('saas.storage.tenant_disks', ['local', 'public']);

        foreach ($disks as $disk) {
            $settings = $this->config->get("filesystems.disks.{$disk}");

            if (! is_array($settings)) {
                continue;
            }

            $this->originalDisks[$disk] ??= $settings;
            $original = $this->originalDisks[$disk];

            $suffix = '/'.$context->storagePrefix();

            $updated = $original;

            // Local-style disks are rooted by path; object stores are rooted
            // by key prefix. Both need handling — the ERP uses local disks
            // today and S3 is the production target.
            if (isset($original['root'])) {
                $updated['root'] = rtrim($original['root'], '/\\').$suffix;
            }

            if (array_key_exists('prefix', $original) || ($original['driver'] ?? null) === 's3') {
                $updated['prefix'] = trim(($original['prefix'] ?? '').$suffix, '/');
            }

            if (isset($original['url'])) {
                $updated['url'] = rtrim($original['url'], '/').$suffix;
            }

            $this->config->set("filesystems.disks.{$disk}", $updated);
            $this->filesystem->forgetDisk($disk);
        }

        $this->active = true;
    }

    public function revert(): void
    {
        if (! $this->active) {
            return;
        }

        foreach ($this->originalDisks as $disk => $settings) {
            $this->config->set("filesystems.disks.{$disk}", $settings);
            $this->filesystem->forgetDisk($disk);
        }

        $this->originalDisks = [];
        $this->active = false;
    }
}
