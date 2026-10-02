<?php

use App\Http\Controllers\PublicStorageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PriceMatrixTestController;

// Price Matrix Test Routes: a debugging page that shows the whole pricing
// model, so only the team that manages prices can open it.
Route::prefix('test/price-matrix')->middleware(['auth', 'role:superadmin,supervisor'])->group(function () {
    Route::get('/', [PriceMatrixTestController::class, 'index'])->name('price-matrix-test');
    Route::post('/calculate', [PriceMatrixTestController::class, 'calculate'])->name('price-matrix-calculate');
    Route::get('/buildings', [PriceMatrixTestController::class, 'buildings'])->name('price-matrix-buildings');
});

Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.+');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*');

Route::view('/{any?}', 'public')
    ->where('any', '.*');
