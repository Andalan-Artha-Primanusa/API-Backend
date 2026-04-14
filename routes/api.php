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
use App\Http\Controllers\Api\PeopleInsightController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\CompetencyController;
use App\Http\Controllers\Api\LeavePolicyController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use App\Http\Controllers\Api\HrServiceRequestController;
use App\Http\Controllers\Api\ReportingController;

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
Route::middleware(['auth:sanctum', 'audit.trail'])->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
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
        Route::get('/payroll/{id}/slip', [PayrollController::class, 'myPayrollSlip']);
        Route::get('/payroll/{id}/export', [PayrollController::class, 'exportSlipCsv']);
        Route::get('/payroll/{id}/export-pdf', [PayrollController::class, 'exportSlipPdf']);

        // Training and competencies
        Route::get('/trainings', [TrainingController::class, 'myTrainings']);
        Route::get('/competencies', [CompetencyController::class, 'myCompetencies']);
        Route::get('/assets', [AssetController::class, 'myAssets']);
        Route::get('/documents', [EmployeeDocumentController::class, 'myDocuments']);
        Route::post('/documents', [EmployeeDocumentController::class, 'store']);
        Route::get('/requests', [HrServiceRequestController::class, 'myRequests']);
        Route::post('/requests', [HrServiceRequestController::class, 'store']);
        Route::get('/requests/{id}', [HrServiceRequestController::class, 'show']);
        Route::post('/requests/{id}/comments', [HrServiceRequestController::class, 'comment']);
    });

    Route::prefix('leaves')->group(function () {
        // ESS Leave Management
        Route::get('/my', [LeaveController::class, 'myLeaves']);
        Route::get('/balance', [LeaveController::class, 'balance']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    Route::prefix('attendance')->group(function () {
        // ESS Attendance Management
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/intelligence', [AttendanceController::class, 'intelligence']);
        Route::get('/overtime', [AttendanceController::class, 'overtime']);
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

        // PEOPLE INSIGHTS DASHBOARD
        Route::get('/insights/people', [PeopleInsightController::class, 'dashboard']);
        Route::get('/insights/people/detailed', [PeopleInsightController::class, 'detailedDashboard']);
        Route::get('/insights/people/trends', [PeopleInsightController::class, 'trends']);
        Route::get('/insights/people/team-health', [PeopleInsightController::class, 'teamHealth']);
        Route::get('/insights/people/employee/{userId}', [PeopleInsightController::class, 'employeeRiskDetail']);

        Route::prefix('attendance')->group(function () {
            Route::get('/employee/{userId}/intelligence', [AttendanceController::class, 'employeeIntelligence']);
        });

        Route::prefix('leave-policies')->group(function () {
            Route::get('/', [LeavePolicyController::class, 'index']);
            Route::post('/', [LeavePolicyController::class, 'store']);
            Route::put('/{id}', [LeavePolicyController::class, 'update']);
            Route::delete('/{id}', [LeavePolicyController::class, 'destroy']);
        });

        Route::prefix('training')->group(function () {
            Route::get('/programs', [TrainingController::class, 'index']);
            Route::post('/programs', [TrainingController::class, 'store']);
            Route::get('/programs/{id}', [TrainingController::class, 'show']);
            Route::put('/programs/{id}', [TrainingController::class, 'update']);
            Route::delete('/programs/{id}', [TrainingController::class, 'destroy']);
            Route::post('/programs/{id}/enroll', [TrainingController::class, 'enroll']);
            Route::put('/enrollments/{id}/complete', [TrainingController::class, 'complete']);
        });

        Route::prefix('competencies')->group(function () {
            Route::get('/', [CompetencyController::class, 'index']);
            Route::post('/', [CompetencyController::class, 'store']);
            Route::get('/{id}', [CompetencyController::class, 'show']);
            Route::put('/{id}', [CompetencyController::class, 'update']);
            Route::delete('/{id}', [CompetencyController::class, 'destroy']);
            Route::post('/{id}/assign', [CompetencyController::class, 'assignToEmployee']);
            Route::get('/employee/{employeeId}', [CompetencyController::class, 'employeeCompetencies']);
        });

        Route::prefix('assets')->group(function () {
            Route::get('/', [AssetController::class, 'index']);
            Route::post('/', [AssetController::class, 'store']);
            Route::get('/{id}', [AssetController::class, 'show']);
            Route::put('/{id}', [AssetController::class, 'update']);
            Route::delete('/{id}', [AssetController::class, 'destroy']);
            Route::post('/{id}/assign', [AssetController::class, 'assign']);
            Route::put('/assignments/{assignmentId}/return', [AssetController::class, 'returnAsset']);
        });

        Route::prefix('documents')->group(function () {
            Route::get('/', [EmployeeDocumentController::class, 'index']);
            Route::post('/', [EmployeeDocumentController::class, 'store']);
            Route::get('/expiring', [EmployeeDocumentController::class, 'expiring']);
            Route::get('/{id}', [EmployeeDocumentController::class, 'show']);
            Route::put('/{id}', [EmployeeDocumentController::class, 'update']);
            Route::delete('/{id}', [EmployeeDocumentController::class, 'destroy']);
            Route::put('/{id}/review', [EmployeeDocumentController::class, 'review']);
        });

        Route::prefix('requests')->group(function () {
            Route::get('/', [HrServiceRequestController::class, 'index']);
            Route::post('/', [HrServiceRequestController::class, 'store']);
            Route::get('/{id}', [HrServiceRequestController::class, 'show']);
            Route::put('/{id}/assign', [HrServiceRequestController::class, 'assign']);
            Route::put('/{id}/status', [HrServiceRequestController::class, 'updateStatus']);
            Route::post('/{id}/comments', [HrServiceRequestController::class, 'comment']);
            Route::delete('/{id}', [HrServiceRequestController::class, 'destroy']);
        });

        // REPORTING & ANALYTICS
        Route::prefix('reports')->group(function () {
            Route::get('/dashboard-summary', [ReportingController::class, 'dashboardSummary']);
            Route::get('/attendance', [ReportingController::class, 'attendanceAnalytics']);
            Route::get('/leave', [ReportingController::class, 'leaveAnalytics']);
            Route::get('/payroll', [ReportingController::class, 'payrollAnalytics']);
            Route::get('/competency', [ReportingController::class, 'competencyAnalytics']);
            Route::get('/employee-lifecycle', [ReportingController::class, 'employeeLifecycleAnalytics']);
            Route::get('/assets', [ReportingController::class, 'assetAnalytics']);
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

    Route::middleware('role:admin,hr,super_admin')->prefix('employees')->group(function () {
        Route::put('/{id}/onboarding/start', [EmployeeController::class, 'startOnboarding']);
        Route::put('/{id}/onboarding/complete', [EmployeeController::class, 'completeOnboarding']);
        Route::put('/{id}/offboarding/start', [EmployeeController::class, 'offboard']);
        Route::put('/{id}/offboarding/complete', [EmployeeController::class, 'completeOffboarding']);
    });

    // PAYROLL (HR / Admin)
    Route::middleware('role:admin,hr,super_admin')->group(function () {
        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::post('/generate/monthly', [PayrollController::class, 'generateMonthly']);
            Route::get('/{id}', [PayrollController::class, 'show']);
            Route::get('/{id}/slip', [PayrollController::class, 'slip']);
            Route::get('/{id}/export', [PayrollController::class, 'exportSlipCsv']);
            Route::get('/{id}/export-pdf', [PayrollController::class, 'exportSlipPdf']);
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

        Route::prefix('admin/notifications')->group(function () {
            Route::post('/', [NotificationController::class, 'store']);
            Route::post('/broadcast', [NotificationController::class, 'broadcast']);
        });

        Route::prefix('admin')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        });

        Route::prefix('admin')->group(function () {
            Route::get('/roles', [RoleController::class, 'index']);
            Route::get('/permissions', [PermissionController::class, 'index']);
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
            Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
        });
    });

});
