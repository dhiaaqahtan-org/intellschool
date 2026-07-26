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
*/

Route::name('saas.webhooks.')->prefix('webhooks')->group(function () {
    Route::post('/billing', [BillingWebhookController::class, 'handle'])
        ->middleware('throttle:60,1')
        ->name('billing');
});
