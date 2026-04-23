
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromotionController;
use App\Services\ProgressiveTaxService;
use App\Http\Controllers\Api\EmploymentLetterController;
use App\Http\Controllers\Api\AssignmentLetterController;
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
// PROMOTION (Kenaikan Jabatan)
Route::post('employees/{employee}/promote', [PromotionController::class, 'promote']);

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
    // Surat Pengalaman Kerja & Surat Keterangan Bekerja
    Route::post('employees/{employee}/experience-letter', [EmploymentLetterController::class, 'generateExperienceLetter']);
    Route::post('employees/{employee}/employment-letter', [EmploymentLetterController::class, 'generateEmploymentLetter']);
Route::get('/documents/{filename}', [EmployeeDocumentController::class, 'download']);
    // Assignment Letter (Surat Tugas) dengan approval
    Route::get('assignment-letters', [AssignmentLetterController::class, 'index']);
    Route::post('assignment-letters', [AssignmentLetterController::class, 'store']);
    Route::get('assignment-letters/{id}', [AssignmentLetterController::class, 'show']);
    Route::post('assignment-letters/{id}/approve', [AssignmentLetterController::class, 'approve']);
    Route::post('assignment-letters/{id}/reject', [AssignmentLetterController::class, 'reject']);
    Route::get('assignment-letters/{id}/pdf', [AssignmentLetterController::class, 'generatePdf']);

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
        Route::get('/benefits', [BenefitController::class, 'myBenefits']);
        Route::get('/performance-reviews', [PerformanceReviewController::class, 'myReviews']);
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

    Route::middleware('role:admin,manager,hr,super_admin')->group(function () {
        Route::prefix('organization')->group(function () {
            Route::get('/directory', [OrgStructureController::class, 'directory']);
            Route::get('/summary', [OrgStructureController::class, 'summary']);
            Route::get('/chart', [OrgStructureController::class, 'orgChart']);
            Route::get('/team/{managerUserId}', [OrgStructureController::class, 'teamMembers']);
            Route::get('/master-data', [OrgStructureController::class, 'masterData']);
        });
    });

    // Approval Flows - SUPER ADMIN ONLY (system-critical configuration)
    Route::middleware('role:super_admin')->prefix('approval-flows')->group(function () {
        Route::get('/', [ApprovalFlowController::class, 'index']);
        Route::post('/', [ApprovalFlowController::class, 'store']);
        Route::get('/{id}', [ApprovalFlowController::class, 'show']);
        Route::put('/{id}', [ApprovalFlowController::class, 'update']);
        Route::delete('/{id}', [ApprovalFlowController::class, 'destroy']);
    });

    Route::middleware('role:admin,hr,super_admin')->prefix('compliance')->group(function () {
        Route::get('/overview', [ComplianceController::class, 'overview']);
        Route::get('/audit-summary', [ComplianceController::class, 'auditSummary']);
        Route::get('/expiring-documents', [ComplianceController::class, 'expiringDocuments']);
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
            Route::get('/assignments', [AssetController::class, 'assignments']);
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
            Route::get('/contracts', [EmployeeDocumentController::class, 'contracts']);
            Route::get('/{id}', [EmployeeDocumentController::class, 'show']);
            Route::put('/{id}', [EmployeeDocumentController::class, 'update']);
            Route::delete('/{id}', [EmployeeDocumentController::class, 'destroy']);
            Route::put('/{id}/review', [EmployeeDocumentController::class, 'review']);
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
            Route::get('/holidays', [WorkforcePolicyController::class, 'holidayCalendarIndex']);
            Route::post('/holidays', [WorkforcePolicyController::class, 'holidayCalendarStore']);
            Route::put('/leave-policies/{id}/advanced', [WorkforcePolicyController::class, 'advancedLeavePolicyUpdate']);
            Route::get('/shift-swaps', [WorkforcePolicyController::class, 'shiftSwapIndex']);
            Route::post('/shift-swaps', [WorkforcePolicyController::class, 'shiftSwapStore']);
            Route::put('/shift-swaps/{id}', [WorkforcePolicyController::class, 'shiftSwapApprove']);
            Route::get('/overtime-rules', [WorkforcePolicyController::class, 'overtimeRuleIndex']);
            Route::post('/overtime-rules', [WorkforcePolicyController::class, 'overtimeRuleStore']);
        });

        Route::prefix('enterprise')->group(function () {
            Route::put('/compensation/employee/{employeeId}', [EnterpriseOpsController::class, 'upsertCompProfile']);
            Route::post('/compensation/retro-adjustments', [EnterpriseOpsController::class, 'addRetroAdjustment']);
            Route::post('/compensation/bank-export-preview', [EnterpriseOpsController::class, 'bankExportPreview']);
            Route::post('/notifications/templates', [EnterpriseOpsController::class, 'notificationTemplateStore']);
            Route::post('/notifications/rules', [EnterpriseOpsController::class, 'notificationRuleStore']);
            Route::post('/notifications/schedules', [EnterpriseOpsController::class, 'scheduleNotification']);
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

    /*
    |--------------------------------------------------------------------------
    | HR / MANAGER / ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    // ATTENDANCE (Admin/HR/Super Admin ONLY)
    Route::middleware('role:admin,hr,super_admin')->prefix('attendance')->group(function () {
        Route::get('/all', [AttendanceController::class, 'all']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);
    });

    // EMPLOYEE MANAGEMENT (Admin/HR/Super Admin ONLY)
    Route::middleware('role:admin,hr,super_admin')->group(function () {
        Route::apiResource('employees', EmployeeController::class);

        Route::prefix('employees')->group(function () {
            Route::put('/{id}/onboarding/start', [EmployeeController::class, 'startOnboarding']);
            Route::put('/{id}/onboarding/complete', [EmployeeController::class, 'completeOnboarding']);
            Route::put('/{id}/offboarding/start', [EmployeeController::class, 'offboard']);
            Route::put('/{id}/offboarding/complete', [EmployeeController::class, 'completeOffboarding']);
        });
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

    // MASTER DATA & SYSTEM SETTINGS (Super Admin & Admin/HR)
    Route::middleware('role:admin,hr,super_admin')->group(function () {
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
            Route::post('/{id}/preview', [NotificationController::class, 'emailTemplatePreview']);
        });
    });

    Route::middleware('role:admin,hr,manager,super_admin')->group(function () {
        Route::prefix('admin/notifications')->group(function () {
            Route::post('/', [NotificationController::class, 'store']);
        });
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('locations', LocationController::class);

        Route::apiResource('work-schedules', WorkScheduleController::class);


        Route::prefix('biometric')->group(function () {
            Route::get('/devices', [BiometricIntegrationController::class, 'deviceIndex']);
            Route::post('/devices', [BiometricIntegrationController::class, 'deviceStore']);
            Route::post('/sync-attendance', [BiometricIntegrationController::class, 'syncAttendance']);
        });

        Route::prefix('admin')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

            // RBAC Management - SUPER ADMIN ONLY
            Route::get('/roles', [RoleController::class, 'index']);
            Route::get('/permissions', [PermissionController::class, 'index']);
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
            Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);

            // Data Import - SUPER ADMIN ONLY
            Route::prefix('import')->group(function () {
                Route::post('/users', [DataImportController::class, 'importUsers']);
                Route::post('/employees', [DataImportController::class, 'importEmployees']);
                Route::get('/template', [DataImportController::class, 'getImportTemplate']);
            });
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
            @exec('rm -rf ' . escapeshellarg($link));
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
