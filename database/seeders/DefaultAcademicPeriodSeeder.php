<?php

namespace Database\Seeders;

use App\Models\Academic\Period;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Ensure every institute has a usable academic period.
 *
 * UserConfig requires a period before most application API routes can run.
 * This seeder is deliberately idempotent so it is safe on both fresh and
 * existing deployments.
 */
class DefaultAcademicPeriodSeeder extends Seeder
{
    public function run(): void
    {
        Team::query()
            ->orderBy('id')
            ->each(function (Team $team): void {
                $period = Period::query()
                    ->where('team_id', $team->id)
                    ->where('is_default', true)
                    ->orderBy('id')
                    ->first();

                if ($period) {
                    return;
                }

                $period = Period::query()
                    ->where('team_id', $team->id)
                    ->orderBy('id')
                    ->first();

                if ($period) {
                    $period->forceFill(['is_default' => true])->save();
                    $this->command?->info("Existing academic period set as default for {$team->name}.");

                    return;
                }

                $startYear = now()->month >= 7 ? now()->year : now()->year - 1;
                $endYear = $startYear + 1;
                $periodConfig = config('provisioning.academic_period', []);
                $startDate = Carbon::parse(data_get($periodConfig, 'start_date') ?: "{$startYear}-07-01");
                $endDate = Carbon::parse(data_get($periodConfig, 'end_date') ?: "{$endYear}-06-30");

                Period::query()->forceCreate([
                    'team_id' => $team->id,
                    'name' => data_get($periodConfig, 'name') ?: "Academic Year {$startYear}-{$endYear}",
                    'code' => data_get($periodConfig, 'code') ?: "AY-{$startYear}-{$endYear}",
                    'shortcode' => data_get($periodConfig, 'shortcode') ?: "{$startYear}-{$endYear}",
                    'alias' => data_get($periodConfig, 'alias') ?: "Academic Year {$startYear}-{$endYear}",
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'is_default' => true,
                    'description' => 'Default academic period created during application provisioning.',
                    'config' => ['enable_registration' => true],
                ]);

                $this->command?->info("Default academic period created for {$team->name}.");
            });
    }
}
