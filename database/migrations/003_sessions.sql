-- Migrasi 003: tabel sessions untuk penyimpanan sesi di database (SESSION_DRIVER=db, dipakai di Wasmer Edge).
-- Untuk database yang sudah ada (tanpa menghapus data):
--   mysql -u root portofolio_umkm < database/migrations/003_sessions.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS sessions (
    id            VARCHAR(128) NOT NULL,
    data          MEDIUMBLOB   NOT NULL,
    last_activity INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
