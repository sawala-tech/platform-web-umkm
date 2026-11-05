<?php
/**
 * Konfigurasi untuk Wasmer Edge.
 *
 * Semua nilai dibaca dari environment variable (di-set di app.yaml, atau secret di Wasmer).
 * File ini di-commit dan dipetakan ke /app/config/config.php di dalam paket Wasmer
 * (lihat wasmer.toml). Kredensial lokal config/config.php TIDAK ikut terkemas.
 */
declare(strict_types=1);

function env_or(string $key, string $default): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

// Koneksi database (MySQL terkelola Wasmer: DB_HOST, DB_PORT, DB_NAME, DB_USERNAME, DB_PASSWORD)
define('DB_HOST', env_or('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) env_or('DB_PORT', '3306'));
define('DB_NAME', env_or('DB_NAME', 'portofolio_umkm'));
define('DB_USER', env_or('DB_USERNAME', ''));
define('DB_PASS', env_or('DB_PASSWORD', ''));

// Alamat dasar aplikasi tanpa garis miring di akhir.
define('BASE_URL', rtrim(env_or('BASE_URL', 'http://localhost:8080'), '/'));

// 'development' menampilkan error, 'production' hanya mencatat ke log
define('APP_ENV', env_or('APP_ENV', 'production'));

// Keamanan & batasan (sama dengan config/config.example.php)
define('SESSION_TIMEOUT', 1800);     // detik tanpa aktivitas sebelum sesi berakhir (30 menit)
define('LOGIN_MAX_ATTEMPTS', 5);     // percobaan login gagal sebelum ditahan
define('LOGIN_LOCK_SECONDS', 300);   // lama penahanan login (5 menit)
define('LOGO_MAX_BYTES', 2097152);   // ukuran logo maksimal (2 MB)
define('PER_PAGE', 20);              // baris per halaman pada daftar
define('GALLERY_MAX_IMAGES', 12);    // jumlah foto galeri produk maksimal per portofolio
define('LINKS_MAX', 5);              // jumlah "link lain" maksimal per portofolio
