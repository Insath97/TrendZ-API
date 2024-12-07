<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLoginRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


class AdminAuthController extends Controller
{
    public function handleLogin(HandleLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('admin')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $admin = auth('admin')->user();

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'data' => $admin,
        ], 200);
    }

    public function logout(Request $request)
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
