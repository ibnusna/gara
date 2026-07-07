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
-- Database: `lms_pembelajaran`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `pertemuan_ke` int NOT NULL DEFAULT '1',
  `pokok_bahasan` text,
  `rangkuman` text,
  `materi` text,
  `metode` varchar(50) DEFAULT 'Tatap Muka',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `absensi_detail`
--

CREATE TABLE `absensi_detail` (
  `id` int NOT NULL,
  `absensi_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `status` enum('H','S','I','A','T') NOT NULL DEFAULT 'H',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agenda_harian`
--

CREATE TABLE `agenda_harian` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `rencana_kegiatan` text,
  `catatan_pelaksanaan` text,
  `siswa_tidak_hadir` longtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catatan_kelas`
--

CREATE TABLE `catatan_kelas` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT 'Jurnal Harian',
  `isi_html` longtext,
  `tanggal_buat` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int NOT NULL,
  `class_id` int NOT NULL COMMENT 'Ref: auth_gara.kelas.id',
  `subject_id` int NOT NULL COMMENT 'Ref: auth_gara.mata_pelajaran.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diskusi_replies`
--

CREATE TABLE `diskusi_replies` (
  `id` int NOT NULL,
  `thread_id` int NOT NULL,
  `isi_balasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_pembuat` enum('siswa','guru') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `siswa_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diskusi_threads`
--

CREATE TABLE `diskusi_threads` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_konten` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_pembuat` enum('guru','siswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guru',
  `siswa_id` int DEFAULT NULL,
  `media_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_pinned` tinyint(1) DEFAULT '0' COMMENT '1 = Disematkan oleh Guru',
  `status` enum('aktif','draft') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `is_closed` tinyint(1) DEFAULT '0' COMMENT '1 = Komentar ditutup'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `external_links`
--

CREATE TABLE `external_links` (
  `id` int NOT NULL,
  `jenis_link` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_scores`
--

CREATE TABLE `final_scores` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `semester` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_score_id` int NOT NULL,
  `batas_kkm` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_akhir_final` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('Lulus','Butuh Pembinaan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Lulus',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru_mapel`
--

CREATE TABLE `guru_mapel` (
  `id` int NOT NULL,
  `guru_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `tahun_ajaran` varchar(20) DEFAULT '2024/2025'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int NOT NULL,
  `nama_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kode_akses_mapel`
--

CREATE TABLE `kode_akses_mapel` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kode_akses` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id` int NOT NULL,
  `nama_mapel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran_backup`
--

CREATE TABLE `mata_pelajaran_backup` (
  `id` int NOT NULL,
  `nama_mapel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_assets`
--

CREATE TABLE `media_assets` (
  `id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_files`
--

CREATE TABLE `media_files` (
  `id` int NOT NULL,
  `asset_id` int NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_tugas`
--

CREATE TABLE `nilai_tugas` (
  `id` int NOT NULL,
  `tugas_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_uas`
--

CREATE TABLE `nilai_uas` (
  `id` int NOT NULL,
  `uas_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `benar_pg` int DEFAULT '0',
  `total_pg` int DEFAULT '40',
  `benar_essay` decimal(5,2) DEFAULT '0.00',
  `nilai_pg_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_essay_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_ujian_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_support` decimal(5,2) DEFAULT '0.00',
  `gunakan_support` tinyint(1) DEFAULT '0' COMMENT '1=Ya, 0=Tidak',
  `nilai` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_ujian_cbt`
--

CREATE TABLE `nilai_ujian_cbt` (
  `id` int NOT NULL,
  `ujian_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL COMMENT 'Snapshot nama saat ujian',
  `kelas_text` varchar(50) NOT NULL COMMENT 'Snapshot kelas saat ujian',
  `jawaban_full` longtext NOT NULL COMMENT 'JSON String Array Jawaban Siswa',
  `nilai_akhir` decimal(5,2) NOT NULL DEFAULT '0.00',
  `jumlah_benar` int DEFAULT '0',
  `jumlah_salah` int DEFAULT '0',
  `percobaan_ke` int DEFAULT '1',
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_uts`
--

CREATE TABLE `nilai_uts` (
  `id` int NOT NULL,
  `uts_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `benar_pg` int DEFAULT '0',
  `total_pg` int DEFAULT '40',
  `benar_essay` decimal(5,2) DEFAULT '0.00',
  `nilai_pg_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_essay_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_ujian_murni` decimal(5,2) DEFAULT '0.00',
  `nilai_support` decimal(5,2) DEFAULT '0.00',
  `gunakan_support` tinyint(1) DEFAULT '0' COMMENT '1=Ya, 0=Tidak',
  `nilai` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_sistem`
--

CREATE TABLE `pengaturan_sistem` (
  `id` int NOT NULL,
  `kunci` varchar(50) NOT NULL,
  `nilai` varchar(255) NOT NULL,
  `deskripsi` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_ujian`
--

CREATE TABLE `pengaturan_ujian` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentasi_quiz`
--

CREATE TABLE `presentasi_quiz` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_mode` enum('quiz_interaktif','ujian_essay','quiz_mastery') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'quiz_interaktif',
  `timer_per_soal` int DEFAULT '0' COMMENT 'Dalam detik. 0 = Manual',
  `tampilkan_jawaban` enum('ya','tidak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ya',
  `bg_music` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path file audio',
  `bg_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path file gambar opening',
  `durasi_total_menit` int DEFAULT '60' COMMENT 'Timer global untuk ujian',
  `jumlah_soal_per_slide` int DEFAULT '3' COMMENT 'Untuk layout landscape (3-5)',
  `status` enum('draft','published') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentasi_soal`
--

CREATE TABLE `presentasi_soal` (
  `id` int NOT NULL,
  `presentasi_id` int NOT NULL,
  `jenis_soal` enum('pg','essay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pg',
  `pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `opsi_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Simpan opsi A,B,C,D dalam format JSON agar fleksibel',
  `kunci_jawaban` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `urutan` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_scores`
--

CREATE TABLE `raw_scores` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `semester` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_absensi` decimal(5,2) DEFAULT '0.00',
  `nilai_tugas` decimal(5,2) DEFAULT '0.00',
  `nilai_uts` decimal(5,2) DEFAULT '0.00',
  `nilai_uas` decimal(5,2) DEFAULT '0.00',
  `nilai_akhir_raw` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekap_harian`
--

CREATE TABLE `rekap_harian` (
  `id` bigint NOT NULL,
  `siswa_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah_aksi` int NOT NULL DEFAULT '1',
  `mapel_terakses` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rpp_materi`
--

CREATE TABLE `rpp_materi` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `semester` int NOT NULL,
  `id_materi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bab` int NOT NULL,
  `bagian` int NOT NULL,
  `judul_materi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_ppt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link_youtube` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link_modul` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link_tugas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link_notebook` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruang_kompetensi`
--

CREATE TABLE `ruang_kompetensi` (
  `id` bigint UNSIGNED NOT NULL,
  `guru_id` bigint UNSIGNED NOT NULL,
  `kelas_id` int NULL,
  `mapel_id` int NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_evaluasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_menit` int NOT NULL,
  `status` enum('aktif','arsip') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT ((now() + interval 8 hour))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `nis` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skema_penilaian`
--

CREATE TABLE `skema_penilaian` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `bobot_absensi` decimal(5,2) DEFAULT '25.00',
  `bobot_tugas` decimal(5,2) DEFAULT '30.00',
  `bobot_uts` decimal(5,2) DEFAULT '20.00',
  `bobot_uas` decimal(5,2) DEFAULT '25.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int NOT NULL,
  `teacher_id` int NOT NULL COMMENT 'Ref: auth_gara.guru.id',
  `subject_id` int NOT NULL COMMENT 'Ref: auth_gara.mata_pelajaran.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaching_assignments`
--

CREATE TABLE `teaching_assignments` (
  `id` int NOT NULL,
  `class_id` int NOT NULL COMMENT 'Ref: auth_gara.kelas.id',
  `subject_id` int NOT NULL COMMENT 'Ref: auth_gara.mata_pelajaran.id',
  `teacher_id` int NOT NULL COMMENT 'Ref: auth_gara.guru.id',
  `academic_year` varchar(20) DEFAULT '2024/2025',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `rpp_id` int DEFAULT NULL,
  `kelas_id` int NOT NULL,
  `tugas_ke` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `tipe_tugas` enum('Individual','Kelompok') DEFAULT NULL,
  `kategori_asesmen` enum('Formatif','Sumatif','P5','Remedial') DEFAULT 'Formatif',
  `teknik_penilaian` enum('Tes Tulis','Performa','Proyek','Produk','Portofolio','Lainnya') DEFAULT 'Tes Tulis',
  `pokok_bahasan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `link_lampiran` text,
  `batas_waktu` datetime DEFAULT NULL,
  `is_auto_close` tinyint(1) DEFAULT '0',
  `allow_upload` tinyint(1) DEFAULT '1',
  `status` enum('aktif','draft') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_pengumpulan`
--

CREATE TABLE `tugas_pengumpulan` (
  `id` int NOT NULL,
  `tugas_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `metode` enum('link','file') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'link',
  `link_pengumpulan` text COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan_siswa` text COLLATE utf8mb4_general_ci,
  `dikumpulkan_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nilai` decimal(5,2) DEFAULT NULL,
  `feedback_guru` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uas`
--

CREATE TABLE `uas` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `semester` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ujian_bank_soal`
--

CREATE TABLE `ujian_bank_soal` (
  `id` int NOT NULL,
  `ujian_id` int NOT NULL,
  `nomor_urut` int NOT NULL,
  `tipe_soal` enum('PG','PG_KOMPLEKS','BENAR_SALAH','ISIAN') NOT NULL DEFAULT 'PG',
  `konten_soal` longtext NOT NULL COMMENT 'Bisa HTML/Text/Gambar Base64',
  `opsi_a` text,
  `opsi_b` text,
  `opsi_c` text,
  `opsi_d` text,
  `opsi_e` text,
  `kunci_jawaban` text NOT NULL COMMENT 'Bisa string tunggal atau koma separated (A,C)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ujian_sesi`
--

CREATE TABLE `ujian_sesi` (
  `id` int NOT NULL,
  `judul_ujian` varchar(255) NOT NULL COMMENT 'Contoh: Ulangan Harian Bab 1',
  `kode_unik_sesi` varchar(50) NOT NULL COMMENT 'Mapping ke Tab Admin (Target Sheet), cth: IPA_7',
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `durasi_menit` int NOT NULL DEFAULT '60',
  `kode_akses` varchar(20) NOT NULL COMMENT 'Token Ujian',
  `tampilkan_kunci` enum('YA','TIDAK') DEFAULT 'TIDAK',
  `izinkan_ulang` enum('YA','TIDAK') DEFAULT 'TIDAK',
  `status` enum('MUNCUL','TIDAK') DEFAULT 'MUNCUL',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uts`
--

CREATE TABLE `uts` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `semester` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_absensi_mapel` (`mapel_id`),
  ADD KEY `idx_absensi_kelas` (`kelas_id`);

--
-- Indexes for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_absensi_siswa` (`absensi_id`,`siswa_id`),
  ADD KEY `idx_detail_absensi` (`absensi_id`),
  ADD KEY `idx_detail_siswa` (`siswa_id`);

--
-- Indexes for table `agenda_harian`
--
ALTER TABLE `agenda_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agenda_mapel` (`mapel_id`),
  ADD KEY `idx_agenda_kelas` (`kelas_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `catatan_kelas`
--
ALTER TABLE `catatan_kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_curriculum` (`class_id`,`subject_id`);

--
-- Indexes for table `diskusi_replies`
--
ALTER TABLE `diskusi_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `diskusi_threads`
--
ALTER TABLE `diskusi_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `external_links`
--
ALTER TABLE `external_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jenis_link` (`jenis_link`);

--
-- Indexes for table `guru_mapel`
--
ALTER TABLE `guru_mapel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kode_akses_mapel`
--
ALTER TABLE `kode_akses_mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mata_pelajaran_backup`
--
ALTER TABLE `mata_pelajaran_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_assets`
--
ALTER TABLE `media_assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_files`
--
ALTER TABLE `media_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_files_asset` (`asset_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai_tugas`
--
ALTER TABLE `nilai_tugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nilai_tugas_siswa` (`tugas_id`,`siswa_id`),
  ADD KEY `idx_nilai_tugas` (`tugas_id`),
  ADD KEY `idx_nilai_siswa` (`siswa_id`);

--
-- Indexes for table `nilai_uas`
--
ALTER TABLE `nilai_uas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nilai_uas_siswa` (`uas_id`,`siswa_id`),
  ADD KEY `idx_nilai_uas_id` (`uas_id`),
  ADD KEY `idx_nilai_uas_siswa` (`siswa_id`);

--
-- Indexes for table `nilai_ujian_cbt`
--
ALTER TABLE `nilai_ujian_cbt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nilai_ujian` (`ujian_id`),
  ADD KEY `idx_nilai_siswa` (`siswa_id`),
  ADD KEY `idx_nilai_nis` (`nis`);

--
-- Indexes for table `nilai_uts`
--
ALTER TABLE `nilai_uts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nilai_uts_siswa` (`uts_id`,`siswa_id`),
  ADD KEY `idx_nilai_uts_id` (`uts_id`),
  ADD KEY `idx_nilai_uts_siswa` (`siswa_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_kunci` (`kunci`);

--
-- Indexes for table `pengaturan_ujian`
--
ALTER TABLE `pengaturan_ujian`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pengumuman` (`mapel_id`,`kelas_id`),
  ADD KEY `fk_pengumuman_kelas` (`kelas_id`);

--
-- Indexes for table `presentasi_quiz`
--
ALTER TABLE `presentasi_quiz`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rekap_harian`
--
ALTER TABLE `rekap_harian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_siswa_tgl` (`siswa_id`,`tanggal`);

--
-- Indexes for table `rpp_materi`
--
ALTER TABLE `rpp_materi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id_materi` (`id_materi`);

--
-- Indexes for table `ruang_kompetensi`
--
ALTER TABLE `ruang_kompetensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_siswa_kelas` (`kelas_id`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_competency` (`teacher_id`,`subject_id`);

--
-- Indexes for table `teaching_assignments`
--
ALTER TABLE `teaching_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_primary_teacher` (`class_id`,`subject_id`,`academic_year`),
  ADD UNIQUE KEY `unique_assignment` (`class_id`,`subject_id`,`teacher_id`,`academic_year`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tugas_kelas` (`mapel_id`,`kelas_id`,`tugas_ke`),
  ADD KEY `fk_tugas_kelas` (`kelas_id`);

--
-- Indexes for table `tugas_pengumpulan`
--
ALTER TABLE `tugas_pengumpulan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uas`
--
ALTER TABLE `uas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uas_mapel` (`mapel_id`),
  ADD KEY `idx_uas_kelas` (`kelas_id`);

--
-- Indexes for table `ujian_bank_soal`
--
ALTER TABLE `ujian_bank_soal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_soal_ujian` (`ujian_id`);

--
-- Indexes for table `ujian_sesi`
--
ALTER TABLE `ujian_sesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kode_unik` (`kode_unik_sesi`),
  ADD KEY `idx_sesi_mapel` (`mapel_id`),
  ADD KEY `idx_sesi_kelas` (`kelas_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `uts`
--
ALTER TABLE `uts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uts_mapel` (`mapel_id`),
  ADD KEY `idx_uts_kelas` (`kelas_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agenda_harian`
--
ALTER TABLE `agenda_harian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catatan_kelas`
--
ALTER TABLE `catatan_kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diskusi_replies`
--
ALTER TABLE `diskusi_replies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diskusi_threads`
--
ALTER TABLE `diskusi_threads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `external_links`
--
ALTER TABLE `external_links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru_mapel`
--
ALTER TABLE `guru_mapel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kode_akses_mapel`
--
ALTER TABLE `kode_akses_mapel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mata_pelajaran_backup`
--
ALTER TABLE `mata_pelajaran_backup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_assets`
--
ALTER TABLE `media_assets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_tugas`
--
ALTER TABLE `nilai_tugas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_uas`
--
ALTER TABLE `nilai_uas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_ujian_cbt`
--
ALTER TABLE `nilai_ujian_cbt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_uts`
--
ALTER TABLE `nilai_uts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presentasi_quiz`
--
ALTER TABLE `presentasi_quiz`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekap_harian`
--
ALTER TABLE `rekap_harian`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpp_materi`
--
ALTER TABLE `rpp_materi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ruang_kompetensi`
--
ALTER TABLE `ruang_kompetensi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teaching_assignments`
--
ALTER TABLE `teaching_assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas_pengumpulan`
--
ALTER TABLE `tugas_pengumpulan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uas`
--
ALTER TABLE `uas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian_bank_soal`
--
ALTER TABLE `ujian_bank_soal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian_sesi`
--
ALTER TABLE `ujian_sesi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uts`
--
ALTER TABLE `uts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  ADD CONSTRAINT `fk_detail_absensi` FOREIGN KEY (`absensi_id`) REFERENCES `absensi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `diskusi_replies`
--
ALTER TABLE `diskusi_replies`
  ADD CONSTRAINT `diskusi_replies_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `diskusi_threads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kode_akses_mapel`
--
ALTER TABLE `kode_akses_mapel`
  ADD CONSTRAINT `fk_kode_akses_mapel` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_files`
--
ALTER TABLE `media_files`
  ADD CONSTRAINT `fk_files_asset` FOREIGN KEY (`asset_id`) REFERENCES `media_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nilai_tugas`
--
ALTER TABLE `nilai_tugas`
  ADD CONSTRAINT `fk_nilai_tugas_soal` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nilai_uas`
--
ALTER TABLE `nilai_uas`
  ADD CONSTRAINT `fk_nilai_uas_header` FOREIGN KEY (`uas_id`) REFERENCES `uas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nilai_uts`
--
ALTER TABLE `nilai_uts`
  ADD CONSTRAINT `fk_nilai_uts_header` FOREIGN KEY (`uts_id`) REFERENCES `uts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `fk_pengumuman_mapel` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ujian_bank_soal`
--
ALTER TABLE `ujian_bank_soal`
  ADD CONSTRAINT `fk_soal_ujian` FOREIGN KEY (`ujian_id`) REFERENCES `ujian_sesi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;