<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// GOOGLE SSO
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

    // PROFILE
    Route::apiResource('profiles', UserProfileController::class);

    // EMPLOYEE
    Route::apiResource('employees', EmployeeController::class);

    // LEAVE
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::put('/leaves/{id}', [LeaveController::class, 'update']);

    // ATTENDANCE
    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/all', [AttendanceController::class, 'all']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN CONTROL (RBAC)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        // 🔥 LIST
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/users', [UserController::class, 'index']);

        // 🔥 ASSIGN
        Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
        Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
    });
});

/*
|--------------------------------------------------------------------------
| LOCATION
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
    Route::apiResource('locations', LocationController::class);
});
