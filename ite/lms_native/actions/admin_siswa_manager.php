<?php
// GARA - Garuda Akademi
// File: actions/admin_siswa_manager.php
// Tujuan: Menangani CRUD Siswa & Reset Password oleh Guru.

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Pastikan hanya Guru yang boleh akses
requireGuru();

// 2. Ambil Action
$action = $_POST['action'] ?? '';

// Default Redirect
$redirect_url = '../admin/pengaturan_siswa.php';

try {
    switch ($action) {
        
        // --- CASE 1: TAMBAH SISWA BARU ---
        case 'add':
            $nis      = trim($_POST['nis']);
            $nama     = trim($_POST['nama']);
            $kelas_id = $_POST['kelas_id'];

            // Validasi NIS Duplikat
            $stmtCek = $pdo->prepare("SELECT id FROM siswa WHERE nis = ?");
            $stmtCek->execute([$nis]);
            if ($stmtCek->rowCount() > 0) {
                header("Location: $redirect_url?status=error&msg=NIS sudah terdaftar");
                exit();
            }

            // Set Password Default: gara17
            $default_pass = password_hash('gara17', PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO siswa (nis, nama, kelas_id, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nis, $nama, $kelas_id, $default_pass])) {
                header("Location: $redirect_url?status=success&msg=Siswa berhasil ditambahkan");
            } else {
                header("Location: $redirect_url?status=error&msg=Gagal menambah siswa");
            }
            break;

        // --- CASE 2: UPDATE DATA SISWA (Nama/Kelas/NIS) ---
        case 'update':
            $id       = $_POST['id'];
            $nis      = trim($_POST['nis']);
            $nama     = trim($_POST['nama']);
            $kelas_id = $_POST['kelas_id'];

            // Cek apakah NIS bentrok dengan siswa lain (kecuali dirinya sendiri)
            $stmtCek = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? AND id != ?");
            $stmtCek->execute([$nis, $id]);
            if ($stmtCek->rowCount() > 0) {
                header("Location: $redirect_url?status=error&msg=NIS sudah digunakan siswa lain");
                exit();
            }

            $stmt = $pdo->prepare("UPDATE siswa SET nis = ?, nama = ?, kelas_id = ? WHERE id = ?");
            if ($stmt->execute([$nis, $nama, $kelas_id, $id])) {
                header("Location: $redirect_url?status=success&msg=Data siswa diperbarui");
            } else {
                header("Location: $redirect_url?status=error&msg=Gagal update siswa");
            }
            break;

        // --- CASE 3: HAPUS SISWA ---
        case 'delete':
            $id = $_POST['id'];
            
            // Hapus siswa (Cascade akan menghapus data terkait di tabel lain jika disetting di DB)
            $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
            if ($stmt->execute([$id])) {
                header("Location: $redirect_url?status=success&msg=Siswa dihapus");
            } else {
                header("Location: $redirect_url?status=error&msg=Gagal menghapus siswa");
            }
            break;

        // --- CASE 4: RESET PASSWORD (FITUR BARU) ---
        case 'reset_password':
            $id = $_POST['id'];
            
            // Kembalikan ke default: gara17
            $reset_pass = password_hash('gara17', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE siswa SET password = ? WHERE id = ?");
            if ($stmt->execute([$reset_pass, $id])) {
                // Kita kirim parameter msg khusus agar bisa ditangkap SweetAlert di frontend
                header("Location: $redirect_url?status=success&msg=Password direset ke: gara17");
            } else {
                header("Location: $redirect_url?status=error&msg=Gagal mereset password");
            }
            break;

        default:
            header("Location: $redirect_url");
            break;
    }

} catch (PDOException $e) {
    // Tangani error database
    $error_msg = urlencode("Database Error: " . $e->getMessage());
    header("Location: $redirect_url?status=error&msg=$error_msg");
    exit();
}
?>