<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\UserService;
use App\Models\Employee;

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Register new user with auto-created employee record.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|email|unique:users,email',
                'password'              => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                'password_confirmation' => 'required',
            ]);

            // Create user via service (handles hashing, etc)
            $user = $this->userService->register($validated);

            // Auto-create employee record in transaction
            Employee::create([
                'user_id'        => $user->id,
                'employee_code'  => 'EMP-' . str_pad((string)$user->id, 4, '0', STR_PAD_LEFT),
                'position'       => 'Staff',
                'department'     => 'General',
                'hire_date'      => now(),
                'salary'         => 0,
            ]);

            // Delete old tokens and create new one
            $user->tokens()->delete();
            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            // Load relations efficiently
            $user->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender',
                'employee:id,user_id,position,department,employee_code',
                'employee.manager:id,user_id,position',
                'employee.manager.profile:id,user_id,phone',
            ]);

            return ApiResponse::success('Registration successful', [
                'user'  => $user,
                'token' => $token,
            ], 201);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Registration failed', null, 500);
        }
    }

    /**
     * Login user and return authentication token.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|min:6',
            ]);

            // Attempt login via service
            $user = $this->userService->login($validated);

            if (!$user) {
                return ApiResponse::error('Invalid credentials', null, 401);
            }

            // Delete old tokens and create new one
            $user->tokens()->delete();
            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            // Load relations efficiently
            $user->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender',
                'employee:id,user_id,position,department,employee_code',
                'employee.manager:id,user_id,position',
                'employee.manager.profile:id,user_id,phone',
            ]);

            return ApiResponse::success('Login successful', [
                'user'  => $user,
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Login failed', null, 500);
        }
    }

    /**
     * Logout user by deleting current access token.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->currentAccessToken();
            
            if ($token) {
                $token->delete();
            }

            return ApiResponse::success('Logout successful');

        } catch (\Exception $e) {
            return ApiResponse::error('Logout failed', null, 500);
        }
    }

    /**
     * Get authenticated user profile for current token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Unauthenticated', null, 401);
            }

            $user->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender',
                'employee:id,user_id,position,department,employee_code',
                'employee.manager:id,user_id,position',
                'employee.manager.profile:id,user_id,phone',
            ]);

            return ApiResponse::success('Authenticated user', [
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch authenticated user', null, 500);
        }
    }
}
