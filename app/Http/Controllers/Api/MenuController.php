<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\MenuPermission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController
{
    public const MENU_DEFINITIONS = [
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

        $items = array_map(function ($def) use ($assignments) {
            $def['assigned_role_ids'] = $assignments[$def['key']] ?? [];
            return $def;
        }, self::MENU_DEFINITIONS);

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
            return ApiResponse::success('All menus', array_column(self::MENU_DEFINITIONS, 'key'));
        }

        $userRoleIds = $user->roles->pluck('id');
        $assignedKeys = MenuPermission::whereIn('role_id', $userRoleIds)
            ->pluck('menu_key')
            ->unique()
            ->values()
            ->toArray();

        $allKeys = array_column(self::MENU_DEFINITIONS, 'key');
        $restrictedKeys = MenuPermission::select('menu_key')->distinct()->pluck('menu_key')->toArray();

        // Menus with no restrictions are visible to everyone
        // Menus with restrictions are only visible if user's role is assigned
        $allowedKeys = [];
        foreach ($allKeys as $key) {
            if (!in_array($key, $restrictedKeys) || in_array($key, $assignedKeys)) {
                $allowedKeys[] = $key;
            }
        }

        return ApiResponse::success('Allowed menus', $allowedKeys);
    }
}
