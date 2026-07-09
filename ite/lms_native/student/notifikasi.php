<?php
// GARA - Garuda Akademi
// File: student/notifikasi.php
// Konsep: Agregasi Data Live (Tanpa Tabel Notifikasi Khusus)

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
requireSiswa();

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];

// --- LOGIKA AGREGASI NOTIFIKASI ---
$notifikasi = [];

try {
    // 1. Ambil 5 Tugas Terbaru (Status Aktif)
    $stmt = $pdo->prepare("SELECT id, judul, created_at, 'tugas' as tipe FROM tugas WHERE mapel_id = ? AND kelas_id = ? AND status = 'aktif' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$mapel_id, $kelas_id]);
    $tugas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Ambil 5 Diskusi Terbaru (Status Aktif)
    $stmt = $pdo->prepare("SELECT id, judul, created_at, 'diskusi' as tipe FROM diskusi_threads WHERE mapel_id = ? AND kelas_id = ? AND status = 'aktif' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$mapel_id, $kelas_id]);
    $diskusi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Gabung dan Urutkan
    $notifikasi = array_merge($tugas, $diskusi);
    
    // Sort desc berdasarkan created_at
    usort($notifikasi, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

} catch (PDOException $e) {
    // Silent fail, list kosong
}
?>
<!DOCTYPE html>
<?php
$page_title = 'Pemberitahuan';
$page_title = 'Pemberitahuan';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<style>
    .notif-list { max-width: 600px; margin: 0 auto; }
    .notif-item {
        background: white;
        padding: 15px;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        align-items: flex-start;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s;
    }
    .notif-item:active { background: #f8f9fa; }
    .icon-box {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px; flex-shrink: 0;
    }
    .icon-tugas { background-color: #e8f5e9; color: #28a745; }
    .icon-diskusi { background-color: #e3f2fd; color: #0d6efd; }
    
    .notif-content { flex: 1; }
    .notif-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 2px; color: #212529; }
    .notif-desc { font-size: 0.85rem; color: #6c757d; }
    .notif-time { font-size: 0.75rem; color: #adb5bd; margin-top: 5px; }
    
    .header-simple {
        background: white; padding: 15px 20px; border-bottom: 1px solid #e4e6eb;
        position: sticky; top: 0; z-index: 100;
    }
</style>

<div class="header-simple d-flex align-items-center">
    <a href="dashboard.php" class="text-dark me-3" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard"><i class="fas fa-arrow-left"></i></a>
    <h6 class="m-0 fw-bold">Aktivitas Terbaru</h6>
</div>

<div class="notif-list">
    <?php if (empty($notifikasi)): ?>
        <div class="text-center py-5 text-muted">
            <i class="far fa-bell-slash fa-3x mb-3 text-light-gray"></i>
            <p>Belum ada pemberitahuan baru.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifikasi as $n): 
            $is_tugas = ($n['tipe'] == 'tugas');
            $icon = $is_tugas ? 'fas fa-clipboard-list' : 'fas fa-comments';
            $bg_cls = $is_tugas ? 'icon-tugas' : 'icon-diskusi';
            $text = $is_tugas ? 'Tugas baru diberikan' : 'Topik diskusi baru';
            $link = $is_tugas ? 'ruang_tugas.php' : 'ruang_diskusi.php'; // Bisa diarahkan ke detail ID jika mau
        ?>
        <a href="<?= $link ?>" class="notif-item" hx-get="<?= $link ?>" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
            <div class="icon-box <?= $bg_cls ?>">
                <i class="<?= $icon ?>"></i>
            </div>
            <div class="notif-content">
                <div class="notif-desc"><?= $text ?></div>
                <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
                <div class="notif-time"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
            </div>
            <?php if(strtotime($n['created_at']) > strtotime('-1 day')): ?>
                <span class="badge bg-danger rounded-circle p-1" style="width:8px;height:8px;"> </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>