<?php
// GARA - Garuda Akademi
// File: admin/ruang_diskusi.php (V5 - Desktop Dashboard Redesign)
// Tujuan: Dashboard Moderasi Guru dengan Layout Grid & Fitur Lihat Balasan Internal

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
requireGuruSession();

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$nama_mapel = $_SESSION['nama_mapel'];

// --- 1. LOGIC: Handle Reply Admin (Self-Processing) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_guru') {
    try {
        $thread_id = $_POST['thread_id'];
        $isi = trim($_POST['isi_balasan']);
        
        if (!empty($isi)) {
            $sql = "INSERT INTO diskusi_replies (thread_id, isi_balasan, role_pembuat, created_at) VALUES (?, ?, 'guru', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$thread_id, $isi]);
            header("Location: ruang_diskusi.php"); 
            exit();
        }
    } catch (PDOException $e) { /* Silent fail */ }
}

// --- 2. LOGIC: Ambil Data Diskusi (Status Aktif Saja) ---
try {
    // Ambil Thread yang AKTIF saja. Arsip dipisah ke file lain.
    $sql = "SELECT 
                t.*, 
                s.nama as nama_siswa,
                s.nis as nis_siswa
            FROM diskusi_threads t
            LEFT JOIN siswa s ON t.siswa_id = s.id
            WHERE t.mapel_id = ? AND t.kelas_id = ? AND t.status = 'aktif'
            ORDER BY t.is_pinned DESC, t.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mapel_id, $kelas_id]);
    $threads = $stmt->fetchAll();

} catch (PDOException $e) { $threads = []; }

// Helper: Ambil Balasan untuk setiap Thread
function getReplies($pdo, $thread_id) {
    $sql = "SELECT r.*, s.nama as nama_siswa FROM diskusi_replies r 
            LEFT JOIN siswa s ON r.siswa_id = s.id 
            WHERE r.thread_id = ? ORDER BY r.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$thread_id]);
    return $stmt->fetchAll();
}

// Helper Waktu
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d > 7) return date('d/m/Y', strtotime($datetime));
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}

// --- TEMPLATE ---
$page_title = 'Diskusi Kelas | ' . htmlspecialchars($nama_mapel);
require_once 'templates/header.php';
require_once 'templates/sidebar.php';
?>

<!-- Import CSS Admin Khusus -->
<link rel="stylesheet" href="css/diskusi_admin.css?v=<?= time() ?>">
<!-- Font Awesome untuk Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Inline Style untuk Preview Gambar (Agar persis Siswa) -->
<style>
    .image-preview-container {
        position: relative;
        margin-top: 10px;
        display: none; /* Hidden by default */
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eff3f4;
    }
    .image-preview-container img {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: cover;
        display: block;
    }
    .btn-remove-image {
        position: absolute;
        top: 8px; 
        left: 8px;
        background: rgba(0,0,0,0.75);
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px; 
        height: 32px;
        cursor: pointer;
        display: flex; 
        align-items: center; 
        justify-content: center;
        transition: background 0.2s;
    }
    .btn-remove-image:hover {
        background: rgba(0,0,0,0.9);
    }
</style>

