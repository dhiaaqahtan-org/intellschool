<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant database credentials, for hosts that issue one MySQL user per
 * database instead of one user per cluster.
 *
 * WHY THIS EXISTS, GIVEN THE ORIGINAL TABLE SAYS CREDENTIALS ARE NEVER STORED
 * HERE — that rule assumes a secret manager exists to point `secret_ref` at.
 * Shared hosting has none: hPanel creates a database and a user bound to it,
 * hands you the password once, and offers no API to retrieve it later. The
 * options are to store it or to not support that hosting at all.
 *
 * So it is stored, encrypted with APP_KEY, and only when the operator supplies
 * it. Be clear about what that buys: a dump of this table alone is useless, but
 * someone holding both the dump and .env can decrypt it. That is materially
 * better than plaintext and materially worse than a secret manager. Tenants
 * provisioned the normal way still carry a pointer and no secret, and moving a
 * tenant onto a real secret manager later means rewriting its `secret_ref` and
 * nulling these two columns.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->table('saas_tenant_databases', function (Blueprint $table) {
            // Null for every tenant that uses cluster-wide credentials, which
            // stays the default. Only the adoption path fills these in.
            $table->string('db_username', 64)->nullable()->after('database_name');

            // Encrypted at rest by the model cast, so the column has to hold
            // ciphertext far longer than the password itself.
            $table->text('db_password')->nullable()->after('db_username');
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->table('saas_tenant_databases', function (Blueprint $table) {
            $table->dropColumn(['db_username', 'db_password']);
        });
    }
};
