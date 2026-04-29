# Payroll Flow Documentation

## 1. Payroll Flow (Alur)

1. **Pengumpulan Data Absensi & Komponen Gaji**
   - Sistem mengumpulkan data absensi, cuti, lembur, dan komponen gaji (tunjangan, potongan, dsb) dari HRIS.
2. **Perhitungan Payroll**
   - Sistem melakukan perhitungan gaji kotor, potongan, pajak, dan menghasilkan gaji bersih.
3. **Generate Payroll**
   - Admin HR melakukan generate payroll untuk periode tertentu.
4. **Approval Payroll**
   - Payroll yang sudah digenerate masuk ke proses approval (misal: Manager/HRD).
5. **Finalisasi & Distribusi Slip Gaji**
   - Setelah disetujui, slip gaji didistribusikan ke karyawan.
6. **Pembayaran**
   - Finance melakukan pembayaran ke rekening karyawan.

## 2. API Payroll

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
        "periode": "2026-04"
      }
    ]
  }
  ```

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
    ### e. Get Slip Gaji (Salary Slip)
    - **Endpoint:** GET /api/payroll/slip/{id}
    - **Deskripsi:** Mendapatkan slip gaji (salary slip) detail untuk karyawan dan periode tertentu.
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
  }
  ```
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

## 3. Catatan
- Flow dan API di atas dapat disesuaikan dengan kebutuhan bisnis dan approval yang berlaku di organisasi Anda.
- Payload dapat dikembangkan sesuai kebutuhan field tambahan.
