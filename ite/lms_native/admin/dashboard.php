<?php
// GARA - Garuda Akademi
// File: admin/dashboard.php (V2 - Dengan Fitur Pengumuman)
// Tujuan: Halaman utama manajemen kelas dengan widget Pengumuman.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

// 1. Cek Sesi Guru (Wajib sudah pilih mapel & kelas)
requireGuruSession();

// Ambil data sesi
$nama_mapel = $_SESSION['nama_mapel'];
$nama_kelas = $_SESSION['nama_kelas'];
$mapel_id   = $_SESSION['mapel_id'];
$kelas_id   = $_SESSION['kelas_id'];

try {
    // 1. Hitung Statistik
    $stmtMateri = $pdo->prepare("SELECT COUNT(*) FROM rpp_materi WHERE mapel_id = ? AND kelas_id = ?");
    $stmtMateri->execute([$mapel_id, $kelas_id]);
    $total_materi = $stmtMateri->fetchColumn();

    $stmtSiswa = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ?");
    $stmtSiswa->execute([$kelas_id]);
    $total_siswa = $stmtSiswa->fetchColumn();

    // 2. Ambil Pengumuman Aktif (Jika ada)
    $stmtInfo = $pdo->prepare("SELECT * FROM pengumuman WHERE mapel_id = ? AND kelas_id = ?");
    $stmtInfo->execute([$mapel_id, $kelas_id]);
    $pengumuman = $stmtInfo->fetch();

} catch (PDOException $e) {
    $total_materi = 0; $total_siswa = 0; $pengumuman = null;
}
?>
<!DOCTYPE html>
<?php
$page_title = 'Dashboard ' . htmlspecialchars($nama_mapel) . ' | GARA Admin';
$active_menu = 'dashboard';
require_once 'templates/header.php';
require_once 'templates/sidebar.php';
?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">Ringkasan Kelas</h1></div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                
                <!-- Info Boxes -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-book"></i></span>
                            <div class="info-box-content"><span class="info-box-text">Materi</span><span class="info-box-number"><?= $total_materi ?></span></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
                            <div class="info-box-content"><span class="info-box-text">Siswa</span><span class="info-box-number"><?= $total_siswa ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Col: Welcome -->
                    <div class="col-md-7">
                        <div class="card card-outline card-primary">
                            <div class="card-header"><h3 class="card-title">Selamat Datang</h3></div>
                            <div class="card-body">
                                <p class="lead">Anda berada di kelas <strong><?= htmlspecialchars($nama_kelas) ?></strong> mata pelajaran <strong><?= htmlspecialchars($nama_mapel) ?></strong>.</p>
                                <p>Gunakan menu di sebelah kiri untuk mengelola pembelajaran.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Pengumuman Widget -->
                    <div class="col-md-5">
                        <div class="card card-outline card-danger">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> Pengumuman Kelas</h3>
                            </div>
                            <div class="card-body">
                                <form action="../actions/pengumuman_manager.php" method="POST">
                                    <input type="hidden" name="action" value="save">
                                    
                                    <div class="form-group">
                                        <label>Judul Info</label>
                                        <input type="text" name="judul" class="form-control" required placeholder="Contoh: Ulangan Harian Senin Depan" 
                                            value="<?= htmlspecialchars($pengumuman['judul'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Isi Pesan</label>
                                        <textarea name="isi" class="form-control" rows="3" required placeholder="Tulis pesan untuk semua siswa..."><?= htmlspecialchars($pengumuman['isi'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-paper-plane mr-1"></i> Publikasikan Info
                                    </button>
                                </form>

                                <!-- Tombol Hapus (Hanya muncul jika ada pengumuman) -->
                                <?php if($pengumuman): ?>
                                    <form action="../actions/pengumuman_manager.php" method="POST" class="mt-2" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-outline-danger btn-block btn-sm">
                                            <i class="fas fa-trash mr-1"></i> Hapus Pengumuman
                                        </button>
                                        <small class="text-muted d-block text-center mt-1">Terakhir update: <?= date('d M H:i', strtotime($pengumuman['updated_at'])) ?></small>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

<?php require_once 'templates/footer.php'; ?>
