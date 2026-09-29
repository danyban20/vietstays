<?php

use App\Http\Controllers\PublicStorageController;
use App\Support\LegacyWordpressUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PriceMatrixTestController;

// Price Matrix Test Routes
Route::prefix('test/price-matrix')->group(function () {
    Route::get('/', [PriceMatrixTestController::class, 'index'])->name('price-matrix-test');
    Route::post('/calculate', [PriceMatrixTestController::class, 'calculate'])->name('price-matrix-calculate');
    Route::get('/buildings', [PriceMatrixTestController::class, 'buildings'])->name('price-matrix-buildings');
});

Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.+');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*');

Route::get('/wp/{path?}', function (Request $request, ?string $path = null) {
    $query = LegacyWordpressUrl::rewriteQuery($request->query());
    $target = '/'.ltrim((string) $path, '/');

    return redirect()->to($target.($query ? '?'.http_build_query($query) : ''), 301);
})->where('path', '.*');

Route::view('/{any?}', 'public')
    ->where('any', '.*');
