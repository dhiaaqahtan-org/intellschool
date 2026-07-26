<?php

namespace Modules\Saas\Domain\Tenancy;

use Modules\Saas\Enums\TenantStatus;

/**
 * Immutable snapshot of the tenant a request or job is executing inside.
 *
 * Immutability is the point. Once resolved, nothing downstream — a controller,
 * a service, a request payload, a queued job — can mutate which tenant is
 * active. Switching tenants means building a new context and handing it to the
 * connection manager, which is an explicit, auditable act.
 *
 * The UUID is the only identifier that may be used to namespace anything
 * (cache keys, storage prefixes, queue payloads, log context). The numeric
 * primary key is never exposed and never used for scoping — numeric IDs
 * collide across tenant databases by design.
 */
final readonly class TenantContext
{
    /**
     * @param  string  $secretRef  A POINTER to the database credentials in a
     *                             secret manager, never the credentials. Kept
     *                             on the context so resolution costs one
     *                             landlord query per request rather than two.
     *                             Excluded from toLogContext() and
     *                             toQueuePayload() on purpose.
     */
    public function __construct(
        public string $uuid,
        public string $slug,
        public TenantStatus $status,
        public string $databaseName,
        public string $connectionName,
        public string $host,
        public string $cluster = 'default',
        public string $secretRef = '',
        public string $locale = 'en',
        public string $timezone = 'UTC',
        public ?string $region = null,
    ) {
    }

    /**
     * Cache keys, lock names and rate-limit keys must all start with this.
     * A global key such as the existing `query_config_list_all` is a
     * cross-tenant leak (plan §7).
     */
    public function cachePrefix(): string
    {
        return "t:{$this->uuid}:";
    }

    /**
     * Object storage paths start here. Downloads must resolve the tenant
     * before touching a path, never the other way round.
     */
    public function storagePrefix(): string
    {
        return "tenants/{$this->uuid}";
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::Active;
    }

    public function canWrite(): bool
    {
        return $this->status->canWrite();
    }

    /**
     * Safe to attach to logs, traces and error reports. Contains no
     * credentials and no student data.
     */
    public function toLogContext(): array
    {
        return [
            'tenant_uuid' => $this->uuid,
            'tenant_slug' => $this->slug,
            'tenant_host' => $this->host,
            'tenant_status' => $this->status->value,
        ];
    }

    /**
     * Minimal payload for queue jobs. The worker rebuilds the full context
     * from the landlord database — it never trusts a serialised connection
     * name or database name off the wire.
     */
    public function toQueuePayload(): array
    {
        return ['tenant_uuid' => $this->uuid];
    }

    public function is(?self $other): bool
    {
        return $other !== null && hash_equals($this->uuid, $other->uuid);
    }
}
