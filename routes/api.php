<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PayrollDetailController;
use App\Http\Controllers\Api\ReimbursementController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | PUBLIC ROUTES
 * |--------------------------------------------------------------------------
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
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// AUTH — rate-limited to prevent brute-force
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1');

// GOOGLE SSO
Route::prefix('auth')->group(function () {
    Route::get('/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});

/*
 * |--------------------------------------------------------------------------
 * | PROTECTED ROUTES (SANCTUM)
 * |--------------------------------------------------------------------------
 */

Route::middleware('auth:sanctum')->group(function () {
    // AUTH
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/my', [KpiController::class, 'myKpi']);
    Route::post('/{id}/submit', [KpiController::class, 'submit']);

    /*
     * |--------------------------------------------------------------------------
     * | EMPLOYEE SELF-SERVICE
     * |--------------------------------------------------------------------------
     */
    Route::prefix('my')->group(function () {
        // KPI
        Route::get('/kpi', [KpiController::class, 'myKpi']);
        Route::post('/kpi/{id}/submit', [KpiController::class, 'submit']);

        // REIMBURSEMENTS
        Route::get('/reimbursements', [ReimbursementController::class, 'myReimbursements']);
        Route::post('/reimbursements', [ReimbursementController::class, 'createMyReimbursement']);
        Route::post('/reimbursements/{id}/submit', [ReimbursementController::class, 'submit']);
    });

    /*
     * |--------------------------------------------------------------------------
     * | USER PROFILE
     * |--------------------------------------------------------------------------
     */
    Route::apiResource('profiles', UserProfileController::class);

    /*
     * |--------------------------------------------------------------------------
     * | EMPLOYEE
     * |--------------------------------------------------------------------------
     */
    Route::apiResource('employees', EmployeeController::class);

    /*
     * |--------------------------------------------------------------------------
     * | USER MANAGEMENT
     * |--------------------------------------------------------------------------
     */
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);

    /*
     * |--------------------------------------------------------------------------
     * | ATTENDANCE (ABSENSI)
     * |--------------------------------------------------------------------------
     */
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

        // 🔥 ADMIN (SEMUA DATA ABSENSI)
        Route::get('/all', [AttendanceController::class, 'all']);

        // 🔥 DETAIL ABSENSI (optional)
        Route::get('/{id}', [AttendanceController::class, 'show']);

        // 🔥 DELETE ABSENSI (optional admin)
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->get('/my-payroll', [PayrollController::class, 'myPayroll']);

    /*
     * |--------------------------------------------------------------------------
     * | LEAVE MANAGEMENT (CUTI)
     * |--------------------------------------------------------------------------
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
 * |--------------------------------------------------------------------------
 * | LOCATION (MASTER DATA LOKASI)
 * |--------------------------------------------------------------------------
 */
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
    Route::apiResource('locations', LocationController::class);
});

Route::middleware(['auth:sanctum', 'role:admin,hr,super_admin'])->group(function () {  // PAYROLL
    Route::prefix('payroll')->group(function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::post('/', [PayrollController::class, 'store']);
        Route::get('/{id}', [PayrollController::class, 'show']);  // ✅ penting
        Route::put('/{id}', [PayrollController::class, 'update']);  // ✅ penting
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

    /*
     * |--------------------------------------------------------------------------
     * | KPI MANAGEMENT
     * |--------------------------------------------------------------------------
     */
    Route::prefix('kpis')->group(function () {
        // ✅ LIST SEMUA KPI
        Route::get('/', [KpiController::class, 'index']);

        // ✅ BUAT KPI
        Route::post('/', [KpiController::class, 'store']);

        // ✅ DETAIL KPI
        Route::get('/{id}', [KpiController::class, 'show']);

        // ✅ UPDATE KPI
        Route::put('/{id}', [KpiController::class, 'update']);

        // ✅ DELETE KPI
        Route::delete('/{id}', [KpiController::class, 'destroy']);

        // ✅ KPI PER EMPLOYEE
        Route::get('/employee/{employee_id}', [KpiController::class, 'byEmployee']);

        // ✅ APPROVAL KPI (optional, kalau ada flow approval)
        Route::put('/{id}/approve', [KpiController::class, 'approve']);
    });

    /*
     * |--------------------------------------------------------------------------
     * | REIMBURSEMENT MANAGEMENT
     * |--------------------------------------------------------------------------
     */
    Route::prefix('reimbursements')->group(function () {
        // ✅ LIST SEMUA REIMBURSEMENT
        Route::get('/', [ReimbursementController::class, 'index']);

        // ✅ BUAT REIMBURSEMENT
        Route::post('/', [ReimbursementController::class, 'store']);

        // ✅ DETAIL REIMBURSEMENT
        Route::get('/{id}', [ReimbursementController::class, 'show']);

        // ✅ UPDATE REIMBURSEMENT
        Route::put('/{id}', [ReimbursementController::class, 'update']);

        // ✅ DELETE REIMBURSEMENT
        Route::delete('/{id}', [ReimbursementController::class, 'destroy']);

        // ✅ REIMBURSEMENT PER EMPLOYEE
        Route::get('/employee/{employee_id}', [ReimbursementController::class, 'byEmployee']);

        // ✅ APPROVAL WORKFLOW
        Route::put('/{id}/approve', [ReimbursementController::class, 'approve']);
        Route::put('/{id}/reject', [ReimbursementController::class, 'reject']);
        Route::put('/{id}/mark-paid', [ReimbursementController::class, 'markAsPaid']);

        // ✅ PENDING REIMBURSEMENTS
        Route::get('/pending', [ReimbursementController::class, 'pending']);

        // ✅ STATISTICS
        Route::get('/statistics', [ReimbursementController::class, 'statistics']);
    });
});
        Route::get('/all', [AttendanceController::class, 'all']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    // LOCATION
    Route::apiResource('locations', LocationController::class);

    /*
    |--------------------------------------------------------------------------
    | ADMIN CONTROL (RBAC)
    |--------------------------------------------------------------------------
    */
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin,super_admin'])
    ->group(function () {

        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/users', [UserController::class, 'index']);

        Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
        Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
    });
