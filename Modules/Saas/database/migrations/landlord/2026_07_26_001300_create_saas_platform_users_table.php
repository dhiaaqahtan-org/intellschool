<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform operator identities (plan آ§5.1, آ§5.4).
 *
 * SaaS operators use a SEPARATE guard and database from tenant users.
 * They must never have implicit access to tenant data â€” only through
 * approved support sessions.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }
    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_platform_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role: super_admin, admin, support, billing, readonly
            $table->string('role', 20)->default('readonly');

            // Status: active, suspended, pending
            $table->string('status', 20)->default('pending');

            // Two-factor authentication
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'role']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_platform_users');
    }
};
