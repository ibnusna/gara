-- Database: `asesment_gara`
CREATE TABLE IF NOT EXISTS `asesmen_config` (
    `id_config` int(11) NOT NULL AUTO_INCREMENT,
    `status_pintu` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Close, 1=Open',
    `jenis_asesmen` enum('ASTS', 'ASAS') NOT NULL DEFAULT 'ASTS',
    `opened_by` int(11) DEFAULT NULL COMMENT 'FK: user_id Operator',
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id_config`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS `bank_soal` (
    `id_soal` int(11) NOT NULL AUTO_INCREMENT,
    `id_guru` int(11) NOT NULL COMMENT 'FK: user_id Guru',
    `mapel` varchar(100) NOT NULL,
    `kelas` varchar(50) NOT NULL,
    `tipe_soal` enum('PG', 'PGK', 'ISIAN', 'BS') NOT NULL,
    `konten_soal` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`konten_soal`)),
    `kunci_jawaban` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `bobot` int(11) NOT NULL DEFAULT 1,
    `status_soal` enum('DRAFT', 'VALIDATED') NOT NULL DEFAULT 'DRAFT',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id_soal`),
    KEY `id_guru` (`id_guru`),
    KEY `mapel` (`mapel`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS `jadwal_ujian` (
    `id_jadwal` int(11) NOT NULL AUTO_INCREMENT,
    `mapel` varchar(100) NOT NULL,
    `kelas` varchar(50) NOT NULL,
    `tanggal_ujian` date NOT NULL,
    `jam_mulai` time NOT NULL,
    `durasi` int(11) NOT NULL COMMENT 'Dalam menit',
    `token` varchar(10) NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_jadwal`),
    UNIQUE KEY `token` (`token`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS `hasil_ujian` (
    `id_hasil` int(11) NOT NULL AUTO_INCREMENT,
    `id_siswa` int(11) NOT NULL COMMENT 'FK: user_id Siswa',
    `id_jadwal` int(11) NOT NULL,
    `jawaban_user` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`jawaban_user`)),
    `skor_akhir` float NOT NULL DEFAULT 0,
    `waktu_mulai` timestamp NULL DEFAULT NULL,
    `waktu_selesai` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_hasil`),
    KEY `id_siswa` (`id_siswa`),
    KEY `id_jadwal` (`id_jadwal`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Insert default config
INSERT IGNORE INTO `asesmen_config` (`id_config`, `status_pintu`, `jenis_asesmen`)
VALUES (1, 0, 'ASTS');