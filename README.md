# Office Admin Web Portal (Laravel 11 & RustFS S3)

Web Portal Administrasi Kantor berbasis **Laravel 11** dengan manajemen peran pengguna (*Role-Based Access Control*) dan integrasi penyimpanan dokumen kantor ke **RustFS Object Storage (S3-compatible)**.

> 💡 **Informasi Desain & Performa:**
> Seluruh antarmuka portal menggunakan **Native CSS murni** (`public/css/app.css`). Proyek ini **100% bebas dari ketergantungan Node.js/npm**, sehingga praktikan tidak perlu menjalankan `npm install` atau `npm run build`.

---

## 📋 1. Kebutuhan Sistem & Dependensi (Prerequisites)

Sebelum memulai konfigurasi, pastikan lingkungan server atau Virtual Machine (Ubuntu Server di VirtualBox) telah memenuhi prasyarat berikut:

### A. Perangkat Keras & Virtual Machine (VirtualBox)
* **Penyimpanan Bebas (Disk Space):** Minimal **10 GB – 15 GB** ruang kosong (untuk menampung image Docker RustFS, AWS-CLI, base PHP/Nginx, dan file upload praktikum).
* **RAM:** Minimal **2 GB** (disarankan **4 GB** agar VM berjalan lancar).
* **Konfigurasi Jaringan VM:** 
  * Disarankan menggunakan **Bridged Adapter** agar VM mendapatkan IP lokal satu jaringan dengan komputer host.
  * *Atau* jika menggunakan **NAT**, tambahkan Port Forwarding:
    * Port Web Laravel: `8000` (atau `8080` jika via Nginx Docker)
    * Port S3 API RustFS: `9000`
    * Port Web Console RustFS: `9001`
    * Port Portainer: `9443` / `9000`

### B. Perangkat Lunak & Ekstensi (Software Dependencies)
1. **PHP 8.3 (CLI & FPM)**  
   Ekstensi PHP yang wajib aktif:
   * `pdo_sqlite` (Database default tanpa perlu instalasi MySQL server)
   * `curl` & `openssl` (Koneksi S3 API)
   * `fileinfo` (Deteksi tipe MIME berkas)
   * `mbstring` & `xml` (String & XML parsing)
   * `zip` (Kompresi arsip)
2. **Composer 2.x** (Package manager PHP)
3. **Docker Engine & Docker Compose v2.x** (Untuk menjalankan container RustFS)
4. **Portainer CE** (Opsional namun sangat disarankan untuk monitoring container via web UI)
5. **Git** (Untuk clone repository)

---

## 👥 2. Akun Bawaan (Default Seeded Users)

Sistem telah dilengkapi seeder akun untuk pengujian hak akses:

| Peran (Role) | Alamat Email | Kata Sandi | Wewenang & Hak Akses |
| :--- | :--- | :--- | :--- |
| **👑 Administrator** | `admin@office.local` | `password` | • Mengunggah, mengunduh, dan **menghapus semua dokumen**.<br>• Mengakses menu **Manajemen Pengguna** (aktif/nonaktifkan akun dan reset password). |
| **👤 Staff 1** | `staff1@office.local` | `password` | • Melihat arsip dokumen kantor.<br>• Mengunggah dokumen baru.<br>• Mengunduh berkas (*Dilarang keras menghapus dokumen*). |
| **👤 Staff 2** | `staff2@office.local` | `password` | • Melihat arsip dokumen kantor.<br>• Mengunggah dokumen baru.<br>• Mengunduh berkas (*Dilarang keras menghapus dokumen*). |

> ⚡ **Tips:** Pada halaman login (`/login`), terdapat tombol **Quick-Fill 1-Klik** untuk langsung mengisi akun Admin, Staff 1, atau Staff 2 secara instan.

---

## 🚀 3. Panduan Instalasi & Menjalankan Proyek

Pilih salah satu metode di bawah ini sesuai kebutuhan praktikum:

