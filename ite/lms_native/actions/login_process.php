<?php
// GARA - Garuda Akademi
// File: actions/login_process.php
// Tujuan: Memproses login dengan logika BARU (NIS + Password), tanpa Kode Mapel.

// ====================================================
// 1. KONFIGURASI SESI ABADI (AUTO LOGIN)
// ====================================================
$lifetime = 31536000; 

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
    exit();
}

$identifier = trim($_POST['identifier'] ?? ''); // NIS / Username Guru
$password   = trim($_POST['password'] ?? '');   // Password Siswa / Password Guru

if (empty($identifier) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username/NIS dan Password wajib diisi.']);
    exit();
}

try {
    // ====================================================
    // 2. CEK LOGIN GURU (HARDCODED - TETAP)
    // ====================================================
    if ($identifier === '252606' && $password === '7654') {
        session_regenerate_id(true);
        $_SESSION['role'] = 'guru';
        $_SESSION['user_id'] = 'guru_utama';
        $_SESSION['nama'] = 'Administrator Guru';
        
        unset($_SESSION['mapel_id']);
        unset($_SESSION['kelas_id']);

        echo json_encode([
            'status' => 'success',
            'role'   => 'guru',
            'redirect' => 'admin/pilih_sesi.php'
        ]);
        exit();
    }

    // ====================================================
    // 3. CEK LOGIN SISWA (LOGIKA BARU)
    // ====================================================
    // Logika Lama: NIS + Kode Mapel
    // Logika Baru: NIS + Password (Hash)

    // Cari siswa berdasarkan NIS
    $stmtSiswa = $pdo->prepare("
        SELECT s.id, s.nis, s.nama, s.password, s.kelas_id, k.nama_kelas
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        WHERE s.nis = ?
    ");
    $stmtSiswa->execute([$identifier]);
    $siswaData = $stmtSiswa->fetch(PDO::FETCH_ASSOC);

    if (!$siswaData) {
        echo json_encode(['status' => 'error', 'message' => 'NIS Siswa tidak ditemukan.']);
        exit();
    }

    // Verifikasi Password
    if (!password_verify($password, $siswaData['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Password salah.']);
        exit();
    }

    // Login Sukses
    session_regenerate_id(true);

    // Set Session Siswa Dasar
    $_SESSION['role']       = 'siswa';
    $_SESSION['user_id']    = $siswaData['id'];
    $_SESSION['nis']        = $siswaData['nis'];
    $_SESSION['nama']       = $siswaData['nama'];
    $_SESSION['kelas_id']   = $siswaData['kelas_id'];
    $_SESSION['nama_kelas'] = $siswaData['nama_kelas'];
    
    // HAPUS Sesi Mapel Lama (Karena sekarang pilih mapel belakangan)
    unset($_SESSION['mapel_id']);
    unset($_SESSION['nama_mapel']);

    echo json_encode([
        'status'   => 'success',
        'role'     => 'siswa',
        // Redirect ke halaman Pilih Mapel (File akan dibuat di Fase 3)
        'redirect' => 'student/pilih_mapel.php' 
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    exit();
}
?>