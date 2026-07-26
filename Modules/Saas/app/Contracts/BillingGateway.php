<?php

namespace Modules\Saas\Contracts;

/**
 * Abstraction over the SaaS subscription billing provider (plan §9).
 *
 * The ERP already handles student-fee payments through Stripe/Razorpay.
 * SaaS subscription billing is a DIFFERENT domain: different tables, routes,
 * secrets, webhooks, and adapters. This interface keeps provider details
 * behind a seam so the platform never depends on one gateway's SDK directly.
 *
 * Start with one approved provider. Provider IDs and payload mapping stay
 * inside the adapter implementation, never in controllers or models.
 */
interface BillingGateway
{
    /**
     * Create a billing customer record for a tenant.
     *
     * @param  array  $attributes  name, email, metadata (tenant UUID, slug).
     * @return array{provider_customer_id: string}
     */
    public function createCustomer(array $attributes): array;

    /**
     * Start a hosted checkout session for a plan.
     *
     * @param  array  $options  customer_id, plan_id, trial_days, success/cancel URLs.
     * @return array{checkout_url: string, session_id: string}
     */
    public function startCheckout(array $options): array;

    /**
     * Create a self-service billing portal session.
     *
     * @return array{portal_url: string}
     */
    public function createPortalSession(string $providerCustomerId, string $returnUrl): array;

    /**
     * Verify the webhook signature. Must be called BEFORE parsing or state
     * changes. Returns false when the signature is invalid.
     */
    public function verifyWebhook(string $payload, string $signature, string $secret): bool;

    /**
     * Parse a verified webhook payload into a normalized event array.
     *
     * @return array{type: string, provider_event_id: string, data: array}
     */
    public function parseWebhook(string $payload): array;

    /**
     * Cancel a subscription at period end or immediately.
     *
     * @return array{status: string, cancelled_at: string, period_end: string}
     */
    public function cancelSubscription(string $providerSubscriptionId, bool $immediately = false): array;

    /**
     * Re-fetch the authoritative subscription state from the provider.
     * Used when webhook order is ambiguous or reconciliation runs.
     *
     * @return array{status: string, plan_id: string, period_end: string, trial_end: ?string}
     */
    public function fetchSubscription(string $providerSubscriptionId): array;
}
