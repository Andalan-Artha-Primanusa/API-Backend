# Permission → Komponen / Endpoint Mapping

Dokumen ini memetakan permission utama ke komponen frontend dan endpoint backend. Gunakan untuk audit dan pembuatan UI yang berbasis permission.

| Permission | Komponen (frontend) | Endpoint (backend) |
|---|---|---|
| `payroll.generate` | `src/features/payroll/components/PayrollGenerate.tsx` | `POST /api/payroll/generate` |
| `payroll.create` | `src/features/payroll/components/PayrollCreate.tsx` | `POST /api/payroll` |
| `payroll.approve` | `src/features/payroll/components/PayrollApprove.tsx` | `POST /api/payroll/{id}/approve` |
| `payroll.pay` | `src/features/payroll/components/PayrollPayment.tsx` | `POST /api/payroll/{id}/pay` |
| `reimbursement.pay` | `src/features/reimbursement/components/ReimbursementDetail.tsx` | `POST /api/reimbursements/{id}/pay` |
| `reimbursement.create` | `src/features/reimbursement/components/ReimbursementForm.tsx` | `POST /api/reimbursements` |
| `overtime.create` | `src/features/attendance/components/OvertimeRequestForm.tsx` | `POST /api/overtime` |
| `overtime.approve` | `src/features/attendance/components/OvertimeApprovalList.tsx` | `POST /api/overtime/{id}/approve` |
| `leave.create` | `src/features/leave/components/CreateLeaveForm.tsx` | `POST /api/leave` |
| `leave.approve` | `src/features/leave/components/LeaveApprovalPage.tsx` | `POST /api/leave/{id}/approve` |
| `attendance.check_in` | `src/pages/attendance/AttendanceCheckInPage.tsx` | `POST /api/attendance/check-in` |
| `attendance.check_out` | `src/pages/attendance/AttendanceCheckInPage.tsx` | `POST /api/attendance/check-out` |
| `attendance.view_own` | `src/features/attendance/components/MyAttendance.tsx` | `GET /api/attendance/me` |
| `employee.create` | `src/features/employee/components/EmployeeForm.tsx` | `POST /api/employees` |
| `user.assign_role` | `src/pages/admin/AdminUsersPage.tsx` | `POST /api/admin/users/{id}/assign-role` |
| `role.assign_permission` | `src/pages/admin/AdminRolesPage.tsx` | `POST /api/admin/roles/{id}/assign-permission` |

Catatan:
- Jalur frontend adalah contoh lokasi komponen — sesuaikan bila repo memiliki struktur berbeda.
- Setelah memperbarui `app/Constants/Permissions.php`, jalankan seeder RBAC:

```bash
php artisan db:seed --class=RbacSeeder
```

File ini bisa dikembangkan lebih lengkap (semua permission → semua komponen/endpoint).
