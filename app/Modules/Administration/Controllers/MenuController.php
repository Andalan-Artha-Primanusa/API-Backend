<?php

namespace App\Modules\Administration\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MenuPermission;
use App\Modules\Administration\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController
{
    private const MENU_PERMISSIONS = [
        'dashboard' => ['reporting.dashboard', 'dashboard.customize_self'],
        'dashboard.overview' => ['reporting.dashboard'],
        'dashboard.custom' => ['dashboard.customize_self'],
        'workforce' => ['employee.view', 'attendance.view_all', 'attendance.view_own', 'leave.view', 'overtime.view', 'patrol.scan'],
        'workforce.employees' => ['employee.view'],
        'workforce.attendance' => ['attendance.view_all', 'attendance.view_own'],
        'workforce.attendance.qr-generator' => ['attendance.qr.generate'],
        'workforce.patrol.scan' => ['patrol.scan'],
        'workforce.patrol.monitor' => ['patrol.view', 'patrol.manage', 'patrol.report'],
        'workforce.leave' => ['leave.view'],
        'workforce.overtime' => ['overtime.view'],
        'compensation' => ['payroll.view', 'reimbursement.view'],
        'compensation.payroll' => ['payroll.view'],
        'compensation.reimbursement' => ['reimbursement.view'],
        'performance-dev' => ['kpi.view', 'training.view', 'competency.view', 'calibration.view'],
        'performance-dev.kpi' => ['kpi.view'],
        'performance-dev.calibration' => ['calibration.view'],
        'performance-dev.training' => ['training.view'],
        'performance-dev.competency' => ['competency.view'],
        'assets' => ['asset.view'],
        'approval-center' => ['admin.approval_flow.manage'],
        'reports' => ['reporting.dashboard'],
        'admin' => ['company.view', 'admin.company.view', 'user.view', 'role.view', 'permission.view', 'admin.audit.view'],
        'admin.companies' => ['company.view', 'company.view_all'],
        'admin.company' => ['admin.company.view', 'company.view'],
        'admin.departments' => ['department.view'],
        'admin.positions' => ['position.view'],
        'admin.locations' => ['location.view'],
        'admin.users' => ['user.view', 'admin.user.view'],
        'admin.roles' => ['role.view', 'admin.role.view'],
        'admin.permissions' => ['permission.view', 'admin.permission.view'],
        'admin.menu-permissions' => ['role.assign_permission', 'role.view'],
        'admin.approval-workflow' => ['admin.approval_flow.manage'],
        'admin.audit-logs' => ['audit.logs.view', 'admin.audit.view'],
        'admin.import' => ['admin.import.execute'],
        'admin.notifications' => ['admin.email.manage'],
        'admin.email-send' => ['admin.email.manage'],
        'admin.email-logs' => ['admin.email.manage'],
        'admin.notification-settings' => ['admin.email.manage'],
        'admin.work-schedules' => ['admin.schedule.manage'],
        'employee-dashboard' => ['dashboard.customize_self', 'attendance.view_own'],
        'ess.profile' => ['profile.update', 'attendance.view_own'],
        'ess.attendance' => ['attendance.check_in', 'attendance.check_out', 'attendance.view_own', 'patrol.scan'],
        'ess.attendance.check-in' => ['attendance.check_in'],
        'ess.attendance.check-out' => ['attendance.check_out'],
        'ess.attendance.patrol' => ['patrol.scan'],
        'ess.attendance.history' => ['attendance.view_own'],
        'ess.leave' => ['leave.view', 'leave.create'],
        'ess.overtime' => ['overtime.view', 'overtime.create'],
        'ess.reimbursement' => ['reimbursement.view', 'reimbursement.create'],
        'ess.payslip' => ['payroll.view', 'payroll.view_own'],
        'ess.kpi' => ['kpi.view'],
        'ess.training' => ['training.view'],
        'ess.competency' => ['competency.view'],
        'ess.assets' => ['asset.view'],
        'ess.documents' => ['document.view'],
        'ess.dashboard.custom' => ['dashboard.customize_self'],
        'ess.notifications' => ['attendance.view_own'],
    ];

    public const MENU_DEFINITIONS = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'path' => '/dashboard'],
        ['key' => 'dashboard.overview', 'label' => 'Overview', 'path' => '/dashboard'],
        ['key' => 'dashboard.custom', 'label' => 'Custom Dashboard', 'path' => '/dashboard/custom'],
        ['key' => 'workforce', 'label' => 'Workforce'],
        ['key' => 'workforce.employees', 'label' => 'Employees', 'path' => '/employees'],
        ['key' => 'workforce.attendance', 'label' => 'Attendance', 'path' => '/attendance'],
        ['key' => 'workforce.attendance.qr-generator', 'label' => 'QR Generator', 'path' => '/attendance/qr-generator'],
        ['key' => 'workforce.patrol.scan', 'label' => 'Patrol Scan', 'path' => '/patrol/scan'],
        ['key' => 'workforce.patrol.monitor', 'label' => 'Patrol Monitor', 'path' => '/patrol/monitor'],
        ['key' => 'workforce.leave', 'label' => 'Leave', 'path' => '/leave/requests'],
        ['key' => 'workforce.overtime', 'label' => 'Overtime', 'path' => '/attendance/overtime'],
        ['key' => 'compensation', 'label' => 'Compensation'],
        ['key' => 'compensation.payroll', 'label' => 'Payroll', 'path' => '/payroll'],
        ['key' => 'compensation.reimbursement', 'label' => 'Reimbursement', 'path' => '/reimbursements'],
        ['key' => 'performance-dev', 'label' => 'Performance & Development'],
        ['key' => 'performance-dev.kpi', 'label' => 'KPI', 'path' => '/kpis'],
        ['key' => 'performance-dev.calibration', 'label' => 'Calibration', 'path' => '/performance/calibration'],
        ['key' => 'performance-dev.training', 'label' => 'Training', 'path' => '/training/programs'],
        ['key' => 'performance-dev.competency', 'label' => 'Competency', 'path' => '/competencies'],
        ['key' => 'assets', 'label' => 'Assets', 'path' => '/assets'],
        ['key' => 'approval-center', 'label' => 'Approval Center', 'path' => '/approval-flows'],
        ['key' => 'reports', 'label' => 'Reports', 'path' => '/reports/dashboard-summary'],
        ['key' => 'admin', 'label' => 'Administration'],
        ['key' => 'admin.companies', 'label' => 'Companies', 'path' => '/companies'],
        ['key' => 'admin.company', 'label' => 'Company Setting', 'path' => '/settings/company'],
        ['key' => 'admin.departments', 'label' => 'Departments', 'path' => '/organization/master-data'],
        ['key' => 'admin.positions', 'label' => 'Positions', 'path' => '/organization/master-data'],
        ['key' => 'admin.locations', 'label' => 'Locations', 'path' => '/locations'],
        ['key' => 'admin.users', 'label' => 'Users', 'path' => '/admin/users'],
        ['key' => 'admin.roles', 'label' => 'Roles & Permissions', 'path' => '/admin/roles'],
        ['key' => 'admin.permissions', 'label' => 'Permissions', 'path' => '/admin/permissions'],
        ['key' => 'admin.menu-permissions', 'label' => 'Menu Access', 'path' => '/admin/menu-permissions'],
        ['key' => 'admin.approval-workflow', 'label' => 'Approval Workflow', 'path' => '/approval-flows'],
        ['key' => 'admin.audit-logs', 'label' => 'Audit Logs', 'path' => '/admin/audit-logs'],
        ['key' => 'admin.import', 'label' => 'Import Center', 'path' => '/admin/import'],
        ['key' => 'admin.notifications', 'label' => 'Admin Notifications', 'path' => '/admin/notifications'],
        ['key' => 'admin.email-send', 'label' => 'Send Email', 'path' => '/admin/notifications/email-send'],
        ['key' => 'admin.email-logs', 'label' => 'Email Logs', 'path' => '/admin/notifications/email-logs'],
        ['key' => 'admin.notification-settings', 'label' => 'Notification Settings', 'path' => '/settings/notifications'],
        ['key' => 'admin.work-schedules', 'label' => 'Work Schedules', 'path' => '/work-schedules'],
        ['key' => 'ess.profile', 'label' => 'My Profile', 'path' => '/my/profile'],
        ['key' => 'ess.attendance', 'label' => 'Attendance'],
        ['key' => 'ess.attendance.check-in', 'label' => 'QR Check In', 'path' => '/attendance/check-in'],
        ['key' => 'ess.attendance.check-out', 'label' => 'QR Check Out', 'path' => '/attendance/check-out'],
        ['key' => 'ess.attendance.patrol', 'label' => 'Patrol Scan', 'path' => '/patrol/scan'],
        ['key' => 'ess.attendance.history', 'label' => 'History', 'path' => '/attendance/history'],
        ['key' => 'ess.leave', 'label' => 'Leave', 'path' => '/leave/my-leave'],
        ['key' => 'ess.overtime', 'label' => 'Overtime', 'path' => '/my/overtime'],
        ['key' => 'ess.reimbursement', 'label' => 'Reimbursement', 'path' => '/my/reimbursements'],
        ['key' => 'ess.payslip', 'label' => 'Payslip', 'path' => '/my/payroll'],
        ['key' => 'ess.kpi', 'label' => 'My KPI', 'path' => '/my/kpi'],
        ['key' => 'ess.training', 'label' => 'Training', 'path' => '/my/trainings'],
        ['key' => 'ess.competency', 'label' => 'Competency', 'path' => '/my/competencies'],
        ['key' => 'ess.assets', 'label' => 'My Assets', 'path' => '/my/assets'],
        ['key' => 'ess.documents', 'label' => 'My Documents', 'path' => '/my/documents'],
        ['key' => 'ess.dashboard.custom', 'label' => 'Custom Dashboard', 'path' => '/dashboard/custom'],
        ['key' => 'ess.notifications', 'label' => 'Notifications', 'path' => '/notifications'],
        ['key' => 'dashboard', 'label' => 'Dashboard', 'path' => '/dashboard'],
        ['key' => 'employee-dashboard', 'label' => 'Dashboard Saya', 'path' => '/employee-dashboard'],
        ['key' => 'employees', 'label' => 'Manajemen Karyawan', 'path' => '/employees'],
        ['key' => 'absensi-waktu', 'label' => 'Absensi & Waktu'],
        ['key' => 'absensi-waktu.overtime', 'label' => 'Lembur', 'path' => '/attendance/overtime'],
        ['key' => 'absensi-waktu.reports', 'label' => 'Laporan', 'path' => '/attendance/reports'],
        ['key' => 'manajemen-cuti', 'label' => 'Manajemen Cuti'],
        ['key' => 'manajemen-cuti.permohonan', 'label' => 'Permohonan Cuti', 'path' => '/leave/requests'],
        ['key' => 'manajemen-cuti.persetujuan', 'label' => 'Persetujuan Cuti', 'path' => '/leave/approval'],
        ['key' => 'manajemen-cuti.kalender', 'label' => 'Kalender Cuti', 'path' => '/leave/calendar'],
        ['key' => 'manajemen-cuti.saldo', 'label' => 'Saldo Cuti', 'path' => '/leave/balance'],
        ['key' => 'penggajian', 'label' => 'Penggajian & Slip Gaji'],
        ['key' => 'penggajian.ringkasan', 'label' => 'Ringkasan', 'path' => '/payroll'],
        ['key' => 'penggajian.daftar', 'label' => 'Daftar Payroll', 'path' => '/payroll/list'],
        ['key' => 'penggajian.proses', 'label' => 'Proses Payroll', 'path' => '/payroll/process'],
        ['key' => 'penggajian.komponen', 'label' => 'Komponen Gaji', 'path' => '/payroll/component'],
        ['key' => 'penggajian.laporan', 'label' => 'Laporan & Pajak', 'path' => '/payroll/reports'],
        ['key' => 'assets', 'label' => 'Aset & Inventaris', 'path' => '/assets'],
        ['key' => 'tasks', 'label' => 'Task Management', 'path' => '/tasks'],
        ['key' => 'legal-dokumen', 'label' => 'Legal & Dokumen'],
        ['key' => 'legal-dokumen.surat-tugas', 'label' => 'Surat Tugas', 'path' => '/admin/assignment-letters'],
        ['key' => 'legal-dokumen.generator', 'label' => 'Generator Surat', 'path' => '/legal/letters'],
        ['key' => 'legal-dokumen.kalkulator', 'label' => 'Kalkulator Pesangon', 'path' => '/legal/severance'],
        ['key' => 'legal-dokumen.pph21', 'label' => 'PPh21 Progresif', 'path' => '/legal/tax'],
        ['key' => 'reimbursements', 'label' => 'Manajemen Reimburse', 'path' => '/reimbursements'],
        ['key' => 'pelatihan-kompetensi', 'label' => 'Pelatihan & Kompetensi'],
        ['key' => 'pelatihan-kompetensi.pelatihan', 'label' => 'Pelatihan & Pendaftaran', 'path' => '/training/programs'],
        ['key' => 'pelatihan-kompetensi.kompetensi', 'label' => 'Kompetensi', 'path' => '/competencies'],
        ['key' => 'karir-promosi', 'label' => 'Karir & Promosi', 'path' => '/promotions'],
        ['key' => 'kpi-kinerja', 'label' => 'KPI & Kinerja', 'path' => '/kpis'],
        ['key' => 'ess', 'label' => 'Employee Self Service (ESS)'],
        ['key' => 'ess.kinerja.kpi', 'label' => 'KPI Saya', 'path' => '/my/kpi'],
        ['key' => 'ess.kinerja.kompetensi', 'label' => 'Kompetensi Saya', 'path' => '/my/competencies'],
        ['key' => 'ess.keuangan.payroll', 'label' => 'Payroll Saya', 'path' => '/my/payroll'],
        ['key' => 'ess.keuangan.reimburse', 'label' => 'Reimburse Saya', 'path' => '/my/reimbursements'],
        ['key' => 'ess.absensi.check-in', 'label' => 'Absen Masuk', 'path' => '/attendance/check-in'],
        ['key' => 'ess.absensi.check-out', 'label' => 'Absen Pulang', 'path' => '/attendance/check-out'],
        ['key' => 'ess.absensi.riwayat', 'label' => 'Riwayat Absensi', 'path' => '/attendance/history'],
        ['key' => 'ess.pengembangan.pelatihan', 'label' => 'Pelatihan Saya', 'path' => '/my/trainings'],
        ['key' => 'ess.pengembangan.lembur', 'label' => 'Lembur Saya', 'path' => '/attendance/overtime'],
        ['key' => 'ess.pengembangan.promosi', 'label' => 'Promosi Saya', 'path' => '/my/promotions'],
        ['key' => 'ess.dokumen-aset.dokumen', 'label' => 'Dokumen Saya', 'path' => '/my/documents'],
        ['key' => 'ess.dokumen-aset.aset', 'label' => 'Aset Saya', 'path' => '/my/assets'],
        ['key' => 'ess.dokumen-aset.surat-tugas', 'label' => 'Surat Tugas', 'path' => '/my/assignment-letters'],
        ['key' => 'ess.dokumen-aset.tugas', 'label' => 'Tugas Saya', 'path' => '/my/tasks'],
        ['key' => 'laporan-analitik', 'label' => 'Laporan & Analitik', 'path' => '/reports/dashboard-summary'],
        ['key' => 'kepatuhan-kebijakan', 'label' => 'Kepatuhan & Kebijakan'],
        ['key' => 'kepatuhan-kebijakan.dashboard', 'label' => 'Dashboard Kepatuhan', 'path' => '/compliance/overview'],
        ['key' => 'kepatuhan-kebijakan.kalender', 'label' => 'Kalender Libur', 'path' => '/workforce/holidays'],
        ['key' => 'kepatuhan-kebijakan.tukar-shift', 'label' => 'Tukar Shift', 'path' => '/workforce/shift-swaps'],
        ['key' => 'kepatuhan-kebijakan.aturan-lembur', 'label' => 'Aturan Lembur', 'path' => '/workforce/overtime-rules'],
        ['key' => 'master-data', 'label' => 'Master Data'],
        ['key' => 'master-data.departemen', 'label' => 'Departemen & Posisi', 'path' => '/organization/master-data'],
        ['key' => 'master-data.jenis-cuti', 'label' => 'Jenis Cuti', 'path' => '/leave/type'],
        ['key' => 'master-data.kebijakan-cuti', 'label' => 'Kebijakan Cuti', 'path' => '/leave/policy'],
        ['key' => 'master-data.pusat-impor', 'label' => 'Pusat Impor', 'path' => '/admin/import'],
        ['key' => 'alat-admin', 'label' => 'Alat Admin'],
        ['key' => 'alat-admin.master.lokasi', 'label' => 'Lokasi', 'path' => '/locations'],
        ['key' => 'alat-admin.master.jadwal-kerja', 'label' => 'Jadwal Kerja', 'path' => '/work-schedules'],
        ['key' => 'alat-admin.manajemen-akses.pengguna', 'label' => 'Pengguna', 'path' => '/admin/users'],
        ['key' => 'alat-admin.manajemen-akses.peran', 'label' => 'Peran', 'path' => '/admin/roles'],
        ['key' => 'alat-admin.manajemen-akses.izin', 'label' => 'Izin', 'path' => '/admin/permissions'],
        ['key' => 'alat-admin.manajemen-akses.menu', 'label' => 'Akses Menu', 'path' => '/admin/menu-permissions'],
        ['key' => 'alat-admin.notifikasi.admin', 'label' => 'Notifikasi Admin', 'path' => '/admin/notifications'],
        ['key' => 'alat-admin.notifikasi.kirim-email', 'label' => 'Kirim Notifikasi Email', 'path' => '/admin/notifications/email-send'],
        ['key' => 'alat-admin.notifikasi.log-email', 'label' => 'Log & Template Email', 'path' => '/admin/notifications/email-logs'],
        ['key' => 'alat-admin.sistem.log-audit', 'label' => 'Log Audit', 'path' => '/admin/audit-logs'],
        ['key' => 'alat-admin.sistem.biometrik', 'label' => 'Perangkat Biometrik', 'path' => '/admin/biometric-devices'],
        ['key' => 'alat-admin.sistem.alur-persetujuan', 'label' => 'Alur Persetujuan', 'path' => '/approval-flows'],
        ['key' => 'alat-admin.pengaturan.perusahaan', 'label' => 'Pengaturan Perusahaan', 'path' => '/settings/company'],
        ['key' => 'alat-admin.pengaturan.notifikasi', 'label' => 'Pengaturan Notifikasi', 'path' => '/settings/notifications'],
    ];

    public function definitions(): JsonResponse
    {
        $roles = Role::where('name', '!=', 'super_admin')->get();
        $assignments = MenuPermission::all()->groupBy('menu_key')->map->pluck('role_id')->toArray();

        $items = collect(self::MENU_DEFINITIONS)
            ->unique('key')
            ->values()
            ->map(function ($def) use ($assignments) {
                $def['assigned_role_ids'] = $assignments[$def['key']] ?? [];
                $def['required_permissions'] = self::MENU_PERMISSIONS[$def['key']] ?? [];
                return $def;
            })
            ->all();

        return ApiResponse::success('Menu definitions', [
            'items' => $items,
            'roles' => $roles,
        ]);
    }

    public function assignRole(Request $request): JsonResponse
    {
        $data = $request->validate([
            'menu_key' => 'required|string',
            'role_id' => 'required|exists:roles,id',
        ]);

        MenuPermission::firstOrCreate([
            'menu_key' => $data['menu_key'],
            'role_id' => $data['role_id'],
        ]);

        return ApiResponse::success('Role assigned to menu');
    }

    public function removeRole(string $menuKey, int $roleId): JsonResponse
    {
        MenuPermission::where('menu_key', $menuKey)
            ->where('role_id', $roleId)
            ->delete();

        return ApiResponse::success('Role removed from menu');
    }

    public function userMenus(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return ApiResponse::success('All menus', collect(self::MENU_DEFINITIONS)->pluck('key')->unique()->values()->all());
        }

        // Jika user tidak punya role, tidak ada menu yang bisa diakses
        if (!$user->roles->count()) {
            return ApiResponse::success('No allowed menus', []);
        }

        // Menu permission sudah dikonfigurasi â†’ hanya return menu yang di-assign ke role user
        $userRoleIds = $user->roles->pluck('id');
        $assignedKeys = MenuPermission::whereIn('role_id', $userRoleIds)
            ->pluck('menu_key')
            ->unique()
            ->values()
            ->filter(fn (string $key) => $this->canAccessMenuKey($user, $key))
            ->toArray();

        return ApiResponse::success('Allowed menus', $assignedKeys);
    }

    private function canAccessMenuKey($user, string $key): bool
    {
        $requiredPermissions = self::MENU_PERMISSIONS[$key] ?? [];

        if (empty($requiredPermissions)) {
            return true;
        }

        return $user->hasAnyPermission($requiredPermissions);
    }
}

