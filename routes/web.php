<?php

use App\Http\Controllers\PublicStorageController;
use Illuminate\Support\Facades\Route;

Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.+');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*');

Route::view('/{any?}', 'public')
    ->where('any', '.*');
