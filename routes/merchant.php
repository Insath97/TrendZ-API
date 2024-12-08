<?php

use App\Http\Controllers\Merchant\MerchantAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'merchant', 'as' => 'merchant.'], function () {

    /* merchant login */
    Route::post('login', [MerchantAuthController::class, 'handleLogin']);
});

Route::group(['prefix' => 'merchant', 'as' => 'merchant.', 'middleware' => ['auth:merchant']], function () {

    /* merchant Logout */
    Route::post('logout', [MerchantAuthController::class, 'logout']);

    /*  */
});
