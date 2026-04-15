# HRIS API Features for PPT

Dokumen ini merangkum seluruh fitur yang terdaftar di `routes/api.php` dan disusun dalam format yang mudah dipindahkan ke presentasi PowerPoint.

## 1. Ringkasan Eksekutif

HRIS backend ini sudah mencakup siklus hidup karyawan end-to-end, mulai dari onboarding, kehadiran, cuti, payroll, reimbursement, performa, hingga modul enterprise seperti recruitment, career development, engagement, workforce policy, compliance, dan reporting.

Sistem juga sudah memiliki:
- Authentication dengan Sanctum dan Google SSO.
- Role-based access control untuk employee, manager, HR, admin, dan super admin.
- Audit trail pada seluruh route authenticated.
- Pemisahan jelas antara self-service, operasional HR, dan system administration.

## 2. Struktur Slide yang Disarankan

### Slide 1 - Cover
- HRIS Backend System
- Production-ready API untuk HR end-to-end
- 20+ modul, puluhan proses bisnis, role-based access control

### Slide 2 - Platform Overview
- API-first architecture
- Authenticated routes dengan audit trail
- Modular dan scalable untuk kebutuhan operasional HR

### Slide 3 - Access Control
- Public, authenticated, dan restricted routes
- Hierarki akses: Super Admin > Admin > HR > Manager > Employee
- Permission dan role management untuk keamanan sistem

### Slide 4 - Employee Self-Service
- Profile pribadi
- Attendance, leave, payroll, KPI, reimbursement
- Training, competency, asset, benefit, document, dan request pribadi

### Slide 5 - Attendance & Leave
- Check-in/check-out
- Riwayat absensi dan intelligence
- Leave balance, leave calendar, approval flow

### Slide 6 - Payroll & Reimbursement
- Payroll pribadi dan administratif
- Export slip PDF/CSV
- Reimbursement request, approval, payment tracking

### Slide 7 - Employee Management
- CRUD employee
- Onboarding dan offboarding
- Manajemen data karyawan oleh HR/Admin/Super Admin

### Slide 8 - Organization & Compliance
- Org structure, directory, chart, team view
- Audit log
- Compliance overview dan expiring document monitoring

### Slide 9 - Training, Competency, Asset, Documents
- Training program dan enrollment
- Competency management
- Asset assignment
- Employee document upload dan review

### Slide 10 - Recruitment & ATS
- Job openings
- Candidate pipeline
- Interview, offer, background check, talent pool

### Slide 11 - Performance Management
- KPI
- Performance review cycle
- OKR
- 360 review
- Calibration

### Slide 12 - Career, Engagement, Workforce
- IDP dan succession planning
- Engagement survey
- Holiday calendar
- Shift swap
- Overtime rules

### Slide 13 - Enterprise Ops
- Compensation profile
- Notification templates and rules
- Compliance tasks and privacy requests
- Biometric integration

### Slide 14 - Reporting & Analytics
- People insights dashboard
- Attendance, leave, payroll, competency, lifecycle, asset analytics

### Slide 15 - Closing
- HRIS siap dipakai untuk operasional harian
- Mendukung pengelolaan SDM skala kecil sampai besar
- Siap dikembangkan ke integrasi enterprise berikutnya

## 3. Daftar Fitur Lengkap Berdasarkan Route

### A. Public Routes
- Halaman root API untuk pengecekan status service.
- Login.
- Register.
- Google SSO redirect dan callback.

### B. Authentication dan Akses Dasar
- Endpoint profil user saat login.
- Logout.
- Semua route protected berada di bawah `auth:sanctum`.
- Audit trail aktif pada route authenticated.

### C. Employee Self-Service
- Profil pengguna.
- KPI pribadi dan submit KPI.
- Reimbursement pribadi dan submit reimbursement.
- Payroll pribadi, slip gaji, export CSV, export PDF.
- Training pribadi.
- Competency pribadi.
- Asset pribadi.
- Benefit pribadi.
- Performance review pribadi.
- Employee document pribadi.
- HR service request pribadi dan komentar.
- Leave pribadi dan saldo cuti.
- Attendance check-in, check-out, history, today, intelligence, overtime.
- Notification center, unread count, read all, read per item, delete notification.

### D. Approval dan Operasional HR
- Leave approval pending, approve, reject.
- KPI management, approval, by employee.
- Reimbursement management, approval, reject, mark paid, statistics.
- People insights dashboard dan team health.
- Employee-specific attendance intelligence.
- Leave policy management.
- Training program management.
- Competency master dan assignment.
- Asset inventory dan assignment.
- Document review, expiring document, contract document list.
- HR service request assignment, status update, komentar, delete.
- Recruitment summary, openings, candidates, interview, offer, talent pool.
- Benefits management dan assignment.
- Performance cycle, reviews, OKR, 360 review, calibration.
- Career development, IDP, succession planning.
- Engagement surveys dan analytics.
- Workforce policy: holidays, shift swap, overtime rules.
- Enterprise operations: compensation, notification rules, compliance tasks.
- Reporting dashboard dan analytics lintas modul.

### E. Organization dan Compliance
- Organization directory.
- Organization summary.
- Org chart.
- Team members by manager.
- Master data organization.
- Compliance overview.
- Audit summary.
- Expiring documents.

### F. Employee Management
- Employee CRUD.
- Onboarding start dan complete.
- Offboarding start dan complete.
- Operasi employee berada di bawah role admin, HR, dan super admin.

### G. Payroll
- Payroll list.
- Payroll create.
- Generate monthly payroll.
- Payroll detail per record.
- Slip gaji.
- Export CSV dan PDF.
- Update, delete, approve, dan pay payroll.
- Payroll details CRUD.

### H. Master Data dan System Settings
- Locations management.
- Work schedules management.
- Role management.
- Permission management.
- User management.
- Assign role to user.
- Assign permission to role.
- Data import untuk users dan employees.
- Import template.

### I. Admin Notifications dan Email
- Notification summary.
- Create notification.
- Broadcast notification.
- Send email notification.
- Email logs.
- Retry email notification.
- Email template management dan preview.

### J. Biometric Integration
- Device list.
- Device registration.
- Sync attendance dari biometric.

## 4. Kategori Fitur per Modul

### Foundation
- Authentication.
- RBAC.
- Audit trail.
- User profile.

### Core HR
- Employee lifecycle.
- Attendance.
- Leave.
- Payroll.
- Reimbursement.

### Operational HR
- Training.
- Competency.
- Documents.
- Assets.
- HR service requests.

### Strategic HR
- Recruitment.
- Performance.
- OKR.
- 360 review.
- Calibration.
- Career development.
- Engagement.

### Enterprise Control
- Organization structure.
- Compliance.
- Biometric integration.
- Workforce policy.
- Notifications.
- Reports and analytics.

## 5. Poin Penting untuk Slide Presentasi

- Sistem ini bukan hanya CRUD HR biasa, tetapi sudah mencakup workflow operasional dan strategic HR.
- Self-service employee sudah dipisahkan dari akses manajerial dan administratif.
- Modul performance management sudah mencakup KPI, review, OKR, 360 review, dan calibration.
- Modul recruitment, career, engagement, workforce, dan enterprise ops sudah tersedia sebagai fondasi sistem HR yang lebih besar.
- Reporting dan compliance sudah ada untuk mendukung monitoring manajemen.

## 6. Ringkasan Siap Pakai untuk Closing Slide

HRIS backend pada `routes/api.php` sudah menyediakan fondasi lengkap untuk pengelolaan SDM modern: aman, modular, scalable, dan siap dipresentasikan sebagai platform HR end-to-end.
