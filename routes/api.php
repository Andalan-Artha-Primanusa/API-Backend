
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromotionController;
use App\Services\ProgressiveTaxService;
use App\Http\Controllers\Api\EmploymentLetterController;
use App\Http\Controllers\Api\AssignmentLetterController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\SeveranceController;
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
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use App\Http\Controllers\Api\HrServiceRequestController;
use App\Http\Controllers\Api\ReportingController;
use App\Http\Controllers\Api\ApprovalFlowController;
use App\Http\Controllers\Api\DataImportController;
use App\Http\Controllers\Api\OrgStructureController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\RecruitmentController;
use App\Http\Controllers\Api\BenefitController;
use App\Http\Controllers\Api\PerformanceReviewController;
use App\Http\Controllers\Api\EnterpriseAtsController;
use App\Http\Controllers\Api\CareerDevelopmentController;
use App\Http\Controllers\Api\BiometricIntegrationController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\WorkforcePolicyController;
use App\Http\Controllers\Api\EnterpriseOpsController;
use App\Http\Controllers\Api\OKRController;
use App\Http\Controllers\Api\Review360Controller;
use App\Http\Controllers\Api\CalibrationController;
use App\Http\Controllers\Api\WorkforceComplianceController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\KpiPeriodController;
// PROGRESSIVE TAX (PPh21 Progresif)
Route::post('tax/progressive/calculate', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'annual_income' => 'required|numeric|min:0',
    ]);
    $service = new ProgressiveTaxService();
    $result = $service->calculate($validated['annual_income']);
    return \App\Helpers\ApiResponse::success('Pajak progresif dihitung', $result);
});

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
// ...existing use statements...
// Surat Pengalaman Kerja & Surat Keterangan Bekerja

// ...existing public routes...

