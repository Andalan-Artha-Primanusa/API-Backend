<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Role Definitions
    |--------------------------------------------------------------------------
    |
    | Daftar role default beserta permission masing-masing.
    | super_admin selalu mendapat semua permission (dihardcode di Permissions.php).
    | Role lain bisa ditambah/dikurangi di sini tanpa mengubah kode.
    |
    | Format:
    | 'nama_role' => ['permission.1', 'permission.2', ...]
    | Khusus 'super_admin' gunakan string '*' artinya semua permission.
    |
    */

    'roles' => [
        'super_admin' => '*',

        'admin' => [
            'employee.view', 'employee.create', 'employee.update', 'employee.delete',
            'employee.onboard', 'employee.offboard',
            'leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve',
            'leave.policy.manage',
            'attendance.view_all', 'attendance.delete', 'attendance.check_in', 'attendance.check_out',
            'attendance.view_own',
            'payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay',
            'payroll.export', 'payroll.reports.view',
            'kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve',
            'reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay',
            'overtime.view', 'overtime.create', 'overtime.approve', 'overtime.manage',
            'training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll',
            'competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign',
            'asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign',
            'document.view', 'document.create', 'document.update', 'document.delete', 'document.review',
            'assignment_letter.view', 'assignment_letter.create', 'assignment_letter.approve', 'assignment_letter.export',
            'hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage',
            'task.view', 'task.create', 'task.update', 'task.delete', 'task.complete',
            'recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update',
            'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage',
            'recruitment.interview.schedule', 'recruitment.offer.create',
            'benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign',
            'performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage',
            'performance.review.view', 'performance.review.create', 'performance.review.update',
            'performance.review.submit', 'performance.review.approve',
            'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress',
            'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback',
            'review360.submit', 'review360.approve',
            'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage',
            'career.idp.view', 'career.idp.create', 'career.idp.update',
            'career.succession.view', 'career.succession.manage',
            'career.promotion.view', 'career.promotion.create', 'career.promotion.update',
            'career.promotion.delete', 'career.promotion.approve',
            'engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond',
            'engagement.survey.analytics',
            'organization.view', 'organization.directory', 'organization.chart', 'organization.team',
            'compliance.view', 'compliance.audit', 'compliance.documents',
            'reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll',
            'reporting.competency', 'reporting.lifecycle', 'reporting.assets',
            'user.view', 'user.assign_role',
            'role.view', 'role.create', 'role.update', 'role.delete', 'role.assign_permission',
            'permission.view',
            'admin.user.view', 'admin.user.assign_role',
            'admin.role.view', 'admin.role.assign_permission',
            'admin.permission.view',
            'admin.audit.view', 'admin.audit.delete',
            'audit.logs.view',
            'location.view', 'location.create', 'location.update', 'location.delete',
            'department.view', 'department.create', 'department.update', 'department.delete',
            'position.view', 'position.create', 'position.update', 'position.delete',
            'profile.view_all', 'profile.update', 'profile.delete',
            'admin.email.manage',
            'admin.location.manage', 'admin.department.manage', 'admin.position.manage',
            'admin.company.view', 'admin.company.update',
            'admin.schedule.manage', 'admin.approval_flow.manage', 'admin.biometric.manage',
            'biometric.devices.view', 'biometric.attendance.sync',
            'admin.import.users', 'admin.import.employees', 'admin.import.execute',
        ],

        'hr' => [
            'employee.view', 'employee.create', 'employee.update', 'employee.delete',
            'employee.onboard', 'employee.offboard',
            'leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve',
            'leave.policy.manage',
            'attendance.view_all', 'attendance.delete', 'attendance.check_in', 'attendance.check_out',
            'attendance.view_own',
            'payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay',
            'payroll.export', 'payroll.reports.view',
            'kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve',
            'reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay',
            'overtime.view', 'overtime.create', 'overtime.approve', 'overtime.manage',
            'training.view', 'training.create', 'training.update', 'training.delete', 'training.enroll',
            'competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign',
            'asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign',
            'document.view', 'document.create', 'document.update', 'document.delete', 'document.review',
            'assignment_letter.view', 'assignment_letter.create', 'assignment_letter.approve', 'assignment_letter.export',
            'hr_request.view', 'hr_request.create', 'hr_request.assign', 'hr_request.manage',
            'task.view', 'task.create', 'task.update', 'task.delete', 'task.complete',
            'recruitment.opening.view', 'recruitment.opening.create', 'recruitment.opening.update',
            'recruitment.opening.delete', 'recruitment.candidate.view', 'recruitment.candidate.manage',
            'recruitment.interview.schedule', 'recruitment.offer.create',
            'benefit.view', 'benefit.create', 'benefit.update', 'benefit.delete', 'benefit.assign',
            'performance.cycle.view', 'performance.cycle.create', 'performance.cycle.manage',
            'performance.review.view', 'performance.review.create', 'performance.review.update',
            'performance.review.submit', 'performance.review.approve',
            'okr.view', 'okr.create', 'okr.update', 'okr.delete', 'okr.submit', 'okr.approve', 'okr.progress',
            'review360.view', 'review360.create', 'review360.assign_feeders', 'review360.provide_feedback',
            'review360.submit', 'review360.approve',
            'calibration.view', 'calibration.create', 'calibration.participate', 'calibration.manage',
            'career.idp.view', 'career.idp.create', 'career.idp.update',
            'career.succession.view', 'career.succession.manage',
            'career.promotion.view', 'career.promotion.create', 'career.promotion.update',
            'career.promotion.delete', 'career.promotion.approve',
            'engagement.survey.view', 'engagement.survey.create', 'engagement.survey.respond',
            'engagement.survey.analytics',
            'organization.view', 'organization.directory', 'organization.chart', 'organization.team',
            'compliance.view', 'compliance.audit', 'compliance.documents',
            'reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll',
            'reporting.competency', 'reporting.lifecycle', 'reporting.assets',
            'admin.email.manage', 'admin.email.manage',
            'profile.view_all', 'profile.update',
            'admin.import.users', 'admin.import.employees', 'admin.import.execute',
            'location.view', 'department.view', 'position.view',
        ],

        'manager' => [
            'employee.view', 'employee.update', 'employee.onboard', 'employee.offboard',
            'leave.view', 'leave.create', 'leave.update', 'leave.delete', 'leave.approve',
            'attendance.view_all', 'attendance.delete', 'attendance.check_in', 'attendance.check_out',
            'attendance.view_own',
            'payroll.view', 'payroll.create', 'payroll.generate', 'payroll.approve', 'payroll.pay',
            'payroll.export', 'payroll.reports.view',
            'kpi.view', 'kpi.create', 'kpi.update', 'kpi.delete', 'kpi.approve',
            'reimbursement.view', 'reimbursement.create', 'reimbursement.approve', 'reimbursement.pay',
            'overtime.view', 'overtime.create', 'overtime.approve', 'overtime.manage',
            'training.view', 'training.enroll',
            'competency.view', 'competency.create', 'competency.update', 'competency.delete', 'competency.assign',
            'asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.assign',
            'document.view', 'document.create', 'document.update', 'document.delete', 'document.review',
            'assignment_letter.view', 'assignment_letter.create',
            'task.view', 'task.complete',
            'user.view',
            'role.view',
            'permission.view',
            'location.view', 'department.view', 'position.view', 'profile.view_all',
            'performance.review.view', 'performance.review.approve',
            'okr.view', 'okr.approve',
            'review360.view', 'review360.approve',
            'organization.view', 'organization.directory', 'organization.chart', 'organization.team',
            'compliance.view', 'compliance.audit', 'compliance.documents',
            'reporting.dashboard', 'reporting.attendance', 'reporting.leave', 'reporting.payroll',
            'reporting.competency', 'reporting.lifecycle', 'reporting.assets',
        ],

        'employee' => [
            'leave.view', 'leave.create',
            'attendance.check_in', 'attendance.check_out', 'attendance.view_own',
            'overtime.create', 'overtime.view',
            'kpi.view',
            'reimbursement.view', 'reimbursement.create',
            'training.view',
            'competency.view',
            'asset.view',
            'document.view',
            'assignment_letter.view', 'assignment_letter.create', 'assignment_letter.export',
            'task.view', 'task.create', 'task.update',
            'hr_request.view', 'hr_request.create',
            'benefit.view',
            'performance.review.view',
            'okr.view',
            'review360.view',
            'career.idp.view',
            'engagement.survey.respond', 'engagement.survey.view',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Level Indicators
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh User.php isAdmin()/isHR()/isManager()/isEmployee()
    | untuk menentukan level user berdasarkan PERMISSION, bukan role name.
    | Super_admin selalu dianggap all-level.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Role for New Registrations
    |--------------------------------------------------------------------------
    |
    | Role name yang akan diberikan ke user baru saat register.
    | Bisa diubah sesuai kebutuhan tanpa mengubah kode.
    |
    */

    'default_role' => env('RBAC_DEFAULT_ROLE', 'employee'),

    'level_indicators' => [
        'admin' => [
            'role.view', 'role.create', 'role.update', 'role.delete', 'role.assign_permission',
            'user.view', 'user.assign_role',
            'permission.view',
            'admin.audit.view',
            'admin.import.execute',
        ],
        'hr' => [
            'employee.create', 'employee.update', 'employee.delete',
            'employee.onboard', 'employee.offboard',
            'leave.approve', 'leave.policy.manage',
            'payroll.create', 'payroll.approve', 'payroll.pay',
        ],
        'manager' => [
            'employee.view', 'employee.update',
            'leave.approve',
            'kpi.approve',
            'overtime.approve',
            'performance.review.approve',
            'okr.approve',
        ],
        'employee' => [
            'attendance.check_in', 'attendance.check_out', 'attendance.view_own',
            'leave.create',
            'overtime.create',
            'reimbursement.create',
        ],
    ],
];
