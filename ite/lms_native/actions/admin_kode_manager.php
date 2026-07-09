<?php
// GARA - Garuda Akademi
// File: actions/admin_kode_manager.php
// Tujuan: Backend untuk mengatur Kode Akses Mata Pelajaran (Update/Insert).

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Cek Login Guru
requireGuru();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/pengaturan_kode.php");
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update':
            $mapel_id = $_POST['mapel_id'];
            $kode_baru = trim($_POST['kode_akses']);

            if (empty($mapel_id) || empty($kode_baru)) {
                throw new Exception("Kode akses tidak boleh kosong.");
            }

            // Cek apakah kode ini sudah dipakai oleh mapel LAIN?
            // (Kode akses sebaiknya unik agar sistem login tidak bingung)
            $stmtCheck = $pdo->prepare("SELECT mapel_id FROM kode_akses_mapel WHERE kode_akses = ? AND mapel_id != ?");
            $stmtCheck->execute([$kode_baru, $mapel_id]);
            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Kode '$kode_baru' sudah digunakan oleh mata pelajaran lain.");
            }

            // Cek apakah mapel ini sudah punya kode di database?
            $stmtExist = $pdo->prepare("SELECT id FROM kode_akses_mapel WHERE mapel_id = ?");
            $stmtExist->execute([$mapel_id]);
            $exists = $stmtExist->fetch();

            if ($exists) {
                // UPDATE jika sudah ada
                $sql = "UPDATE kode_akses_mapel SET kode_akses = ? WHERE mapel_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kode_baru, $mapel_id]);
                $_SESSION['success_message'] = "Kode akses berhasil diperbarui.";
            } else {
                // INSERT jika belum ada
                $sql = "INSERT INTO kode_akses_mapel (mapel_id, kode_akses) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$mapel_id, $kode_baru]);
                $_SESSION['success_message'] = "Kode akses berhasil dibuat.";
            }
            break;
            
        // Fitur Hapus Kode (Opsional, jarang dipakai tapi bagus ada)
        case 'delete':
             // Implementasi jika diperlukan
             break;
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
}

header("Location: ../admin/pengaturan_kode.php");
exit();
?>