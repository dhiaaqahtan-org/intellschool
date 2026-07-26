<?php

namespace Modules\Saas\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Saas\Events\TenantProvisioned;
use Modules\Saas\Events\TenantStatusChanged;
use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Listeners\FlushEntitlementCacheOnSubscriptionChange;
use Modules\Saas\Listeners\FlushResolutionCacheOnStatusChange;
use Modules\Saas\Listeners\RecordAuditOnTenantProvisioned;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TenantProvisioned::class => [
            RecordAuditOnTenantProvisioned::class,
        ],

        TenantStatusChanged::class => [
            FlushResolutionCacheOnStatusChange::class,
        ],

        SubscriptionStateChanged::class => [
            FlushEntitlementCacheOnSubscriptionChange::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
