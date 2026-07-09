<?php
// GARA - Garuda Akademi
// File: admin/pilih_sesi.php
// Tujuan: Halaman portal guru untuk memilih mata pelajaran dan kelas yang akan dikelola.

require_once '../actions/auth_helper.php';

// Wajib Login sebagai Guru
requireGuru();

// Data Hardcoded Sesi Mengajar (Sesuai Konsep GARA)
$daftar_sesi = [
    // Kelompok IPA (Mapel ID: 1)
    [
        'mapel_id' => 1, 
        'kelas_id' => 1, 
        'judul' => 'IPA - Kelas 7', 
        'subjudul' => 'Ilmu Pengetahuan Alam',
        'icon' => 'fa-flask', 
        'bg' => 'icon-success'
    ],
    [
        'mapel_id' => 1, 
        'kelas_id' => 2, 
        'judul' => 'IPA - Kelas 8', 
        'subjudul' => 'Ilmu Pengetahuan Alam',
        'icon' => 'fa-flask', 
        'bg' => 'icon-success'
    ],
    [
        'mapel_id' => 1, 
        'kelas_id' => 3, 
        'judul' => 'IPA - Kelas 9', 
        'subjudul' => 'Ilmu Pengetahuan Alam',
        'icon' => 'fa-flask', 
        'bg' => 'icon-success'
    ],
    // Kelompok Informatika (Mapel ID: 2)
    [
        'mapel_id' => 2, 
        'kelas_id' => 1, 
        'judul' => 'Informatika - Kelas 7', 
        'subjudul' => 'Teknologi Informasi',
        'icon' => 'fa-laptop-code', 
        'bg' => 'icon-primary'
    ],
    [
        'mapel_id' => 2, 
        'kelas_id' => 2, 
        'judul' => 'Informatika - Kelas 8', 
        'subjudul' => 'Teknologi Informasi',
        'icon' => 'fa-laptop-code', 
        'bg' => 'icon-primary'
    ],
    [
        'mapel_id' => 2, 
        'kelas_id' => 3, 
        'judul' => 'Informatika - Kelas 9', 
        'subjudul' => 'Teknologi Informasi',
        'icon' => 'fa-laptop-code', 
        'bg' => 'icon-primary'
    ],
];
?>
<!DOCTYPE html>
<?php
$page_title = 'Pilih Sesi | GARA Admin';
require_once 'templates/header_portal.php';
?>

  <div class="gara-container">
    <div class="mb-4">
        <h2>Portal Guru</h2>
        <div class="gara-alert gara-alert-warning mt-3">
            <i class="fas fa-info-circle mt-1"></i>
            <div>
                <strong>Selamat Datang!</strong><br>
                Silakan pilih mata pelajaran dan kelas yang ingin Anda kelola hari ini.
            </div>
        </div>
    </div>

    <!-- Grid Sesi -->
    <div class="gara-grid cols-3">
        <?php foreach($daftar_sesi as $sesi): ?>
        
        <!-- Form Sesi -->
        <form action="../actions/set_session.php" method="POST" class="h-100">
            <input type="hidden" name="mapel_id" value="<?= $sesi['mapel_id'] ?>">
            <input type="hidden" name="kelas_id" value="<?= $sesi['kelas_id'] ?>">
            
            <button type="submit" class="gara-card interactive text-left w-100 h-100 border-0 text-left p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="gara-icon-box <?= $sesi['bg'] ?>">
                        <i class="fas <?= $sesi['icon'] ?>"></i>
                    </div>
                </div>
                
                <h4 class="gara-card-title mb-1"><?= $sesi['judul'] ?></h4>
                <p class="text-muted small mb-4"><?= $sesi['subjudul'] ?></p>
                
                <div class="mt-auto text-primary font-weight-bold small">
                    Masuk Ruang Kelas <i class="fas fa-arrow-right ml-1"></i>
                </div>
            </button>
        </form>

        <?php endforeach; ?>
    </div>
  </div>

<?php require_once 'templates/footer_portal.php'; ?>
