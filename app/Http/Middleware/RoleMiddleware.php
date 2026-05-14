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

        // Eager-load roles once for this request
        $user->loadMissing('roles');

        // 1. Lapisan Kompatibilitas Lama: Periksa string literal peran standar
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // 2. Pemetaan Eksplisit Terpusat (Route-Permission Map)
        // Memastikan otorisasi API berbasis kapabilitas spesifik tanpa inferensi heuristik dari URL
        $path = '/' . ltrim($request->path(), '/');

        $explicitMap = [
            '/api/admin/audit-logs' => ['audit.logs.view', 'admin.audit.view'],
            '/admin/audit-logs' => ['audit.logs.view', 'admin.audit.view'],
            '/api/biometric/devices' => ['biometric.devices.view'],
            '/biometric/devices' => ['biometric.devices.view'],
            '/api/biometric/sync-attendance' => ['biometric.attendance.sync'],
            '/biometric/sync-attendance' => ['biometric.attendance.sync'],
            '/api/admin/import' => ['admin.import.execute'],
            '/admin/import' => ['admin.import.execute'],
            '/api/payroll/reports' => ['payroll.reports.view'],
            '/payroll/reports' => ['payroll.reports.view'],
            '/api/reports' => ['reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll', 'reporting.competency', 'reporting.lifecycle', 'reporting.assets'],
            '/reports' => ['reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll', 'reporting.competency', 'reporting.lifecycle', 'reporting.assets'],
            '/api/attendance/reports' => ['reporting.attendance'],
            '/attendance/reports' => ['reporting.attendance'],
            '/api/leave/approval' => ['leave.approve'],
            '/leave/approval' => ['leave.approve'],
            '/api/leaves/pending' => ['leave.approve'],
            '/leaves/pending' => ['leave.approve'],
            '/api/leaves' => ['leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve'],
            '/leaves' => ['leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve'],
            '/api/leave-types' => ['leave.policy.manage'],
            '/leave-types' => ['leave.policy.manage'],
            '/api/leave-policies' => ['leave.policy.manage'],
            '/leave-policies' => ['leave.policy.manage'],
            '/api/approval-flows' => ['admin.approval_flow.manage'],
            '/approval-flows' => ['admin.approval_flow.manage'],
            '/api/kpis' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/kpis' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/api/kpi-periods' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/kpi-periods' => ['kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve'],
            '/api/overtime/approval' => ['overtime.approve'],
            '/overtime/approval' => ['overtime.approve'],
            '/api/overtime/requests' => ['overtime.view', 'overtime.approve', 'overtime.manage'],
            '/overtime/requests' => ['overtime.view', 'overtime.approve', 'overtime.manage'],
            '/api/overtime/evidences' => ['overtime.view', 'overtime.approve', 'overtime.manage'],
            '/overtime/evidences' => ['overtime.view', 'overtime.approve', 'overtime.manage'],
            '/api/documents' => ['document.view', 'document.create', 'document.update', 'document.delete', 'document.review'],
            '/documents' => ['document.view', 'document.create', 'document.update', 'document.delete', 'document.review'],
            '/api/assets' => ['asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign'],
            '/assets' => ['asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign'],
            '/api/competencies' => ['competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign'],
            '/competencies' => ['competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign'],
            '/api/organization' => ['organization.view', 'organization.directory', 'organization.chart', 'organization.team'],
            '/organization' => ['organization.view', 'organization.directory', 'organization.chart', 'organization.team'],
            '/api/benefits' => ['benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign'],
            '/benefits' => ['benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign'],
            '/api/trainings' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/trainings' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/api/training' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/training' => ['training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll'],
            '/api/recruitment' => ['recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update', 'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage', 'recruitment.interview.schedule', 'recruitment.offer.create'],
            '/recruitment' => ['recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update', 'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage', 'recruitment.interview.schedule', 'recruitment.offer.create'],
            '/api/performance' => ['performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage', 'performance.review.view', 'performance.review.create', 'performance.review.update', 'performance.review.submit', 'performance.review.approve', 'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress', 'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback', 'review360.submit', 'review360.approve', 'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage'],
            '/performance' => ['performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage', 'performance.review.view', 'performance.review.create', 'performance.review.update', 'performance.review.submit', 'performance.review.approve', 'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress', 'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback', 'review360.submit', 'review360.approve', 'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage'],
            '/api/reimbursements' => ['reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay'],
            '/reimbursements' => ['reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay'],
            '/api/requests' => ['hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage'],
            '/requests' => ['hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage'],
            '/api/payroll' => ['payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay', 'payroll.export', 'payroll.reports.view'],
            '/payroll' => ['payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay', 'payroll.export', 'payroll.reports.view'],
            '/api/promotions' => ['career.promotion.view', 'career.promotion.create', 'career.promotion.approve', 'career.promotion.delete'],
            '/promotions' => ['career.promotion.view', 'career.promotion.create', 'career.promotion.approve', 'career.promotion.delete'],
            '/api/career' => ['career.idp.view', 'career.idp.create', 'career.idp.update', 'career.succession.view', 'career.succession.manage', 'career.promotion.view', 'career.promotion.create', 'career.promotion.update', 'career.promotion.delete', 'career.promotion.approve'],
            '/career' => ['career.idp.view', 'career.idp.create', 'career.idp.update', 'career.succession.view', 'career.succession.manage', 'career.promotion.view', 'career.promotion.create', 'career.promotion.update', 'career.promotion.delete', 'career.promotion.approve'],
            '/api/engagement' => ['engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond', 'engagement.survey.analytics'],
            '/engagement' => ['engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond', 'engagement.survey.analytics'],
            '/api/admin/notifications' => ['admin.email.manage'],
            '/admin/notifications' => ['admin.email.manage'],
            '/api/admin/email-notifications' => ['admin.email.manage'],
            '/admin/email-notifications' => ['admin.email.manage'],
            '/api/admin/email-templates' => ['admin.email.manage'],
            '/admin/email-templates' => ['admin.email.manage'],
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

        return ApiResponse::error('Forbidden', 'Insufficient role or explicit capability', 403);
    }
}
