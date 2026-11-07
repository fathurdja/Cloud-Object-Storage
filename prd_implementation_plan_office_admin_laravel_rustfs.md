# Product Requirements Document (PRD) & Implementation Plan
**Project Name:** Office Admin Web Portal (Laravel + RustFS)  
**Target Environment:** Docker (Local Development / Antigravity)  
**Version:** 1.0.0 (MVP)

---

## 1. Overview & Objective
Membangun web portal administrasi kantor sederhana berbasis **Laravel** dengan sistem manajemen peran pengguna (*role-based*) dan integrasi penyimpanan dokumen kantor ke **RustFS** (S3-compatible object storage via Docker).

### Tujuan Utama MVP:
- Manajemen dokumen administrasi kantor sederhana (surat masuk/keluar, berkas operasional).
- Hak akses terbatas: **1 Admin** dan **2 User Biasa**.
- Integrasi storage file kantor ke RustFS melalui driver S3 Laravel (`league/flysystem-aws-s3-v3`).

---

## 2. User Roles & Permissions

| Role | Kuota Akun | Kewenangan & Hak Akses |
| :--- | :--- | :--- |
| **Admin** | 1 User (`admin@office.local`) | - Akses semua dokumen kantor.<br>- Unggah, unduh, dan hapus dokumen.<br>- Kelola user dasar (aktivasi / reset password). |
| **User Biasa** | 2 User (`staff1@office.local`, `staff2@office.local`) | - Melihat daftar dokumen sesuai departemen/divisi.<br>- Mengunggah dokumen baru.<br>- Mengunduh berkas (tanpa hak menghapus dokumen publik). |

---

## 3. Tech Stack
- **Backend/Framework:** Laravel 11.x (PHP 8.2+)
- **Database:** SQLite / MySQL 8.0 (sesuai container Docker)
- **Object Storage:** RustFS (S3-compatible API service running on Docker)
- **Frontend:** Laravel Blade + Tailwind CSS / Bootstrap 5 (ringan & sederhana)
- **Authentication:** Laravel Breeze atau native session auth

---

## 4. Key Functional Requirements

### 4.1 Authentication & User Seeding
- Halaman Login & Logout berbasis session.
- Database seeder bawaan untuk langsung mengenerate:
  - 1 akun Admin
  - 2 akun User Biasa

### 4.2 Document Management (Office Files)
- Form upload dokumen: Judul dokumen, Kategori (e.g., Surat Masuk, Surat Keluar, Invoice, Laporan), Berkas (PDF, DOCX, XLSX, JPG/PNG, maks 10MB).
- List/Tabel dokumen dengan pencarian sederhana.
- Download link yang mengambil file langsung atau via pre-signed URL dari RustFS.
- Soft delete / hard delete (khusus Admin).

### 4.3 RustFS Storage Integration
- Menggunakan driver `s3` pada `config/filesystems.php`.
- File dipetakan ke bucket default: `office-documents`.
- Path struktur penyimpanan: `uploads/{kategori}/{Y-m-d}_{uuid}.{ext}`.

---

## 5. Database Schema (Draft Ringkas)

### Table: `users`
- `id` (PK)
- `name` (varchar)
- `email` (varchar, unique)
- `password` (varchar)
- `role` (enum: `admin`, `staff`)
- `timestamps`

### Table: `documents`
- `id` (PK)
- `user_id` (FK -> `users.id`)
- `title` (varchar)
- `category` (varchar: `surat_masuk`, `surat_keluar`, `nota_dinas`, `lainnya`)
- `file_path` (varchar - path di RustFS)
- `file_name` (varchar - nama asli file)
- `file_size` (unsignedBigInteger)
- `mime_type` (varchar)
- `timestamps`

---

## 6. Implementation Plan (Milestones)

### Phase 1: Environment & Docker Setup
- [ ] Konfigurasi `docker-compose.yml`:
  - Service `app` (Laravel / PHP-FPM + Nginx)
  - Service `rustfs` (Object storage container dengan expose port S3 API, e.g., `9000`)
- [ ] Buat bucket default `office-documents` di RustFS saat inisialisasi.

### Phase 2: Laravel Core & Auth Setup
- [ ] Setup Laravel project via Composer.
- [ ] Pasang driver S3:
  ```bash
  composer require league/flysystem-aws-s3-v3 "^3.0"
  ```
- [ ] Buat migrasi tabel `users` (tambahkan kolom `role`) & tabel `documents`.
- [ ] Buat `UserSeeder` berisi 1 akun admin dan 2 akun staf.

### Phase 3: RustFS Integration & Filesystem Config
- [ ] Sesuaikan `.env`:
  ```env
  FILESYSTEM_DISK=rustfs

  AWS_ACCESS_KEY_ID=rustfs_admin
  AWS_SECRET_ACCESS_KEY=rustfs_secret_key
  AWS_DEFAULT_REGION=us-east-1
  AWS_BUCKET=office-documents
  AWS_ENDPOINT=http://rustfs:9000
  AWS_USE_PATH_STYLE_ENDPOINT=true
  ```
- [ ] Daftarkan disk `rustfs` di `config/filesystems.php`.

### Phase 4: CRUD Document & Role Authorization
- [ ] Buat `DocumentController` (`index`, `create`, `store`, `download`, `destroy`).
- [ ] Implementasikan upload file stream ke disk `rustfs`.
- [ ] Buat middleware / Laravel Policy untuk pembatasan aksi delete hanya bagi Admin.

### Phase 5: Testing & Verification
- [ ] Uji login untuk ketiga akun yang telah di-seed.
- [ ] Uji coba upload file dari user biasa dan verifikasi file tersimpan di container RustFS.
- [ ] Uji coba download file dan tes proteksi hapus file untuk user non-admin.