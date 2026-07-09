<?php
// GARA - Garuda Akademi
// File: actions/set_session.php
// Tujuan: Menyimpan pilihan mapel dan kelas guru ke dalam sesi aktif, lalu redirect ke dashboard.

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Pastikan yang akses adalah Guru
requireGuru();

// 2. Hanya terima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/pilih_sesi.php");
    exit();
}

// 3. Ambil Input
$mapel_id = $_POST['mapel_id'] ?? null;
$kelas_id = $_POST['kelas_id'] ?? null;

// 4. Validasi Sederhana
if (!$mapel_id || !$kelas_id) {
    header("Location: ../admin/pilih_sesi.php?msg=invalid_data");
    exit();
}

try {
    // 5. Ambil Nama Mapel & Kelas dari Database
    // Kita butuh namanya untuk ditampilkan di Header Dashboard nanti
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT nama_mapel FROM mata_pelajaran WHERE id = :mid) as nama_mapel,
            (SELECT nama_kelas FROM kelas WHERE id = :kid) as nama_kelas
    ");
    
    $stmt->execute(['mid' => $mapel_id, 'kid' => $kelas_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['nama_mapel'] && $result['nama_kelas']) {
        // 6. SIMPAN KE SESSION (Inti dari script ini)
        $_SESSION['mapel_id']   = $mapel_id;
        $_SESSION['kelas_id']   = $kelas_id;
        $_SESSION['nama_mapel'] = $result['nama_mapel'];
        $_SESSION['nama_kelas'] = $result['nama_kelas'];

        // 7. Redirect ke Dashboard Admin Utama
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        // Data tidak ditemukan di DB
        header("Location: ../admin/pilih_sesi.php?msg=data_not_found");
        exit();
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>