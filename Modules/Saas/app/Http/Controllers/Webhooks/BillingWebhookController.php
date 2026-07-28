<?php

namespace Modules\Saas\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Domain\Billing\SubscriptionStatus;
use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Models\Landlord\AuditEvent;

/**
 * Receives and processes billing provider webhooks (plan §9.2).
 *
 * Processing rules:
 *  1. Verify the provider signature BEFORE parsing or changing state.
 *  2. Persist each external event under a unique provider/event ID.
 *  3. Acknowledge quickly.
 *  4. Handlers are idempotent AND order-tolerant.
 *  5. Record a safe error and let reconciliation retry.
 *
 * Webhooks are unauthenticated by necessity — the signature IS the
 * authentication. Everything here is written on the assumption that the
 * request is hostile until the signature says otherwise.
 */
class BillingWebhookController extends Controller
{
    public function handle(Request $request, BillingGateway $gateway): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-Webhook-Signature', '');
        $secret = (string) config('saas.billing.webhook_secret', '');

        /*
         * Step 1: signature. FAIL CLOSED.
         *
         * This previously read `if (! empty($secret) && ! verify(...))`, which
         * meant that with no secret configured every unsigned payload was
         * accepted and applied to subscription state. An unconfigured secret is
         * a deployment mistake, not permission to skip authentication on the
         * one endpoint that is deliberately unauthenticated.
         */
        if ($secret === '') {
            Log::critical('saas.webhook.secret_missing', ['ip' => $request->ip()]);

            return response()->json(['error' => 'Billing webhooks are not configured.'], 503);
        }

