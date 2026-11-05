<!--
Sync Impact Report
- Version change: (template, belum diisi) → 1.0.0
- Modified principles: semua placeholder diganti (adopsi awal)
  - [PRINCIPLE_1_NAME] → I. PHP Native & MySQL Tanpa Framework
  - [PRINCIPLE_2_NAME] → II. Integritas Slug/URL Unik
  - [PRINCIPLE_3_NAME] → III. Keamanan Autentikasi Admin (NON-NEGOTIABLE)
  - [PRINCIPLE_4_NAME] → IV. Jejak Audit Aktivitas Admin
  - [PRINCIPLE_5_NAME] → V. Akses Publik Hanya untuk Portofolio Published
  - (baru) VI. FigCraft sebagai Sumber Kebenaran Desain
  - (baru) VII. Kesederhanaan untuk Pembelajaran
- Added sections: Pendahuluan & Tujuan Proyek (bagian dari Core Principles preamble),
  Batasan Teknologi & Data, Alur Kerja Pengembangan
- Removed sections: tidak ada
- Templates: tidak dimodifikasi (dibaca saat runtime oleh command Spec Kit)
- Follow-up TODOs: tidak ada placeholder yang ditunda
-->

# Platform Web Portofolio UMKM Constitution

## Core Principles

Platform Web Portofolio UMKM adalah aplikasi berbasis PHP Native yang memungkinkan Admin
mengelola dan menerbitkan halaman portofolio digital untuk Usaha Mikro, Kecil, dan Menengah
(UMKM). Proyek ini dibuat untuk mempraktikkan dasar-dasar CRUD menggunakan PHP dan MySQL,
serta menerapkan validasi URL/slug unik dan pencatatan aktivitas Admin.

Tujuan & manfaat yang menjadi acuan setiap keputusan:

- Memahami cara kerja PHP Native dalam mengembangkan aplikasi web.
- Mempelajari konsep CRUD dan pengelolaan database MySQL.
- Memahami proses pengelolaan dan penerbitan halaman portofolio UMKM melalui dashboard Admin.
- Menerapkan validasi slug unik dan pencatatan aktivitas perubahan data.
- Membantu UMKM memiliki halaman portofolio digital yang dapat diakses publik melalui URL unik.

### I. PHP Native & MySQL Tanpa Framework

- Aplikasi MUST ditulis dengan PHP Native; framework PHP (Laravel, CodeIgniter, Symfony, dll.)
  MUST NOT digunakan.
- Penyimpanan data MUST menggunakan MySQL/MariaDB melalui PDO atau MySQLi dengan prepared
  statement; konkatenasi input pengguna ke dalam query SQL MUST NOT dilakukan.
- Aplikasi MUST dapat dijalankan di server lokal XAMPP tanpa dependensi tambahan yang wajib.
- Library pihak ketiga hanya boleh dipakai bila tidak menggantikan konsep inti yang sedang
  dipelajari (routing, CRUD, session, validasi), dan MUST dijustifikasi di rencana fitur.

Rasional: tujuan utama proyek adalah memahami dasar PHP dan MySQL; framework akan
menyembunyikan konsep yang justru ingin dipraktikkan.

### II. Integritas Slug/URL Unik

- Setiap portofolio UMKM MUST memiliki slug yang unik di seluruh tabel `site_profile`.
- Ketersediaan slug MUST divalidasi di sisi server sebelum operasi simpan maupun perbarui;
  saat edit, slug milik record itu sendiri tidak dihitung sebagai bentrok.
- Keunikan slug MUST juga ditegakkan di level database (constraint `UNIQUE` pada kolom `slug`)
  sebagai lapisan pertahanan terakhir.
- Slug MUST dinormalisasi ke format URL-safe (huruf kecil, angka, tanda hubung).
- Bila slug sudah digunakan, sistem MUST menampilkan pesan "slug sudah digunakan" dan
  mempertahankan input form lainnya.

Rasional: slug adalah alamat publik portofolio; duplikasi akan membuat halaman UMKM tidak
dapat dijangkau atau tertukar.

### III. Keamanan Autentikasi Admin (NON-NEGOTIABLE)

- Password MUST disimpan sebagai hash menggunakan `password_hash()` dan diverifikasi dengan
  `password_verify()`; password plaintext MUST NOT disimpan atau dicatat di log.
- Semua halaman dashboard Admin MUST memeriksa session login; akses tanpa session MUST
  diarahkan ke halaman login.
- Session ID MUST diregenerasi setelah login berhasil; logout MUST menghapus session
  sepenuhnya lalu redirect ke halaman login.
- Ganti password MUST memverifikasi password lama sebelum menyimpan hash password baru.
- Semua form yang mengubah data MUST menggunakan metode POST dan dilindungi token CSRF.
- Semua output data dinamis ke HTML MUST di-escape (`htmlspecialchars`) untuk mencegah XSS.
- Upload logo MUST divalidasi tipe (MIME gambar yang diizinkan) dan ukurannya, serta disimpan
  dengan nama file yang dibuat server.

Rasional: aplikasi menerbitkan konten ke publik; kompromi akun Admin berarti seluruh
portofolio UMKM dapat dirusak.

### IV. Jejak Audit Aktivitas Admin

- Setiap operasi yang mengubah data (tambah, ubah, hapus, publish portofolio; ganti password)
  MUST mencatat entri di tabel `activity_logs` berisi `admin_id`, `action`, `description`,
  dan `created_at`.
- Log aktivitas MUST dicatat hanya bila perubahan data berhasil; perubahan data dan pencatatan
  log MUST berada dalam satu transaksi database sehingga keduanya berhasil atau dibatalkan
  bersama.
