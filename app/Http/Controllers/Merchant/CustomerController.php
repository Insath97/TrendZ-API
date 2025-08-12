<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResourse;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {

        $customer = Customer::with('location')->get();

        dd($customer);

        if ($customer->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
        ], 200);
    }
}
