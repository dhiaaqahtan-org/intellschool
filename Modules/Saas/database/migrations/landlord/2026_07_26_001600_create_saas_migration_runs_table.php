<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration run history (plan §5.1, Phase 4).
 *
 * Records every migration run per-tenant for:
 *  - Tracking schema versions across many tenant databases.
 *  - Canary/batch rollout with pause/resume.
 *  - Failure isolation and recovery.
 *  - Audit trail of schema changes.
 */
return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->create('saas_migration_runs', function (Blueprint $table) {
            $table->id();

            // Which tenant this migration run is for.
            $table->uuid('tenant_uuid');
            $table->foreign('tenant_uuid')
                ->references('uuid')->on('saas_tenants')
                ->cascadeOnDelete();

            // Release/version identifier for this migration batch.
            $table->string('release', 50);

            // Application version at the time of migration.
            $table->string('application_version', 50)->nullable();

            // Run status: pending, running, completed, failed, rolled_back
            $table->string('status', 20)->default('pending');

            // Timing.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Results.
            $table->unsignedInteger('migrations_run')->default(0);
            $table->text('error_summary')->nullable();

            // For rollback reference.
            $table->string('rollback_to_version', 100)->nullable();
            $table->timestamp('rolled_back_at')->nullable();

            // Batch grouping for canary rollouts.
            $table->unsignedInteger('batch_number')->nullable();

            $table->timestamps();

            $table->index(['tenant_uuid', 'status']);
            $table->index(['release', 'status']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('saas_migration_runs');
    }
};
