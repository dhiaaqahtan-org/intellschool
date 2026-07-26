<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant ownership records (plan §5.1, §5.4).
 *
 * Links a tenant to its owner(s). The owner is a user in the TENANT database,
 * not the landlord database. We store the tenant-local user UUID and normalized
 * email here for cross-reference, but there is NO database foreign key because
 * the users table lives in a different database.
 */
return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->create('saas_tenant_owners', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Which tenant this owner belongs to.
            $table->uuid('tenant_uuid');
            $table->foreign('tenant_uuid')
                ->references('uuid')->on('saas_tenants')
                ->cascadeOnDelete();

            // Reference to the user in the tenant database (NOT a FK).
            $table->uuid('tenant_user_uuid')->nullable();
            $table->string('email');

            // Owner role: owner, admin, billing
            $table->string('role', 20)->default('owner');

            // Invitation/acceptance status.
            $table->string('status', 20)->default('invited'); // invited, active, removed
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('removed_at')->nullable();

            $table->timestamps();

            $table->unique(['tenant_uuid', 'email']);
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('saas_tenant_owners');
    }
};
