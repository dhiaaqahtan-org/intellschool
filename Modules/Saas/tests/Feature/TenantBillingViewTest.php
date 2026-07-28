<?php

use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Services\TenantBillingService;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

it('renders a localized server-first billing page with explicit Vue props', function (string $locale, string $heading) {
    app()->setLocale($locale);
    $tenant = $this->makeTenant('billing-view-'.$locale, "billing-view-{$locale}.test");
    $plan = Plan::create([
        'plan_code' => 'growth',
        'version' => 1,
        'display_name' => 'Growth',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 14900,
        'active_from' => now()->subMinute(),
        'is_public' => true,
    ]);
    $subscription = Subscription::create([
        'tenant_uuid' => $tenant->uuid,
        'plan_id' => $plan->id,
        'provider' => 'manual',
        'status' => 'active',
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ])->load('plan.features');
    $billing = app(TenantBillingService::class);

    $html = view('saas::billing.index', [
        'context' => $tenant->toContext("billing-view-{$locale}.test"),
        'subscription' => $subscription,
        'summary' => $billing->summary($subscription),
    ])->render();

    preg_match('/<script type="application\/json" data-props>(.*?)<\/script>/s', $html, $matches);
    $props = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

    expect($html)->toContain($heading)
        ->and($html)->toContain('Growth')
        ->and($html)->toContain('data-vue-component="subscription-summary"')
        ->and($props['endpoint'])->toBe('/api/saas/tenant/subscription')
        ->and($html)->not->toContain('wire:');
})->with([
    ['en', 'Subscription and billing'],
    ['ar', 'الاشتراك والفوترة'],
]);

it('keeps the no-subscription state useful without JavaScript', function () {
    app()->setLocale('en');
    $tenant = $this->makeTenant('billing-empty', 'billing-empty.test');
    $billing = app(TenantBillingService::class);

    $html = view('saas::billing.index', [
        'context' => $tenant->toContext('billing-empty.test'),
        'subscription' => null,
        'summary' => $billing->summary(null),
    ])->render();

    expect($html)->toContain('No subscription is attached to this school.')
        ->and($html)->toContain('data-props');
});
