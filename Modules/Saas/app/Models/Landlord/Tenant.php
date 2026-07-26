<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;

/**
 * @property string $uuid
 * @property string $slug
 * @property TenantStatus $status
 * @property ProvisioningState $provisioning_state
 */
class Tenant extends LandlordModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'saas_tenants';

    protected $fillable = [
        'uuid', 'slug', 'display_name', 'legal_name', 'status', 'tier',
        'region', 'locale', 'timezone', 'provisioning_state',
        'trial_ends_at', 'suspended_at', 'cancelled_at', 'purge_after', 'meta',
    ];

    protected $casts = [
        'status' => TenantStatus::class,
        'provisioning_state' => ProvisioningState::class,
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'purge_after' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * HasUuids would otherwise treat `uuid` as the route key and primary key.
     * We keep the auto-increment id internally and expose the uuid.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_uuid', 'uuid');
    }

    public function database(): HasOne
    {
        return $this->hasOne(TenantDatabase::class, 'tenant_uuid', 'uuid');
    }

    public function provisioningRuns(): HasMany
    {
        return $this->hasMany(ProvisioningRun::class, 'tenant_uuid', 'uuid');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_uuid', 'uuid')
            ->latestOfMany('created_at');
    }

    public function owners(): HasMany
    {
        return $this->hasMany(TenantOwner::class, 'tenant_uuid', 'uuid');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class, 'tenant_uuid', 'uuid');
    }

    public function primaryDomain(): ?TenantDomain
    {
        return $this->domains->firstWhere('is_primary', true)
            ?? $this->domains->first();
    }

    /**
     * Build the immutable context used for the rest of the request.
     *
     * Fails loudly when the tenant has no database record: a tenant that
     * resolves but has nowhere to connect must not silently fall through to
     * the application's default connection.
     */
    public function toContext(string $host): TenantContext
    {
        $database = $this->database;

        if ($database === null) {
            throw new \RuntimeException(
                "Tenant [{$this->uuid}] has no database record; refusing to resolve."
            );
        }

        return new TenantContext(
            uuid: $this->uuid,
            slug: $this->slug,
            status: $this->status,
            databaseName: $database->database_name,
            connectionName: config('saas.database.tenant_connection', 'tenant'),
            host: $host,
            cluster: $database->cluster,
            secretRef: $database->secret_ref,
            locale: $this->locale ?? 'en',
            timezone: $this->timezone ?? 'UTC',
            region: $this->region,
        );
    }

    /**
     * Only tenants that finished provisioning may serve traffic. A tenant
     * still migrating has a half-built schema; serving it produces confusing
     * errors at best and partial writes at worst.
     */
    public function isServable(): bool
    {
        return $this->provisioning_state === ProvisioningState::Ready
            && $this->status->canServeRequests();
    }

    public function scopeServable($query)
    {
        return $query
            ->where('provisioning_state', ProvisioningState::Ready->value)
            ->whereIn('status', [
                TenantStatus::Active->value,
                TenantStatus::Suspended->value,
                TenantStatus::Cancelled->value,
            ]);
    }
}
