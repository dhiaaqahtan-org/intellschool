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
        Schema::connection($this->getConnection())->create('saas_demo_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 120);
            $table->string('school', 180);
            $table->string('email', 190)->index();
            $table->string('phone', 40)->nullable();
            $table->string('school_size', 40)->nullable();
            $table->text('message')->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('status', 30)->default('new')->index();
            $table->timestamp('consent_at');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('purge_after')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_demo_requests');
    }
};
