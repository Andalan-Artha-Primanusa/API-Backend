<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TestingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

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
