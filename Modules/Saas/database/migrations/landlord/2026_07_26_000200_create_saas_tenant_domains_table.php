<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hostnames that route to a tenant.
 *
 * `hostname` is globally unique and stored already normalised (lowercase,
 * punycode, no port, no trailing dot) so that lookup is a single exact match.
 * Never do suffix or LIKE matching against this column.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid')->index();

            // 253 is the DNS maximum. Unique across every tenant — two
            // tenants claiming one hostname is an isolation failure.
            $table->string('hostname', 253)->unique();

            // 'subdomain' = issued by us on the platform suffix, trusted on
            // creation. 'custom' = customer-owned, untrusted until verified.
            $table->string('type', 20)->default('subdomain');

            $table->boolean('is_primary')->default(false);

            // A custom domain MUST NOT route until verified_at is set.
            $table->string('verification_token', 64)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->string('tls_status', 20)->default('pending');
            $table->timestamp('tls_issued_at')->nullable();

            $table->timestamps();

            // Resolution index: only verified domains of a live tenant.
            $table->index(['tenant_uuid', 'is_primary']);
            $table->index(['type', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_tenant_domains');
    }
};
