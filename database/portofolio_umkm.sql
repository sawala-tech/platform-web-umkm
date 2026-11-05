-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 05, 2026 at 11:43 AM
-- Server version: 8.0.40
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portofolio_umkm`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `description`, `created_at`) VALUES
(1, 1, 'update_portfolio', 'Mengubah portofolio \"Warung Bu Sri\" (/warung-bu-sri-budo): slug, bio', '2026-11-05 06:17:54'),
(2, 1, 'create_portfolio', 'Menambah portofolio \"Uji Galeri Kontak\" (/uji-galeri-kontak)', '2026-11-05 08:02:37'),
(3, 1, 'update_portfolio', 'Mengubah portofolio \"Uji Galeri Kontak\" (/uji-galeri-kontak): link lain, galeri', '2026-11-05 08:02:48'),
(4, 1, 'update_portfolio', 'Mengubah portofolio \"Uji Galeri Kontak\" (/uji-galeri-kontak): Instagram, TikTok, Shopee, link lain', '2026-11-05 08:02:56'),
(5, 1, 'publish_portfolio', 'Menerbitkan halaman \"Uji Galeri Kontak\" (/uji-galeri-kontak)', '2026-11-05 08:02:56'),
(6, 1, 'update_portfolio', 'Mengubah portofolio \"Uji Galeri Kontak\" (/uji-galeri-kontak): Instagram, TikTok, Shopee, Tokopedia, link lain', '2026-11-05 08:03:12'),
(7, 1, 'delete_portfolio', 'Menghapus portofolio \"Uji Galeri Kontak\" (/uji-galeri-kontak)', '2026-11-05 08:04:03'),
(8, 1, 'update_portfolio', 'Mengubah portofolio \"Warung Bu Sri\" (/warung-bu-sri-budo): galeri', '2026-11-05 08:44:27'),
(9, 1, 'update_portfolio', 'Mengubah portofolio \"Warung Bu Sri\" (/warung-bu-sri-budo): Instagram', '2026-11-05 08:45:03'),
(10, 1, 'update_portfolio', 'Mengubah portofolio \"Warung Bu Sri\" (/warung-bu-sri-budo): logo', '2026-11-05 09:17:01'),
(11, 1, 'update_portfolio', 'Mengubah portofolio \"Batik Sari\" (/batik-sari): bio, WhatsApp, Instagram, TikTok', '2026-11-05 09:19:28'),
(12, 1, 'publish_portfolio', 'Menerbitkan halaman \"Batik Sari\" (/batik-sari)', '2026-11-05 09:19:31'),
(13, 1, 'update_portfolio', 'Mengubah portofolio \"Batik Sari\" (/batik-sari): logo', '2026-11-05 09:21:17'),
(14, 1, 'update_portfolio', 'Mengubah portofolio \"Batik Sari\" (/batik-sari): galeri', '2026-11-05 09:33:11'),
(15, 1, 'create_portfolio', 'Menambah portofolio \"Testing\" (/testing)', '2026-11-05 09:54:19'),
(16, 1, 'delete_portfolio', 'Menghapus portofolio \"Testing\" (/testing)', '2026-11-05 09:54:24'),
(17, 1, 'update_portfolio', 'Mengubah portofolio \"Batik Sari\" (/batik-sari): logo, galeri', '2026-11-05 11:40:53'),
(18, 1, 'update_portfolio', 'Mengubah portofolio \"Warung Bu Sri\" (/warung-bu-sri-budo): logo, galeri', '2026-11-05 11:41:09');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `username`, `password_hash`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$12$2y4xSEkHpkOrHy8s3bIgQ.itq1fi.Ys38SL4jrfySM2eKUad9HiB6', '2026-11-05 04:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_images`
--

CREATE TABLE `portfolio_images` (
  `id` int UNSIGNED NOT NULL,
  `portfolio_id` int UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_links`
--

CREATE TABLE `portfolio_links` (
  `id` int UNSIGNED NOT NULL,
  `portfolio_id` int UNSIGNED NOT NULL,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_profile`
--

CREATE TABLE `site_profile` (
  `id` int UNSIGNED NOT NULL,
  `business_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shopee_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokopedia_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_profile`
--

INSERT INTO `site_profile` (`id`, `business_name`, `slug`, `bio`, `logo_path`, `whatsapp`, `instagram`, `tiktok`, `shopee_url`, `tokopedia_url`, `status`, `updated_at`) VALUES
(1, 'Warung Bu Sri', 'warung-bu-sri-budo', 'Warung makan rumahan dengan menu masakan Jawa autentik, dimasak setiap pagi dengan bahan segar\r\n\r\ntesting testing', NULL, '6281234567890', 'warungsari', NULL, NULL, NULL, 'published', '2026-11-05 11:41:09'),
(2, 'Batik Sari', 'batik-sari', 'Menyediakan koleksi batik pilihan berkualiatas tinggi dengan perpaduan motif tradisional yang kaya makna dan potongan modern yang elegan. Dari busana kerja, pakaian kasual harian, hingga busana formal untuk acara spesial — kami siap melengkapi penampilan Anda dengan pesona budaya Nusantara.', NULL, '6281234567890', 'batiksari', 'batiksari', NULL, NULL, 'published', '2026-11-05 11:40:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_created` (`created_at`),
  ADD KEY `idx_activity_logs_admin` (`admin_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_username` (`username`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_user_time` (`username`,`attempted_at`);

--
-- Indexes for table `portfolio_images`
--
ALTER TABLE `portfolio_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_portfolio_images_portfolio` (`portfolio_id`,`sort_order`);

--
-- Indexes for table `portfolio_links`
--
ALTER TABLE `portfolio_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_portfolio_links_portfolio` (`portfolio_id`,`sort_order`);

--
-- Indexes for table `site_profile`
--
ALTER TABLE `site_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_site_profile_slug` (`slug`),
  ADD KEY `idx_site_profile_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `portfolio_images`
--
ALTER TABLE `portfolio_images`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `portfolio_links`
--
ALTER TABLE `portfolio_links`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `site_profile`
--
ALTER TABLE `site_profile`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `portfolio_images`
--
ALTER TABLE `portfolio_images`
  ADD CONSTRAINT `fk_portfolio_images_portfolio` FOREIGN KEY (`portfolio_id`) REFERENCES `site_profile` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio_links`
--
ALTER TABLE `portfolio_links`
  ADD CONSTRAINT `fk_portfolio_links_portfolio` FOREIGN KEY (`portfolio_id`) REFERENCES `site_profile` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