// AUTH
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
// Backward-compatible alias for older frontend bundles
Route::post('/vendor-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

// GOOGLE SSO
Route::prefix('auth')->group(function () {
    Route::get('/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});

Route::get('/documents/{filename}', [EmployeeDocumentController::class, 'download']);
Route::get('/overtime/evidences/{id}/file', [OvertimeController::class, 'downloadEvidence'])
    ->middleware('signed')
    ->whereNumber('id')
    ->name('overtime.evidences.file');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTHENTICATED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'audit.trail'])->group(function () {
    // Surat Pengalaman Kerja & Surat Keterangan Bekerja
    Route::post('employees/{employee}/experience-letter', [EmploymentLetterController::class, 'generateExperienceLetter']);
    Route::post('employees/{employee}/employment-letter', [EmploymentLetterController::class, 'generateEmploymentLetter']);
    // Assignment Letter (Surat Tugas) dengan approval
    Route::get('assignment-letters', [AssignmentLetterController::class, 'index']);
    Route::post('assignment-letters', [AssignmentLetterController::class, 'store']);
    Route::get('assignment-letters/{id}', [AssignmentLetterController::class, 'show']);
    Route::post('assignment-letters/{id}/approve', [AssignmentLetterController::class, 'approve']);
    Route::post('assignment-letters/{id}/reject', [AssignmentLetterController::class, 'reject']);
    Route::get('assignment-letters/{id}/pdf', [AssignmentLetterController::class, 'generatePdf']);

    // Task Management
    Route::get('tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{id}', [TaskController::class, 'show']);
    Route::put('tasks/{id}', [TaskController::class, 'update']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
    Route::get('my/tasks', [TaskController::class, 'myTasks']);

    // Promotions
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::post('promotions', [PromotionController::class, 'store']);
    Route::post('promotions/{id}/approve', [PromotionController::class, 'approve']);
    Route::post('promotions/{id}/reject', [PromotionController::class, 'reject']);
    Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);

    Route::middleware('role:*')->group(function () {
        // Backward-compatible single-endpoint approval (accepts JSON { id })
        Route::match(['get', 'post', 'put'], '/overtime/approval', [OvertimeController::class, 'approveByBody']);
        Route::get('/overtime/requests', [OvertimeController::class, 'index']);
        Route::get('/overtime/requests/pending', [OvertimeController::class, 'pending']);
        Route::put('/overtime/requests/{id}/approve', [OvertimeController::class, 'approve'])->whereNumber('id');
        Route::put('/overtime/requests/{id}/reject', [OvertimeController::class, 'reject'])->whereNumber('id');
        Route::get('/overtime/evidences/request/{id}', [OvertimeController::class, 'overtimeEvidences'])->whereNumber('id');
        Route::put('/overtime/evidences/{id}/approve', [OvertimeController::class, 'approveEvidence'])->whereNumber('id');
        Route::put('/overtime/evidences/{id}/reject', [OvertimeController::class, 'rejectEvidence'])->whereNumber('id');
        Route::post('promotions/{id}/report/approve', [PromotionController::class, 'approveReport']);
        Route::post('promotions/{id}/report/reject', [PromotionController::class, 'rejectReport']);
    });

    // Pesangon PP 35/2021
    Route::get('employees/{employee}/severance/calculate', [SeveranceController::class, 'calculate']);
    Route::get('employees/{employee}/severance/export', [SeveranceController::class, 'exportExcel']);

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
        // Profile
        Route::get('/profile', [UserProfileController::class, 'me']);
        // KPI (Legacy)
        Route::get('/kpi', [KpiController::class, 'myKpi']);
        Route::post('/kpi/{id}/accept', [KpiController::class, 'accept']);
        Route::put('/kpi/{id}/progress', [KpiController::class, 'updateProgress']);
        Route::post('/kpi/{id}/submit', [KpiController::class, 'submit']);

        // KPI Periods (ESS)
        Route::get('/kpi-periods', [KpiPeriodController::class, 'myKpiPeriods']);
        Route::put('/kpi-periods/{id}/items', [KpiPeriodController::class, 'myUpdateItems']);
        Route::post('/kpi-periods/{id}/submit', [KpiPeriodController::class, 'mySubmit']);

        // Reimbursements
        Route::get('/reimbursements', [ReimbursementController::class, 'myReimbursements']);
        Route::post('/reimbursements', [ReimbursementController::class, 'createMyReimbursement']);
        Route::put('/reimbursements/{id}', [ReimbursementController::class, 'update']);
        Route::post('/reimbursements/{id}/submit', [ReimbursementController::class, 'submit']);

        // Payroll
        Route::get('/payroll', [PayrollController::class, 'myPayroll']);
        Route::get('/payroll/{id}/slip', [PayrollController::class, 'myPayrollSlip']);
        Route::get('/payroll/{id}/export', [PayrollController::class, 'exportSlipCsv']);
        Route::get('/payroll/{id}/export-pdf', [PayrollController::class, 'exportSlipPdf']);

        // Training and competencies
        Route::get('/trainings', [TrainingController::class, 'myTrainings']);
        Route::get('/trainings/available', [TrainingController::class, 'availableTrainings']);
        Route::post('/trainings/{id}/enroll', [TrainingController::class, 'selfEnroll']);
        Route::get('/competencies', [CompetencyController::class, 'myCompetencies']);
        Route::get('/assets', [AssetController::class, 'myAssets']);
        Route::put('/assets/return/{assignmentId}', [AssetController::class, 'returnAssetByEmployee']);
        Route::get('/benefits', [BenefitController::class, 'myBenefits']);
        Route::get('/performance-reviews', [PerformanceReviewController::class, 'myReviews']);
        Route::get('/documents', [EmployeeDocumentController::class, 'myDocuments']);
        Route::post('/documents', [EmployeeDocumentController::class, 'store']);
        Route::get('/requests', [HrServiceRequestController::class, 'myRequests']);
        Route::post('/requests', [HrServiceRequestController::class, 'store']);
        Route::get('/requests/{id}', [HrServiceRequestController::class, 'show']);
        Route::post('/requests/{id}/comments', [HrServiceRequestController::class, 'comment']);

        // Overtime requests (ESS)
        Route::get('/overtime', [OvertimeController::class, 'myOvertimeRequests']);
        Route::put('/overtime/{id}/reason', [OvertimeController::class, 'addReason']);
        Route::post('/overtime/{id}/evidence', [OvertimeController::class, 'uploadEvidence']);
        Route::get('/overtime/{id}/evidences', [OvertimeController::class, 'myOvertimeEvidences']);

        Route::post('/promotions/{id}/report/submit', [PromotionController::class, 'submitReport']);

        // Promotions (ESS)
        Route::get('/promotions', [PromotionController::class, 'myPromotions']);
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

    Route::middleware('role:*')->group(function () {
        Route::prefix('organization')->group(function () {
            Route::get('/directory', [OrgStructureController::class, 'directory']);
            Route::get('/summary', [OrgStructureController::class, 'summary']);
            Route::get('/chart', [OrgStructureController::class, 'orgChart']);
            Route::get('/team/{managerUserId}', [OrgStructureController::class, 'teamMembers']);
            Route::get('/master-data', [OrgStructureController::class, 'masterData']);
        });
    });

// Approval Flows - admin/HR/super_admin can manage
Route::middleware('role:*')->prefix('approval-flows')->group(function () {
    Route::get('/', [ApprovalFlowController::class, 'index']);
    Route::post('/', [ApprovalFlowController::class, 'store']);
    Route::get('/{id}', [ApprovalFlowController::class, 'show']);
    Route::put('/{id}', [ApprovalFlowController::class, 'update']);
    Route::delete('/{id}', [ApprovalFlowController::class, 'destroy']);
});

// Approval History - authenticated users can view history
Route::middleware('auth:sanctum')->prefix('approval-history')->group(function () {
    Route::get('/{module}/{moduleId}', [ApprovalFlowController::class, 'history']);
});

    Route::middleware('role:*')->prefix('compliance')->group(function () {
        Route::get('/overview', [ComplianceController::class, 'overview']);
        Route::get('/audit-summary', [ComplianceController::class, 'auditSummary']);
        Route::get('/expiring-documents', [ComplianceController::class, 'expiringDocuments']);
    });

    Route::prefix('attendance')->group(function () {
        // ESS Attendance Management
        Route::get('/locations', [LocationController::class, 'activeLocations']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/intelligence', [AttendanceController::class, 'intelligence']);
        Route::get('/overtime', [AttendanceController::class, 'overtime']);
        // Backward-compatible reporting route: /api/attendance/reports
        Route::get('/reports', [ReportingController::class, 'attendanceAnalytics']);

        Route::middleware('role:*')->group(function () {
            Route::get('/all', [AttendanceController::class, 'all']);
            Route::get('/{id}', [AttendanceController::class, 'show'])->whereNumber('id');
            Route::delete('/{id}', [AttendanceController::class, 'destroy'])->whereNumber('id');
        });
    });

    // MANAGER / HR / ADMIN (Role based grouped endpoints)
    Route::middleware('role:*')->group(function () {

        // APPROVALS FOR MANAGERS / HR (LEAVES)
        Route::prefix('leaves')->group(function () {
            Route::get('/pending', [LeaveController::class, 'pending']);
            Route::put('/{id}/approve', [LeaveController::class, 'approve']);
            Route::put('/{id}/reject', [LeaveController::class, 'reject']);
        });

        // OVERTIME APPROVAL
        Route::prefix('overtime/requests')->group(function () {
            Route::get('/', [OvertimeController::class, 'index']);
            Route::get('/pending', [OvertimeController::class, 'pending']);
            Route::put('/{id}/approve', [OvertimeController::class, 'approve']);
            Route::put('/{id}/reject', [OvertimeController::class, 'reject']);
        });

        // OVERTIME EVIDENCES
        Route::prefix('overtime/evidences')->group(function () {
            Route::get('/request/{id}', [OvertimeController::class, 'overtimeEvidences']);
            Route::put('/{id}/approve', [OvertimeController::class, 'approveEvidence']);
            Route::put('/{id}/reject', [OvertimeController::class, 'rejectEvidence']);
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

        // KPI PERIOD MANAGEMENT (Period-based KPI)
        Route::prefix('kpi-periods')->group(function () {
            Route::get('/', [KpiPeriodController::class, 'index']);
            Route::post('/', [KpiPeriodController::class, 'store']);
            Route::get('/{id}', [KpiPeriodController::class, 'show']);
            Route::put('/{id}/items', [KpiPeriodController::class, 'updateItems']);
            Route::put('/{id}/approve', [KpiPeriodController::class, 'approve']);
            Route::delete('/{id}', [KpiPeriodController::class, 'destroy']);
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

        Route::prefix('training')->group(function () {
            Route::get('/programs', [TrainingController::class, 'index']);
            Route::post('/programs', [TrainingController::class, 'store']);
            Route::get('/programs/{id}', [TrainingController::class, 'show']);
            Route::put('/programs/{id}', [TrainingController::class, 'update']);
            Route::delete('/programs/{id}', [TrainingController::class, 'destroy']);
            Route::post('/programs/{id}/enroll', [TrainingController::class, 'enroll']);
            Route::get('/enrollments', [TrainingController::class, 'enrollmentsIndex']);
            Route::put('/enrollments/{id}/complete', [TrainingController::class, 'complete']);
            Route::put('/enrollments/{id}/approve', [TrainingController::class, 'approveEnrollment']);
            Route::put('/enrollments/{id}/reject', [TrainingController::class, 'rejectEnrollment']);
        });

        Route::prefix('competencies')->group(function () {
            Route::get('/', [CompetencyController::class, 'index']);
            Route::post('/', [CompetencyController::class, 'store']);
            Route::get('/{id}', [CompetencyController::class, 'show']);
            Route::put('/{id}', [CompetencyController::class, 'update']);
            Route::delete('/{id}', [CompetencyController::class, 'destroy']);
            Route::post('/{id}/assign', [CompetencyController::class, 'assignToEmployee']);
            Route::post('/assignment/{id}/assess', [CompetencyController::class, 'assessCompetency']);
            Route::get('/employee/{employeeId}', [CompetencyController::class, 'employeeCompetencies']);
        });

        Route::prefix('assets')->group(function () {
            Route::get('/', [AssetController::class, 'index']);
            Route::post('/', [AssetController::class, 'store']);
            Route::get('/assignments', [AssetController::class, 'assignments']);
            Route::get('/{id}', [AssetController::class, 'show']);
            Route::put('/{id}', [AssetController::class, 'update']);
            Route::delete('/{id}', [AssetController::class, 'destroy']);
            Route::post('/{id}/assign', [AssetController::class, 'assign']);
            Route::put('/assignments/{assignmentId}/return', [AssetController::class, 'returnAsset']);
            Route::put('/assignments/{assignmentId}/approve', [AssetController::class, 'approveAssignment']);
            Route::put('/assignments/{assignmentId}/reject', [AssetController::class, 'rejectAssignment']);
        });

        Route::prefix('documents')->group(function () {
            Route::get('/', [EmployeeDocumentController::class, 'index']);
            Route::post('/', [EmployeeDocumentController::class, 'store']);
            Route::get('/expiring', [EmployeeDocumentController::class, 'expiring']);
            Route::get('/contracts', [EmployeeDocumentController::class, 'contracts']);
            Route::get('/{id}', [EmployeeDocumentController::class, 'show']);
            Route::put('/{id}', [EmployeeDocumentController::class, 'update']);
            Route::delete('/{id}', [EmployeeDocumentController::class, 'destroy']);
            Route::put('/{id}/review', [EmployeeDocumentController::class, 'review']);
            Route::put('/{id}/approve', [EmployeeDocumentController::class, 'approveDocument']);
            Route::put('/{id}/reject', [EmployeeDocumentController::class, 'rejectDocument']);
        });

        Route::prefix('requests')->group(function () {
            Route::get('/', [HrServiceRequestController::class, 'index']);
            Route::get('/sla-summary', [HrServiceRequestController::class, 'slaSummary']);
            Route::post('/', [HrServiceRequestController::class, 'store']);
            Route::get('/{id}', [HrServiceRequestController::class, 'show']);
            Route::put('/{id}/assign', [HrServiceRequestController::class, 'assign']);
            Route::put('/{id}/status', [HrServiceRequestController::class, 'updateStatus']);
            Route::post('/{id}/comments', [HrServiceRequestController::class, 'comment']);
            Route::delete('/{id}', [HrServiceRequestController::class, 'destroy']);
        });

        Route::prefix('recruitment')->group(function () {
            Route::get('/summary', [RecruitmentController::class, 'summary']);

            Route::prefix('openings')->group(function () {
                Route::get('/', [RecruitmentController::class, 'openingsIndex']);
                Route::post('/', [RecruitmentController::class, 'openingsStore']);
                Route::get('/{id}', [RecruitmentController::class, 'openingsShow']);
                Route::put('/{id}', [RecruitmentController::class, 'openingsUpdate']);
                Route::delete('/{id}', [RecruitmentController::class, 'openingsDestroy']);
            });

            Route::prefix('candidates')->group(function () {
                Route::get('/', [RecruitmentController::class, 'candidatesIndex']);
                Route::post('/', [RecruitmentController::class, 'candidatesStore']);
                Route::get('/{id}', [RecruitmentController::class, 'candidatesShow']);
                Route::put('/{id}', [RecruitmentController::class, 'candidatesUpdate']);
                Route::put('/{id}/stage', [RecruitmentController::class, 'candidatesMoveStage']);
                Route::delete('/{id}', [RecruitmentController::class, 'candidatesDestroy']);

                Route::post('/{id}/interviews', [EnterpriseAtsController::class, 'scheduleInterview']);
                Route::post('/{id}/offer', [EnterpriseAtsController::class, 'createOffer']);
                Route::put('/{id}/background-check', [EnterpriseAtsController::class, 'upsertBackgroundCheck']);
                Route::post('/{id}/talent-pool', [EnterpriseAtsController::class, 'addToTalentPool']);
            });

            Route::post('/interviews/{id}/evaluate', [EnterpriseAtsController::class, 'evaluateInterview']);
            Route::put('/offers/{id}/status', [EnterpriseAtsController::class, 'updateOfferStatus']);
            Route::get('/talent-pool', [EnterpriseAtsController::class, 'talentPoolIndex']);
        });

        Route::prefix('benefits')->group(function () {
            Route::get('/', [BenefitController::class, 'index']);
            Route::post('/', [BenefitController::class, 'store']);
            Route::get('/employee/{employeeId}', [BenefitController::class, 'employeeBenefits']);
            Route::get('/{id}', [BenefitController::class, 'show']);
            Route::put('/{id}', [BenefitController::class, 'update']);
            Route::delete('/{id}', [BenefitController::class, 'destroy']);
            Route::post('/{id}/assign', [BenefitController::class, 'assignToEmployee']);
            Route::put('/assignments/{assignmentId}/approve', [BenefitController::class, 'approveBenefitAssignment']);
            Route::put('/assignments/{assignmentId}/reject', [BenefitController::class, 'rejectBenefitAssignment']);
        });

        Route::prefix('performance')->group(function () {
            Route::get('/summary', [PerformanceReviewController::class, 'summary']);

            Route::prefix('cycles')->group(function () {
                Route::get('/', [PerformanceReviewController::class, 'cyclesIndex']);
                Route::post('/', [PerformanceReviewController::class, 'cyclesStore']);
                Route::get('/{id}', [PerformanceReviewController::class, 'cyclesShow']);
                Route::put('/{id}', [PerformanceReviewController::class, 'cyclesUpdate']);
                Route::put('/{id}/close', [PerformanceReviewController::class, 'cyclesClose']);
            });

            Route::prefix('reviews')->group(function () {
                Route::get('/', [PerformanceReviewController::class, 'reviewsIndex']);
                Route::post('/', [PerformanceReviewController::class, 'reviewsStore']);
                Route::get('/{id}', [PerformanceReviewController::class, 'reviewsShow']);
                Route::put('/{id}', [PerformanceReviewController::class, 'reviewsUpdate']);
                Route::put('/{id}/submit', [PerformanceReviewController::class, 'submit']);
                Route::put('/{id}/review', [PerformanceReviewController::class, 'review']);
                Route::put('/{id}/approve', [PerformanceReviewController::class, 'approve']);
            });

            Route::prefix('okrs')->group(function () {
                Route::get('/', [OKRController::class, 'index']);
                Route::post('/', [OKRController::class, 'store']);
                Route::get('/{id}', [OKRController::class, 'show']);
                Route::put('/{id}', [OKRController::class, 'update']);
                Route::put('/{id}/submit', [OKRController::class, 'submit']);
                Route::put('/{id}/approve', [OKRController::class, 'approve']);
                Route::put('/{id}/progress', [OKRController::class, 'updateProgress']);
                Route::put('/{id}/start', [OKRController::class, 'markInProgress']);
                Route::put('/{id}/complete', [OKRController::class, 'markCompleted']);
                Route::delete('/{id}', [OKRController::class, 'destroy']);
            });

            Route::prefix('360-reviews')->group(function () {
                Route::get('/', [Review360Controller::class, 'index']);
                Route::post('/', [Review360Controller::class, 'store']);
                Route::get('/{id}', [Review360Controller::class, 'show']);
                Route::post('/{id}/feeders', [Review360Controller::class, 'assignFeeders']);
                Route::get('/{id}/feeder-status', [Review360Controller::class, 'getFeederStatus']);
                Route::post('/{reviewId}/feeders/{feederId}/submit', [Review360Controller::class, 'submitFeederFeedback']);
                Route::put('/{id}/self-assessment', [Review360Controller::class, 'submitSelfAssessment']);
                Route::put('/{id}/manager-assessment', [Review360Controller::class, 'submitManagerAssessment']);
                Route::put('/{id}/complete', [Review360Controller::class, 'completeReview']);
                Route::put('/{id}/submit-review', [Review360Controller::class, 'submitForReview']);
                Route::put('/{id}/approve', [Review360Controller::class, 'approveReview']);
            });

            Route::prefix('calibration')->group(function () {
                Route::get('/', [CalibrationController::class, 'index']);
                Route::post('/', [CalibrationController::class, 'store']);
                Route::get('/{id}', [CalibrationController::class, 'show']);
                Route::post('/{id}/participants', [CalibrationController::class, 'addParticipants']);
                Route::post('/{id}/reviews', [CalibrationController::class, 'addReviewsForCalibration']);
                Route::put('/{id}/start', [CalibrationController::class, 'startSession']);
                Route::put('/{sessionId}/calibrate/{calibrationReviewId}', [CalibrationController::class, 'calibrateEmployee']);
                Route::get('/{id}/report', [CalibrationController::class, 'getCalibrationReport']);
                Route::put('/{id}/complete', [CalibrationController::class, 'completeSession']);
                Route::delete('/{id}', [CalibrationController::class, 'destroy']);
            });
        });

        Route::prefix('career')->group(function () {
            Route::get('/idps', [CareerDevelopmentController::class, 'idpIndex']);
            Route::post('/idps', [CareerDevelopmentController::class, 'idpStore']);
            Route::put('/idps/{id}', [CareerDevelopmentController::class, 'idpUpdate']);
            Route::get('/succession', [CareerDevelopmentController::class, 'successionMatrix']);
            Route::post('/succession', [CareerDevelopmentController::class, 'successionStore']);
        });

        Route::prefix('engagement')->group(function () {
            Route::get('/surveys', [EngagementController::class, 'surveyIndex']);
            Route::post('/surveys', [EngagementController::class, 'surveyStore']);
            Route::post('/surveys/{id}/responses', [EngagementController::class, 'submitResponse']);
            Route::get('/surveys/{id}/analytics', [EngagementController::class, 'analytics']);
        });


        Route::prefix('workforce')->group(function () {
            Route::get('/compliance/stats', [WorkforceComplianceController::class, 'stats']);
            Route::get('/compliance/documents', [WorkforceComplianceController::class, 'documents']);
            Route::get('/holidays', [WorkforcePolicyController::class, 'holidayCalendarIndex']);
            Route::post('/holidays', [WorkforcePolicyController::class, 'holidayCalendarStore']);
            Route::get('/holidays/{id}', [WorkforcePolicyController::class, 'holidayCalendarShow']);
            Route::put('/holidays/{id}', [WorkforcePolicyController::class, 'holidayCalendarUpdate']);
            Route::delete('/holidays/{id}', [WorkforcePolicyController::class, 'holidayCalendarDestroy']);
            Route::put('/leave-policies/{id}/advanced', [WorkforcePolicyController::class, 'advancedLeavePolicyUpdate']);
            Route::get('/shift-swaps', [WorkforcePolicyController::class, 'shiftSwapIndex']);
            Route::post('/shift-swaps', [WorkforcePolicyController::class, 'shiftSwapStore']);
            Route::put('/shift-swaps/{id}', [WorkforcePolicyController::class, 'shiftSwapApprove']);
            Route::put('/shift-swaps/{id}/approve', [WorkforcePolicyController::class, 'shiftSwapApproveAction']);
            Route::put('/shift-swaps/{id}/reject', [WorkforcePolicyController::class, 'shiftSwapRejectAction']);
            Route::get('/overtime-rules', [WorkforcePolicyController::class, 'overtimeRuleIndex']);
            Route::post('/overtime-rules', [WorkforcePolicyController::class, 'overtimeRuleStore']);
            Route::get('/overtime-rules/{id}', [WorkforcePolicyController::class, 'overtimeRuleShow']);
            Route::put('/overtime-rules/{id}', [WorkforcePolicyController::class, 'overtimeRuleUpdate']);
            Route::delete('/overtime-rules/{id}', [WorkforcePolicyController::class, 'overtimeRuleDestroy']);
        });

        Route::prefix('enterprise')->group(function () {
            Route::put('/compensation/employee/{employeeId}', [EnterpriseOpsController::class, 'upsertCompProfile']);
            Route::post('/compensation/retro-adjustments', [EnterpriseOpsController::class, 'addRetroAdjustment']);
            Route::post('/compensation/bank-export-preview', [EnterpriseOpsController::class, 'bankExportPreview']);
            Route::post('/notifications/templates', [EnterpriseOpsController::class, 'notificationTemplateStore']);
            Route::post('/notifications/rules', [EnterpriseOpsController::class, 'notificationRuleStore']);
            Route::post('/notifications/schedules', [EnterpriseOpsController::class, 'scheduleNotification']);
            Route::get('/compliance/retention-policies', [EnterpriseOpsController::class, 'getRetentionPolicies']);
            Route::delete('/compliance/retention-policies/{module}', [EnterpriseOpsController::class, 'deactivateRetentionPolicy']);
            Route::get('/compliance/privacy-requests', [EnterpriseOpsController::class, 'getPrivacyRequests']);
            Route::post('/compliance/retention-policies', [EnterpriseOpsController::class, 'retentionPolicyStore']);
            Route::post('/compliance/tasks', [EnterpriseOpsController::class, 'complianceTaskStore']);
            Route::post('/compliance/privacy-requests', [EnterpriseOpsController::class, 'privacyRequestStore']);
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

    // 📖 Leave Reference Data - accessible to all authenticated users for ESS leave creation
    Route::prefix('leave-types')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index']);
        Route::get('/{id}', [LeaveTypeController::class, 'show']);
        Route::post('/', [LeaveTypeController::class, 'store']);
        Route::put('/{id}', [LeaveTypeController::class, 'update']);
        Route::delete('/{id}', [LeaveTypeController::class, 'destroy']);
    });

    Route::prefix('leave-policies')->group(function () {
        Route::get('/', [LeavePolicyController::class, 'index']);
        Route::get('/{id}', [LeavePolicyController::class, 'show']);
        Route::post('/', [LeavePolicyController::class, 'store']);
        Route::put('/{id}', [LeavePolicyController::class, 'update']);
        Route::delete('/{id}', [LeavePolicyController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | HR / MANAGER / ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    // ATTENDANCE (Admin/Manager/HR)
    Route::middleware('role:*')->prefix('attendance')->group(function () {
        Route::get('/all', [AttendanceController::class, 'all']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    // EMPLOYEE MANAGEMENT (Admin/Manager/HR)
    Route::middleware('role:*')->group(function () {
        Route::apiResource('employees', EmployeeController::class);

        Route::prefix('employees')->group(function () {
            Route::put('/{id}/onboarding/start', [EmployeeController::class, 'startOnboarding']);
            Route::put('/{id}/onboarding/complete', [EmployeeController::class, 'completeOnboarding']);
            Route::put('/{id}/offboarding/start', [EmployeeController::class, 'offboard']);
            Route::put('/{id}/offboarding/complete', [EmployeeController::class, 'completeOffboarding']);
        });
    });

    // PAYROLL (HR / Admin / Manager)
    Route::middleware('role:*')->group(function () {
        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::post('/generate/monthly', [PayrollController::class, 'generateMonthly']);
            Route::post('/bulk-pay', [PayrollController::class, 'bulkPay']);          // NEW
            Route::get('/export/bca-klikpay', [PayrollController::class, 'exportBcaKlikPay']);
            Route::get('/export/summary', [PayrollController::class, 'exportPayrollSummaryCsv']);
            Route::get('/{id}', [PayrollController::class, 'show']);
            Route::get('/{id}/slip', [PayrollController::class, 'slip']);
            Route::get('/{id}/export', [PayrollController::class, 'exportSlipCsv']);
            Route::get('/{id}/export-pdf', [PayrollController::class, 'exportSlipPdf']);
            Route::put('/{id}', [PayrollController::class, 'update']);
            Route::delete('/{id}', [PayrollController::class, 'destroy']);
            Route::post('/{id}/approve', [PayrollController::class, 'approve']);         // backward-compat
            Route::post('/{id}/manager-approve', [PayrollController::class, 'managerApprove']); // NEW
            Route::post('/{id}/hr-approve', [PayrollController::class, 'hrApprove']);   // NEW
            Route::post('/{id}/reject', [PayrollController::class, 'reject']);           // NEW
            Route::post('/{id}/pay', [PayrollController::class, 'pay']);
        });


        Route::prefix('payroll-details')->group(function () {
            Route::get('/{payroll_id}', [PayrollDetailController::class, 'index']);
            Route::post('/', [PayrollDetailController::class, 'store']);
            Route::put('/{id}', [PayrollDetailController::class, 'update']);
            Route::delete('/{id}', [PayrollDetailController::class, 'destroy']);
        });
    });

    // MASTER DATA & SYSTEM SETTINGS (Super Admin & Admin/HR/Manager)
    Route::middleware('role:*')->group(function () {
        Route::prefix('admin/notifications')->group(function () {
            Route::get('/summary', [NotificationController::class, 'summary']);
            Route::post('/broadcast', [NotificationController::class, 'broadcast']);
        });

        Route::prefix('admin/email-notifications')->group(function () {
            Route::post('/', [NotificationController::class, 'sendEmailNotification']);
            Route::get('/logs', [NotificationController::class, 'getEmailLogs']);
            Route::post('/{id}/retry', [NotificationController::class, 'retryEmailNotification']);
        });

        Route::prefix('admin/email-templates')->group(function () {
            Route::get('/', [NotificationController::class, 'emailTemplateIndex']);
            Route::post('/', [NotificationController::class, 'emailTemplateStore']);
            Route::put('/{id}', [NotificationController::class, 'emailTemplateUpdate']);
            Route::delete('/{id}', [NotificationController::class, 'emailTemplateDestroy']);
            Route::post('/{id}/preview', [NotificationController::class, 'emailTemplatePreview']);
        });

        // Notification Settings
        Route::prefix('notification-settings')->group(function () {
            Route::get('/', [NotificationSettingController::class, 'index']);
            Route::put('/{category}', [NotificationSettingController::class, 'update']);
        });
    });

    Route::middleware('role:*')->group(function () {
        Route::prefix('admin/notifications')->group(function () {
            Route::post('/', [NotificationController::class, 'store']);
        });
    });

    // MENU PERMISSIONS — accessible by PERMISSION via explicit map (role.assign_permission)
    Route::prefix('admin/menus')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\MenuController::class, 'definitions']);
        Route::post('/assign-role', [\App\Http\Controllers\Api\MenuController::class, 'assignRole']);
        Route::delete('/{menuKey}/roles/{roleId}', [\App\Http\Controllers\Api\MenuController::class, 'removeRole']);
    });

    // USER MENUS (accessible to all authenticated users)
    Route::get('/user/menus', [\App\Http\Controllers\Api\MenuController::class, 'userMenus']);

    Route::middleware('role:*')->group(function () {
        Route::apiResource('locations', LocationController::class);

        Route::apiResource('departments', DepartmentController::class);

        Route::apiResource('positions', PositionController::class);

        Route::get('/company', [CompanyController::class, 'show']);
        Route::post('/company', [CompanyController::class, 'store']);
        Route::put('/company/{id}', [CompanyController::class, 'update']);
        Route::post('/company/{id}/logo', [CompanyController::class, 'uploadLogo']);
        Route::delete('/company/{id}/logo', [CompanyController::class, 'deleteLogo']);

        Route::apiResource('work-schedules', WorkScheduleController::class);


        Route::prefix('biometric')->group(function () {
            Route::get('/devices', [BiometricIntegrationController::class, 'deviceIndex']);
            Route::post('/devices', [BiometricIntegrationController::class, 'deviceStore']);
            Route::post('/sync-attendance', [BiometricIntegrationController::class, 'syncAttendance']);
        });
    });

    // Admin RBAC Routes — tanpa hardcode role name, controller & explicit map handle permission
    Route::prefix('admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

        // RBAC Management
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
        Route::delete('/roles/{id}/remove-permission/{permissionId}', [RoleController::class, 'removePermission']);
        Route::get('/roles/{id}/can-modify', [RoleController::class, 'canModify']);
        Route::get('/roles/{id}/can-assign', [RoleController::class, 'canAssign']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
        Route::delete('/users/{id}/remove-role/{roleId}', [UserController::class, 'removeRole']);

        // Data Import
        Route::prefix('import')->group(function () {
            Route::post('/users', [DataImportController::class, 'importUsers']);
            Route::post('/employees', [DataImportController::class, 'importEmployees']);
            Route::get('/template', [DataImportController::class, 'getImportTemplate']);
        });
    });

});

// Helper for storage link on restricted hosting
Route::get('/setup-storage', function () {
    $link = public_path('storage');
    
    // 1. Hapus jika sudah ada (link rusak atau folder)
    if (file_exists($link) || is_link($link)) {
        if (windows_os()) {
            if (is_dir($link) && !is_link($link)) {
                // Di windows, folder tidak bisa di-unlink
                \Illuminate\Support\Facades\File::deleteDirectory($link);
            } else {
                @unlink($link);
            }
        } else {
            if (is_dir($link) && !is_link($link)) {
                \Illuminate\Support\Facades\File::deleteDirectory($link);
            } else {
                @unlink($link);
            }
        }
    }

    try {
        // 2. Gunakan RELATIVE path (Lebih aman untuk Hostinger)
        // Di Hostinger: public_html/storage -> ../storage/app/public
        if (symlink('../storage/app/public', $link)) {
            return response()->json(['message' => 'Relative Symlink created successfully!']);
        }
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Symlink failed. Fallback route in web.php is active.',
            'error' => $e->getMessage()
        ], 500);
    }
});
