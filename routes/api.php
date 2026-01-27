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

    Route::get('/users', [UserProfileController::class, 'index']);
    Route::post('/users', [UserProfileController::class, 'store']);
    Route::get('/users/{id}', [UserProfileController::class, 'show']);
    Route::put('/users/{id}', [UserProfileController::class, 'update']);
    Route::delete('/users/{id}', [UserProfileController::class, 'destroy']);
});
