-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 01, 2026 at 11:21 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asesmen_gara`
--

-- --------------------------------------------------------

--
-- Table structure for table `asesmen_config`
--

CREATE TABLE `asesmen_config` (
  `id_config` int NOT NULL,
  `status_pintu` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Close, 1=Open',
  `status_pintu_siswa` tinyint(1) NOT NULL DEFAULT '0',
  `grade_lock` tinyint(1) NOT NULL DEFAULT '0',
  `grade_lock_date` timestamp NULL DEFAULT NULL,
  `jenis_asesmen` enum('ASTS','ASAS') NOT NULL DEFAULT 'ASTS',
  `opened_by` int DEFAULT NULL COMMENT 'FK: user_id Operator',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_soal`
--

CREATE TABLE `bank_soal` (
  `id_soal` int NOT NULL,
  `id_guru` int NOT NULL COMMENT 'FK: user_id Guru',
  `mapel` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `tipe_soal` enum('PG','PG_KOMPLEKS','ISIAN','BENAR_SALAH','PGK','BS') NOT NULL DEFAULT 'PG',
  `konten_soal` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `opsi_e` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `opsi_d` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `opsi_c` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `opsi_b` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `opsi_a` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kunci_jawaban` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `bobot` int NOT NULL DEFAULT '1',
  `status_soal` enum('DRAFT','SUBMITTED','VALIDATED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_ujian`
--

CREATE TABLE `hasil_ujian` (
  `id_hasil` int NOT NULL,
  `id_siswa` int NOT NULL COMMENT 'FK: user_id Siswa',
  `id_jadwal` int NOT NULL,
  `jawaban_user` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `skor_akhir` float NOT NULL DEFAULT '0',
  `waktu_mulai` timestamp NULL DEFAULT NULL,
  `waktu_selesai` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_ujian`
--

CREATE TABLE `jadwal_ujian` (
  `id_jadwal` int NOT NULL,
  `mapel` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `jenis_asesmen` enum('ASTS','ASAS') NOT NULL DEFAULT 'ASTS',
  `tanggal_ujian` date NOT NULL,
  `hari` varchar(15) NOT NULL DEFAULT '' COMMENT 'Nama hari',
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL DEFAULT '00:00:00' COMMENT 'Waktu selesai ujian',
  `durasi` int NOT NULL COMMENT 'Dalam menit',
  `pengulangan` enum('YA','TIDAK') DEFAULT 'TIDAK',
  `tampilkan_jawaban` enum('YA','TIDAK') DEFAULT 'TIDAK',
  `tampilkan_nilai` enum('YA','TIDAK') NOT NULL DEFAULT 'YA',
  `mode_submit` enum('MANDIRI','SERENTAK') NOT NULL DEFAULT 'MANDIRI',
  `hasil_dirilis_ke_guru` enum('YA','TIDAK') DEFAULT 'TIDAK',
  `token` varchar(10) DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soal_assets`
--

CREATE TABLE `soal_assets` (
  `id` bigint UNSIGNED NOT NULL,
  `bank_soal_id` int UNSIGNED NOT NULL,
  `asset_type` enum('local_image','external_image','youtube_link','audio_mp3','google_drive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_source` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ujian_drafts`
--

CREATE TABLE `ujian_drafts` (
  `id` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_jadwal` int NOT NULL,
  `jawaban_draft` longtext NOT NULL,
  `last_sync` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asesmen_config`
--
ALTER TABLE `asesmen_config`
  ADD PRIMARY KEY (`id_config`);

--
-- Indexes for table `bank_soal`
--
ALTER TABLE `bank_soal`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `mapel` (`mapel`);

--
-- Indexes for table `hasil_ujian`
--
ALTER TABLE `hasil_ujian`
  ADD PRIMARY KEY (`id_hasil`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `jadwal_ujian`
--
ALTER TABLE `jadwal_ujian`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD UNIQUE KEY `uniq_token` (`token`);

--
-- Indexes for table `soal_assets`
--
ALTER TABLE `soal_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `soal_assets_bank_soal_id_index` (`bank_soal_id`);

--
-- Indexes for table `ujian_drafts`
--
ALTER TABLE `ujian_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_draft` (`id_siswa`,`id_jadwal`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asesmen_config`
--
ALTER TABLE `asesmen_config`
  MODIFY `id_config` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_soal`
--
ALTER TABLE `bank_soal`
  MODIFY `id_soal` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hasil_ujian`
--
ALTER TABLE `hasil_ujian`
  MODIFY `id_hasil` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_ujian`
--
ALTER TABLE `jadwal_ujian`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soal_assets`
--
ALTER TABLE `soal_assets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian_drafts`
--
ALTER TABLE `ujian_drafts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;