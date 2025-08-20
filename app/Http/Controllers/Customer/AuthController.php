<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $customer = new Customer();
        $customer->location_id = $request->location_id;
        $customer->image = "/image";
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->password =  bcrypt($request->password);
        $customer->gender = $request->gender;
        $customer->dob = $request->dob;
        $customer->phone_number = $request->phone_number;
        $customer->save();

        // Generate JWT token
        $token = JWTAuth::fromUser($customer);

        return response()->json([
            'success' => true,
            'message' => 'Customer registered successfully',
            'data' => $customer,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        // Validate login data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check credentials
        if (!$token = auth('customer')->attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $customer = auth('customer')->user();
        $customer = Customer::with('location')->find($customer->id);

        $cookie = cookie(
            'auth_token',
            $token,
            60 * 24 * 7,
            '/',
            null,
            false,
            true
        );

        // Return token on successful login
        return response()->json([
            'success' => true,
            'message' => 'Customer logged in successfully',
            'token' => $token,
            'data' => $customer
        ], 200)->cookie($cookie);
    }
}
