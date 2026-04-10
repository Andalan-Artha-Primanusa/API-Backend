<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Services\UserService;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Return the Google OAuth redirect URL for the SPA frontend.
     * 
     * @return JsonResponse
     */
    public function redirect(): JsonResponse
    {
        try {
            $url = Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return ApiResponse::success('Google OAuth redirect URL', ['url' => $url]);

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get redirect URL', null, 500);
        }
    }

    /**
     * Handle the Google OAuth callback.
     * 
     * Validates email, creates/updates user, and returns API token.
     * 
     * @return JsonResponse
     */
    public function callback(): JsonResponse
    {
        try {
            // Fetch Google user
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Validate email exists and is verified
            $email = $googleUser->getEmail();
            if (!$email) {
                return ApiResponse::error('Email not provided by Google', null, 400);
            }

            // Find or create user via service
            $user = $this->userService->findOrCreateFromGoogle($googleUser);

            if (!$user) {
                return ApiResponse::error('Failed to create user account', null, 500);
            }

            // Revoke old tokens and create new one
            $user->tokens()->delete();
            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            // Load relations efficiently with selected columns
            $user->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender',
                'employee:id,user_id,position,department,employee_code',
                'employee.manager:id,user_id,position',
                'employee.manager.profile:id,user_id,phone',
            ]);

            return ApiResponse::success('Google login successful', [
                'user'  => $user,
                'token' => $token,
            ]);

        } catch (InvalidStateException $e) {
            return ApiResponse::error('Invalid OAuth state', null, 401);

        } catch (\Throwable $e) {
            return ApiResponse::error('Google authentication failed', null, 500);
        }
    }
}
