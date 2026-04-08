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

<<<<<<< HEAD
=======
        // 🔥 AUTO CREATE EMPLOYEE
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            'position' => 'Staff',
            'department' => 'General',
            'hire_date' => now(),
            'salary' => 0, // default
        ]);

        // 🔑 TOKEN

>>>>>>> 00291747f9f9ea27e31930f137014d77d4b4870f
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success('Registration successful', [
            'user' => $user->load('roles'),
            'token' => $token,
        ], 201);
    }

<<<<<<< HEAD
=======
    // 📌 LOGIN
>>>>>>> 00291747f9f9ea27e31930f137014d77d4b4870f
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
