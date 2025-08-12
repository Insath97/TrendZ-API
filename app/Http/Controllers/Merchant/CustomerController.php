<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResourse;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function getCustomers()
    {
        $customer = Customer::with('location')->get();

        if ($customer->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
        ], 200);
    }
}
