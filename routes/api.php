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
| PROTECTED ROUTES (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // AUTH
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */
    Route::apiResource('profiles', UserProfileController::class);

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE
    |--------------------------------------------------------------------------
    */
    Route::apiResource('employees', EmployeeController::class);

    /*
    |--------------------------------------------------------------------------
    | LEAVE
    |--------------------------------------------------------------------------
    */
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::put('/leaves/{id}', [LeaveController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE (ABSENSI)
    |--------------------------------------------------------------------------
    */
    Route::prefix('attendance')->group(function () {

    // ✅ CHECK-IN & CHECK-OUT
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    // ✅ USER
    Route::get('/history', [AttendanceController::class, 'history']);
    Route::get('/today', [AttendanceController::class, 'today']);

    // 🔥 ADMIN (SEMUA DATA ABSENSI)
    Route::get('/all', [AttendanceController::class, 'all']);

    // 🔥 DETAIL ABSENSI (optional)
    Route::get('/{id}', [AttendanceController::class, 'show']);

    // 🔥 DELETE ABSENSI (optional admin)
    Route::delete('/{id}', [AttendanceController::class, 'destroy']);

});
});
/*
|--------------------------------------------------------------------------
| LOCATION (MASTER DATA LOKASI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
    Route::apiResource('locations', LocationController::class);
});