<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->table('saas_tenant_owners', function (Blueprint $table) {
            $table->string('name')->nullable()->after('tenant_user_uuid');
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->table('saas_tenant_owners', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
