<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // 🔥 SINGLE LOGIN
            $user->tokens()->delete();

            $token = $user->createToken('google-auth')->plainTextToken;

            return ApiResponse::success(
                'Login Google berhasil',
                [
                    'user' => $user,
                    'token' => $token,
                ]
            );
        } catch (\Throwable $e) {
            return ApiResponse::error(
                'Google authentication gagal',
                'Invalid OAuth response',
                401
            );
        }
    }
}
