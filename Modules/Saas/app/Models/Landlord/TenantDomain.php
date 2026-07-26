<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Saas\Domain\Tenancy\HostNormalizer;

/**
 * @property string $hostname  Always stored normalised.
 * @property string $type      subdomain|custom
 */
class TenantDomain extends LandlordModel
{
    public const TYPE_SUBDOMAIN = 'subdomain';
    public const TYPE_CUSTOM = 'custom';

    protected $table = 'saas_tenant_domains';

    protected $fillable = [
        'tenant_uuid', 'hostname', 'type', 'is_primary',
        'verification_token', 'verified_at', 'tls_status', 'tls_issued_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
        'tls_issued_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Normalise on the way in, so the unique index actually prevents two
        // tenants claiming `Example.COM` and `example.com.` separately, and so
        // lookup never has to normalise the stored side.
        static::saving(function (self $domain) {
            $normalized = HostNormalizer::normalize($domain->hostname);

            if ($normalized === null) {
                throw new \InvalidArgumentException(
                    "Refusing to store unparseable hostname [{$domain->hostname}]."
                );
            }

            $domain->hostname = $normalized;
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    /**
     * A subdomain we issued is trusted on creation. A customer-owned domain
     * is untrusted until DNS ownership is proven (plan §6 rule 5) — otherwise
     * anyone could point their DNS at us and claim someone else's traffic.
     */
    public function isRoutable(): bool
    {
        return $this->type === self::TYPE_SUBDOMAIN || $this->verified_at !== null;
    }

    public function scopeRoutable($query)
    {
        return $query->where(function ($q) {
            $q->where('type', self::TYPE_SUBDOMAIN)
                ->orWhereNotNull('verified_at');
        });
    }
}
