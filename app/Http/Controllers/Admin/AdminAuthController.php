<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLoginRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use PhpOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
            // Invalidate the token
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'Logout successful',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to logout. Token might be invalid or expired.',
            ], 400);
        }
    }
}
