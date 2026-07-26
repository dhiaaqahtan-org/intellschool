<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Tenant user invitation (plan §5.1).
 *
 * Invitations allow users to join a tenant. The token is stored hashed
 * so a database leak does not expose valid invitation links.
 */
class Invitation extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_invitations';

    protected $fillable = [
        'uuid', 'tenant_uuid', 'token_hash', 'email', 'name', 'role',
        'invited_by_type', 'invited_by_email', 'expires_at', 'accepted_at', 'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = ['token_hash'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    /**
     * Create a new invitation with a secure token.
     *
     * @return array{invitation: self, token: string} The plaintext token is
     *                returned ONLY once — it is never stored.
     */
    public static function createWithToken(array $attributes): array
    {
        $token = Str::random(64);

        $invitation = static::create(array_merge($attributes, [
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ]));

        return ['invitation' => $invitation, 'token' => $token];
    }

    /**
     * Find an invitation by its plaintext token.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token_hash', hash('sha256', $token))->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isAccepted() && ! $this->isRevoked();
    }

    public function accept(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public function scopeValid($query)
    {
        return $query->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForTenant($query, string $tenantUuid)
    {
        return $query->where('tenant_uuid', $tenantUuid);
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
