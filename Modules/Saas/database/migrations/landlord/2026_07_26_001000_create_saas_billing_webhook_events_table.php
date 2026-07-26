<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent webhook inbox (plan §9.2).
 *
 * Every external billing event is persisted under a unique provider/event ID
 * BEFORE processing. This makes handlers idempotent and order-tolerant:
 * a replayed or duplicated delivery collides on the unique key and is
 * safely ignored.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_billing_webhook_events', function (Blueprint $table) {
            $table->id();

            // Provider + external event ID: the idempotency key.
            $table->string('provider', 30)->default('stripe');
            $table->string('provider_event_id', 128);

            // Event type, e.g. 'customer.subscription.updated'.
            $table->string('event_type', 80)->index();

            // Signature verification result.
            $table->boolean('signature_valid')->default(false);

            // Processing state.
            $table->string('processing_status', 20)->default('received'); // received|processing|done|failed
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->text('failure_summary')->nullable();

            // The tenant this event affects (nullable until parsed).
            $table->uuid('tenant_uuid')->nullable()->index();

            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'provider_event_id']);
            $table->index(['processing_status', 'retry_count']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_billing_webhook_events');
    }
};
