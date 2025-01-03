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
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['auth:customer']], function () {

    /* get shops */
    Route::get('shop/{id}', [HomeController::class, 'cusShops']);

    /* get services */
    Route::get('service/{id}', [HomeController::class, 'cusServices']);

    /* get slots */
    Route::get('slot/{id}', [HomeController::class, 'cusSlots']);
});
