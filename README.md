# Platform Web Portofolio UMKM

Aplikasi PHP Native + MySQL yang memungkinkan Admin mengelola dan menerbitkan halaman portofolio
digital untuk Usaha Mikro, Kecil, dan Menengah (UMKM). Proyek ini dibuat untuk mempraktikkan
dasar-dasar CRUD dengan PHP dan MySQL, validasi URL/slug unik, serta pencatatan aktivitas Admin.

Fitur: login/logout Admin (dengan pembatasan percobaan gagal & timeout sesi), ubah password,
CRUD portofolio (nama usaha, slug, bio, WhatsApp, logo), validasi slug unik, publish/unpublish,
log aktivitas, halaman publik `/<slug>`, dan halaman 404.

## Prasyarat

- PHP ≥ 8.2 dengan ekstensi `pdo_mysql` dan `fileinfo` (aktif default di XAMPP)
- MySQL 8 / MariaDB 10.4+
- Apache dengan `mod_rewrite` dan `AllowOverride All` (XAMPP), **atau** server bawaan `php -S`

Tidak ada framework maupun Composer.

## Setup

```bash
# 1. Konfigurasi (file ini tidak di-commit)
cp config/config.example.php config/config.php
#    lalu sesuaikan DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, BASE_URL

# 2. Database
mysql -u root -e "CREATE DATABASE portofolio_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root portofolio_umkm < database/schema.sql     # atau phpMyAdmin → Import
php database/seed.php                                   # admin default + data contoh
```

### Menjalankan

- **Server bawaan PHP** (pengembangan cepat):
  ```bash
  php -S localhost:8080 -t public public/index.php
  ```
  Pastikan `BASE_URL` di `config/config.php` sama dengan alamat yang dipakai.
- **XAMPP**: buat VirtualHost dengan `DocumentRoot` ke folder `public/` (mis. `http://umkm.test`),
  atau taruh proyek di `htdocs/` dan set `BASE_URL` ke
  `http://localhost/platform-web-umkm/public`.

### Akun awal

| Username | Password |
|---|---|
| `admin` | `admin12345` |

**Segera ganti password** lewat menu *Ubah Password* setelah login pertama.

## Pengujian

```bash
php tests/run.php     # uji fungsi murni: normalisasi slug, WhatsApp, validasi password
```

Skenario uji manual lengkap (US1–US6 + pemeriksaan keamanan) ada di
[`specs/001-portfolio-umkm-platform/quickstart.md`](specs/001-portfolio-umkm-platform/quickstart.md).

## Struktur

```text
config/        konfigurasi (config.example.php di-commit, config.php lokal)
database/      schema.sql, seed.php
public/        document root: index.php (front controller), assets/, uploads/
src/lib/       helper bersama: db, auth, csrf, view, validation, activity_log, upload
src/repositories/  query per tabel (PDO prepared statement)
src/handlers/  logika per rute
src/views/     template HTML (layouts, partials, admin, public)
tests/         run.php
specs/         spesifikasi, rencana, dan tugas (Spec Kit)
```

Desain UI mengacu pada file FigCraft `portofolio-umkm.figcraft`; deviasi desain dicatat di
[`specs/001-portfolio-umkm-platform/plan.md`](specs/001-portfolio-umkm-platform/plan.md).
