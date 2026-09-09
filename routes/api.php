<?php

use App\Http\Controllers\Api\ApartmentAvailabilityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApartmentController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HostApplicationController;
use App\Http\Controllers\Api\LanguageSettingsController;
use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PublicApartmentController;
use App\Http\Controllers\Api\PublicBookingController;
use App\Http\Controllers\Api\PublicHostApplicationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/public/apartments/search', [PublicApartmentController::class, 'search']);
Route::get('/public/apartments', [PublicApartmentController::class, 'index']);
Route::get('/public/apartments/{apartment}', [PublicApartmentController::class, 'show']);
Route::get('/public/districts', [PublicApartmentController::class, 'districts']);
Route::get('/public/host-applications/options', [PublicHostApplicationController::class, 'options']);
Route::post('/public/host-applications', [PublicHostApplicationController::class, 'store']);
Route::post('/public/bookings/quote', [PublicBookingController::class, 'quote']);
Route::post('/public/bookings', [PublicBookingController::class, 'store']);

Route::get('/locales', [LocaleController::class, 'index']);
Route::get('/locales/{locale}/messages', [LocaleController::class, 'messages']);
Route::get('/locale', [LocaleController::class, 'current']);
Route::put('/public/locale', [LocaleController::class, 'updatePublic']);

Route::post('/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['web', 'auth:sanctum']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/locale', [LocaleController::class, 'updatePreference']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/locations/cities', [LocationController::class, 'cities']);
    Route::get('/locations/districts', [LocationController::class, 'districts']);
    Route::get('/locations/buildings', [LocationController::class, 'buildings']);
    Route::get('/locations/filters', [LocationController::class, 'filterOptions']);
    Route::get('/facilities', [LocationController::class, 'facilities']);

    Route::get('/apartments', [ApartmentController::class, 'index']);
    Route::post('/apartments', [ApartmentController::class, 'store']);
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
    Route::post('/apartments/{apartment}/photos', [ApartmentController::class, 'uploadPhotos']);
    Route::get('/apartments/{apartment}/suggested-price', [ApartmentController::class, 'suggestedPrice']);
    Route::get('/apartments/{apartment}/periods', [ApartmentAvailabilityController::class, 'index']);
    Route::patch('/apartments/{apartment}/periods/{period}', [ApartmentAvailabilityController::class, 'update']);
    Route::delete('/apartments/{apartment}/periods/{period}', [ApartmentAvailabilityController::class, 'destroy']);

    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::post('/bookings/{booking}/move', [BookingController::class, 'move']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invitations', [TeamController::class, 'storeInvitation']);
    Route::patch('/team/members/{member}/guest-info', [TeamController::class, 'updateGuestInfo']);
    Route::delete('/team/invitations/{invitation}', [TeamController::class, 'destroyInvitation']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [UserAdminController::class, 'index']);
        Route::post('/admin/users', [UserAdminController::class, 'store']);
        Route::patch('/admin/users/{user}', [UserAdminController::class, 'update']);

        Route::get('/settings/email', [SettingsController::class, 'showEmail']);
        Route::put('/settings/email', [SettingsController::class, 'updateEmail']);
        Route::get('/settings/email-templates', [SettingsController::class, 'indexEmailTemplates']);
        Route::put('/settings/email-templates/{code}', [SettingsController::class, 'updateEmailTemplate']);

        Route::get('/host-applications', [HostApplicationController::class, 'index']);
        Route::get('/host-applications/{application}', [HostApplicationController::class, 'show']);
        Route::patch('/host-applications/{application}/status', [HostApplicationController::class, 'updateStatus']);

        Route::get('/settings/languages', [LanguageSettingsController::class, 'index']);
        Route::post('/settings/languages', [LanguageSettingsController::class, 'store']);
        Route::get('/settings/languages/{locale}', [LanguageSettingsController::class, 'show']);
        Route::put('/settings/languages/{locale}', [LanguageSettingsController::class, 'update']);
        Route::delete('/settings/languages/{locale}', [LanguageSettingsController::class, 'destroy']);
    });
});
