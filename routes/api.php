<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/testing', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'API running'
    ], 200);
});

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

    // USER PROFILE CRUD
    Route::get('/users', [UserProfileController::class, 'index']);
    Route::post('/users', [UserProfileController::class, 'store']);
    Route::get('/users/{id}', [UserProfileController::class, 'show']);
    Route::put('/users/{id}', [UserProfileController::class, 'update']);
    Route::delete('/users/{id}', [UserProfileController::class, 'destroy']);

});