<div class="content-wrapper">
    <div class="diskusi-wrapper">
        
        <!-- HEADER -->
        <div class="diskusi-header">
            <div class="diskusi-title">
                <h1>Diskusi Kelas</h1>
                <span>Manajemen diskusi aktif untuk <?= htmlspecialchars($nama_mapel) ?></span>
            </div>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" data-toggle="modal" data-target="#modalPostAdmin">
                <i class="fas fa-plus mr-2"></i> Buat Topik
            </button>
        </div>

        <!-- TAB NAVIGASI -->
        <div class="nav-tabs-custom">
            <a href="ruang_diskusi.php" class="nav-item-custom active">Halaman Utama (Aktif)</a>
            <a href="ruang_diskusi_arsip.php" class="nav-item-custom">Arsip & Draft</a>
        </div>

        <!-- GRID CONTENT -->
        <div class="diskusi-grid">
            <?php if(empty($threads)): ?>
                <div class="col-12 text-center py-5">
                    <img src="../assets/img/3dlogo.svg" style="height:100px; opacity:0.2; margin-bottom:20px;">
                    <h5 class="text-muted">Belum ada diskusi aktif.</h5>
                    <p class="text-muted small">Buat topik baru untuk memulai interaksi kelas.</p>
                </div>
            <?php else: ?>
                <?php foreach($threads as $t): 
                    $isGuru = ($t['role_pembuat'] === 'guru');
                    // FIX: Handle Null Name (Deleted Student)
                    $rawNama = $t['nama_siswa'] ?? 'Siswa Tidak Dikenal';
                    $nama = $isGuru ? 'Ibnu Sina Sudrajat' : htmlspecialchars($rawNama);
                    $avatar = $isGuru ? '../img/guru.webp' : "https://api.dicebear.com/9.x/fun-emoji/svg?seed=".urlencode($rawNama);
                    $badgeClass = $isGuru ? 'badge-guru' : 'badge-siswa';
                    $badgeLabel = $isGuru ? 'GURU' : 'SISWA';
                    $isPinned = ($t['is_pinned'] == 1);
                    
                    // Ambil Balasan
                    $replies = getReplies($pdo, $t['id']);
                    $replyCount = count($replies);
                ?>
                    <!-- KARTU DISKUSI -->
                    <article class="post-card <?= $isPinned ? 'pinned' : '' ?>">
                        <!-- Header Kartu -->
                        <div class="post-header">
                            <div class="user-info">
                                <img src="<?= $avatar ?>" class="user-avatar">
                                <div class="user-text">
                                    <h4><?= $nama ?> <span class="post-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></h4>
                                    <span><?= time_elapsed_string($t['created_at']) ?></span>
                                </div>
                            </div>
                            <!-- Menu Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm text-secondary" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                <div class="dropdown-menu dropdown-menu-right shadow">
                                    <form action="../actions/diskusi_manager.php" method="POST">
                                        <input type="hidden" name="action" value="toggle_pin">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <input type="hidden" name="current_pin" value="<?= $t['is_pinned'] ?>">
                                        <button class="dropdown-item small"><i class="fas fa-thumbtack mr-2 text-warning"></i> <?= $isPinned ? 'Lepas Pin' : 'Sematkan' ?></button>
                                    </form>
                                    <form action="../actions/diskusi_manager.php" method="POST">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <input type="hidden" name="current_status" value="aktif">
                                        <button class="dropdown-item small"><i class="fas fa-archive mr-2 text-primary"></i> Arsipkan</button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form action="../actions/diskusi_manager.php" method="POST" onsubmit="return confirm('Hapus permanen?')">
                                        <input type="hidden" name="action" value="delete_thread_guru">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button class="dropdown-item small text-danger"><i class="fas fa-trash mr-2"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Isi Konten -->
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($t['isi_konten'])) ?>
                            <?php if($t['media_path']): ?>
                                <img src="../<?= $t['media_path'] ?>" class="post-image" onclick="window.open(this.src)">
                            <?php endif; ?>
                        </div>

                        <!-- Footer & Aksi Cepat -->
                        <div class="post-footer">
                            <div class="stat-group">
                                <div class="stat-item" style="cursor:pointer;" onclick="toggleComments(<?= $t['id'] ?>)">
                                    <i class="far fa-comment-alt"></i> <?= $replyCount ?> Balasan
                                </div>
                            </div>
                            <div class="btn-group-admin">
                                <button class="btn-action" onclick="toggleComments(<?= $t['id'] ?>)" title="Lihat/Balas"><i class="fas fa-reply"></i></button>
                                <?php if($isPinned): ?><span class="text-warning small align-self-center mr-2"><i class="fas fa-thumbtack"></i></span><?php endif; ?>
                            </div>
                        </div>

                        <!-- BAGIAN KOMENTAR (Hidden by default) -->
                        <div id="comments-<?= $t['id'] ?>" class="comments-section">
                            <!-- List Balasan -->
                            <?php if(empty($replies)): ?>
                                <p class="text-muted small text-center mb-3">Belum ada balasan.</p>
                            <?php else: ?>
                                <?php foreach($replies as $r): 
                                    $isGuruReply = ($r['role_pembuat'] === 'guru');
                                    // FIX: Handle Null Name (Deleted Student)
                                    $rawNamaReply = $r['nama_siswa'] ?? 'Siswa Tidak Dikenal';
                                    $rNama = $isGuruReply ? 'Guru' : htmlspecialchars($rawNamaReply);
                                    $rAvatar = $isGuruReply ? '../img/guru.webp' : "https://api.dicebear.com/9.x/fun-emoji/svg?seed=".urlencode($rawNamaReply);
                                    $bgReply = $isGuruReply ? '#e3f2fd' : 'white';
                                ?>
                                    <div class="comment-item">
                                        <img src="<?= $rAvatar ?>" class="comment-avatar">
                                        <div class="comment-box" style="background:<?= $bgReply ?>">
                                            <div class="comment-header">
                                                <strong><?= $rNama ?></strong>
                                                <span><?= time_elapsed_string($r['created_at']) ?></span>
                                            </div>
                                            <?= nl2br(htmlspecialchars($r['isi_balasan'])) ?>
                                            <?php if($r['media_path']): ?><br><a href="../<?= $r['media_path'] ?>" target="_blank" class="small text-primary">[Lihat Gambar]</a><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Form Balas Admin -->
                            <form action="" method="POST" class="mt-2 d-flex">
                                <input type="hidden" name="action" value="reply_guru">
                                <input type="hidden" name="thread_id" value="<?= $t['id'] ?>">
                                <input type="text" name="isi_balasan" class="form-control form-control-sm rounded-pill mr-2" placeholder="Tulis balasan..." required>
                                <button class="btn btn-sm btn-primary rounded-circle"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>

                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

