<?php

namespace Modules\Saas\Services\Billing;

use Modules\Saas\Contracts\BillingGateway;
use RuntimeException;

/**
 * Placeholder billing gateway that refuses all operations.
 *
 * Bound until a real provider is approved (plan §9). Every method throws so
 * that no code path can accidentally create a customer, start a checkout, or
 * process a webhook before the billing domain is intentionally wired.
 *
 * Replace the binding in SaasServiceProvider with a provider adapter
 * (e.g. StripeBillingGateway) once the commercial decision is made.
 */
class NullBillingGateway implements BillingGateway
{
    public function createCustomer(array $attributes): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function startCheckout(array $options): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function createPortalSession(string $providerCustomerId, string $returnUrl): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function verifyWebhook(string $payload, string $signature, string $secret): bool
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function parseWebhook(string $payload): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function cancelSubscription(string $providerSubscriptionId, bool $immediately = false): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }

    public function fetchSubscription(string $providerSubscriptionId): array
    {
        throw new RuntimeException(
            'Billing is not configured. Bind a real BillingGateway implementation in SaasServiceProvider.'
        );
    }
}
