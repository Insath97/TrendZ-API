<?php

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {

    /* registration */
    Route::post('register', [AuthController::class, 'register']);

    /* login */
    Route::post('login', [AuthController::class, 'login']);

    /* location */
    Route::get('get-location', [HomeController::class, 'getLocation']);

    /* testing data */
    Route::get('check', [HomeController::class, 'check']);
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['auth:customer']], function () {

    /* get shops */
    Route::get('shop/{id}', [HomeController::class, 'cusShops']);

    /* get services */
    Route::get('service/{id}', [HomeController::class, 'cusServices']);

    /* get slots */
    Route::get('slot/{id}', [HomeController::class, 'cusSlots']);

    /* create booking */
    Route::post('booking/create', [HomeController::class, 'createBooking']);

    /* cancel booking */
    Route::post('booking/{id}/cancel', [HomeController::class, 'cancelBooking']);

    /* rescheduling booking */
    Route::post('booking/{id}/reschedule', [HomeController::class, 'rescheduleBooking']);

    /* pending booking list */
    Route::get('booking/pending', [HomeController::class, 'pendingBooking']);

    /* compeled booking list */
    Route::get('booking/completed', [HomeController::class, 'completedBooking']);

    /* cancelled booking list */
    Route::get('booking/cancelled', [HomeController::class, 'cancelledBooking']);

    /* list all booking */
    Route::get('booking/all', [HomeController::class, 'allBooking']);
});
