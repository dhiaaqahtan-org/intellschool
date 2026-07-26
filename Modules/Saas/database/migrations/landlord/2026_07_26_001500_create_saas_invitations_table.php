<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant user invitations (plan §5.1).
 *
 * Invitations are hashed tokens that allow a user to join a tenant.
 * The token is hashed so a database leak does not expose valid invitations.
 */
return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->create('saas_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Which tenant the invitation is for.
            $table->uuid('tenant_uuid');
            $table->foreign('tenant_uuid')
                ->references('uuid')->on('saas_tenants')
                ->cascadeOnDelete();

            // Hashed invitation token (never store plaintext).
            $table->string('token_hash', 64)->unique();

            // Invited user details.
            $table->string('email');
            $table->string('name')->nullable();

            // Role the user will receive upon acceptance.
            $table->string('role', 50)->default('user');

            // Who sent the invitation (platform operator or tenant admin).
            $table->string('invited_by_type', 20)->default('tenant'); // tenant, platform
            $table->string('invited_by_email')->nullable();

            // Lifecycle.
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_uuid', 'email']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('saas_invitations');
    }
};
