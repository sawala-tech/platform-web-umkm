-- Migrasi 002: galeri foto produk + kontak media sosial & link lain.
-- Untuk database yang dibuat dari schema.sql versi sebelumnya (tanpa menghapus data):
--   mysql -u root portofolio_umkm < database/migrations/002_gallery_contacts.sql

SET NAMES utf8mb4;

ALTER TABLE site_profile
    ADD COLUMN instagram     VARCHAR(30)  NULL AFTER whatsapp,
    ADD COLUMN tiktok        VARCHAR(30)  NULL AFTER instagram,
    ADD COLUMN shopee_url    VARCHAR(255) NULL AFTER tiktok,
    ADD COLUMN tokopedia_url VARCHAR(255) NULL AFTER shopee_url;

CREATE TABLE IF NOT EXISTS portfolio_images (
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

CREATE TABLE IF NOT EXISTS portfolio_links (
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
