<?php

use App\Http\Controllers\Site\PageActionController;
use App\Http\Controllers\Site\PageController;
use Illuminate\Support\Facades\Route;

Route::prefix('site')->name('site.')->group(function () {
    Route::get('pages/pre-requisite', [PageController::class, 'preRequisite'])->name('pages.preRequisite');

    Route::post('pages/{page}/assets/{type}', [PageActionController::class, 'uploadAsset'])->name('pages.uploadAsset')->whereIn('type', ['cover', 'og']);
    Route::delete('pages/{page}/assets/{type}', [PageActionController::class, 'removeAsset'])->name('pages.removeAsset')->whereIn('type', ['cover', 'og']);

    Route::post('pages/{page}/cta', [PageActionController::class, 'updateCTA'])->name('pages.updateCTA');
    Route::post('pages/{page}/meta', [PageActionController::class, 'updateMeta'])->name('pages.updateMeta');

    Route::apiResource('pages', PageController::class);

});
