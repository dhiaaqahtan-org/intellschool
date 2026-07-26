<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only record of control-plane actions (plan §5.1, §7).
 *
 * Covers tenant lifecycle, domain changes, entitlement overrides, support
 * sessions and platform-operator activity. Nothing in here should ever be
 * updated or deleted by application code — that is what makes it evidence.
 *
 * Payloads must be redacted before they land here. An audit table full of
 * student names is a privacy incident waiting for a subpoena.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_audit_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('tenant_uuid')->nullable()->index();

            // Dotted action name, e.g. tenant.suspended, domain.verified,
            // support.session.started.
            $table->string('action', 80)->index();

            // Who did it: platform user, tenant user, system, or console.
            $table->string('actor_type', 32)->default('system');
            $table->string('actor_id', 64)->nullable();
            $table->string('actor_label')->nullable();

            $table->string('subject_type', 64)->nullable();
            $table->string('subject_id', 64)->nullable();

            $table->string('ip_hash', 64)->nullable();
            $table->string('correlation_id', 64)->nullable()->index();

            $table->json('context')->nullable();

            // No updated_at: these rows are never modified.
            $table->timestamp('created_at')->index();

            $table->index(['tenant_uuid', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_audit_events');
    }
};
