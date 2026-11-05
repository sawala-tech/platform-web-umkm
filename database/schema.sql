-- Skema database Platform Web Portofolio UMKM
-- MySQL 8 / MariaDB 10.4+ (XAMPP), InnoDB, utf8mb4
-- Pemakaian:
--   mysql -u root -e "CREATE DATABASE portofolio_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
--   mysql -u root portofolio_umkm < database/schema.sql
--   php database/seed.php

SET NAMES utf8mb4;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS portfolio_links;
DROP TABLE IF EXISTS portfolio_images;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS site_profile;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_profile (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_name VARCHAR(150) NOT NULL,
    slug          VARCHAR(150) NOT NULL,
    bio           TEXT NULL,
    logo_path     VARCHAR(255) NULL,
    whatsapp      VARCHAR(20)  NULL,
    instagram     VARCHAR(30)  NULL,
    tiktok        VARCHAR(30)  NULL,
    shopee_url    VARCHAR(255) NULL,
    tokopedia_url VARCHAR(255) NULL,
    status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_site_profile_slug (slug),
    KEY idx_site_profile_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galeri foto produk (opsional, maks GALLERY_MAX_IMAGES per portofolio)
CREATE TABLE portfolio_images (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    portfolio_id INT UNSIGNED NOT NULL,
    image_path   VARCHAR(255) NOT NULL,
    sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_portfolio_images_portfolio (portfolio_id, sort_order),
    CONSTRAINT fk_portfolio_images_portfolio FOREIGN KEY (portfolio_id)
        REFERENCES site_profile (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link kontak tambahan (label + URL), mis. Website, GoFood, Linktree
CREATE TABLE portfolio_links (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    portfolio_id INT UNSIGNED NOT NULL,
    label        VARCHAR(50)  NOT NULL,
    url          VARCHAR(255) NOT NULL,
    sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_portfolio_links_portfolio (portfolio_id, sort_order),
    CONSTRAINT fk_portfolio_links_portfolio FOREIGN KEY (portfolio_id)
        REFERENCES site_profile (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id    INT UNSIGNED NOT NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_logs_created (created_at),
    KEY idx_activity_logs_admin (admin_id),
    CONSTRAINT fk_activity_logs_admin FOREIGN KEY (admin_id)
        REFERENCES admins (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel pendukung (di luar ERD) untuk pembatasan login gagal (FR-007, research R6)
CREATE TABLE login_attempts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username     VARCHAR(50) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_attempts_user_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
