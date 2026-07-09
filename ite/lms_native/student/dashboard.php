<?php
// GARA - Garuda Akademi
// File: student/dashboard.php (V2 - Modular & Integrated)
// Tujuan: Halaman utama siswa yang memanggil berbagai modul UI/UX baru.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

// 1. Cek Akses & Sesi
requireSiswa();

// 2. Cek Pengumuman Aktif (Fitur Legacy yang dipertahankan)
// Agar siswa tetap tahu info dari guru meskipun dashboard berubah tampilan
// 2. Cek Pengumuman Aktif (Sekarang via Async API)
// Logic lama dipindahkan ke modules/api_pengumuman.php untuk mempercepat load render awal (First Paint)
$infoKelas = null; // Default null, nanti diisi oleh JS/API


$page_title = 'Dashboard Siswa | GARA';

// CSS Optimization (Fix FOUC)
// Load CSS via header helper to ensure it's in <head>
$additional_css = [
    'css/root.css',
    'css/dashboard_v2.css'
];

if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<?php if (is_ajax_request()): ?>
    <!-- Inject CSS for Partial Render -->
    <link rel="stylesheet" href="css/root.css?v=<?= filemtime('css/root.css') ?>">
    <link rel="stylesheet" href="css/dashboard_v2.css?v=<?= filemtime('css/dashboard_v2.css') ?>">
<?php endif; ?>


<div class="dashboard-container">

    <!-- MODUL 1: HERO SECTION (Greeting & Motivasi) -->
    <?php include 'modules/hero_section.php'; ?>

    <!-- MODUL 2: PENGUMUMAN (Async) -->
    <!-- Wadah untuk pengumuman. Default KOSONG (No Skeleton). Akan di-replace JS jika ada data. -->
    <div id="pengumuman-container"></div>

    <!-- MODUL 3: MENU UTAMA (Grid Navigasi) -->
    <?php include 'modules/menu_grid.php'; ?>

    <!-- MODUL 4: WIDGET API (Jadwal Sholat & Trivia) -->
    <div class="section-label mt-2">Zona Produktif</div>
    <div class="api-widget-grid">
        <!-- Kolom Kiri: Jadwal Sholat -->
        <div class="widget-wrapper">
            <?php include 'modules/widget_api_sholat.php'; ?>
        </div>

        <!-- Kolom Kanan: Brain Warmup (Trivia) -->
        <div class="widget-wrapper">
            <?php include 'modules/widget_api_trivia.php'; ?>
        </div>
    </div>

    <!-- MODUL 5: EXTERNAL LINKS (Jelajah Ilmu) -->
    <?php include 'modules/widget_external.php'; ?>

</div>

<!-- LOAD JS V2 (Di akhir body) -->
<!-- LOAD JS V2 (Di akhir body) -->
<script src="js/dashboard_v2.js?v=<?= filemtime('js/dashboard_v2.js') ?>"></script>

<?php
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>