<?php
// GARA - Garuda Akademi
// File: admin/ruang_diskusi_arsip.php
// Tujuan: Menampilkan Diskusi yang Diarsipkan (Draft/Closed)

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
requireGuruSession();

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$nama_mapel = $_SESSION['nama_mapel'];

// --- LOGIC: Ambil Data Arsip (Status != 'aktif') ---
try {
    $sql = "SELECT 
                t.*, 
                s.nama as nama_siswa,
                (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
            FROM diskusi_threads t
            LEFT JOIN siswa s ON t.siswa_id = s.id
            WHERE t.mapel_id = ? AND t.kelas_id = ? AND t.status != 'aktif'
            ORDER BY t.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mapel_id, $kelas_id]);
    $archives = $stmt->fetchAll();

} catch (PDOException $e) { $archives = []; }

// Helper Waktu
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d > 7) return date('d/m/Y', strtotime($datetime));
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    return 'Baru saja';
}

$page_title = 'Arsip Diskusi | ' . htmlspecialchars($nama_mapel);
require_once 'templates/header.php';
require_once 'templates/sidebar.php';
?>

<!-- Import CSS Admin Khusus -->
<link rel="stylesheet" href="css/diskusi_admin.css?v=<?= time() ?>">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="content-wrapper">
    <div class="diskusi-wrapper">
        
        <!-- HEADER -->
        <div class="diskusi-header">
            <div class="diskusi-title">
                <h1>Arsip Diskusi</h1>
                <span>Daftar diskusi yang disembunyikan dari siswa.</span>
            </div>
            <a href="ruang_diskusi.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <!-- TAB NAVIGASI -->
        <div class="nav-tabs-custom">
            <a href="ruang_diskusi.php" class="nav-item-custom">Halaman Utama (Aktif)</a>
            <a href="ruang_diskusi_arsip.php" class="nav-item-custom active">Arsip & Draft</a>
        </div>

        <!-- LIST ARSIP -->
        <?php if(empty($archives)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3" style="opacity:0.3"></i>
                <h5 class="text-muted">Tidak ada arsip.</h5>
            </div>
        <?php else: ?>
            <div class="diskusi-grid">
                <?php foreach($archives as $t): 
                    $isGuru = ($t['role_pembuat'] === 'guru');
                    $nama = $isGuru ? 'Anda (Guru)' : htmlspecialchars($t['nama_siswa']);
                    $avatar = $isGuru ? '../img/guru.webp' : "https://ui-avatars.com/api/?name=".urlencode($nama)."&background=random&color=fff&size=40";
                ?>
                    <article class="post-card draft">
                        <div class="post-header mb-2">
                            <div class="user-info">
                                <img src="<?= $avatar ?>" class="user-avatar" style="width:35px;height:35px;filter:grayscale(100%);">
                                <div class="user-text">
                                    <h4 class="text-muted"><?= $nama ?> <span class="badge badge-secondary" style="font-size:0.6rem">ARSIP</span></h4>
                                    <span><?= time_elapsed_string($t['created_at']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Menu Restore/Delete -->
                            <div>
                                <form action="../actions/diskusi_manager.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="current_status" value="draft">
                                    <button class="btn btn-sm btn-success rounded-pill px-3" title="Aktifkan Kembali">
                                        <i class="fas fa-history mr-1"></i> Pulihkan
                                    </button>
                                </form>
                                <form action="../actions/diskusi_manager.php" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                    <input type="hidden" name="action" value="delete_thread_guru">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger rounded-circle ml-1" title="Hapus Permanen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="post-content text-muted">
                            <?= nl2br(htmlspecialchars($t['isi_konten'])) ?>
                        </div>
                        
                        <div class="post-footer py-2 mt-2" style="border-top:1px dashed #ddd;">
                            <small class="text-muted"><i class="far fa-comment-alt"></i> <?= $t['jumlah_balasan'] ?> Balasan tersimpan</small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>