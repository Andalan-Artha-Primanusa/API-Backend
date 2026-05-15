<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Checks roles via the pivot table (user_roles), NOT the deprecated users.role column.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Unauthenticated', null, 401);
        }

        $user->loadMissing('roles');

        $path = '/' . ltrim($request->path(), '/');

        // 0. Super Admin selalu bypass semua pengecekan
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 1. Pemetaan Eksplisit Terpusat (Route-Permission Map)
        // Dicek DULU — dynamic role dengan permission yg sesuai bisa lewat
        // tanpa harus punya literal role name (admin/hr/manager/super_admin)

        $explicitMap = [
            // RBAC Admin Routes
            '/api/admin/roles' => ['role.view', 'role.create', 'role.update', 'role.delete', 'role.assign_permission'],
            '/admin/roles' => ['role.view', 'role.create', 'role.update', 'role.delete', 'role.assign_permission'],
            '/api/admin/permissions' => ['permission.view'],
            '/admin/permissions' => ['permission.view'],
            '/api/admin/users' => ['user.view', 'user.assign_role'],
            '/admin/users' => ['user.view', 'user.assign_role'],
            '/api/admin/menus' => ['role.assign_permission', 'role.view'],
            '/admin/menus' => ['role.assign_permission', 'role.view'],
            '/api/admin/audit-logs' => ['audit.logs.view', 'admin.audit.view'],
            '/admin/audit-logs' => ['audit.logs.view', 'admin.audit.view'],
            '/api/admin/import' => ['admin.import.execute'],
            '/admin/import' => ['admin.import.execute'],
            '/api/admin/notifications' => ['admin.email.manage'],
            '/admin/notifications' => ['admin.email.manage'],
            '/api/admin/email-notifications' => ['admin.email.manage'],
            '/admin/email-notifications' => ['admin.email.manage'],
            '/api/admin/email-templates' => ['admin.email.manage'],
            '/admin/email-templates' => ['admin.email.manage'],

            // Master Data
            '/api/locations' => ['location.view', 'location.create', 'location.update', 'location.delete'],
            '/locations' => ['location.view', 'location.create', 'location.update', 'location.delete'],
            '/api/departments' => ['department.view', 'department.create', 'department.update', 'department.delete'],
            '/departments' => ['department.view', 'department.create', 'department.update', 'department.delete'],
            '/api/positions' => ['position.view', 'position.create', 'position.update', 'position.delete'],
            '/positions' => ['position.view', 'position.create', 'position.update', 'position.delete'],
            '/api/company' => ['admin.company.view', 'admin.company.update'],
            '/company' => ['admin.company.view', 'admin.company.update'],
            '/api/work-schedules' => ['admin.schedule.manage'],
            '/work-schedules' => ['admin.schedule.manage'],

            // Employees
            '/api/employees' => ['employee.view', 'employee.create', 'employee.update', 'employee.delete', 'employee.onboard', 'employee.offboard'],
            '/employees' => ['employee.view', 'employee.create', 'employee.update', 'employee.delete', 'employee.onboard', 'employee.offboard'],

            // Biometric
            '/api/biometric/devices' => ['biometric.devices.view'],
            '/biometric/devices' => ['biometric.devices.view'],
            '/api/biometric/sync-attendance' => ['biometric.attendance.sync'],
            '/biometric/sync-attendance' => ['biometric.attendance.sync'],

            // Payroll
            '/api/payroll' => ['payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay', 'payroll.export', 'payroll.reports.view'],
            '/payroll' => ['payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay', 'payroll.export', 'payroll.reports.view'],
            '/api/payroll/reports' => ['payroll.reports.view'],
            '/payroll/reports' => ['payroll.reports.view'],

            // Reports
            '/api/reports' => ['reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll', 'reporting.competency', 'reporting.lifecycle', 'reporting.assets'],
            '/reports' => ['reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll', 'reporting.competency', 'reporting.lifecycle', 'reporting.assets'],

            // Leave
            '/api/leaves' => ['leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve'],
            '/leaves' => ['leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve'],
            '/api/leave-types' => ['leave.policy.manage'],
            '/leave-types' => ['leave.policy.manage'],
            '/api/leave-policies' => ['leave.policy.manage'],
            '/leave-policies' => ['leave.policy.manage'],

            // Approval Flows
            '/api/approval-flows' => ['admin.approval_flow.manage'],
            '/approval-flows' => ['admin.approval_flow.manage'],

            // KPI
            '/api/kpis' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/kpis' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/api/kpi-periods' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/kpi-periods' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],

            // Overtime
            '/api/overtime' => ['overtime.view', 'overtime.create', 'overtime.approve', 'overtime.manage'],
            '/overtime' => ['overtime.view', 'overtime.create', 'overtime.approve', 'overtime.manage'],

            // Documents, Assets, Competencies
            '/api/documents' => ['document.view', 'document.create', 'document.update', 'document.delete', 'document.review'],
            '/documents' => ['document.view', 'document.create', 'document.update', 'document.delete', 'document.review'],
            '/api/assets' => ['asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign'],
            '/assets' => ['asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign'],
            '/api/competencies' => ['competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign'],
            '/competencies' => ['competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign'],

            // Organization
            '/api/organization' => ['organization.view', 'organization.directory', 'organization.chart', 'organization.team'],
            '/organization' => ['organization.view', 'organization.directory', 'organization.chart', 'organization.team'],

            // Benefits, Training
            '/api/benefits' => ['benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign'],
            '/benefits' => ['benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign'],
            '/api/trainings' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/trainings' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/api/training' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/training' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],

            // Recruitment
            '/api/recruitment' => ['recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update', 'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage', 'recruitment.interview.schedule', 'recruitment.offer.create'],
            '/recruitment' => ['recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update', 'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage', 'recruitment.interview.schedule', 'recruitment.offer.create'],

            // Performance, OKR, 360, Calibration
            '/api/performance' => ['performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage', 'performance.review.view', 'performance.review.create', 'performance.review.update', 'performance.review.submit', 'performance.review.approve', 'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress', 'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback', 'review360.submit', 'review360.approve', 'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage'],
            '/performance' => ['performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage', 'performance.review.view', 'performance.review.create', 'performance.review.update', 'performance.review.submit', 'performance.review.approve', 'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress', 'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback', 'review360.submit', 'review360.approve', 'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage'],

            // Reimbursements, HR Requests
            '/api/reimbursements' => ['reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay'],
            '/reimbursements' => ['reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay'],
            '/api/requests' => ['hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage'],
            '/requests' => ['hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage'],

            // Promotions, Career
            '/api/promotions' => ['career.promotion.view', 'career.promotion.create', 'career.promotion.approve', 'career.promotion.delete'],
            '/promotions' => ['career.promotion.view', 'career.promotion.create', 'career.promotion.approve', 'career.promotion.delete'],
            '/api/career' => ['career.idp.view', 'career.idp.create', 'career.idp.update', 'career.succession.view', 'career.succession.manage', 'career.promotion.view', 'career.promotion.create', 'career.promotion.update', 'career.promotion.delete', 'career.promotion.approve'],
            '/career' => ['career.idp.view', 'career.idp.create', 'career.idp.update', 'career.succession.view', 'career.succession.manage', 'career.promotion.view', 'career.promotion.create', 'career.promotion.update', 'career.promotion.delete', 'career.promotion.approve'],

            // Engagement
            '/api/engagement' => ['engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond', 'engagement.survey.analytics'],
            '/engagement' => ['engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond', 'engagement.survey.analytics'],

            // Attendance
            '/api/attendance' => ['attendance.view_all', 'attendance.delete', 'attendance.check_in', 'attendance.check_out'],
            '/attendance' => ['attendance.view_all', 'attendance.delete', 'attendance.check_in', 'attendance.check_out'],

            // Compliance
            '/api/compliance' => ['compliance.view', 'compliance.audit', 'compliance.documents'],
            '/compliance' => ['compliance.view', 'compliance.audit', 'compliance.documents'],
        ];

        foreach ($explicitMap as $routePrefix => $requiredPermissions) {
            if (str_starts_with($path, $routePrefix)) {
                $user->loadMissing('roles.permissions');
                foreach ($requiredPermissions as $perm) {
                    if ($user->hasPermission($perm)) {
                        return $next($request);
                    }
                }

                return ApiResponse::error('Forbidden', 'Missing explicit permission: ' . implode(' / ', $requiredPermissions), 403);
            }
        }

        // 2. Wildcard mode (*) — route pake role:* berarti ga ada literal role fallback
        if (in_array('*', $roles)) {
            return ApiResponse::error('Forbidden', 'No permission for this route', 403);
        }

        // 3. Lapisan Kompatibilitas: literal role name (backward compat untuk route lama)
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        return ApiResponse::error('Forbidden', 'Insufficient role or explicit capability', 403);
    }
}
