<?php
// GARA - Garuda Akademi
// File: student/ruang_ujian.php (V10 - Auto Redirect Optimization)

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

requireSiswa();

// 1. Ambil Data Sesi
$siswa_nis   = $_SESSION['nis'];
$nama_mapel  = $_SESSION['nama_mapel'];
$nama_kelas  = $_SESSION['nama_kelas'];

// URL Web Ujian (Netlify) & Portal
$exam_base_url = "https://garudakademi.netlify.app/summary.html";
$portal_url    = "https://garudakademi.netlify.app/"; 

// URL CSV
$csv_url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vRJHk7Z_Xn9ZZQsU3SwQgH8GqfkH08VdvTUuwFZnUvPJ9u_BVFZRxb_4z6CmTpqGHsqDQWPWAC8dBDY/pub?gid=381986173&single=true&output=csv";

// --- HELPER FUNCTIONS ---
function get_exams_from_csv($url) {
    if (!ini_get('allow_url_fopen')) return [];
    // Gunakan stream context untuk timeout agar tidak hanging jika internet lambat
    // REVISI V1.1: Timeout dinaikkan ke 10s agar lebih reliable
    $ctx = stream_context_create(array('http'=> array('timeout' => 10))); 
    // Tambahkan header anti-cache untuk fetch file (opsional, tergantung server target)
    // header("Cache-Control: no-cache"); // PHP file() context doesn't easily support headers without more verbose setup, but the URL param usually busts cache if needed.
    // Kita biarkan standard file(), tapi pastikan output halaman ini tidak dicache. 
    $csvData = @file($url, false, $ctx);
    if (!$csvData) return [];
    
    $rows = array_map('str_getcsv', $csvData);
    $header = array_shift($rows); 
    return $rows;
}

function normalize_kelas($kelas_str) {
    $str = strtoupper(trim($kelas_str));
    if (strpos($str, 'IX') !== false) return '9';
    if (strpos($str, 'VIII') !== false) return '8';
    if (strpos($str, 'VII') !== false) return '7';
    if (preg_match('/\d+/', $str, $matches)) return $matches[0];
    return '';
}

// --- PROSES DATA (BACKEND LOGIC) ---
$ujian_tersedia = [];
$ujian_tersedia = [];
// Pastikan tidak ada cache di level browser/HTMX
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$raw_data = get_exams_from_csv($csv_url);
$kelas_angka = normalize_kelas($nama_kelas);

// Normalisasi Mapel (Title Case)
$mapel_bersih = ucwords(strtolower($nama_mapel));
$kunci_pencarian = $mapel_bersih . '_' . $kelas_angka;
$kunci_pencarian_caps = strtoupper($nama_mapel) . '_' . $kelas_angka;

foreach ($raw_data as $row) {
    if (count($row) < 8) continue;
    $topik          = $row[1];
    $target_sheet   = trim($row[2]);
    $durasi         = $row[3];
    $token          = $row[4];
    $tampilan       = strtoupper(trim($row[7]));

    if ($tampilan !== 'MUNCUL' && $tampilan !== 'YA') continue;

    if (strcasecmp($target_sheet, $kunci_pencarian) === 0 || strcasecmp($target_sheet, $kunci_pencarian_caps) === 0) {
        $ujian_tersedia[] = [
            'topik' => $topik,
            'durasi' => $durasi,
            'token' => $token
        ];
    }
}

// ================================================================
// FITUR UX OPTIMIZATION: AUTO-REDIRECT
// ================================================================
// Jika hanya ada SATU ujian yang tersedia, langsung arahkan siswa 
// ke halaman ujian tanpa menampilkan daftar.
if (count($ujian_tersedia) === 1) {
    $ujian_tunggal = $ujian_tersedia[0];
    
    // Bangun URL Auto Login
    $link_auto = $exam_base_url . "?" . http_build_query([
        'nis' => $siswa_nis,
        'token' => $ujian_tunggal['token'],
        'auto' => 'true',
        'source' => 'lms'
    ]);
    
    // Server-side Redirect (Cepat & Efisien)
    // FIX: Handle HTMX Request differently
    if (is_ajax_request()) {
        // Gunakan HX-Redirect agar browser melakukan full page navigation ke URL eksternal
        header("HX-Redirect: " . $link_auto);
    } else {
        // Standard redirect untuk akses langsung
        header("Location: " . $link_auto);
    }
    exit(); // Hentikan script agar HTML di bawah tidak dirender
}
// ================================================================

// Jika 0 atau > 1 ujian, render tampilan di bawah ini
// Jika 0 atau > 1 ujian, render tampilan di bawah ini
$page_title = 'Ruang Ujian';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>
<link rel="stylesheet" href="css/root.css">
<!-- Load CSS Khusus Ujian -->
<link rel="stylesheet" href="css/ujian.css">

<div class="app-header">
    <div class="header-left">
        <a href="dashboard.php" class="btn-back" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Ruang Ujian</h1>
            <span class="page-subtitle"><?= htmlspecialchars($_SESSION['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
        </div>
    </div>
</div>

<div class="container-fluid" style="margin-top: 2rem;">

    <!-- 1. CARD UTAMA: PORTAL APLIKASI (Untuk Login Manual) -->
    <!-- Tetap ditampilkan jika user punya >1 ujian atau 0 ujian -->
    <div class="portal-card animate-up">
        <div class="portal-icon">
            <i class="fas fa-laptop-code"></i>
        </div>
        <h3 class="portal-title">Aplikasi Ujian Utama</h3>
        <p class="portal-desc">Akses portal ujian untuk login manual menggunakan NIS & Token.</p>
        
        <a href="<?= $portal_url ?>" target="_self" class="btn-portal">
            Buka Aplikasi <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- 2. DAFTAR TOKEN UJIAN -->
    <div class="section-label">Ujian Tersedia (<?= htmlspecialchars($mapel_bersih . ' ' . $kelas_angka) ?>)</div>

    <?php if (empty($ujian_tersedia)): ?>
        <div class="empty-state animate-up">
            <i class="far fa-folder-open"></i>
            <h5>Belum Ada Ujian</h5>
            <p class="small">Tidak ada jadwal ujian aktif untuk kelas Anda saat ini.</p>
        </div>
    <?php else: ?>
        <!-- Loop ini hanya jalan jika ujian > 1 -->
        <?php foreach ($ujian_tersedia as $ujian): ?>
            <?php
                $link_ujian = $exam_base_url . "?" . http_build_query([
                    'nis' => $siswa_nis,
                    'token' => $ujian['token'],
                    'auto' => 'true',
                    'source' => 'lms'
                ]);
            ?>
            <div class="exam-card animate-up">
                <div class="exam-header">
                    <div class="exam-info">
                        <h4><?= htmlspecialchars($ujian['topik']) ?></h4>
                        <div class="exam-badges">
                            <span class="badge-item badge-time">
                                <i class="far fa-clock"></i> <?= htmlspecialchars($ujian['durasi']) ?> Menit
                            </span>
                        </div>
                    </div>
                </div>

                <a href="<?= $link_ujian ?>" target="_self" class="btn-start-exam">
                    Mulai Kerjakan <i class="fas fa-play-circle ms-1"></i>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>