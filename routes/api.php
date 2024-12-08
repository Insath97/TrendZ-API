<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Admin Route */

require __DIR__ . '/admin.php';

/* Merchant Route */
require __DIR__ . '/merchant.php';

/* Customer Route */
require __DIR__ . '/customer.php';

// Other common API routes (if any)
Route::get('/health-check', function () {
    return response()->json(['status' => 'API is working!']);
});
