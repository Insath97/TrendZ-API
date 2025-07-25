<?php

use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Merchant\HomeController;
use App\Http\Controllers\Merchant\MerchantAuthController;
use App\Http\Controllers\Merchant\ServiceController;
use App\Http\Controllers\Merchant\SlotController;
use App\Http\Controllers\Merchant\WalkingCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'merchant', 'as' => 'merchant.'], function () {

    /* merchant login */
    Route::post('login', [MerchantAuthController::class, 'handleLogin']);
});

Route::group(['prefix' => 'merchant', 'as' => 'merchant.', 'middleware' => ['auth:merchant']], function () {

    /* merchant Logout */
    Route::post('logout', [MerchantAuthController::class, 'logout']);

    /* fetch all shops */
    Route::get('get-shop', [ShopController::class, 'getShops']);

    /* Particular Shop Services */
    Route::get('deactivate-service/{id}', [ServiceController::class, 'deactivateService']);
    Route::apiResource('services', ServiceController::class);

    /* particular shop time slots */
    Route::get('deactivate-slot/{id}', [SlotController::class, 'deactivateSlot']);
    Route::apiResource('slots', SlotController::class);

    /* walking customer */
    Route::apiResource('walking-customer', WalkingCustomer::class);
});

/*
    Pending Work

    1. forgot password
    2. reset password
    3. refresh token
    4.
*/
