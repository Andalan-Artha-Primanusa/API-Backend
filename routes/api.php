<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\EmployeeController;

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

    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::put('/leaves/{id}', [LeaveController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::apiResource('profiles', UserProfileController::class);

    // 🔐 ROLE BASED ROUTES

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin-only', function () {
            return response()->json(['message' => 'Super Admin Only']);
        });
    });

    Route::middleware('role:admin|super_admin')->group(function () {
        Route::get('/admin-area', function () {
            return response()->json(['message' => 'Admin Area']);
        });
    });

    Route::middleware('role:hr')->group(function () {
        Route::get('/hr-only', function () {
            return response()->json(['message' => 'HR Only']);
        });
    });

    Route::middleware('role:manager')->group(function () {
        Route::get('/manager-only', function () {
            return response()->json(['message' => 'Manager Only']);
        });
    });
});

use App\Http\Controllers\Api\UserController;

Route::middleware(['auth:sanctum', 'role:admin|super_admin'])->group(function () {
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('employees', EmployeeController::class);
});
