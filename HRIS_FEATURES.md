# HRIS Feature Coverage and Recommended HR Modules

Dokumen ini merangkum fitur yang sudah tersedia di backend HRIS ini dan fitur tambahan yang biasanya dibutuhkan agar kebutuhan HR menjadi lebih lengkap, operasional, dan siap dipakai untuk skala tim yang lebih besar.

## 1. Modul yang Sudah Ada

### Authentication dan Access Control
- Login, register, logout.
- Google SSO.
- Sanctum authentication untuk route protected.
- Role-based access control untuk admin, HR, manager, super admin.
- Permission-based guard untuk endpoint sensitif.

### User Profile dan Employee Data
- CRUD profile user.
- Employee management.
- Relasi employee ke user, manager, dan profile.
- Pemisahan data self-service dan data administratif.

### Attendance
- Check-in dan check-out.
- Riwayat attendance pribadi.
- Attendance hari ini.
- Daftar attendance seluruh karyawan untuk admin.
- Detail dan delete attendance.

### Leave Management
- Pengajuan cuti.
- Daftar cuti pribadi.
- Saldo cuti.
- Kalender cuti.
- Pending approval.
- Approve dan reject leave.

### KPI
- CRUD KPI.
- KPI per employee.
- Approve KPI.
- Submit KPI oleh employee.
- KPI self-service.

### Reimbursement
- Pengajuan reimbursement.
- Daftar reimbursement pribadi.
- Daftar reimbursement administratif.
- Pending, approve, reject, mark as paid.
- Statistik reimbursement.

### Payroll
- Daftar payroll.
- Payroll pribadi.
- Create payroll.
- Generate monthly payroll.
- Approve dan pay payroll.
- Payroll details.

### Master Data dan System Settings
- Location management.
- Work schedule management.
- Role management.
- Permission management.
- User management.

### Analytics
- People insights dashboard.
- Trend dan team health data.

### System Workflow Dasar
- Route protected berbasis Sanctum.
- Middleware role dan permission untuk pembatasan akses.
- Entity relasi inti antara user, employee, manager, dan approver.

## 2. Fitur HR yang Umumnya Masih Dibutuhkan

### Recruitment / ATS
- Lowongan kerja.
- Kandidat dan pipeline seleksi.
- Jadwal interview.
- Evaluasi interview.
- Offer letter.
- Status hiring per tahap.
- Background check kandidat.
- Talent pool dan kandidat non-active.
- Requisition form sebelum opening posisi.

### Onboarding dan Offboarding
- Checklist onboarding karyawan baru.
- Serah terima aset.
- Clearance offboarding.
- Exit interview.
- Status final settlement.
- Tanda tangan dokumen onboarding.
- Aktivasi akun dan akses sistem bertahap.
- Checklist masa probation.
- Implementasi awal lifecycle karyawan sudah tersedia lewat status employee dan endpoint onboarding/offboarding.

### Employee Lifecycle
- Promosi dan mutasi jabatan.
- Transfer antar divisi atau cabang.
- Riwayat perubahan posisi.
- Status probation, aktif, nonaktif, resign, terminated.
- Onboarding dan offboarding sudah bisa dijalankan lewat endpoint employee lifecycle.

### Kontrak dan Dokumen Karyawan
- Masa berlaku kontrak.
- Jenis kontrak: PKWT, PKWTT, magang, kontrak proyek.
- Upload dokumen pribadi dan dokumen kerja.
- Reminder dokumen dan kontrak yang akan habis.
- Template kontrak dan surat kerja.
- Validasi kelengkapan dokumen wajib.
- Riwayat versi dokumen.

### Shift dan Overtime
- Jadwal shift harian atau mingguan.
- Pola kerja fleksibel.
- Aturan overtime.
- Cut-off attendance.
- Toleransi telat dan pulang cepat.
- Shift swapping antar employee.
- Approval lembur.
- Perhitungan lembur otomatis berdasarkan aturan perusahaan.
- Time clock policy untuk lokasi atau cabang tertentu.

