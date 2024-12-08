<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLoginRequest;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class MerchantAuthController extends Controller
{
    public function handleLogin(HandleLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('merchant')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $admin = auth('merchant')->user();

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'data' => $admin,
        ], 200);
    }

    public function logout()
    {
        try {
            // Get the token from the request
            $token = JWTAuth::parseToken();

            // Invalidate the token
            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Logout successful',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Failed to logout. Token might be invalid or expired.',
            ], 400);
        }
    }
}
