<?php

namespace Modules\Saas\Bootstrappers;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Config\Repository as Config;
use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Namespaces every cache key, lock and rate-limit key by tenant UUID.
 *
 * The plan calls out a real example already in this codebase (§2.1):
 * User::setCurrentTeamId() forgets a global key named `query_config_list_all`.
 * On a shared cache that key is one bucket for every school on the platform —
 * tenant A's config list served to tenant B, and tenant A's write invalidating
 * tenant B's cache.
 *
 * Changing `cache.prefix` and rebuilding the store fixes this for every caller
 * at once, including code we have not audited, which matters when there are
 * 690 service classes.
 */
class CacheBootstrapper implements TenantBootstrapper
{
    private ?string $originalPrefix = null;

    /** @var array<string, string> store name => original path, for file stores. */
    private array $originalPaths = [];

    private bool $active = false;

    public function __construct(
        private readonly CacheManager $cache,
        private readonly Config $config,
    ) {
    }

    public function bootstrap(TenantContext $context): void
    {
        $this->originalPrefix ??= $this->config->get('cache.prefix');

        // Covers redis, memcached, database and dynamodb, which all apply the
        // configured prefix to every key they write.
        $this->config->set('cache.prefix', $this->originalPrefix.$context->cachePrefix());

        $this->isolateFileStores($context);

        $this->refreshStores();

        $this->active = true;
    }

    /**
     * Laravel's file cache store IGNORES cache.prefix — it hashes the key
     * straight into a directory path. So on a file store the prefix provides
     * no isolation at all, and every school shares one cache namespace.
     *
     * That is not a theoretical concern here: .env.example ships with the file
     * cache as the default, so this is the configuration a first deployment
     * actually runs. Redis is the production target (plan §14), but the module
     * must not silently depend on that being done.
     *
     * Re-rooting the store's path gives file stores the same separation the
     * prefix gives everyone else.
     */
    private function isolateFileStores(TenantContext $context): void
    {
        foreach ((array) $this->config->get('cache.stores', []) as $name => $settings) {
            if (($settings['driver'] ?? null) !== 'file' || ! isset($settings['path'])) {
                continue;
            }

            $this->originalPaths[$name] ??= $settings['path'];

            $this->config->set(
                "cache.stores.{$name}.path",
                rtrim($this->originalPaths[$name], '/\\').DIRECTORY_SEPARATOR.$context->uuid
            );
        }
    }

    public function revert(): void
    {
        if (! $this->active) {
            return;
        }

        $this->config->set('cache.prefix', $this->originalPrefix);

        foreach ($this->originalPaths as $name => $path) {
            $this->config->set("cache.stores.{$name}.path", $path);
        }

        $this->refreshStores();

        $this->originalPrefix = null;
        $this->originalPaths = [];
        $this->active = false;
    }

    /**
     * Resolved stores capture the prefix at construction, so changing config
     * alone is not enough — the cached store instances have to be dropped.
     */
    private function refreshStores(): void
    {
        foreach (array_keys($this->config->get('cache.stores', [])) as $name) {
            $this->cache->forgetDriver($name);
        }
    }
}
