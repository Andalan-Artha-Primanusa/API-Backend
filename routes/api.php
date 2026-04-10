<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\ReimbursementController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PayrollDetailController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkScheduleController;

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
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');

// GOOGLE SSO
Route::prefix('auth')->group(function () {
    Route::get('/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTHENTICATED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */
    Route::apiResource('profiles', UserProfileController::class);

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE SELF-SERVICE (ESS)
    |--------------------------------------------------------------------------
    | All routes that apply to the currently authenticated user's employee file
    */
    Route::prefix('my')->group(function () {
        // KPI
        Route::get('/kpi', [KpiController::class, 'myKpi']);
        Route::post('/kpi/{id}/submit', [KpiController::class, 'submit']);

        // Reimbursements
        Route::get('/reimbursements', [ReimbursementController::class, 'myReimbursements']);
        Route::post('/reimbursements', [ReimbursementController::class, 'createMyReimbursement']);
        Route::post('/reimbursements/{id}/submit', [ReimbursementController::class, 'submit']);

        // Payroll
        Route::get('/payroll', [PayrollController::class, 'myPayroll']);
    });

    Route::prefix('leaves')->group(function () {
        // ESS Leave Management
        Route::get('/my', [LeaveController::class, 'myLeaves']);
        Route::get('/balance', [LeaveController::class, 'balance']);
    });

    Route::prefix('attendance')->group(function () {
        // ESS Attendance Management
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/today', [AttendanceController::class, 'today']);
    });

    // MANAGER / HR / ADMIN (Role based grouped endpoints)
    Route::middleware('role:admin,manager,hr,super_admin')->group(function () {

        // APPROVALS FOR MANAGERS / HR (LEAVES)
        Route::prefix('leaves')->group(function () {
            Route::get('/pending', [LeaveController::class, 'pending']);
            Route::put('/{id}/approve', [LeaveController::class, 'approve']);
            Route::put('/{id}/reject', [LeaveController::class, 'reject']);
        });

        // KPI MANAGEMENT
        Route::prefix('kpis')->group(function () {
            Route::get('/', [KpiController::class, 'index']);
            Route::post('/', [KpiController::class, 'store']);
            Route::get('/employee/{employee_id}', [KpiController::class, 'byEmployee']);
            Route::get('/{id}', [KpiController::class, 'show']);
            Route::put('/{id}', [KpiController::class, 'update']);
            Route::delete('/{id}', [KpiController::class, 'destroy']);
            Route::put('/{id}/approve', [KpiController::class, 'approve']);
        });

        // REIMBURSEMENT MANAGEMENT
        Route::prefix('reimbursements')->group(function () {
            Route::get('/', [ReimbursementController::class, 'index']);
            Route::post('/', [ReimbursementController::class, 'store']);
            Route::get('/pending', [ReimbursementController::class, 'pending']);
            Route::get('/statistics', [ReimbursementController::class, 'statistics']);
            Route::get('/employee/{employee_id}', [ReimbursementController::class, 'byEmployee']);
            Route::get('/{id}', [ReimbursementController::class, 'show']);
            Route::put('/{id}', [ReimbursementController::class, 'update']);
            Route::delete('/{id}', [ReimbursementController::class, 'destroy']);
            Route::put('/{id}/approve', [ReimbursementController::class, 'approve']);
            Route::put('/{id}/reject', [ReimbursementController::class, 'reject']);
            Route::put('/{id}/mark-paid', [ReimbursementController::class, 'markAsPaid']);
        });

    });

    // We keep these base leaves endpoints for standard access
    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::get('/calendar', [LeaveController::class, 'calendar']);
        Route::get('/{id}', [LeaveController::class, 'show']);
        Route::put('/{id}', [LeaveController::class, 'update']);
        Route::delete('/{id}', [LeaveController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | HR / MANAGER / ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    // ATTENDANCE (Admin)
    Route::prefix('attendance')->group(function () {
        Route::get('/all', [AttendanceController::class, 'all']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    // EMPLOYEE MANAGEMENT
    Route::apiResource('employees', EmployeeController::class);

    // PAYROLL (HR / Admin)
    Route::middleware('role:admin,hr,super_admin')->group(function () {
        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::post('/generate/monthly', [PayrollController::class, 'generateMonthly']);
            Route::get('/{id}', [PayrollController::class, 'show']);
            Route::put('/{id}', [PayrollController::class, 'update']);
            Route::delete('/{id}', [PayrollController::class, 'destroy']);
            Route::post('/{id}/approve', [PayrollController::class, 'approve']);
            Route::post('/{id}/pay', [PayrollController::class, 'pay']);
        });

        Route::prefix('payroll-details')->group(function () {
            Route::get('/{payroll_id}', [PayrollDetailController::class, 'index']);
            Route::post('/', [PayrollDetailController::class, 'store']);
            Route::put('/{id}', [PayrollDetailController::class, 'update']);
            Route::delete('/{id}', [PayrollDetailController::class, 'destroy']);
        });
    });

    // MASTER DATA & SYSTEM SETTINGS (Admin ONLY)
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::apiResource('locations', LocationController::class);

        Route::apiResource('work-schedules', WorkScheduleController::class);

        Route::prefix('admin')->group(function () {
            Route::get('/roles', [RoleController::class, 'index']);
            Route::get('/permissions', [PermissionController::class, 'index']);
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
            Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
        });
    });

});