### Leave Policy yang Lebih Lengkap
- Kategori cuti detail: tahunan, sakit, izin, melahirkan, unpaid leave, special leave.
- Carry over cuti.
- Kuota cuti per tahun.
- Approval flow bertingkat.
- Integrasi kalender libur nasional.
- Leave encashment.
- Blackout period pada periode sibuk.
- Auto-deduction leave untuk ketidakhadiran tertentu.
- Cuti per status kontrak atau masa kerja.
- Implementasi awal leave policy dan annual balance sudah tersedia lewat `leave_policies` dan `employee_leave_balances`.

### Performance Management
- OKR selain KPI.
- Performance review berkala.
- 1:1 check-in.
- 360 review.
- Kompetensi dan appraisal history.
- Goal setting per kuartal atau semester.
- Calibration antar manajer.
- PIP atau performance improvement plan.

### Competency dan Career Development
- Matriks kompetensi per jabatan.
- Skill gap analysis.
- Career path internal.
- Succession planning.
- Rencana pengembangan individu.

### Training dan Learning
- Program training.
- Riwayat training per karyawan.
- Sertifikat.
- Kompetensi hasil training.
- Attendance training.
- LMS sederhana untuk materi internal.
- Pre-test dan post-test training.
- Training budget per departemen.
- Implementasi awal training program, enrollment, completion, competency matrix, dan assignment kompetensi sudah tersedia.

### Organization Structure
- Struktur divisi, departemen, dan posisi.
- Org chart.
- Riwayat mutasi jabatan.
- Mapping atasan langsung dan matriks approval.
- Branch atau cabang perusahaan.
- Cost center.
- Position level dan job grade.

### Master Data HR
- Department.
- Job title atau position.
- Branch atau office.
- Employment type.
- Holiday calendar.
- Reason master untuk cuti, absensi, dan reimbursement.

### Asset Management
- Peminjaman dan assignment aset.
- Tracking laptop, ID card, kendaraan, akses sistem.
- Riwayat serah terima aset.
- Pengembalian aset saat resign.
- Asset request workflow.
- Asset stock dan inventory status.
- Maintenance dan warranty tracking.
- Implementasi awal asset registry, assignment, return, dan self-service asset view sudah tersedia.

### Facilities dan General Administration
- Ruang meeting dan booking kalender.
- Permintaan fasilitas kantor.
- Visitor management.
- Kantin atau subsidi makan jika relevan.

### Payroll yang Lebih Detail
- Komponen gaji: basic salary, allowance, deduction, bonus, incentive.
- BPJS dan pajak.
- THR dan bonus periodik.
- Slip gaji digital.
- Export payroll ke PDF/CSV.
- Potongan pinjaman atau salary advance.
- Komponen bank transfer berbeda per employee.
- Payroll correction dan retroactive adjustment.
- Tax evidence dan pay slip archive.
- Implementasi awal slip gaji dan export CSV/PDF sudah tersedia lewat endpoint payroll.

### Benefits dan Compensation
- Tunjangan transport, makan, kesehatan, jabatan.
- Benefit by grade atau level.
- Asuransi kesehatan dan benefit keluarga.
- Reward dan bonus non-gaji.

### Attendance Intelligence
- Geo-fencing.
- Device-based attendance.
- Deteksi keterlambatan dan absensi berulang.
- Attendance anomaly alert.
- Rekap keterlambatan, early leave, dan overtime.
- Implementasi awal attendance intelligence dan overtime summary sudah tersedia lewat endpoint attendance.

### Document Management dan Contract Tracking
- Upload dokumen karyawan: kontrak, surat perjanjian, KTP, NPWP, sertifikat, dan dokumen HR lain.
- Status review dokumen: pending, approved, rejected, archived.
- Tanggal kedaluwarsa dokumen dan daftar dokumen yang segera habis masa berlakunya.
- Self-service upload dokumen untuk employee dan review/approval untuk HR.
- Implementasi awal repository dokumen karyawan, file upload, review status, dan notifikasi sudah tersedia.

### Helpdesk dan Service Request
- Ticket HR untuk pertanyaan umum, surat keterangan, dan permintaan verifikasi.
- Status ticket: open, in_progress, waiting_for_employee, resolved, closed, cancelled.
- Assignment ticket ke HR tertentu.
- Komentar internal dan komentar untuk employee.
- Notifikasi otomatis saat ticket dibuat, diassign, atau status berubah.
- Implementasi awal helpdesk request, comment thread, assignment, dan status workflow sudah tersedia.