        if (! $gateway->verifyWebhook($payload, $signature, $secret)) {
            Log::warning('saas.webhook.signature_invalid', ['ip' => $request->ip()]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Step 2: parse.
        try {
            $event = $gateway->parseWebhook($payload);
        } catch (\Throwable $e) {
            Log::error('saas.webhook.parse_failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Unparseable payload'], 400);
        }

        $providerEventId = (string) ($event['provider_event_id'] ?? '');
        $eventType = (string) ($event['type'] ?? 'unknown');
        // Taken from the parsed event, not hardcoded: two providers can and do
        // issue colliding event IDs, and the uniqueness key is (provider, id).
        $provider = (string) ($event['provider'] ?? 'stripe');

        if ($providerEventId === '') {
            // Without an ID there is no idempotency key, so a retry would be
            // applied twice. Refuse rather than process something unrepeatable.
            return response()->json(['error' => 'Missing provider event id'], 400);
        }

        $connection = config('saas.database.landlord_connection', 'landlord');
        $table = fn () => DB::connection($connection)->table('saas_billing_webhook_events');

        // Step 3: idempotency. A provider that retries a delivery it already
        // sent must get an ack, not a second state change.
        $already = $table()
            ->where('provider', $provider)
            ->where('provider_event_id', $providerEventId)
            ->where('processing_status', 'done')
            ->exists();

        if ($already) {
            return response()->json(['status' => 'already_processed']);
        }

        // Step 4: inbox — record before processing, so a crash mid-handler
        // leaves evidence rather than silence.
        $table()->upsert([
            'provider' => $provider,
            'provider_event_id' => $providerEventId,
            'event_type' => $eventType,
            'signature_valid' => true,
            'processing_status' => 'processing',
            'tenant_uuid' => $event['data']['metadata']['tenant_uuid'] ?? null,
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], ['provider', 'provider_event_id'], [
            'processing_status' => 'processing',
            'updated_at' => now(),
        ]);

        // Step 5: process.
        try {
            $this->processEvent($eventType, $event['data'] ?? [], $event);

            $table()
                ->where('provider', $provider)
                ->where('provider_event_id', $providerEventId)
                ->update(['processing_status' => 'done', 'processed_at' => now(), 'updated_at' => now()]);

            return response()->json(['status' => 'processed']);
        } catch (\Throwable $e) {
            $table()
                ->where('provider', $provider)
                ->where('provider_event_id', $providerEventId)
                ->update([
                    'processing_status' => 'failed',
                    'retry_count' => DB::raw('retry_count + 1'),
                    'failure_summary' => $this->safeError($e),
                    'updated_at' => now(),
                ]);

            Log::error('saas.webhook.processing_failed', [
                'event_id' => $providerEventId,
                'type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            // 200 so the provider does not hammer us; ReconcileSubscriptions
            // picks failed rows back up.
            return response()->json(['status' => 'queued_for_retry']);
        }
    }

    private function processEvent(string $type, array $data, array $event): void
    {
        match (true) {
            str_starts_with($type, 'customer.subscription.') => $this->handleSubscriptionEvent($type, $data, $event),
            str_starts_with($type, 'invoice.') => $this->handleInvoiceEvent($type, $data),
            str_starts_with($type, 'customer.') => Log::info("saas.webhook.customer_event: {$type}"),
            default => Log::info("saas.webhook.unhandled_type: {$type}"),
        };
    }

    private function handleSubscriptionEvent(string $type, array $data, array $event): void
    {
        $tenantUuid = $data['metadata']['tenant_uuid'] ?? null;
        $providerSubId = (string) ($data['id'] ?? '');

        if ($tenantUuid === null || $providerSubId === '') {
            Log::warning('saas.webhook.subscription_unroutable', ['type' => $type]);

            return;
        }

        $connection = config('saas.database.landlord_connection', 'landlord');

        $subscription = DB::connection($connection)
            ->table('saas_subscriptions')
            ->where('tenant_uuid', $tenantUuid)
            ->where('provider_subscription_id', $providerSubId)
            ->first();

        if ($subscription === null) {
            Log::warning('saas.webhook.subscription_not_found', [
                'tenant' => $tenantUuid,
                'provider_subscription_id' => $providerSubId,
            ]);

            return;
        }

        /*
         * ORDER TOLERANCE (plan §9.2).
         *
         * Webhooks arrive out of order routinely — a retry of an older event
         * can land after a newer one. Applying it blind would resurrect a
         * superseded status: a customer who upgraded could be flipped back to
         * past_due by a delayed retry, losing access they have paid for.
         *
         * The provider's own event timestamp is the ordering authority. We keep
         * the last applied one in provider_meta and ignore anything older.
         */
        $meta = json_decode((string) ($subscription->provider_meta ?? '{}'), true) ?: [];
        $eventAt = $this->eventTimestamp($event, $data);
        $lastAppliedAt = isset($meta['last_event_at']) ? Carbon::parse($meta['last_event_at']) : null;

        if ($eventAt !== null && $lastAppliedAt !== null && $eventAt->lessThan($lastAppliedAt)) {
            Log::info('saas.webhook.stale_event_ignored', [
                'type' => $type,
                'event_at' => $eventAt->toIso8601String(),
                'last_applied_at' => $lastAppliedAt->toIso8601String(),
            ]);

            return;
        }

        $newStatus = $this->mapProviderStatus((string) ($data['status'] ?? ''));
        $currentStatus = $subscription->status;

        $meta['last_event_at'] = ($eventAt ?? now())->toIso8601String();
        $meta['last_event_type'] = $type;

        DB::connection($connection)
            ->table('saas_subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => $newStatus,
                'current_period_start' => isset($data['current_period_start'])
                    ? date('Y-m-d H:i:s', (int) $data['current_period_start'])
                    : $subscription->current_period_start,
                'current_period_end' => isset($data['current_period_end'])
                    ? date('Y-m-d H:i:s', (int) $data['current_period_end'])
                    : $subscription->current_period_end,
                'cancel_at_period_end' => (bool) ($data['cancel_at_period_end'] ?? false),
                'provider_meta' => json_encode($meta),
                'updated_at' => now(),
            ]);

        // Entitlements are cached; without this the tenant keeps their old
        // access until the TTL expires.
        if ($currentStatus !== $newStatus) {
            SubscriptionStateChanged::dispatch($tenantUuid, $currentStatus ?? 'unknown', $newStatus, $providerSubId);
        }

        AuditEvent::record(
            action: "subscription.{$type}",
            tenantUuid: $tenantUuid,
            context: ['from' => $currentStatus, 'to' => $newStatus, 'provider_sub_id' => $providerSubId],
            actorType: 'billing_provider',
        );
    }

    private function handleInvoiceEvent(string $type, array $data): void
    {
        if ($type !== 'invoice.payment_failed') {
            return;
        }

        $tenantUuid = $data['metadata']['tenant_uuid'] ?? null;

        if ($tenantUuid === null) {
            return;
        }

        // Recorded, not acted on. A failed payment moves the SUBSCRIPTION
        // through the provider's own status events; it must never directly
        // suspend or delete a school's data (plan §12).
        AuditEvent::record(
            action: 'billing.payment_failed',
            tenantUuid: $tenantUuid,
            context: ['amount_due' => $data['amount_due'] ?? 0],
            actorType: 'billing_provider',
        );
    }

    private function eventTimestamp(array $event, array $data): ?Carbon
    {
        $raw = $event['created'] ?? $event['created_at'] ?? $data['created'] ?? null;

        if ($raw === null) {
            return null;
        }

        return is_numeric($raw) ? Carbon::createFromTimestamp((int) $raw) : Carbon::parse($raw);
    }

    /**
     * Map provider subscription status to our internal state machine.
     *
     * Note the spelling: the rest of the system uses `cancelled` (two Ls).
     * Stripe sends `canceled` (one L). Returning the provider's spelling put
     * rows into a status nothing else matched — not the entitlement check, not
     * the platform filters — so a cancelled tenant kept working.
     */
    private function mapProviderStatus(string $providerStatus): string
    {
        return (SubscriptionStatus::fromProvider($providerStatus) ?? SubscriptionStatus::Pending)->value;
    }

    private function safeError(\Throwable $e): string
    {
        $message = preg_replace('/(password|secret|token|key|sk_live|sk_test)\s*[=:]\s*\S+/i', '$1=[REDACTED]', $e->getMessage());

        return substr((string) $message, 0, 500);
    }
}
