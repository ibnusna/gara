<?php
// GARA - Garuda Akademi
// File: actions/auth_helper.php
// Tujuan: Mengelola sesi login dan validasi hak akses (Satpam Utama).

// --- FIX UTAMA DISINI ---
// Kita pastikan session_start() SELALU dipanggil pertama kali.
// Pengecekan 'PHP_SESSION_NONE' berguna agar tidak error jika session sudah jalan.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Fungsi Cek Login Guru (Level 1: Hanya butuh login akun)
// Dipakai di: pilih_sesi.php, dashboard utama admin
function requireGuru() {
    // Cek apakah variabel sesi 'role' ada dan isinya 'guru'
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
        // Jika tidak, tendang ke login
        header("Location: ../index.php?error=belum_login");
        exit();
    }
}

// 2. Fungsi Cek Sesi Mengajar (Level 2: Butuh login + sudah pilih kelas)
// Dipakai di: ruang_materi.php, ruang_diskusi.php, ruang_tugas.php
function requireGuruSession() {
    // Cek login dasar dulu
    requireGuru();

    // Cek apakah Guru sudah memilih Mapel dan Kelas?
    if (!isset($_SESSION['mapel_id']) || !isset($_SESSION['kelas_id'])) {
        // Jika belum pilih (misal langsung tembak URL), lempar ke halaman pilih sesi
        // Gunakan path absolut/relatif yang aman
        header("Location: ../admin/pilih_sesi.php?msg=pilih_sesi_dulu");
        exit();
    }
}

// 3. Fungsi Cek Login Siswa (UPDATED LOGIC)
// Dipakai di semua halaman siswa (dashboard, ujian, dll)
function requireSiswa() {
    // A. Cek Apakah Login sebagai Siswa?
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
        header("Location: ../index.php?error=belum_login");
        exit();
    }

    // B. Cek Pengecualian (Bypass)
    // Jika halaman yang memanggil fungsi ini mendefinisikan 'SKIP_MAPEL_CHECK',
    // maka lewati pengecekan mapel. Ini KHUSUS untuk file 'pilih_mapel.php'
    // agar tidak terjadi infinite loop redirect.
    if (defined('SKIP_MAPEL_CHECK') && SKIP_MAPEL_CHECK === true) {
        return;
    }

    // C. Cek Apakah Sudah Pilih Mapel?
    // Jika mapel_id belum ada di sesi, PAKSA ke halaman pilih mapel
    if (!isset($_SESSION['mapel_id'])) {
        header("Location: ../student/pilih_mapel.php");
        exit();
    }
}

// 4. Helper API / AJAX Detection
// Cek apakah request datang dari Javascript (fetch/xhr)
function is_ajax_request() {
    // Cek header standar X-Requested-With (jQuery/Axios biasanya kirim ini)
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }
    
    // Cek header custom kita 'X-Partial-Render' (untuk Fetch API manual)
    if (isset($_SERVER['HTTP_X_PARTIAL_RENDER']) && $_SERVER['HTTP_X_PARTIAL_RENDER'] == 'true') {
        return true;
    }

    // Cek Header HTTP standar dari HTMX
    if (isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] == 'true') {
        return true;
    }

    return false;
}
// 5. SECURITY: Enforce App-Only Access (Satpam Akses)
// Memblokir akses dari browser HP biasa, tapi mengizinkan Desktop & Aplikasi Gara
function enforceAppAccess() {
    // A. CEK LOCALHOST (Developer Mode) - VIP PASS
    // Izinkan akses bebas jika diakses dari komputer lokal (127.0.0.1 / ::1)
    $whitelist_ip = ['127.0.0.1', '::1', 'localhost'];
    if (in_array($_SERVER['REMOTE_ADDR'], $whitelist_ip)) {
        return; // Lolos pemeriksaan
    }

    // B. Ambil Data Pengunjung
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // C. Deteksi Apakah Pengunjung Menggunakan Ponsel (Mobile)?
    // Regex mendeteksi Android, iPhone, iPod. Tablet/iPad seringkali dianggap mobile, 
    // tapi kita fokus ke pemblokiran browser HP.
    $isMobileDevice = preg_match('/(android|iphone|ipod|blackberry|iemobile|opera mini)/i', $ua);

    // D. Deteksi Apakah Pengunjung Membawa "Kunci Rahasia" Gara?
    // Mengecek string yang kita suntikkan di Android Studio tadi
    $isGaraApp = strpos($ua, 'GARA_OFFICIAL_APP') !== false;

    // E. LOGIKA VONIS (The Judge)
    // Blokir JIKA: Perangkatnya Mobile DAN BUKAN dari Aplikasi Gara
    if ($isMobileDevice && !$isGaraApp) {
        // Tampilkan Halaman 404 Palsu (Security by Obscurity)
        http_response_code(404);
        die('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
<hr>
<address>Apache/2.4.41 (Ubuntu) Server at garudakademi.ct.ws Port 80</address>
</body></html>');
    }
}

?>