### Employee Self-Service yang Lebih Kaya
- Update data pribadi terbatas.
- Upload dokumen sendiri.
- Riwayat attendance.
- Riwayat cuti.
- Riwayat reimbursement.
- Slip gaji dan dokumen payroll.
- Pengajuan surat keterangan kerja.
- Permintaan perubahan data pribadi.
- Pengajuan perpindahan divisi atau lokasi.
- Portal notifikasi dan task pribadi.

### Audit dan Compliance
- Audit log perubahan data.
- Siapa mengubah apa dan kapan.
- Approval trail lengkap.
- Riwayat status perubahan data penting.
- Data privacy control.
- Retention policy dokumen.
- Export data untuk kebutuhan audit.
- Compliance checklist.
- Implementasi awal audit trail sudah tersedia lewat `audit_logs` dan middleware `audit.trail` pada route authenticated.

### Notification Center
- Notifikasi approval.
- Reminder kontrak habis.
- Reminder cuti atau reimbursement pending.
- Reminder attendance atau payroll ready.
- Notifikasi perubahan status employee.
- Reminder training, probation, dan appraisal.
- Notifikasi dokumen wajib belum lengkap.
- Implementasi awal notification center sudah tersedia lewat `user_notifications` dan endpoint `/notifications`.

### Announcement dan Engagement
- Pengumuman perusahaan.
- Birthday dan work anniversary.
- Polling atau survey internal.
- Employee engagement pulse survey.

### Helpdesk dan Service Request
- Ticket HR untuk pertanyaan umum.
- Permintaan surat, dokumen, dan verifikasi.
- SLA penanganan request.
- Kategori permintaan HR.

### Reporting dan Dashboard
- Headcount per divisi.
- Turnover.
- Attendance rate.
- Leave utilization.
- Overtime summary.
- Payroll summary.
- Reimbursement trend.
- Attrition per period.
- Absenteeism rate.
- Cost of manpower.
- Headcount movement.
- Training completion rate.
- Performance distribution.
- Implementasi dashboard detail HR sudah tersedia via endpoint people insights untuk ringkasan lintas attendance, leave, payroll, reimbursement, training, helpdesk, dan dokumen.

## 3. Rekomendasi Prioritas Implementasi

### Prioritas Tinggi
1. Audit log.
2. Notifikasi approval dan reminder.
3. Kontrak dan dokumen karyawan.
4. Slip gaji dan payroll breakdown.
5. Attendance policy dan overtime.
6. Master data HR dan org structure.
7. Leave policy yang lebih lengkap.

### Prioritas Menengah
1. Onboarding/offboarding.
2. Org chart dan struktur jabatan.
3. Training dan certification.
4. Reporting dashboard yang lebih detail.
5. Leave policy yang lebih lengkap.
6. Benefits dan compensation.
7. Attendance intelligence.
8. Employee lifecycle.

### Prioritas Lanjutan
1. Recruitment / ATS.
2. Performance review dan 360 feedback.
3. Asset management.
4. Integration ke biometric device dan sistem pihak ketiga.
5. Survey dan engagement.
6. Helpdesk dan service request.
7. Career development dan succession planning.

## 4. Dampak Bisnis untuk HR

- Memudahkan HR mengelola lifecycle karyawan dari masuk sampai keluar.
- Mengurangi proses manual seperti approval via chat atau spreadsheet.
- Memperkuat kontrol internal lewat audit trail dan role permission.
- Memberi visibilitas ke manajemen lewat dashboard dan analytics.
- Membuat sistem lebih siap untuk skala perusahaan yang lebih besar.

## 5. Catatan Implementasi

- Fitur tambahan sebaiknya dibuat bertahap agar tidak mengganggu modul yang sudah berjalan.
- Untuk kebutuhan HR operasional, mulai dari audit log, notification, payroll detail, dan document management biasanya memberi dampak paling cepat.
- Untuk kebutuhan enterprise, kombinasikan approval flow, org chart, dan reporting agar data mudah dipantau.
- Jika ingin sistem terasa lengkap di mata HR, tambahkan juga master data yang rapi, employee lifecycle, dan attendance intelligence.