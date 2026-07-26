<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant feature overrides (plan §8).
 *
 * Overrides sit ON TOP of plan features: they can grant a feature the plan
 * lacks, or revoke one the plan includes, with a validity window and an
 * audit reason. The effective entitlement snapshot merges plan + overrides.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_tenant_entitlements', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid')->index();

            $table->string('feature_code', 80);

            // Override direction: grant or revoke.
            $table->boolean('enabled')->default(true);

            // Optional capacity override (null = use plan default).
            $table->unsignedInteger('limit_value')->nullable();

            // Why this override exists: 'support_grant', 'trial_extension',
            // 'enterprise_contract', 'migration_grace'.
            $table->string('source', 40)->default('manual');
            $table->text('reason')->nullable();

            // Validity window.
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            $table->string('granted_by', 64)->nullable();
            $table->timestamps();

            $table->unique(['tenant_uuid', 'feature_code']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_tenant_entitlements');
    }
};