- Nilai `action` MUST berasal dari daftar konstanta yang terdefinisi (mis. `create_portfolio`,
  `update_portfolio`, `delete_portfolio`, `publish_portfolio`, `change_password`).
- Log aktivitas bersifat append-only dari sisi aplikasi; fitur edit/hapus log MUST NOT dibuat.

Rasional: pencatatan aktivitas adalah salah satu tujuan pembelajaran utama dan memberi
akuntabilitas atas setiap perubahan konten publik.

### V. Akses Publik Hanya untuk Portofolio Published

- Halaman publik MUST mengambil portofolio berdasarkan slug dari URL dan hanya menampilkan
  data dengan status `published`.
- Slug yang tidak ditemukan atau berstatus selain `published` MUST menghasilkan halaman 404
  dengan HTTP status code 404.
- Publish MUST didahului pemeriksaan kelengkapan data (minimal nama usaha, slug valid, bio,
  dan kontak WhatsApp); data yang belum lengkap MUST ditolak dengan pesan yang menjelaskan
  field yang kurang.
- Setelah publish berhasil, sistem MUST menampilkan URL publik portofolio kepada Admin.

Rasional: pengunjung tidak boleh melihat draf atau data setengah jadi, dan URL tidak valid
harus ditangani secara konsisten.

### VI. FigCraft sebagai Sumber Kebenaran Desain

- Desain UI MUST mengacu pada file `/Users/albifajar/FigCraft/portofolio-umkm.figcraft`.
- Sebelum merencanakan atau mengimplementasikan halaman/komponen UI, file desain tersebut MUST
  diperiksa ulang untuk perubahan terbaru (layout, warna, tipografi, komponen).
- Penyimpangan dari desain MUST dicatat beserta alasannya di dokumen rencana fitur.
- Bila desain dan spesifikasi bertentangan, konflik MUST dikonfirmasi ke pemilik proyek
  sebelum implementasi dilanjutkan.

Rasional: satu sumber desain menjaga konsistensi tampilan dashboard Admin dan halaman publik.

### VII. Kesederhanaan untuk Pembelajaran

- Struktur kode MUST mudah ditelusuri: pemisahan sederhana antara konfigurasi/koneksi database,
  logika (proses form/query), dan tampilan (template HTML).
- Fitur di luar daftar fitur yang disepakati MUST NOT ditambahkan tanpa amandemen spesifikasi
  (YAGNI).
- Fungsi yang dipakai berulang (koneksi DB, cek session, pencatatan log, validasi slug) MUST
  dibuat sekali di file helper dan dipakai ulang.
- Pesan kesalahan dan label antarmuka MUST menggunakan Bahasa Indonesia.

Rasional: proyek ini adalah sarana belajar; kode yang sederhana dan konsisten lebih bernilai
daripada abstraksi yang canggih.

## Batasan Teknologi & Data

- Stack: PHP 8.x Native, MySQL/MariaDB (bawaan XAMPP), HTML, CSS, dan JavaScript vanilla
  secukupnya.
- Lingkungan pengembangan dan demo: XAMPP (Apache + MySQL) di localhost.
- Skema database inti MUST mengikuti ERD yang disepakati, dengan tabel `admins`,
  `activity_logs`, dan `site_profile`; perubahan skema MUST disertai file SQL migrasi/skema
  yang diperbarui di repositori.
- Relasi: satu admin dapat memiliki banyak `activity_logs` (`activity_logs.admin_id` →
  `admins.id`, NOT NULL).
- `admins.username` dan `site_profile.slug` MUST unik di level database.
- `site_profile.status` MUST dibatasi pada nilai yang terdefinisi (minimal `draft` dan
  `published`).
- Kredensial database MUST disimpan di satu file konfigurasi dan file konfigurasi lokal yang
  berisi kredensial asli MUST NOT di-commit.
- File logo hasil upload MUST disimpan di direktori upload khusus di luar direktori kode, dan
  `logo_path` menyimpan path relatif.

## Alur Kerja Pengembangan

- Setiap fitur MUST melalui alur Spec Kit: specify → (clarify) → plan → tasks → implement.
- Spesifikasi dan rencana MUST konsisten dengan ERD dan flowchart yang disepakati; deviasi
  MUST dicatat di plan beserta alasannya.
- Constitution Check di setiap `plan.md` MUST memverifikasi prinsip I–VII sebelum desain dan
  diulang setelah desain.
- Setiap fitur MUST diuji secara manual di XAMPP mengikuti flowchart terkait, termasuk jalur
  gagal (login gagal, slug bentrok, data belum lengkap, slug tidak valid → 404).
- Skrip SQL skema dan data awal (seed akun Admin dengan password ter-hash) MUST tersedia agar
  aplikasi dapat dijalankan ulang dari nol.

## Governance

- Constitution ini mengatur seluruh praktik pengembangan proyek dan berada di atas preferensi
  individual; bila terjadi konflik, constitution yang berlaku.
- Amandemen MUST didokumentasikan di file ini dengan Sync Impact Report, disetujui pemilik
  proyek, dan disertai rencana penyesuaian untuk artefak yang terdampak (spec, plan, tasks).
- Versioning mengikuti semantic versioning:
  - MAJOR: penghapusan atau pendefinisian ulang prinsip yang tidak kompatibel.
  - MINOR: penambahan prinsip/bagian baru atau perluasan panduan yang material.
  - PATCH: klarifikasi, perbaikan kata, dan perbaikan non-semantik.
- Setiap review spec, plan, dan implementasi MUST memeriksa kepatuhan terhadap constitution;
  pelanggaran yang disengaja MUST dijustifikasi di bagian Complexity Tracking pada plan.

**Version**: 1.0.0 | **Ratified**: 2026-11-05 | **Last Amended**: 2026-11-05
