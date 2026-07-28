<?php

use Illuminate\Support\Facades\Event;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

beforeEach(function () {
    $this->tenant = $this->makeTenant('alpha', 'alpha.test');
    $this->plan = Plan::create([
        'plan_code' => 'growth',
        'version' => 1,
        'display_name' => 'Growth',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 14900,
        'trial_days' => 14,
    ]);
});

function bindReconciliationGateway(array $state): void
{
    app()->bind(BillingGateway::class, fn () => new class($state) implements BillingGateway
    {
        public function __construct(private readonly array $state) {}

        public function verifyWebhook(string $payload, string $signature, string $secret): bool
        {
            return true;
        }

        public function parseWebhook(string $payload): array
        {
            return [];
        }

        public function createCustomer(array $attributes): array
        {
            return [];
        }

        public function startCheckout(array $options): array
        {
            return [];
        }

        public function createPortalSession(string $id, string $url): array
        {
            return [];
        }

        public function cancelSubscription(string $id, bool $now = false): array
        {
            return [];
        }

        public function fetchSubscription(string $id): array
        {
            return $this->state;
        }
    });
}

it('normalises provider cancellation and dispatches the correct event contract', function () {
    Event::fake([SubscriptionStateChanged::class]);
    bindReconciliationGateway(['status' => 'canceled']);

    $subscription = Subscription::create([
        'tenant_uuid' => $this->tenant->uuid,
        'plan_id' => $this->plan->id,
        'provider' => 'stripe',
        'provider_subscription_id' => 'sub_cancelled',
        'status' => 'active',
    ]);

    $this->artisan('saas:reconcile-subscriptions', ['--tenant' => $this->tenant->uuid])
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe('cancelled')
        ->and($subscription->fresh()->isTerminated())->toBeTrue();

    Event::assertDispatched(
        SubscriptionStateChanged::class,
        fn (SubscriptionStateChanged $event) => $event->tenantUuid === $this->tenant->uuid
            && $event->previousState === 'active'
            && $event->newState === 'cancelled'
            && $event->providerEventId === 'reconciliation'
    );
});

it('moves an expired grace period to paused and suspends an active tenant', function () {
    Event::fake([SubscriptionStateChanged::class]);
    bindReconciliationGateway(['status' => 'unpaid']);

    $subscription = Subscription::create([
        'tenant_uuid' => $this->tenant->uuid,
        'plan_id' => $this->plan->id,
        'provider' => 'stripe',
        'provider_subscription_id' => 'sub_grace',
        'status' => 'grace',
        'grace_ends_at' => now()->subMinute(),
    ]);

    $this->artisan('saas:reconcile-subscriptions', ['--tenant' => $this->tenant->uuid])
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe('paused')
        ->and($this->tenant->fresh()->status)->toBe(TenantStatus::Suspended);
});
