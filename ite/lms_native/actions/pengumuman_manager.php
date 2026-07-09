<?php
// GARA - Garuda Akademi
// File: actions/pengumuman_manager.php
// Tujuan: Backend CRUD Pengumuman Kelas (Single Active Announcement).

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Cek Login Guru
requireGuruSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/dashboard.php");
    exit();
}

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$action   = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            $judul = trim($_POST['judul']);
            $isi   = trim($_POST['isi']);

            if (empty($judul) || empty($isi)) {
                throw new Exception("Judul dan Isi pengumuman wajib diisi.");
            }

            // Gunakan REPLACE INTO atau INSERT ... ON DUPLICATE KEY UPDATE
            // Karena kita sudah set UNIQUE KEY (mapel_id, kelas_id) di database
            $sql = "INSERT INTO pengumuman (mapel_id, kelas_id, judul, isi, updated_at) 
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE judul = VALUES(judul), isi = VALUES(isi), updated_at = NOW()";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mapel_id, $kelas_id, $judul, $isi]);
            
            $_SESSION['success_message'] = "Pengumuman berhasil dipublikasikan.";
            break;

        case 'delete':
            // Hapus pengumuman aktif untuk kelas ini
            $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE mapel_id = ? AND kelas_id = ?");
            $stmt->execute([$mapel_id, $kelas_id]);
            
            $_SESSION['success_message'] = "Pengumuman dihapus.";
            break;
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
}

header("Location: ../admin/dashboard.php");
exit();
?>