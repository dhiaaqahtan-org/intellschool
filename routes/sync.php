<?php

/**
 * Offline sync endpoints for the Flutter client.
 * 
 * These are the ONLY server additions to InstiKit (per flutter-implementation-plan.md §10.1).
 * They live under /api/v1/app/sync and require Sanctum auth + user.config middleware.
 * 
 * POST /api/v1/app/sync/pull — incremental pull of changes since cursor
 * POST /api/v1/app/sync/push — apply batch of offline mutations from client outbox
 */

use App\Http\Controllers\Sync\SyncController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'two.factor.security', 'screen.lock', 'under.maintenance', 'user.config'])
    ->prefix('app/sync')
    ->group(function () {
        Route::post('pull', [SyncController::class, 'pull'])->name('sync.pull');
        Route::post('push', [SyncController::class, 'push'])->name('sync.push');
    });
