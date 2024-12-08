<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {

    /* Admin Login*/
    Route::post('login', [AdminAuthController::class, 'handleLogin']);

    /*  Forgot password */
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLink']);

    /* Reset password */
    Route::get('reset-password/{token}', [AdminAuthController::class, 'ResetPassword']);
    Route::post('reset-password', [AdminAuthController::class, 'handleResetPassword']);
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth:admin']], function () {

    /* Admin Logout */
    Route::post('logout', [AdminAuthController::class, 'logout']);

    /* Trendz Branch */
    Route::get('get-shop', [ShopController::class, 'getShops']);
    Route::apiResource('shop', ShopController::class);

    /* Merchant */
    Route::get('deactive-merchant/{id}', [MerchantController::class, 'MerchantToggle']);
    Route::apiResource('merchant', MerchantController::class);

});

/*
    Pending Work

    1. admin deactivate particular shop
    2. reset password
    3. forgot password
    4. refresh token
*/
