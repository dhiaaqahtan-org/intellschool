<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Billing webhook processing (plan §9.2, §17.2).
 *
 * The endpoint is deliberately unauthenticated — the signature IS the
 * authentication — so it is treated as hostile input throughout. What is
 * asserted here is the plan's four hard requirements: verify before touching
 * state, be idempotent under replay, be tolerant of out-of-order delivery, and
 * never let a billing event destroy school data.
 */
uses(TenancyTestCase::class);

/** A gateway that accepts one fixed signature and echoes back a canned event. */
function fakeGateway(array $event, bool $signatureValid = true): void
{
    app()->bind(BillingGateway::class, fn () => new class($event, $signatureValid) implements BillingGateway
    {
        public function __construct(private array $event, private bool $valid) {}

        public function verifyWebhook(string $payload, string $signature, string $secret): bool
        {
            return $this->valid;
        }

        public function parseWebhook(string $payload): array
        {
            return $this->event;
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
            return [];
        }
    });
}

function subscriptionEvent(string $id, string $status, int $created, string $tenantUuid, string $subId = 'sub_1'): array
{
    return [
        'provider' => 'stripe',
        'provider_event_id' => $id,
        'type' => 'customer.subscription.updated',
        'created' => $created,
        'data' => [
            'id' => $subId,
            'status' => $status,
            'metadata' => ['tenant_uuid' => $tenantUuid],
        ],
    ];
}

beforeEach(function () {
    config()->set('saas.billing.webhook_secret', 'test-secret');

    // The global ResolveTenant middleware runs before route matching and
    // 404s any host that is neither a known tenant nor a control-plane host.
    // The webhook endpoint therefore has to be published on a control-plane
    // host — see the note in routes/webhooks.php.
    config()->set('saas.hosts.platform', 'localhost');

    $this->tenant = $this->makeTenant('alpha', 'alpha.test');

    $plan = Plan::create([
        'plan_code' => 'growth', 'version' => 1, 'display_name' => 'Growth',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 14900,
    ]);

    $this->subscription = Subscription::create([
        'tenant_uuid' => $this->tenant->uuid,
        'plan_id' => $plan->id,
        'provider' => 'stripe',
        'provider_subscription_id' => 'sub_1',
        'status' => 'active',
    ]);

    // Absolute URL on the control-plane host, on purpose. A relative path is
    // resolved against APP_URL (127.0.0.1 here), which is NOT a configured
    // control-plane host — so ResolveTenant 404s it before the controller
    // runs. That is the real deployment trap this pins down: point the billing
    // provider at a bare IP and every delivery silently 404s.
    $this->post = fn (array $body = ['x' => 1]) => $this->postJson(
        'http://localhost/webhooks/billing',
        $body,
        ['X-Webhook-Signature' => 'sig']
    );
});

it('refuses to process anything when no webhook secret is configured', function () {
    config()->set('saas.billing.webhook_secret', '');
    fakeGateway(subscriptionEvent('evt_1', 'past_due', 1000, $this->tenant->uuid));

    // Previously this SKIPPED verification and applied the payload. An
    // unconfigured secret is a deployment mistake, not permission to accept
    // unsigned instructions about who has paid.
    ($this->post)()->assertStatus(503);

    expect($this->subscription->fresh()->status)->toBe('active');
});

it('rejects a payload whose signature does not verify', function () {
    fakeGateway(subscriptionEvent('evt_1', 'cancelled', 1000, $this->tenant->uuid), signatureValid: false);

    ($this->post)()->assertStatus(401);

    expect($this->subscription->fresh()->status)->toBe('active')
        ->and(DB::connection('landlord')->table('saas_billing_webhook_events')->count())->toBe(0);
});

it('applies a verified subscription status change', function () {
    fakeGateway(subscriptionEvent('evt_1', 'past_due', 1000, $this->tenant->uuid));

    ($this->post)()->assertOk()->assertJson(['status' => 'processed']);

    expect($this->subscription->fresh()->status)->toBe('past_due');
});

it('acknowledges a replayed event without applying it twice', function () {
    fakeGateway(subscriptionEvent('evt_1', 'past_due', 1000, $this->tenant->uuid));
    ($this->post)()->assertOk();

    // Same event id redelivered — providers do this routinely on timeout.
    ($this->post)()->assertOk()->assertJson(['status' => 'already_processed']);

    expect(DB::connection('landlord')->table('saas_billing_webhook_events')->count())->toBe(1);
});

