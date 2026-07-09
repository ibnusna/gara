<?php
// GARA - Garuda Akademi
// File: actions/tugas_manager.php (V4 - File Cleanup)
// FIX: Delete physical files when Task is deleted

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. HARDCODE ZONA WAKTU (Agar konsisten)
date_default_timezone_set('Asia/Jakarta');

requireGuruSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/ruang_tugas.php");
    exit();
}

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$action   = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $judul = trim($_POST['judul']);
            $deskripsi = trim($_POST['deskripsi']);
            $link = trim($_POST['link_lampiran']);
            $batas = !empty($_POST['batas_waktu']) ? $_POST['batas_waktu'] : null;
            $is_auto_close = isset($_POST['is_auto_close']) ? 1 : 0;
            $allow_upload  = isset($_POST['allow_upload']) ? 1 : 0;
            
            // FIX TIMEZONE: Generate waktu di PHP (WIB)
            $waktu_sekarang = date('Y-m-d H:i:s');
            
            // Default Status = Aktif saat buat baru
            $sql = "INSERT INTO tugas (mapel_id, kelas_id, judul, deskripsi, link_lampiran, batas_waktu, is_auto_close, allow_upload, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif', ?)";
            
            $stmt = $pdo->prepare($sql);
            // Masukkan $waktu_sekarang sebagai parameter terakhir
            $stmt->execute([$mapel_id, $kelas_id, $judul, $deskripsi, $link, $batas, $is_auto_close, $allow_upload, $waktu_sekarang]);
            
            $_SESSION['success_message'] = "Tugas diterbitkan.";
            break;

        case 'update':
            $id = $_POST['id'];
            $judul = trim($_POST['judul']);
            $deskripsi = trim($_POST['deskripsi']);
            $link = trim($_POST['link_lampiran']);
            $batas = !empty($_POST['batas_waktu']) ? $_POST['batas_waktu'] : null;
            $is_auto_close = isset($_POST['is_auto_close']) ? 1 : 0;
            $allow_upload  = isset($_POST['allow_upload']) ? 1 : 0;

            $sql = "UPDATE tugas SET judul=?, deskripsi=?, link_lampiran=?, batas_waktu=?, is_auto_close=?, allow_upload=? WHERE id=? AND mapel_id=? AND kelas_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$judul, $deskripsi, $link, $batas, $is_auto_close, $allow_upload, $id, $mapel_id, $kelas_id]);
            $_SESSION['success_message'] = "Tugas diperbarui.";
            break;

        // FITUR BARU: TOGGLE STATUS
        case 'toggle_status':
            $id = $_POST['id'];
            $current_status = $_POST['current_status'];
            $new_status = ($current_status === 'aktif') ? 'draft' : 'aktif';
            
            $sql = "UPDATE tugas SET status = ? WHERE id = ? AND mapel_id = ? AND kelas_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_status, $id, $mapel_id, $kelas_id]);
            
            $msg = ($new_status === 'aktif') ? "Tugas diaktifkan (Muncul di Siswa)." : "Tugas diarsipkan ke Draft (Disembunyikan).";
            $_SESSION['success_message'] = $msg;
            break;

        case 'delete':
            // Hapus Permanen
            $id = $_POST['id'];

            // 1. CLEANUP FILES (Hapus semua file yang diupload siswa untuk tugas ini)
            try {
                $stmtFiles = $pdo->prepare("SELECT link_pengumpulan, metode FROM tugas_pengumpulan WHERE tugas_id = ?");
                $stmtFiles->execute([$id]);
                $files = $stmtFiles->fetchAll();

                foreach ($files as $f) {
                    if ($f['metode'] === 'file' && !empty($f['link_pengumpulan'])) {
                        $path = '../uploads/tugas/' . $f['link_pengumpulan'];
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }
            } catch (Exception $e) {
                // Ignore cleanup error, continue to db delete
            }

            // 2. DELETE RECORD
            $stmt = $pdo->prepare("DELETE FROM tugas WHERE id = ? AND mapel_id = ? AND kelas_id = ?");
            $stmt->execute([$id, $mapel_id, $kelas_id]);
            $_SESSION['success_message'] = "Tugas dan seluruh file siswa dihapus permanen.";
            break;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

header("Location: ../admin/ruang_tugas.php");
exit();
?>