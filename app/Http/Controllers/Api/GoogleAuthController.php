<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Services\UserService;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}
    /**
     * Return the Google OAuth redirect URL for the SPA frontend.
     */
    public function redirect()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return ApiResponse::success('Google OAuth redirect URL', ['url' => $url]);
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // ✅ VALIDASI EMAIL (SETELAH ambil user)
            if (!$googleUser->getEmail()) {
                return ApiResponse::error('Email not provided by Google', null, 400);
            }

            $user = $this->userService->findOrCreateFromGoogle($googleUser);

            // 🔥 samakan dengan login biasa
            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;

            return ApiResponse::success(
                'Google login successful',
                [
                    'user' => $user->load('roles'),
                    'token' => $token,
                ]
            );

        } catch (\Throwable $e) {
            \Log::error('Google SSO Error', [
                'message' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                'Google authentication failed',
                'Internal server error',
                500
            );
        }
    }
}
