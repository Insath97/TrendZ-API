<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'phone_number' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::create([
            'location_id' => $request->location_id,
            'image' => "/image",
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'gender' => $request->gender,
            'dob' => $request->dob,
            'phone_number' => $request->phone_number,
        ]);

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

        // Return token on successful login
        return response()->json([
            'success' => true,
            'message' => 'Customer logged in successfully',
            'token' => $token,
            'data' => $customer
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            auth('customer')->logout();

            // Invalidate the token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again.'
            ], 500);
        }
    }

    public function redirectToGoogle()
    {
        // For web - redirect to Google OAuth
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            return $this->handleGoogleUser($googleUser);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // New endpoint for mobile apps to authenticate with Google token
    public function loginWithGoogleMobile(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify the Google token
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->id_token);

            return $this->handleGoogleUser($googleUser);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper method to handle Google user data
    private function handleGoogleUser($googleUser)
    {
        // Check if customer already exists
        $customer = Customer::where('email', $googleUser->getEmail())->first();

        if (!$customer) {
            // Create new customer
            $customer = Customer::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(24)), // Random password
                'image' => $googleUser->getAvatar(),
                'email_verified_at' => now(), // Mark email as verified
            ]);
        }

        // Generate JWT token
        $token = JWTAuth::fromUser($customer);

        return response()->json([
            'success' => true,
            'message' => 'Login with Google successful',
            'token' => $token,
            'data' => $customer
        ], 200);
    }
}
