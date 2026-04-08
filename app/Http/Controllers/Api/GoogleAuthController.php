<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
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

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                ]);

                // Assign default employee role via RBAC pivot table
                $employeeRole = Role::where('name', User::ROLE_EMPLOYEE)->first();
                if ($employeeRole) {
                    $user->roles()->attach($employeeRole->id);
                }
            }

            $token = $user->createToken('google-auth')->plainTextToken;

            return ApiResponse::success(
                'Google login successful',
                [
                    'user' => $user->load('roles'),
                    'token' => $token,
                ]
            );
        } catch (\Throwable $e) {
            return ApiResponse::error(
                'Google authentication failed',
                'Invalid OAuth response',
                401
            );
        }
    }
}
