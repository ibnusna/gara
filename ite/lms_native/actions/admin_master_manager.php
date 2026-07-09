<?php
// GARA - Garuda Akademi
// File: actions/admin_master_manager.php
// Tujuan: Backend CRUD Data Master (Kelas & Mata Pelajaran).

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Cek Login Guru
requireGuru();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/pengaturan_master.php");
    exit();
}

$type = $_POST['type'] ?? ''; // 'kelas' atau 'mapel'
$action = $_POST['action'] ?? '';

try {
    
    // --- MANAJEMEN KELAS ---
    if ($type === 'kelas') {
        switch ($action) {
            case 'add':
                $nama_kelas = trim($_POST['nama_kelas']);
                if (empty($nama_kelas)) throw new Exception("Nama kelas tidak boleh kosong.");
                
                // Validasi Duplikat
                $stmtCheck = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
                $stmtCheck->execute([$nama_kelas]);
                if ($stmtCheck->rowCount() > 0) throw new Exception("Kelas '$nama_kelas' sudah ada.");

                $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
                $stmt->execute([$nama_kelas]);
                $_SESSION['success_message'] = "Kelas baru berhasil ditambahkan.";
                break;

            case 'update':
                $id = $_POST['id'];
                $nama_kelas = trim($_POST['nama_kelas']);
                
                $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas = ? WHERE id = ?");
                $stmt->execute([$nama_kelas, $id]);
                $_SESSION['success_message'] = "Nama kelas diperbarui.";
                break;

            case 'delete':
                $id = $_POST['id'];
                // Hati-hati: Delete Cascade akan menghapus semua siswa di kelas ini!
                $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = "Kelas berhasil dihapus permanen.";
                break;
        }
    }

    // --- MANAJEMEN MATA PELAJARAN ---
    elseif ($type === 'mapel') {
        switch ($action) {
            case 'add':
                $nama_mapel = trim($_POST['nama_mapel']);
                if (empty($nama_mapel)) throw new Exception("Nama mata pelajaran wajib diisi.");

                $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (nama_mapel) VALUES (?)");
                $stmt->execute([$nama_mapel]);
                $_SESSION['success_message'] = "Mata pelajaran baru berhasil ditambahkan.";
                break;

            case 'update':
                $id = $_POST['id'];
                $nama_mapel = trim($_POST['nama_mapel']);
                
                $stmt = $pdo->prepare("UPDATE mata_pelajaran SET nama_mapel = ? WHERE id = ?");
                $stmt->execute([$nama_mapel, $id]);
                $_SESSION['success_message'] = "Nama mata pelajaran diperbarui.";
                break;

            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = "Mata pelajaran dihapus.";
                break;
        }
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
}

header("Location: ../admin/pengaturan_master.php");
exit();
?>