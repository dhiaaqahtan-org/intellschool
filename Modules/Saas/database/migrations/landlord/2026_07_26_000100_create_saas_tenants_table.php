<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Control-plane record for one SaaS customer.
 *
 * This table holds NO student, academic, finance, payroll, health, discipline
 * or document data (plan §5.1). If a column here would ever contain school
 * content, it belongs in the tenant database instead.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_tenants', function (Blueprint $table) {
            $table->id();

            // The only identifier that may be used externally or for scoping.
            // Numeric IDs collide across tenant databases by design.
            $table->uuid('uuid')->unique();

            // Normalised, reserved-word-checked. Also the default subdomain.
            $table->string('slug', 63)->unique();

            $table->string('display_name');
            $table->string('legal_name')->nullable();

            $table->string('status', 32)->index();
            $table->string('tier', 32)->default('standard');

            $table->string('region', 32)->nullable()->index();
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 64)->default('UTC');

            $table->string('provisioning_state', 40)->index();

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Set when an irreversible deletion is scheduled; the tenant must
            // stop serving before this is reached.
            $table->timestamp('purge_after')->nullable()->index();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Hot path: resolve an active tenant during a request.
            $table->index(['status', 'provisioning_state']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_tenants');
    }
};
