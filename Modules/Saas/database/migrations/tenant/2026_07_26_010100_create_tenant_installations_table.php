<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Runs INSIDE each tenant database (plan §5.2).
 *
 * Two jobs:
 *
 * 1. Self-identification. A tenant database that knows its own UUID can be
 *    checked against the connection that opened it. If they disagree, the
 *    application has connected to the wrong database and must refuse to
 *    continue — this is the last line of defence behind host resolution, and
 *    it is what VerifyTenantIsolation asserts.
 *
 * 2. Schema version tracking, so batched migration rollout knows which
 *    tenants are behind without opening every database.
 *
 * Note there is NO tenant_id column added to existing ERP tables. The physical
 * database is the boundary; team_id remains the campus boundary inside it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_installations', function (Blueprint $table) {
            $table->id();

            // Must match the tenant whose connection opened this database.
            $table->uuid('tenant_uuid')->unique();
            $table->string('tenant_slug', 63);

            $table->string('schema_version', 255)->nullable();
            $table->string('app_version', 40)->nullable();

            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('last_migrated_at')->nullable();

            // Cached entitlement snapshot so the ERP keeps working through a
            // control-plane outage. Authoritative source stays the landlord.
            $table->json('entitlements_snapshot')->nullable();
            $table->timestamp('entitlements_synced_at')->nullable();

            // Drives the in-app banner during suspension or grace, without a
            // landlord round trip on every request.
            $table->string('access_state', 32)->default('active');
            $table->text('access_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_installations');
    }
};
