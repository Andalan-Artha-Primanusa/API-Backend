<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\Employee;

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    // 📌 REGISTER + AUTO CREATE EMPLOYEE 🔥
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $this->userService->register($validated);

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success('Registration successful', [
            'user' => $user->load('roles'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->userService->login($validated);

        // 🔑 TOKEN
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success('Login successful', [
            'user' => $user->load('roles'),
            'token' => $token,
    }

    // 📌 LOGOUT
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success('Logout successful');
    }
}