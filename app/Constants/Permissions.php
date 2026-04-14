<?php

namespace App\Constants;

/**
 * Permission Registry - Centralized permission & role management
 * Linked to actual API routes for easy maintenance & auditing
 * 
 * Super Admin can customize role-permission mappings via:
 * POST /admin/roles/{id}/assign-permission
 */
class Permissions
{
    // ==========================================
    // RESOURCE-BASED PERMISSIONS
    // ==========================================
    
    const EMPLOYEE = [
        'employee.view' => 'View employee list & details',
        'employee.create' => 'Create new employee',
        'employee.update' => 'Update employee profile',
        'employee.delete' => 'Delete employee',
        'employee.onboard' => 'Start onboarding process',
        'employee.offboard' => 'Start offboarding process',
    ];

    const LEAVE = [
        'leave.view' => 'View leave requests',
        'leave.create' => 'Create leave request',
        'leave.approve' => 'Approve/reject leave (Manager/HR)',
        'leave.policy.manage' => 'Manage leave policies',
    ];

    const ATTENDANCE = [
        'attendance.view_all' => 'View all attendance records',
        'attendance.delete' => 'Delete attendance records',
        'attendance.check_in' => 'Employee check-in',
        'attendance.check_out' => 'Employee check-out',
        'attendance.view_own' => 'View own attendance history',
    ];

    const PAYROLL = [
        'payroll.view' => 'View payroll records',
        'payroll.create' => 'Create payroll run',
        'payroll.approve' => 'Approve payroll payment',
        'payroll.pay' => 'Process payroll payment',
        'payroll.export' => 'Export payroll slips',
    ];

    const KPI = [
        'kpi.view' => 'View KPI records',
        'kpi.create' => 'Create KPI',
        'kpi.update' => 'Update KPI',
        'kpi.approve' => 'Approve KPI submission',
    ];

    const REIMBURSEMENT = [
        'reimbursement.view' => 'View reimbursement requests',
        'reimbursement.create' => 'Create reimbursement request',
        'reimbursement.approve' => 'Approve reimbursement',
        'reimbursement.pay' => 'Mark reimbursement as paid',
    ];

    const TRAINING = [
        'training.view' => 'View training programs',
        'training.create' => 'Create training program',
        'training.update' => 'Update training program',
        'training.delete' => 'Delete training program',
        'training.enroll' => 'Enroll employee in training',
    ];

    const COMPETENCY = [
        'competency.view' => 'View competencies',
        'competency.create' => 'Create competency',
        'competency.update' => 'Update competency',
        'competency.delete' => 'Delete competency',
        'competency.assign' => 'Assign competency to employee',
    ];

    const ASSET = [
        'asset.view' => 'View assets',
        'asset.create' => 'Create asset',
        'asset.update' => 'Update asset',
        'asset.delete' => 'Delete asset',
        'asset.assign' => 'Assign asset to employee',
    ];

    const DOCUMENT = [
        'document.view' => 'View employee documents',
        'document.create' => 'Create/upload document',
        'document.update' => 'Update document',
        'document.delete' => 'Delete document',
        'document.review' => 'Review/approve document',
    ];

    const HR_REQUEST = [
        'hr_request.view' => 'View HR service requests',
        'hr_request.create' => 'Create HR request (employee)',
        'hr_request.assign' => 'Assign request to HR staff',
        'hr_request.manage' => 'Manage & close requests',
    ];

    const RECRUITMENT = [
        'recruitment.opening.view' => 'View job openings',
        'recruitment.opening.create' => 'Create job opening',
        'recruitment.opening.update' => 'Update job opening',
        'recruitment.candidate.view' => 'View candidates',
        'recruitment.candidate.manage' => 'Manage candidate pipeline',
        'recruitment.interview.schedule' => 'Schedule interviews',
        'recruitment.offer.create' => 'Create offers',
    ];

    const BENEFIT = [
        'benefit.view' => 'View benefits',
        'benefit.create' => 'Create benefit plan',
        'benefit.update' => 'Update benefit plan',
        'benefit.assign' => 'Assign benefit to employee',
    ];