it('ignores an out-of-order event that is older than one already applied', function () {
    // Newer event first: cancelled at t=2000.
    fakeGateway(subscriptionEvent('evt_new', 'cancelled', 2000, $this->tenant->uuid));
    ($this->post)()->assertOk();
    expect($this->subscription->fresh()->status)->toBe('cancelled');

    // A delayed retry of an OLDER event arrives afterwards.
    fakeGateway(subscriptionEvent('evt_old', 'active', 1000, $this->tenant->uuid));
    ($this->post)()->assertOk();

    // Applying it blind would resurrect a superseded status.
    expect($this->subscription->fresh()->status)->toBe('cancelled');
});

it('applies a newer event that arrives after an older one', function () {
    fakeGateway(subscriptionEvent('evt_old', 'past_due', 1000, $this->tenant->uuid));
    ($this->post)()->assertOk();

    fakeGateway(subscriptionEvent('evt_new', 'active', 2000, $this->tenant->uuid));
    ($this->post)()->assertOk();

    expect($this->subscription->fresh()->status)->toBe('active');
});

it('normalises the provider spelling of cancelled', function () {
    // Stripe sends `canceled` (one L); the rest of the system matches
    // `cancelled` (two). Storing the provider's spelling left the row in a
    // status nothing else recognised, so a cancelled tenant kept working.
    fakeGateway(subscriptionEvent('evt_1', 'canceled', 1000, $this->tenant->uuid));

    ($this->post)()->assertOk();

    expect($this->subscription->fresh()->status)->toBe('cancelled');
});

it('maps unpaid to grace rather than cutting access off', function () {
    fakeGateway(subscriptionEvent('evt_1', 'unpaid', 1000, $this->tenant->uuid));

    ($this->post)()->assertOk();

    // Plan §12: a payment problem degrades access, it never destroys data.
    expect($this->subscription->fresh()->status)->toBe('grace');
});

it('rejects an event with no provider event id', function () {
    $event = subscriptionEvent('', 'active', 1000, $this->tenant->uuid);
    fakeGateway($event);

    // Without an id there is no idempotency key, so a retry would apply twice.
    ($this->post)()->assertStatus(400);
});

it('ignores an event for a subscription it does not know', function () {
    fakeGateway(subscriptionEvent('evt_1', 'cancelled', 1000, $this->tenant->uuid, subId: 'sub_unknown'));

    ($this->post)()->assertOk();

    expect($this->subscription->fresh()->status)->toBe('active');
});

it('records the event in the inbox before processing it', function () {
    fakeGateway(subscriptionEvent('evt_1', 'past_due', 1000, $this->tenant->uuid));
    ($this->post)()->assertOk();

    $row = DB::connection('landlord')->table('saas_billing_webhook_events')->first();

    expect($row->provider_event_id)->toBe('evt_1')
        ->and($row->processing_status)->toBe('done')
        ->and((bool) $row->signature_valid)->toBeTrue()
        ->and($row->tenant_uuid)->toBe($this->tenant->uuid);
});

it('returns 400 for a payload the gateway cannot parse', function () {
    app()->bind(BillingGateway::class, fn () => new class implements BillingGateway
    {
        public function verifyWebhook(string $p, string $s, string $sec): bool
        {
            return true;
        }

        public function parseWebhook(string $p): array
        {
            throw new RuntimeException('bad json');
        }

        public function createCustomer(array $a): array
        {
            return [];
        }

        public function startCheckout(array $o): array
        {
            return [];
        }

        public function createPortalSession(string $i, string $u): array
        {
            return [];
        }

        public function cancelSubscription(string $i, bool $n = false): array
        {
            return [];
        }

        public function fetchSubscription(string $i): array
        {
            return [];
        }
    });

    ($this->post)()->assertStatus(400);
});

it('records a payment failure without changing subscription state', function () {
    fakeGateway([
        'provider' => 'stripe',
        'provider_event_id' => 'evt_inv',
        'type' => 'invoice.payment_failed',
        'created' => 1000,
        'data' => ['amount_due' => 14900, 'metadata' => ['tenant_uuid' => $this->tenant->uuid]],
    ]);

    ($this->post)()->assertOk();

    expect($this->subscription->fresh()->status)->toBe('active')
        ->and(AuditEvent::where('action', 'billing.payment_failed')->exists())->toBeTrue();
});

it('keeps the endpoint free of csrf and auth so providers can reach it', function () {
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->getName() === 'saas.webhooks.billing');

    expect($route)->not->toBeNull();

    $middleware = $route->gatherMiddleware();

    expect($middleware)->not->toContain('web')
        ->and($middleware)->not->toContain('auth:platform')
        ->and($middleware)->toContain('saas.landlord-host')
        // But it must still be rate limited — it is a public endpoint.
        ->and(collect($middleware)->contains(fn ($m) => str_starts_with((string) $m, 'throttle:')))->toBeTrue();
});
