<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Marketing\DemoRequestController;
use Modules\Saas\Http\Controllers\Marketing\PageController;

/*
|--------------------------------------------------------------------------
| Marketing routes
|--------------------------------------------------------------------------
| Registered by Modules\Saas\Providers\RouteServiceProvider, which applies the
| configured marketing host constraint. Nothing here may run on a tenant host:
| `/` on a tenant host belongs to the school's own public website in
| routes/site.php.
|
| SCOPE: the home page and the demo flow are built. The remaining pages in the
| implementation plan's sitemap (§10.3 — role pages, per-module pages,
| resources, company) are Phase 7 work and are not stubbed here, because a
| registered route pointing at an empty view is worse than a missing route.
*/

Route::name('saas.marketing.')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/demo', [PageController::class, 'demo'])->name('demo');

    // Progressive enhancement: one endpoint answers both a normal form POST
    // (redirect back with a flash message) and the Vue island's JSON request.
    Route::post('/demo', [DemoRequestController::class, 'store'])
        ->middleware('throttle:saas-leads')
        ->name('demo.store');

    /*
     * Legal pages are required before launch (plan §10.3). They are routed now
     * so the footer never links to a 404, and each view carries a visible
     * "not yet reviewed" notice until legal counsel supplies the text.
     */
    Route::view('/legal/terms', 'saas::marketing.legal.stub', ['doc' => 'terms'])->name('legal.terms');
    Route::view('/legal/privacy', 'saas::marketing.legal.stub', ['doc' => 'privacy'])->name('legal.privacy');
    Route::view('/legal/dpa', 'saas::marketing.legal.stub', ['doc' => 'dpa'])->name('legal.dpa');
});
