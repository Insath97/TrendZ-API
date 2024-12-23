<?php

use App\Http\Controllers\Customer\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {

    /* registration */
    Route::post('register', [AuthController::class, 'register']);

    /* login */
    Route::post('login', [AuthController::class, 'login']);
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['auth:customer']], function () {
    
});
