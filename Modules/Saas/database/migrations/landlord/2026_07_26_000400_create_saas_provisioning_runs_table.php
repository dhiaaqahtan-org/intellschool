<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One provisioning attempt, step by step (plan §11).
 *
 * `idempotency_key` is unique. That single constraint is what makes retrying
 * safe: a replayed signup, a duplicated queue message or an impatient customer
 * clicking twice all collide on the same key and reuse the same run instead of
 * allocating a second database.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_provisioning_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_uuid')->index();

            // The idempotency guarantee.
            $table->string('idempotency_key', 128)->unique();

            $table->string('state', 40)->index();
            $table->string('step', 40)->nullable();

            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Operator-facing summary. Must never contain credentials,
            // connection strings or personal data — see the redaction helper
            // in TenantProvisioner.
            $table->text('error_summary')->nullable();

            // Per-step outcomes: {step: {started_at, finished_at, attempts, ok}}
            $table->json('steps')->nullable();

            $table->timestamps();

            $table->index(['tenant_uuid', 'state']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_provisioning_runs');
    }
};
