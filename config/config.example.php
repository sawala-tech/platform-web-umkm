<?php
/**
 * Contoh konfigurasi aplikasi.
 *
 * Cara pakai:
 *   cp config/config.example.php config/config.php
 * lalu sesuaikan nilai di config/config.php (file itu TIDAK di-commit).
 */

// Koneksi database (MySQL/MariaDB bawaan XAMPP)
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'portofolio_umkm';
const DB_USER = 'root';
const DB_PASS = 'root';

// Alamat dasar aplikasi tanpa garis miring di akhir.
// Contoh XAMPP subfolder: 'http://localhost/platform-web-umkm/public'
const BASE_URL = 'http://localhost:8080';

// 'development' menampilkan error, 'production' hanya mencatat ke log
const APP_ENV = 'development';

// Keamanan & batasan
const SESSION_TIMEOUT    = 1800;     // detik tanpa aktivitas sebelum sesi berakhir (30 menit)
const LOGIN_MAX_ATTEMPTS = 5;        // percobaan login gagal sebelum ditahan
const LOGIN_LOCK_SECONDS = 300;      // lama penahanan login (5 menit)
const LOGO_MAX_BYTES     = 2097152;  // ukuran logo maksimal (2 MB)
const PER_PAGE           = 20;       // baris per halaman pada daftar