    const PERFORMANCE = [
        'performance.cycle.view' => 'View performance cycles',
        'performance.cycle.create' => 'Create performance cycle',
        'performance.cycle.manage' => 'Manage performance cycles',
        'performance.review.view' => 'View performance reviews',
        'performance.review.create' => 'Create performance review',
        'performance.review.submit' => 'Submit performance review',
        'performance.review.approve' => 'Approve performance review',
    ];

    const OKR = [
        'okr.view' => 'View OKRs',
        'okr.create' => 'Create OKR',
        'okr.submit' => 'Submit OKR for approval',
        'okr.approve' => 'Approve OKR',
        'okr.progress' => 'Update OKR progress',
    ];

    const REVIEW360 = [
        'review360.view' => 'View 360 reviews',
        'review360.create' => 'Create 360 review cycle',
        'review360.provide_feedback' => 'Provide 360 feedback as feeder',
        'review360.submit' => 'Submit 360 review',
        'review360.approve' => 'Approve 360 review',
    ];

    const CALIBRATION = [
        'calibration.view' => 'View calibration sessions',
        'calibration.create' => 'Create calibration session',
        'calibration.participate' => 'Participate in calibration',
        'calibration.manage' => 'Manage calibration process',
    ];

    const CAREER = [
        'career.idp.view' => 'View Individual Development Plans',
        'career.idp.create' => 'Create IDP',
        'career.succession.view' => 'View succession planning',
        'career.succession.manage' => 'Manage succession planning',
    ];

    const ENGAGEMENT = [
        'engagement.survey.view' => 'View engagement surveys',
        'engagement.survey.create' => 'Create engagement survey',
        'engagement.survey.respond' => 'Respond to survey',
        'engagement.survey.analytics' => 'View survey analytics',
    ];

    const ORGANIZATION = [
        'organization.view' => 'View organization structure',
        'organization.directory' => 'Access employee directory',
        'organization.chart' => 'View organization chart',
        'organization.team' => 'View team members',
    ];

    const COMPLIANCE = [
        'compliance.view' => 'View compliance overview',
        'compliance.audit' => 'View audit trail',
        'compliance.documents' => 'View document compliance',
    ];

    const REPORTING = [
        'reporting.dashboard' => 'View analytics dashboard',
        'reporting.attendance' => 'View attendance analytics',
        'reporting.leave' => 'View leave analytics',
        'reporting.payroll' => 'View payroll analytics',
        'reporting.competency' => 'View competency analytics',
        'reporting.lifecycle' => 'View employee lifecycle analytics',
        'reporting.assets' => 'View asset analytics',
    ];

    // ==========================================
    // SYSTEM ADMIN PERMISSIONS (Super Admin ONLY)
    // ==========================================

    const ADMIN_SYSTEM = [
        'admin.user.view' => 'View all users',
        'admin.user.assign_role' => 'Assign roles to users',
        'admin.role.view' => 'View roles',
        'admin.role.assign_permission' => 'Assign permissions to roles',
        'admin.permission.view' => 'View permissions',
        'admin.audit.view' => 'View audit logs',
        'admin.audit.delete' => 'Delete audit records',
    ];

    const ADMIN_SETTINGS = [
        'admin.email.manage' => 'Manage email notifications & templates',
        'admin.location.manage' => 'Manage locations',
        'admin.schedule.manage' => 'Manage work schedules',
        'admin.approval_flow.manage' => 'Manage approval workflows',
        'admin.biometric.manage' => 'Manage biometric devices',
    ];

    const ADMIN_DATA = [
        'admin.import.users' => 'Bulk import users',
        'admin.import.employees' => 'Bulk import employees',
    ];

    // ==========================================
    // PERMISSION GROUPS
    // ==========================================

