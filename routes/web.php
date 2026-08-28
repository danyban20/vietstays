<?php

use Illuminate\Support\Facades\Route;

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*');

Route::view('/{any?}', 'public')
    ->where('any', '.*');
