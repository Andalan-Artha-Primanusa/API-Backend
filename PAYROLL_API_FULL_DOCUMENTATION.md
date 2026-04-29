# Payroll & Slip Gaji API Documentation

## 1. Payroll Flow (Alur)

1. **Pengumpulan Data**
   - Data absensi, cuti, lembur, tunjangan, potongan, dan komponen gaji lain dikumpulkan dari HRIS.
2. **Perhitungan Payroll**
   - Sistem menghitung gaji kotor, potongan, pajak, dan menghasilkan gaji bersih.
3. **Generate Payroll**
   - Admin HR melakukan generate payroll untuk periode tertentu.
4. **Approval Payroll**
   - Payroll hasil generate masuk proses approval (Manager/HRD).
5. **Finalisasi & Distribusi Slip Gaji**
   - Setelah disetujui, slip gaji didistribusikan ke karyawan.
6. **Pembayaran**
   - Finance melakukan pembayaran ke rekening karyawan.

## 2. Daftar Endpoint API Payroll & Slip Gaji

### a. Generate Payroll
- **Endpoint:** POST /api/payroll/generate
- **Deskripsi:** Menghitung dan membuat data payroll untuk periode tertentu.
- **Request Payload:**
  ```json
  {
    "periode": "2026-04",
    "karyawan_ids": [1,2,3]
  }
  ```
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Payroll generated successfully",
    "data": [
      {
        "karyawan_id": 1,
        "gaji_bersih": 5000000,
        "periode": "2026-04"
      }
    ]
  }
  ```

### b. Get Payroll List
- **Endpoint:** GET /api/payroll?periode=2026-04
- **Deskripsi:** Mendapatkan daftar payroll untuk periode tertentu.
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": [
      {
        "karyawan_id": 1,
        "nama": "Budi",
        "gaji_bersih": 5000000,
        "status_approval": "approved"
      }
    ]
  }
  ```

### c. Approve Payroll
- **Endpoint:** POST /api/payroll/approve
- **Deskripsi:** Approve payroll yang sudah digenerate.
- **Request Payload:**
  ```json
  {
    "payroll_ids": [1,2,3]
  }
  ```
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Payroll approved"
  }
  ```

### d. Get Payroll Detail
- **Endpoint:** GET /api/payroll/{id}
- **Deskripsi:** Mendapatkan detail payroll per karyawan.
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": {
      "karyawan_id": 1,
      "nama": "Budi",
      "gaji_pokok": 4000000,
      "tunjangan": 1000000,
      "potongan": 0,
      "pajak": 0,
      "gaji_bersih": 5000000,
      "periode": "2026-04"
    }
  }
  ```

### e. Get Slip Gaji (Salary Slip)
- **Endpoint:** GET /api/my/payroll/{id}/slip
- **Deskripsi:** Mendapatkan slip gaji detail untuk karyawan dan periode tertentu (ESS).
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": {
      "karyawan_id": 1,
      "nama": "Budi",
      "jabatan": "Staff IT",
      "departemen": "IT",
      "periode": "2026-04",
      "gaji_pokok": 4000000,
      "tunjangan": 1000000,
      "lembur": 200000,
      "potongan": 0,
      "pajak": 0,
      "gaji_bersih": 5200000,
      "rekening": "1234567890",
      "bank": "BCA"
    }
  }
  ```

### f. Export Slip Gaji (CSV/PDF)
- **Endpoint:**
  - GET /api/my/payroll/{id}/export (CSV)
  - GET /api/my/payroll/{id}/export-pdf (PDF)
- **Deskripsi:** Mendapatkan slip gaji dalam format CSV/PDF.
- **Response:** File download (CSV/PDF)

### g. List Payroll (ESS)
- **Endpoint:** GET /api/my/payroll
- **Deskripsi:** Mendapatkan daftar payroll milik karyawan yang sedang login.
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": [
      {
        "id": 1,
        "periode": "2026-04",
        "gaji_bersih": 5200000,
        "status_approval": "approved"
      }
    ]
  }
  ```

## 3. Catatan
- Endpoint /api/payroll/* untuk admin/hr, endpoint /api/my/payroll/* untuk karyawan (ESS).
- Payload dapat dikembangkan sesuai kebutuhan field tambahan.
- Export slip gaji tersedia dalam format CSV dan PDF.
