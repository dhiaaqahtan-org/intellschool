<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Support access sessions (plan آ§7, آ§12).
 *
 * Platform operators access a tenant ONLY through an approved, time-limited,
 * fully audited session. This table records every session for compliance
 * and visibility.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }
    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_support_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Which tenant is being accessed.
            $table->uuid('tenant_uuid');
            $table->foreign('tenant_uuid')
                ->references('uuid')->on('saas_tenants')
                ->cascadeOnDelete();

            // The platform operator requesting access.
            $table->unsignedBigInteger('operator_id');
            $table->string('operator_email');

            // Who approved the access (may be the tenant owner or another operator).
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_email')->nullable();

            // Why access was requested. Required, free-text, audited.
            $table->text('reason');

            // Access scope: 'read' (default) or 'write'.
            $table->string('scope', 10)->default('read');

            // Lifecycle timestamps.
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_by')->nullable();

            // Status: requested, approved, active, expired, revoked, denied.
            $table->string('status', 20)->default('requested');

            $table->timestamps();

            $table->index(['tenant_uuid', 'status']);
            $table->index(['operator_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_support_sessions');
    }
};