<!-- MODAL BUAT TOPIK (REVISI UI/UX) -->
<div class="modal fade" id="modalPostAdmin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <!-- Header Minimalis -->
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <button type="button" class="close" data-dismiss="modal" style="opacity:1;">
                    <i class="fas fa-times text-dark"></i>
                </button>
            </div>
            
            <div class="modal-body pt-0 px-3 pb-3">
                <form action="../actions/diskusi_manager.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_thread_guru">
                    
                    <!-- Area Input -->
                    <div class="d-flex gap-3">
                        <!-- Avatar Guru -->
                        <div class="mr-3">
                            <img src="../img/guru.webp" class="rounded-circle border" width="48" height="48" style="object-fit:cover;">
                        </div>
                        
                        <!-- Input Wrapper -->
                        <div style="flex:1;">
                            <!-- Textarea (Tanpa Judul) -->
                            <textarea name="isi_konten" class="form-control border-0 p-0" rows="4" 
                                placeholder="Apa yang ingin didiskusikan hari ini?" 
                                style="resize:none; font-size:1.1rem; box-shadow:none; background:transparent;"></textarea>
                            
                            <!-- Image Preview (Hidden by default) -->
                            <div id="previewContainer" class="image-preview-container">
                                <button type="button" class="btn-remove-image" id="btnRemoveImage">
                                    <i class="fas fa-times"></i>
                                </button>
                                <img id="imagePreview" src="" alt="Preview">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3 flex-wrap">
                        <div class="d-flex align-items-center w-auto modal-actions-left">
                            <!-- Tombol Upload Icon -->
                            <label class="btn text-primary rounded-circle mb-0 mr-2 p-2" style="cursor:pointer;" title="Media">
                                <i class="far fa-image fa-lg"></i>
                                <input type="file" id="fileInput" name="media" accept="image/*" hidden>
                            </label>
                            
                            <!-- Toggle Pin -->
                            <div class="custom-control custom-switch d-inline-block">
                                <input type="checkbox" class="custom-control-input" id="pinSwitch" name="is_pinned" value="1">
                                <label class="custom-control-label small pt-1 text-secondary" for="pinSwitch">Pin</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold w-auto btn-posting">Posting</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Logic Toggle Comments
function toggleComments(id) {
    $('#comments-' + id).slideToggle('fast');
}

// Logic Image Preview (Persis Siswa)
document.getElementById('fileInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

document.getElementById('btnRemoveImage').addEventListener('click', function() {
    document.getElementById('fileInput').value = ''; // Reset input
    document.getElementById('previewContainer').style.display = 'none'; // Hide preview
    document.getElementById('imagePreview').src = '';
});
</script>