<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant usage counters for metered plan limits (plan §7, §8).
 *
 * Metrics: 'active_students', 'active_staff', 'storage_bytes',
 * 'campuses', 'api_calls_month'. Counters are aggregated periodically
 * and checked at write time for hard limits.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_usage_counters', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid')->index();

            // Metric identifier, e.g. 'active_students', 'storage_bytes'.
            $table->string('metric', 60);

            // Period bucket: 'all_time', '2026-07', etc.
            $table->string('period', 20)->default('all_time');

            $table->unsignedBigInteger('quantity')->default(0);

            $table->timestamp('last_aggregated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_uuid', 'metric', 'period']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_usage_counters');
    }
};
