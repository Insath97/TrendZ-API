<?php

use App\Http\Controllers\Admin\AdminAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {

    /* Admin Login*/
    Route::post('login', [AdminAuthController::class, 'handleLogin']);

    /* Admin Logout */
    Route::post('logout', [AdminAuthController::class, 'logout']);

    /*  Forgot password */
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLink']);

    /* Reset password */
    Route::get('reset-password/{token}', [AdminAuthController::class, 'ResetPassword']);
    Route::post('reset-password', [AdminAuthController::class, 'handleResetPassword']);
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth:api']], function(){

    
});

