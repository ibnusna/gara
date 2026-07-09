<?php
// GARA - Garuda Akademi
// File: actions/set_mapel_siswa.php
// Tujuan: Menangani pemilihan mata pelajaran oleh siswa dan menyimpannya ke sesi.

// PENTING: Bypass cek mapel karena kita justru sedang mau setting mapelnya di sini.
define('SKIP_MAPEL_CHECK', true);

require_once 'auth_helper.php';
require_once '../config/database.php';

// 1. Pastikan user adalah siswa yang sudah login
requireSiswa();

// 2. Hanya terima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika ditembak langsung via URL (GET), kembalikan ke pilih mapel
    header("Location: ../student/pilih_mapel.php");
    exit();
}

// 3. Ambil data input
$mapel_id = $_POST['mapel_id'] ?? null;

if (!$mapel_id) {
    header("Location: ../student/pilih_mapel.php?error=no_selection");
    exit();
}

try {
    // 4. Validasi & Ambil Nama Mapel dari Database
    // Kita perlu nama mapelnya untuk ditampilkan di header Dashboard nanti
    $stmt = $pdo->prepare("SELECT nama_mapel FROM mata_pelajaran WHERE id = ?");
    $stmt->execute([$mapel_id]);
    $mapel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mapel) {
        // 5. SIMPAN KE SESSION (Inti dari fitur ini)
        $_SESSION['mapel_id']   = $mapel_id;
        $_SESSION['nama_mapel'] = $mapel['nama_mapel'];

        // 6. Redirect Sukses ke Dashboard Utama
        header("Location: ../student/dashboard.php");
        exit();
    } else {
        // ID Mapel tidak valid (misal diinspect element diubah valuenya)
        header("Location: ../student/pilih_mapel.php?error=invalid_mapel");
        exit();
    }

} catch (PDOException $e) {
    // Error handler jika DB bermasalah
    die("Terjadi kesalahan sistem saat memilih mapel. Silakan coba lagi.");
}
?>