<?php
// GARA - Garuda Akademi
// File: student/ruang_belajar.php
// Tujuan: LEVEL 1 - Menampilkan Daftar BAB (Chapter List)
// Deskripsi: Mengelompokkan materi berdasarkan kolom 'bab' dan mengambil judul dari materi pertama sebagai nama bab.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
require_once '../actions/func_helper.php'; // Helper baru kita

// 1. Cek Akses Siswa
requireSiswa();

// Ambil Konteks
$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];

// --- LOGIKA GROUPING BAB ---
// Query ini sedikit kompleks karena kita harus mengambil judul materi pertama (bagian terkecil)
// sebagai representasi Judul Bab, karena tidak ada tabel master 'bab'.
$sql = "SELECT 
            r1.bab, 
            r1.semester,
            COUNT(r1.id) as total_topik,
            (SELECT judul_materi 
             FROM rpp_materi r2 
             WHERE r2.mapel_id = r1.mapel_id 
               AND r2.kelas_id = r1.kelas_id 
               AND r2.bab = r1.bab 
             ORDER BY bagian ASC LIMIT 1) as nama_bab_otomatis
        FROM rpp_materi r1
        WHERE r1.mapel_id = ? AND r1.kelas_id = ?
GROUP BY r1.bab, r1.semester, r1.mapel_id, r1.kelas_id
        ORDER BY r1.bab ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mapel_id, $kelas_id]);
    $list_bab = $stmt->fetchAll();
} catch (PDOException $e) {
    $list_bab = [];
    $error = $e->getMessage();
}

// Set Page Title
$page_title = 'Materi Belajar';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<?php if (is_ajax_request()): ?>
    <!-- Inject CSS for Partial Render -->
    <link rel="stylesheet" href="css/belajar.css?v=<?= filemtime('css/belajar.css') ?>">
<?php endif; ?>

<!-- Header App -->
<div class="app-header">
    <div class="header-left">
        <a href="dashboard.php" class="btn-back" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Ruang Belajar</h1>
            <span class="page-subtitle"><?= htmlspecialchars($_SESSION['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
        </div>
    </div>
</div>

<!-- LEVEL 1: DAFTAR BAB -->
<div class="bab-list-container">

    <?php if (empty($list_bab)): ?>
        <div class="empty-state text-center py-5">
            <img src="../assets/img/3dlogo.svg" alt="Empty" style="width: 80px; opacity: 0.5; margin-bottom: 20px;">
            <h5 class="text-muted">Belum ada materi</h5>
            <p class="text-muted small">Guru belum mengupload materi untuk pelajaran ini.</p>
        </div>
    <?php else: ?>

        <?php foreach($list_bab as $b): ?>
            <!-- Kartu Bab -->
            <!-- Link menuju Level 2: List Sub-bab -->
            <a href="ruang_belajar_bab.php?bab=<?= $b['bab'] ?>" class="card-bab"
               hx-get="ruang_belajar_bab.php?bab=<?= $b['bab'] ?>" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
                
                <!-- Kotak Nomor Besar -->
                <div class="bab-number-box">
                    <span class="bab-label">BAB</span>
                    <span class="bab-digit"><?= formatNomorBab($b['bab']) ?></span>
                </div>

                <!-- Info Bab -->
                <div class="bab-info">
                    <div class="bab-meta">
                        <span>SEMESTER <?= $b['semester'] ?></span>
                        <span>&bull;</span>
                        <span><?= $b['total_topik'] ?> Topik</span>
                    </div>
                    
                    <!-- Judul Bab (Diambil dari materi pertama) -->
                    <h3 class="bab-title">
                        <?= htmlspecialchars($b['nama_bab_otomatis'] ?? 'Bab ' . $b['bab']) ?>
                    </h3>

                    <!-- Progress Bar (Dummy Static 0% dulu, nanti bisa di-connect logika progress) -->
                    <div class="progress-mini">
                        <div class="progress-bar-fill" style="width: 0%"></div> 
                    </div>
                </div>

                <!-- Panah Kanan -->
                <div class="bab-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<!-- Padding bottom agar tidak ketutup nav jika ada -->
<div style="height: 50px;"></div>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>