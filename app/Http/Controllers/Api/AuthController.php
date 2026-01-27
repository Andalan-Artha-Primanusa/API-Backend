<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 🔥 SINGLE LOGIN
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success(
            'Register berhasil',
            [
                'user' => $user,
                'token' => $token,
            ],
            201
        );
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        // 🔥 SINGLE LOGIN
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success(
            'Login berhasil',
            [
                'user' => $user,
                'token' => $token,
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success('Logout berhasil');
    }
}
