<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Webhooks\BillingWebhookController;

/*
|--------------------------------------------------------------------------
| Billing webhook routes
|--------------------------------------------------------------------------
| These endpoints receive asynchronous notifications from the billing
| provider (plan §9.2). They are:
|  - NOT behind CSRF (providers cannot send tokens)
|  - NOT behind authentication (providers use signature verification)
|  - Rate-limited to prevent abuse
|  - Idempotent: duplicate deliveries are safely ignored
|
| Signature verification happens INSIDE the controller before any state
| change. An unverified payload never touches the database.
|
| DEPLOYMENT REQUIREMENT — the webhook URL you register with the billing
| provider MUST be on a control-plane host (SAAS_PLATFORM_HOST or
| SAAS_MARKETING_HOST). These routes carry no domain constraint, but the
| global ResolveTenant middleware runs before route matching and returns 404
| for any host that is neither a known tenant nor a configured control-plane
| host. Pointing the provider at a bare IP or an unconfigured hostname will
| silently 404 every delivery.
*/

Route::name('saas.webhooks.')->prefix('webhooks')->group(function () {
    Route::post('/billing', [BillingWebhookController::class, 'handle'])
        ->middleware(['saas.landlord-host', 'throttle:60,1'])
        ->name('billing');
});