    public static function all(): array
    {
        return array_merge(
            self::EMPLOYEE,
            self::LEAVE,
            self::ATTENDANCE,
            self::PAYROLL,
            self::KPI,
            self::REIMBURSEMENT,
            self::TRAINING,
            self::COMPETENCY,
            self::ASSET,
            self::DOCUMENT,
            self::HR_REQUEST,
            self::RECRUITMENT,
            self::BENEFIT,
            self::PERFORMANCE,
            self::OKR,
            self::REVIEW360,
            self::CALIBRATION,
            self::CAREER,
            self::ENGAGEMENT,
            self::ORGANIZATION,
            self::COMPLIANCE,
            self::REPORTING,
            self::ADMIN_SYSTEM,
            self::ADMIN_SETTINGS,
            self::ADMIN_DATA,
        );
    }

    // ==========================================
    // ROLE PERMISSION MAPPINGS
    // Maps roles to their default permissions
    // Super Admin can customize via API later
    // ==========================================

    public static function roleDefaultPermissions(): array
    {
        return [
            'super_admin' => array_keys(self::all()),

            'admin' => array_merge(
                array_keys(self::EMPLOYEE),
                array_keys(self::LEAVE),
                array_keys(self::ATTENDANCE),
                array_keys(self::PAYROLL),
                array_keys(self::KPI),
                array_keys(self::REIMBURSEMENT),
                array_keys(self::TRAINING),
                array_keys(self::COMPETENCY),
                array_keys(self::ASSET),
                array_keys(self::DOCUMENT),
                array_keys(self::HR_REQUEST),
                array_keys(self::RECRUITMENT),
                array_keys(self::BENEFIT),
                array_keys(self::PERFORMANCE),
                array_keys(self::OKR),
                array_keys(self::REVIEW360),
                array_keys(self::CALIBRATION),
                array_keys(self::CAREER),
                array_keys(self::ENGAGEMENT),
                array_keys(self::ORGANIZATION),
                array_keys(self::COMPLIANCE),
                array_keys(self::REPORTING),
                array_keys(self::ADMIN_SYSTEM),
            ),

            'hr' => array_merge(
                array_keys(self::EMPLOYEE),
                array_keys(self::LEAVE),
                array_keys(self::ATTENDANCE),
                array_keys(self::PAYROLL),
                array_keys(self::KPI),
                array_keys(self::REIMBURSEMENT),
                array_keys(self::TRAINING),
                array_keys(self::COMPETENCY),
                array_keys(self::DOCUMENT),
                array_keys(self::HR_REQUEST),
                array_keys(self::RECRUITMENT),
                array_keys(self::BENEFIT),
                array_keys(self::PERFORMANCE),
                array_keys(self::OKR),
                array_keys(self::REVIEW360),
                array_keys(self::CALIBRATION),
                array_keys(self::CAREER),
                array_keys(self::ORGANIZATION),
                array_keys(self::COMPLIANCE),
                array_keys(self::REPORTING),
            ),

            'manager' => array_merge(
                ['employee.view', 'employee.update'],
                array_keys(self::LEAVE),
                ['attendance.view_all'],
                ['kpi.view', 'kpi.approve'],
                ['performance.review.view', 'performance.review.approve'],
                ['okr.view', 'okr.approve'],
                ['review360.view', 'review360.approve'],
                array_keys(self::ORGANIZATION),
                array_keys(self::COMPLIANCE),
                array_keys(self::REPORTING),
            ),

            'employee' => [
                'leave.view',
                'leave.create',
                'attendance.check_in',
                'attendance.check_out',
                'attendance.view_own',
                'kpi.view',
                'reimbursement.view',
                'reimbursement.create',
                'training.view',
                'competency.view',
                'asset.view',
                'document.view',
                'hr_request.view',
                'hr_request.create',
                'benefit.view',
                'performance.review.view',
                'okr.view',
                'review360.view',
                'career.idp.view',
                'engagement.survey.respond',
                'engagement.survey.view',
            ],
        ];
    }

    /**
     * Get permission description
     */
    public static function description(string $permission): string
    {
        return self::all()[$permission] ?? "Permission: $permission";
    }

    /**
     * Get all permissions for a role (default)
     */
    public static function forRole(string $role): array
    {
        return self::roleDefaultPermissions()[$role] ?? [];
    }
}
