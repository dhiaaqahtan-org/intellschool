<?php

namespace Modules\Saas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Domain\Usage\UsageMetric;

/**
 * Tracks and enforces metered plan limits (plan §8).
 *
 * Usage counters live in the landlord database (saas_usage_counters) and are
 * aggregated periodically from tenant databases. Hard limits are checked at
 * write time; soft limits produce warnings.
 *
 * This service is the enforcement point — hiding a button in the UI is NOT
 * a limit. The actual write path calls assertCapacity() before inserting.
 */
class UsageMeter
{
    public function __construct(
        private readonly CurrentTenant $tenant,
        private readonly EntitlementChecker $entitlements,
    ) {
    }

    /**
     * Current usage for a metric in the active tenant.
     */
    public function current(string $metric, string $period = 'all_time'): int
    {
        $uuid = $this->tenant->uuid();

        if ($uuid === null) {
            return 0;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        return (int) DB::connection($connection)
            ->table('saas_usage_counters')
            ->where('tenant_uuid', $uuid)
            ->where('metric', $metric)
            ->where('period', $period)
            ->value('quantity') ?? 0;
    }

    /**
     * Remaining capacity for a metric, or null if unlimited.
     */
    public function remaining(string $metric, string $period = 'all_time'): ?int
    {
        $limit = $this->entitlements->remaining($this->metricToFeatureCode($metric));

        if ($limit === null) {
            return null; // unlimited
        }

        $used = $this->current($metric, $period);

        return max(0, $limit - $used);
    }

    /**
     * Assert that capacity exists before a write. Throws when exhausted.
     *
     * @throws \Modules\Saas\Exceptions\EntitlementDenied
     */
    public function assertCapacity(string $metric, int $additionalUnits = 1, string $period = 'all_time'): void
    {
        $remaining = $this->remaining($metric, $period);

        // null = unlimited, always passes.
        if ($remaining === null) {
            return;
        }

        if ($remaining < $additionalUnits) {
            $featureCode = $this->metricToFeatureCode($metric);
            $this->entitlements->ensure($featureCode);
        }
    }

    /**
     * Increment a usage counter. Called after a successful write.
     */
    public function increment(string $metric, int $amount = 1, string $period = 'all_time'): void
    {
        $uuid = $this->tenant->uuid();

        if ($uuid === null) {
            return;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        DB::connection($connection)
            ->table('saas_usage_counters')
            ->upsert(
                [
                    'tenant_uuid' => $uuid,
                    'metric' => $metric,
                    'period' => $period,
                    'quantity' => $amount,
                    'last_aggregated_at' => now(),
                    'updated_at' => now(),
                ],
                ['tenant_uuid', 'metric', 'period'],
                [
                    'quantity' => DB::raw("quantity + {$amount}"),
                    'last_aggregated_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }

    /**
     * Decrement a usage counter. Called after a delete.
     */
    public function decrement(string $metric, int $amount = 1, string $period = 'all_time'): void
    {
        $uuid = $this->tenant->uuid();

        if ($uuid === null) {
            return;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        DB::connection($connection)
            ->table('saas_usage_counters')
            ->where('tenant_uuid', $uuid)
            ->where('metric', $metric)
            ->where('period', $period)
            ->where('quantity', '>=', $amount)
            ->decrement('quantity', $amount);
    }

    /**
     * Set an absolute value. Used by the periodic aggregation job that
     * counts actual records in the tenant database.
     */
    public function setAbsolute(string $metric, int $quantity, string $period = 'all_time'): void
    {
        $uuid = $this->tenant->uuid();

        if ($uuid === null) {
            return;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        DB::connection($connection)
            ->table('saas_usage_counters')
            ->upsert(
                [
                    'tenant_uuid' => $uuid,
                    'metric' => $metric,
                    'period' => $period,
                    'quantity' => $quantity,
                    'last_aggregated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['tenant_uuid', 'metric', 'period'],
                [
                    'quantity' => $quantity,
                    'last_aggregated_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }

    /**
     * Map a usage metric to its entitlement feature code.
     */
    private function metricToFeatureCode(string $metric): string
    {
        return UsageMetric::tryFrom($metric)?->featureCode() ?? $metric;
    }
}
