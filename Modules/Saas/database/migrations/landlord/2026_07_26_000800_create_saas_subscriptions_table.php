<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription lifecycle (plan §9.2).
 *
 * States: pending → trialing → active → past_due → grace → paused →
 *         canceled → terminated
 *
 * Access is provisioned from verified webhook state, NEVER from the browser
 * redirect. Provider IDs are stored for reconciliation; the authoritative
 * state is re-fetched from the provider when webhook order is ambiguous.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_uuid')->index();

            // Plan version this subscription is on.
            $table->unsignedBigInteger('plan_id')->index();

            // Provider references.
            $table->string('provider', 30)->default('stripe');
            $table->string('provider_customer_id', 128)->nullable()->index();
            $table->string('provider_subscription_id', 128)->nullable()->unique();

            // Lifecycle state machine.
            $table->string('status', 30)->default('pending')->index();

            // Period tracking.
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('terminated_at')->nullable();

            // Cancellation metadata.
            $table->string('cancel_reason', 40)->nullable();
            $table->boolean('cancel_at_period_end')->default(false);

            $table->json('provider_meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_uuid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_subscriptions');
    }
};
