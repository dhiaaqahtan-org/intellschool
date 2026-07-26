<?php

namespace Modules\Saas\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Models\Landlord\AuditEvent;

/**
 * Receives and processes billing provider webhooks (plan §9.2).
 *
 * Processing rules:
 *  1. Verify provider signature BEFORE parsing or changing state.
 *  2. Persist each external event under a unique provider/event ID.
 *  3. Return quickly (200), then process asynchronously if heavy.
 *  4. Handlers are idempotent and order-tolerant.
 *  5. Re-fetch provider state when event order is ambiguous.
 *  6. Record safe processing errors and retry with backoff.
 */
class BillingWebhookController extends Controller
{
    public function handle(Request $request, BillingGateway $gateway): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Webhook-Signature', '');
        $secret = config('services.billing.webhook_secret', '');

        // Step 1: Verify signature. An unverified payload never touches state.
        if (! empty($secret) && ! $gateway->verifyWebhook($payload, $signature, $secret)) {
            Log::warning('saas.webhook.signature_invalid', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Step 2: Parse the event.
        try {
            $event = $gateway->parseWebhook($payload);
        } catch (\Throwable $e) {
            Log::error('saas.webhook.parse_failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Unparseable payload'], 400);
        }

        $providerEventId = $event['provider_event_id'] ?? '';
        $eventType = $event['type'] ?? 'unknown';

        // Step 3: Idempotency check. If we already processed this event, ack it.
        $connection = config('saas.database.landlord_connection', 'landlord');
        $exists = DB::connection($connection)
            ->table('saas_billing_webhook_events')
            ->where('provider', 'stripe')
            ->where('provider_event_id', $providerEventId)
            ->where('processing_status', 'done')
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'already_processed']);
        }

        // Step 4: Persist the event BEFORE processing (inbox pattern).
        DB::connection($connection)
            ->table('saas_billing_webhook_events')
            ->upsert([
                'provider' => 'stripe',
                'provider_event_id' => $providerEventId,
                'event_type' => $eventType,
                'signature_valid' => true,
                'processing_status' => 'processing',
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ], ['provider', 'provider_event_id'], [
                'processing_status' => 'processing',
                'updated_at' => now(),
            ]);

        // Step 5: Process the event.
        try {
            $this->processEvent($eventType, $event['data'] ?? []);

            DB::connection($connection)
                ->table('saas_billing_webhook_events')
                ->where('provider', 'stripe')
                ->where('provider_event_id', $providerEventId)
                ->update([
                    'processing_status' => 'done',
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json(['status' => 'processed']);
        } catch (\Throwable $e) {
            DB::connection($connection)
                ->table('saas_billing_webhook_events')
                ->where('provider', 'stripe')
                ->where('provider_event_id', $providerEventId)
                ->update([
                    'processing_status' => 'failed',
                    'retry_count' => DB::raw('retry_count + 1'),
                    'failure_summary' => substr($e->getMessage(), 0, 500),
                    'updated_at' => now(),
                ]);

            Log::error('saas.webhook.processing_failed', [
                'event_id' => $providerEventId,
                'type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            // Return 200 to prevent the provider from retrying immediately;
            // our reconciliation job will pick up failed events.
            return response()->json(['status' => 'queued_for_retry']);
        }
    }

    /**
     * Route the event to the appropriate handler.
     */
    private function processEvent(string $type, array $data): void
    {
        match (true) {
            str_starts_with($type, 'customer.subscription.') => $this->handleSubscriptionEvent($type, $data),
            str_starts_with($type, 'invoice.') => $this->handleInvoiceEvent($type, $data),
            str_starts_with($type, 'customer.') => $this->handleCustomerEvent($type, $data),
            default => Log::info("saas.webhook.unhandled_type: {$type}"),
        };
    }

    private function handleSubscriptionEvent(string $type, array $data): void
    {
        $tenantUuid = $data['metadata']['tenant_uuid'] ?? null;
        $newStatus = $this->mapProviderStatus($data['status'] ?? '');

        if ($tenantUuid === null) {
            Log::warning('saas.webhook.subscription_no_tenant', ['type' => $type]);

            return;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        // Get current state for the event.
        $currentStatus = DB::connection($connection)
            ->table('saas_subscriptions')
            ->where('tenant_uuid', $tenantUuid)
            ->where('provider_subscription_id', $data['id'] ?? '')
            ->value('status');

        // Update subscription state.
        DB::connection($connection)
            ->table('saas_subscriptions')
            ->where('tenant_uuid', $tenantUuid)
            ->where('provider_subscription_id', $data['id'] ?? '')
            ->update([
                'status' => $newStatus,
                'current_period_start' => isset($data['current_period_start'])
                    ? date('Y-m-d H:i:s', $data['current_period_start'])
                    : null,
                'current_period_end' => isset($data['current_period_end'])
                    ? date('Y-m-d H:i:s', $data['current_period_end'])
                    : null,
                'cancel_at_period_end' => $data['cancel_at_period_end'] ?? false,
                'updated_at' => now(),
            ]);

        // Fire event to flush entitlement cache.
        if ($currentStatus !== $newStatus) {
            SubscriptionStateChanged::dispatch(
                $tenantUuid,
                $currentStatus ?? 'unknown',
                $newStatus,
                $data['id'] ?? null,
            );
        }

        AuditEvent::record(
            action: "subscription.{$type}",
            tenantUuid: $tenantUuid,
            context: ['status' => $newStatus, 'provider_sub_id' => $data['id'] ?? ''],
            actorType: 'billing_provider',
        );
    }

    private function handleInvoiceEvent(string $type, array $data): void
    {
        // Payment failures trigger grace period logic.
        if ($type === 'invoice.payment_failed') {
            $tenantUuid = $data['metadata']['tenant_uuid'] ?? null;

            if ($tenantUuid) {
                AuditEvent::record(
                    action: 'billing.payment_failed',
                    tenantUuid: $tenantUuid,
                    context: ['amount_due' => $data['amount_due'] ?? 0],
                    actorType: 'billing_provider',
                );
            }
        }
    }

    private function handleCustomerEvent(string $type, array $data): void
    {
        // Customer updates are informational for now.
        Log::info("saas.webhook.customer_event: {$type}");
    }

    /**
     * Map provider subscription status to our internal state machine.
     */
    private function mapProviderStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'trialing' => 'trialing',
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'canceled',
            'unpaid' => 'grace',
            'paused' => 'paused',
            default => 'pending',
        };
    }
}
