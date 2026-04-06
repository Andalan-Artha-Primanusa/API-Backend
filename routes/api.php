<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| AUTH GOOGLE (SSO)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::get('/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profiles', [UserProfileController::class, 'index']);
    Route::post('/profiles', [UserProfileController::class, 'store']);
    Route::get('/profiles/{id}', [UserProfileController::class, 'show']);
    Route::put('/profiles/{id}', [UserProfileController::class, 'update']);
    Route::delete('/profiles/{id}', [UserProfileController::class, 'destroy']);
});
