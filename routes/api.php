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
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PayrollDetailController;
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API HRIS aktif 🚀'
    ]);
});
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

    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/history', [AttendanceController::class, 'history']);
    Route::get('/today', [AttendanceController::class, 'today']);

    // 🔥 ADMIN (SEMUA DATA ABSENSI)
    Route::get('/all', [AttendanceController::class, 'all']);

    // 🔥 DETAIL ABSENSI (optional)
    Route::get('/{id}', [AttendanceController::class, 'show']);

    // 🔥 DELETE ABSENSI (optional admin)
    Route::delete('/{id}', [AttendanceController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->get('/my-payroll', [PayrollController::class, 'myPayroll']);


/*
|--------------------------------------------------------------------------
| LEAVE MANAGEMENT (CUTI)
|--------------------------------------------------------------------------
*/
Route::prefix('leaves')->group(function () {

    // ✅ USER
    Route::get('/', [LeaveController::class, 'index']);
    Route::post('/', [LeaveController::class, 'store']);
    Route::get('/my', [LeaveController::class, 'myLeaves']);
    Route::get('/balance', [LeaveController::class, 'balance']);
    Route::get('/calendar', [LeaveController::class, 'calendar']);

    // ✅ DETAIL
    Route::get('/{id}', [LeaveController::class, 'show']);
    Route::delete('/{id}', [LeaveController::class, 'destroy']);
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

    Route::middleware(['auth:sanctum', 'role:admin,hr,super_admin'])->group(function () {        // PAYROLL
        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::get('/{id}', [PayrollController::class, 'show']); // ✅ penting
            Route::put('/{id}', [PayrollController::class, 'update']); // ✅ penting
            Route::delete('/{id}', [PayrollController::class, 'destroy']);

            Route::post('/generate/monthly', [PayrollController::class, 'generateMonthly']);
            Route::post('/{id}/approve', [PayrollController::class, 'approve']);
            Route::post('/{id}/pay', [PayrollController::class, 'pay']);
        });

        // PAYROLL DETAIL (ikut protect)
        Route::prefix('payroll-details')->group(function () {
            Route::get('/{payroll_id}', [PayrollDetailController::class, 'index']);
            Route::post('/', [PayrollDetailController::class, 'store']);
            Route::put('/{id}', [PayrollDetailController::class, 'update']);
            Route::delete('/{id}', [PayrollDetailController::class, 'destroy']);
        });

    });

    Route::middleware(['auth:sanctum', 'role:manager,hr,super_admin'])->group(function () {

    Route::prefix('leaves')->group(function () {

        Route::get('/pending', [LeaveController::class, 'pending']);
        Route::put('/{id}/approve', [LeaveController::class, 'approve']);
        Route::put('/{id}/reject', [LeaveController::class, 'reject']);

    });

});