---

### METODE A: Menjalankan Langsung via PHP Artisan + Docker RustFS (Direkomendasikan untuk Praktik)

Metode ini paling cepat dan ringan: Laravel berjalan langsung di PHP server, sementara RustFS berjalan di Docker.

#### Langkah 1: Clone Repository
```bash
git clone <URL_REPOSITORY_ANDA> office-portal
cd office-portal
```

#### Langkah 2: Konfigurasi Environment (`.env`)
Salin file contoh ke `.env`:
```bash
cp .env.example .env
```
Periksa pengaturan storage di `.env`:
```env
FILESYSTEM_DISK=rustfs

AWS_ACCESS_KEY_ID=rustfs_admin
AWS_SECRET_ACCESS_KEY=rustfs_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=office-documents
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

#### Langkah 3: Pasang Dependensi Composer & Generate App Key
```bash
composer install
php artisan key:generate
```

#### Langkah 4: Migrasi Database & Seeding Akun
```bash
php artisan migrate:fresh --seed
```
*Database SQLite otomatis dibuat dan 3 akun pengguna bawaan siap digunakan.*

#### Langkah 5: Jalankan Service RustFS via Docker
```bash
docker compose up -d rustfs rustfs-init
```
*Container `rustfs` akan berjalan pada port 9000 & 9001, dan container `rustfs-init` otomatis membuat bucket `office-documents`.*

#### Langkah 6: Jalankan Server Laravel
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

#### Langkah 7: Akses Melalui Web Browser
Buka browser Anda dan akses:
* **Web Portal:** `http://<IP_SERVER_ATAU_LOCALHOST>:8000`
* **RustFS Web Console:** `http://<IP_SERVER_ATAU_LOCALHOST>:9001`  
  *(Login: `rustfs_admin` / `rustfs_secret_key`)*
* **Portainer (Jika terinstall):** `http://<IP_SERVER_ATAU_LOCALHOST>:9000` atau `https://<IP_SERVER_ATAU_LOCALHOST>:9443`

---

### METODE B: Full Container Deployment (Laravel App + Nginx + RustFS via Docker)

Jika ingin seluruh stack berjalan di dalam Docker:

1. Salin `.env`:
   ```bash
   cp .env.example .env
   ```
2. Ubah `AWS_ENDPOINT` pada `.env` agar menggunakan nama service container internal:
   ```env
   AWS_ENDPOINT=http://rustfs:9000
   ```
3. Build dan jalankan seluruh service:
   ```bash
   docker compose up -d --build
   ```
4. Jalankan migrasi dan seeder di dalam container:
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```
5. Akses melalui browser:
   * **Web Portal (Nginx):** `http://<IP_SERVER>:8080`
   * **RustFS Console:** `http://<IP_SERVER>:9001`

---

## 🧪 4. Menjalankan Pengujian Otomatis (Automated Testing)

Untuk memastikan seluruh modul (Autentikasi, CRUD Dokumen, Hak Akses Admin vs Staff, dan Storage) berfungsi dengan sempurna:

```bash
php artisan test
```

Semua 14 test suite akan dieksekusi dengan status **PASSED (100%)**.

---

## 📁 5. Struktur Penyimpanan Objek di RustFS

Dokumen yang diunggah dikategorikan ke dalam struktur direktori berikut:
```text
office-documents (Bucket)
  └── uploads/
       ├── surat_masuk/
       │    └── 2026-11-07_a1b2c3d4-e5f6-7890.pdf
       ├── surat_keluar/
       ├── nota_dinas/
       ├── laporan/
       ├── invoice/
       └── lainnya/
```

Fitur perlindungan berkas:
* Staf dapat mengunggah dan mengunduh berkas.
* Tombol hapus hanya muncul dan hanya dapat dieksekusi oleh akun **Administrator**. Jika staf mencoba menghapus berkas via direct HTTP request, sistem otomatis mengembalikan status **403 Forbidden**.
