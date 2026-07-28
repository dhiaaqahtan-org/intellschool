<?php

namespace Modules\Saas\Domain\Entitlements;

/**
 * Immutable, transport-safe view of a tenant's effective feature grants.
 */
final readonly class EntitlementSnapshot
{
    /**
     * @param  array<string, array{enabled: bool, limit: ?int, source: string}>  $features
     */
    public function __construct(
        public ?string $planCode,
        public array $features,
        public ?string $cachedAt = null,
    ) {
    }

    public static function fromArray(array $snapshot): self
    {
        return new self(
            planCode: $snapshot['plan_code'] ?? $snapshot['plan_id'] ?? null,
            features: $snapshot['features'] ?? [],
            cachedAt: $snapshot['cached_at'] ?? null,
        );
    }

    public function has(string $featureCode): bool
    {
        return ($this->features[$featureCode]['enabled'] ?? false) === true;
    }

    public function remaining(string $featureCode): ?int
    {
        if (! $this->has($featureCode)) {
            return 0;
        }

        return $this->features[$featureCode]['limit'] ?? null;
    }

    public function limits(): array
    {
        $limits = [];

        foreach ($this->features as $code => $feature) {
            if (array_key_exists('limit', $feature) && $feature['limit'] !== null) {
                $limits[$code] = $feature['limit'];
            }
        }

        return $limits;
    }

    public function toArray(): array
    {
        return [
            'plan_id' => $this->planCode,
            'plan_code' => $this->planCode,
            'features' => $this->features,
            'limits' => $this->limits(),
            'cached_at' => $this->cachedAt,
        ];
    }
}
