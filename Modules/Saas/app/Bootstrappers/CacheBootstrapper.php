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

    private bool $active = false;

    public function __construct(
        private readonly CacheManager $cache,
        private readonly Config $config,
    ) {
    }

    public function bootstrap(TenantContext $context): void
    {
        $this->originalPrefix ??= $this->config->get('cache.prefix');

        $this->config->set('cache.prefix', $this->originalPrefix.$context->cachePrefix());

        $this->refreshStores();

        $this->active = true;
    }

    public function revert(): void
    {
        if (! $this->active) {
            return;
        }

        $this->config->set('cache.prefix', $this->originalPrefix);
        $this->refreshStores();

        $this->originalPrefix = null;
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
