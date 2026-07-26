<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versioned plan definitions (plan §9.3).
 *
 * A price or feature change creates a NEW plan version; it must never
 * silently rewrite an existing customer contract. The `version` column
 * combined with `plan_code` forms the immutable identity.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Stable code: 'starter', 'growth', 'enterprise'.
            $table->string('plan_code', 40)->index();

            // Monotonically increasing per plan_code. A change = new version.
            $table->unsignedSmallInteger('version')->default(1);

            $table->string('display_name');
            $table->text('description')->nullable();

            // Billing interval and currency.
            $table->string('billing_interval', 20)->default('monthly'); // monthly|yearly
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('price_cents')->default(0);
            $table->unsignedInteger('trial_days')->default(14);

            // Active window: when this version can be subscribed to.
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();

            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(['plan_code', 'version']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_plans');
    }
};
