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
  `http://localhost/platform-web-umkm` (`.htaccess` di root meneruskan semua request ke `public/`).

### Akun awal

| Username | Password |
|---|---|
| `admin` | `admin12345` |

**Segera ganti password** lewat menu *Ubah Password* setelah login pertama.

## Pengujian

```bash
php tests/run.php     # uji fungsi murni: normalisasi slug, WhatsApp, validasi password
```

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
```

### Deploy ke Wasmer Edge

Paket dan konfigurasi Wasmer: `wasmer.toml` (runtime `php/php` 8.3.4 + pemetaan `[fs]`),
`app.yaml` (MySQL terkelola + volume `uploads`), `deploy/wasmer/config/config.php` (konfigurasi
dari environment variable), dan `deploy/wasmer/php/php.ini`.

```bash
# Uji lokal dengan runtime Wasmer (butuh Wasmer CLI)
wasmer run .

# Deploy
# 1. Ganti `owner` dan `BASE_URL` di app.yaml
wasmer deploy
# 2. Ambil kredensial database terkelola, lalu impor skema & jalankan seed sekali
wasmer app database list --with-password
#    impor database/schema.sql, database/migrations/003_sessions.sql, lalu php database/seed.php
```

Catatan:
- `SESSION_DRIVER=db` (di `app.yaml`) menyimpan sesi di tabel `sessions`, agar login tetap
  berlaku di banyak instance. Di XAMPP/lokal tanpa variabel ini, sesi tetap berbasis file.
- Gambar upload disimpan di volume `uploads`; filesystem di luar volume tidak dipertahankan antar deploy.
- `database/schema.sql` berisi `DROP TABLE`; jalankan hanya untuk setup awal, jangan saat deploy ulang.

Desain UI mengacu pada file FigCraft `portofolio-umkm.figcraft`.
