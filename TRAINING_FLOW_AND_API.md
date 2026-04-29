# Training Flow & API Documentation

## 1. Training Flow (Alur)

1. **Pembuatan Program Training**
   - Admin/HR membuat program pelatihan/training baru.
2. **Pendaftaran Peserta**
   - Karyawan dapat mendaftar (enroll) ke program training yang tersedia.
3. **Pelaksanaan Training**
   - Training dilaksanakan sesuai jadwal dan materi yang telah ditentukan.
4. **Penyelesaian Training**
   - Setelah training selesai, status peserta diupdate menjadi selesai/complete.
5. **Evaluasi & Sertifikat (opsional)**
   - Peserta dapat dievaluasi dan diberikan sertifikat jika diperlukan.

## 1b. Competency Flow (Alur Kompetensi)

1. **Pembuatan Kompetensi**
  - HR/Admin mendefinisikan kompetensi yang dibutuhkan untuk jabatan/posisi tertentu.
2. **Penugasan Kompetensi ke Karyawan**
  - Kompetensi ditugaskan ke karyawan sesuai jabatan/role.
3. **Penilaian Kompetensi**
  - Kompetensi karyawan dinilai melalui assessment, review, atau hasil training.
4. **Pengembangan Kompetensi**
  - Karyawan mengikuti pelatihan/training untuk meningkatkan kompetensi.
5. **Evaluasi & Monitoring**
  - HR/Admin memonitor perkembangan kompetensi karyawan secara berkala.

## 2. Daftar Endpoint API Training

### a. List Program Training
- **Endpoint:** GET /api/training/programs
- **Deskripsi:** Mendapatkan daftar seluruh program training.
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": [
      {
        "id": 1,
        "nama": "Pelatihan Leadership",
        "deskripsi": "Pengembangan kepemimpinan untuk supervisor",
        "jadwal": "2026-05-10",
        "status": "open"
      }
    ]
  }
  ```

### b. Buat Program Training
- **Endpoint:** POST /api/training/programs
- **Deskripsi:** Membuat program training baru.
- **Request Payload:**
  ```json
  {
    "nama": "Pelatihan Leadership",
    "deskripsi": "Pengembangan kepemimpinan untuk supervisor",
    "jadwal": "2026-05-10"
  }
  ```
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Program training berhasil dibuat",
    "data": {
      "id": 1
    }
  }
  ```

### c. Detail Program Training
- **Endpoint:** GET /api/training/programs/{id}
- **Deskripsi:** Mendapatkan detail program training tertentu.
- **Response Payload:**
  ```json
  {
    "status": true,
    "data": {
      "id": 1,
      "nama": "Pelatihan Leadership",
      "deskripsi": "Pengembangan kepemimpinan untuk supervisor",
      "jadwal": "2026-05-10",
      "status": "open"
    }
  }
  ```

### d. Update Program Training
- **Endpoint:** PUT /api/training/programs/{id}
- **Deskripsi:** Mengubah data program training.
- **Request Payload:**
  ```json
  {
    "nama": "Pelatihan Leadership Advanced",
    "deskripsi": "Level lanjutan",
    "jadwal": "2026-06-01"
  }
  ```
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Program training berhasil diupdate"
  }
  ```

### e. Hapus Program Training
- **Endpoint:** DELETE /api/training/programs/{id}
- **Deskripsi:** Menghapus program training tertentu.
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Program training berhasil dihapus"
  }
  ```

### f. Enroll Training
- **Endpoint:** POST /api/training/programs/{id}/enroll
- **Deskripsi:** Mendaftarkan karyawan ke program training.
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Berhasil mendaftar ke training"
  }
  ```

### g. Selesaikan Training
- **Endpoint:** PUT /api/training/enrollments/{id}/complete
- **Deskripsi:** Menandai peserta telah menyelesaikan training.
- **Response Payload:**
  ```json
  {
    "status": true,
    "message": "Training selesai"
  }
  ```

## 3. Catatan
- Endpoint di atas dapat dikembangkan sesuai kebutuhan field tambahan (misal: sertifikat, evaluasi, dsb).
- Flow dapat disesuaikan dengan proses bisnis organisasi Anda.
