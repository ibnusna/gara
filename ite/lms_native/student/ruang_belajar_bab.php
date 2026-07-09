<?php
// GARA - Garuda Akademi
// File: student/ruang_belajar_bab.php
// Tujuan: LEVEL 2 - Menampilkan Daftar Materi/Sub-Bab dalam satu BAB tertentu.
// Revisi: Ganti istilah "Pertemuan" -> "Bagian"

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
require_once '../actions/func_helper.php';

// 1. Cek Akses Siswa
requireSiswa();

// 2. Validasi Parameter URL
if (!isset($_GET['bab']) || empty($_GET['bab'])) {
    header("Location: ruang_belajar.php"); // Balik ke daftar bab jika tidak ada parameter
    exit();
}

$bab_ke   = intval($_GET['bab']);
$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];

// 3. Ambil Daftar Materi dalam Bab ini
// Urutkan berdasarkan 'bagian' (Bagian 1, 2, dst)
$sql = "SELECT * FROM rpp_materi 
        WHERE mapel_id = ? AND kelas_id = ? AND bab = ? 
        ORDER BY bagian ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mapel_id, $kelas_id, $bab_ke]);
    $materi_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $materi_list = [];
}

// Ambil Judul Bab dari materi pertama (untuk Header)
// Jika list kosong, set default.
$judul_bab = !empty($materi_list) ? $materi_list[0]['judul_materi'] : 'Materi Bab ' . $bab_ke;

$page_title = 'Bab ' . $bab_ke;
$page_title = 'Bab ' . $bab_ke;
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<!-- Header App: Logo Center, No Back Button Here (sesuai request) -->
<!-- Kita tetap butuh tombol Back di Level 2 ini karena ini masih navigasi list, 
     tapi jika Anda ingin konsisten tombol back di bawah, kita bisa sesuaikan. 
     Namun standar UX list view biasanya ada back di atas. 
     Untuk amannya, di level LIST SUB-BAB ini saya Biarkan tombol back kecil, 
     karena request "Hapus tombol back" spesifik untuk Halaman VIDEO agar tidak ketutup. -->
<div class="app-header">
    <div class="header-left">
        <a href="ruang_belajar.php" class="btn-back" hx-get="ruang_belajar.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Daftar Topik</h1>
        </div>
    </div>
</div>

<!-- Header Bab (Gradient Besar) -->
<div class="subbab-header">
    <p>BAB <?= formatNomorBab($bab_ke) ?></p>
    <!-- Kita gunakan judul materi pertama sebagai 'tema' bab jika tidak ada tabel bab khusus -->
    <h2>Topik Pembelajaran</h2> 
    <p style="opacity: 0.8; font-size: 0.85rem; margin-top: 5px;">
        Total <?= count($materi_list) ?> Bagian
    </p>
</div>

<!-- LEVEL 2: LIST MATERI -->
<div class="materi-list-group">
    
    <?php if (empty($materi_list)): ?>
        <div class="p-4 text-center text-muted">
            <i class="fas fa-box-open fa-2x mb-2"></i>
            <p>Belum ada topik di bab ini.</p>
        </div>
    <?php else: ?>

        <?php foreach($materi_list as $m): ?>
            <!-- Cek Status Selesai (Simulasi) -->
            <?php $is_completed = isMateriCompleted($m['id_materi']); ?>

            <!-- Link ke Level 3: Detail Materi -->
            <a href="materi_detail.php?id=<?= $m['id'] ?>" class="materi-item <?= $is_completed ? 'completed' : '' ?>"
               hx-get="materi_detail.php?id=<?= $m['id'] ?>" hx-target="#app-main" hx-push-url="true">
                
                <!-- Indikator Status (Centang Hijau / Bulat Kosong) -->
                <div class="status-indicator">
                    <?php if($is_completed): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <span style="font-size: 0.7rem; font-weight: 700;"><?= $m['bagian'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Info Materi -->
                <div class="materi-info">
                    <h4><?= htmlspecialchars($m['judul_materi']) ?></h4>
                    <span>Bagian ke-<?= $m['bagian'] ?></span>
                    
                    <!-- Icon Indikator Tipe Konten (Kecil) -->
                    <div style="margin-top: 5px; font-size: 0.75rem; color: #999;">
                        <?php if($m['link_youtube']): ?> <i class="fab fa-youtube text-danger me-2"></i> <?php endif; ?>
                        <?php if($m['link_ppt']): ?> <i class="fas fa-file-powerpoint text-warning me-2"></i> <?php endif; ?>
                        <?php if($m['link_modul']): ?> <i class="fas fa-file-pdf text-primary me-2"></i> <?php endif; ?>
                        <?php if($m['link_tugas']): ?> <i class="fas fa-tasks text-success me-2"></i> <?php endif; ?>
                    </div>
                </div>

                <!-- Chevron -->
                <div style="margin-left: auto; color: #ccc;">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<!-- Padding Bottom -->
<div style="height: 50px;"></div>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>