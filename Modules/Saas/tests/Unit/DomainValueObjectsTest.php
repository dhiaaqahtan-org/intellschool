<?php

use Modules\Saas\Domain\Billing\SubscriptionStatus;
use Modules\Saas\Domain\Entitlements\FeatureCode;
use Modules\Saas\Domain\Identity\InvitationToken;
use Modules\Saas\Domain\Provisioning\ProvisioningStep;
use Modules\Saas\Domain\Support\SupportScope;
use Modules\Saas\Domain\Tenancy\TenantLifecycle;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;

it('accepts only configured entitlement feature codes', function () {
    $supported = ['students.core', 'finance.fees'];

    expect(FeatureCode::from(' Students.Core ', $supported)->value)->toBe('students.core')
        ->and(FeatureCode::tryFrom('students.unknown', $supported))->toBeNull()
        ->and(FeatureCode::tryFrom('../students.core', $supported))->toBeNull();
});

it('hashes invitation secrets without retaining the plain token', function () {
    $token = InvitationToken::generate();
    $restored = InvitationToken::fromPlainText($token->plainText);

    expect($token->plainText)->toHaveLength(64)
        ->and($token->digest())->toHaveLength(64)
        ->and($restored->digest())->toBe($token->digest())
        ->and($token->digest())->not->toBe($token->plainText);
});

it('maps provisioning steps to forward states and progress', function () {
    expect(ProvisioningStep::AllocateDatabase->state())->toBe(ProvisioningState::AllocatingDatabase)
        ->and(ProvisioningStep::Verify->state())->toBe(ProvisioningState::Verifying)
        ->and(ProvisioningStep::AllocateDatabase->progress())
        ->toBeLessThan(ProvisioningStep::Verify->progress());
});

it('enforces tenant lifecycle transitions', function () {
    $lifecycle = new TenantLifecycle;

    expect(fn () => $lifecycle->assertTransition(TenantStatus::Active, TenantStatus::Suspended))
        ->not->toThrow(DomainException::class)
        ->and(fn () => $lifecycle->assertTransition(TenantStatus::Terminated, TenantStatus::Active))
        ->toThrow(DomainException::class)
        ->and(fn () => $lifecycle->assertTransition(TenantStatus::Active, TenantStatus::Active))
        ->toThrow(DomainException::class);
});

it('normalizes provider subscription states and support scopes', function () {
    expect(SubscriptionStatus::fromProvider('past-due'))->toBe(SubscriptionStatus::PastDue)
        ->and(SubscriptionStatus::fromProvider('canceled'))->toBe(SubscriptionStatus::Cancelled)
        ->and(SubscriptionStatus::Active->grantsEntitlements())->toBeTrue()
        ->and(SubscriptionStatus::Terminated->isTerminal())->toBeTrue()
        ->and(SupportScope::Read->permitsWrites())->toBeFalse()
        ->and(SupportScope::Write->permitsWrites())->toBeTrue();
});
