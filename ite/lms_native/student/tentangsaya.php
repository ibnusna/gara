<?php
// GARA - Garuda Akademi
// File: student/tentangsaya.php
// Tujuan: Profil Siswa, Ganti Password, & Info Guru

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
require_once '../components/avatar_helper.php';

requireSiswa();

$siswa_id   = $_SESSION['user_id'];
$nama_siswa = $_SESSION['nama'];
$nis_siswa  = $_SESSION['nis'] ?? '-';
$nama_kelas = $_SESSION['nama_kelas'];
$mapel_id   = $_SESSION['mapel_id'];

// --- LOGIKA GANTI PASSWORD (SELF-CONTAINED) ---
$pesan_sukses = '';
$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_ganti_pass'])) {
    $pass_lama  = $_POST['pass_lama'] ?? '';
    $pass_baru  = $_POST['pass_baru'] ?? '';
    $pass_konf  = $_POST['pass_konf'] ?? '';

    if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
        $pesan_error = "Semua kolom password wajib diisi.";
    } elseif ($pass_baru !== $pass_konf) {
        $pesan_error = "Konfirmasi password baru tidak cocok.";
    } elseif (strlen($pass_baru) < 6) {
        $pesan_error = "Password baru minimal 6 karakter.";
    } else {
        // Cek Password Lama di Database
        $stmt = $pdo->prepare("SELECT password FROM siswa WHERE id = ?");
        $stmt->execute([$siswa_id]);
        $data_user = $stmt->fetch();

        if ($data_user && password_verify($pass_lama, $data_user['password'])) {
            // Hash Password Baru & Update
            $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE siswa SET password = ? WHERE id = ?");
            if ($update->execute([$hash_baru, $siswa_id])) {
                $pesan_sukses = "Password berhasil diperbarui! Silakan ingat password baru Anda.";
            } else {
                $pesan_error = "Gagal mengupdate database.";
            }
        } else {
            $pesan_error = "Password lama Anda salah.";
        }
    }
}
// ----------------------------------------------

?>
<!DOCTYPE html>
<?php
$page_title = 'Tentang Saya';
$page_title = 'Tentang Saya';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<style>
    .profile-container { max-width: 500px; margin: 0 auto; padding-bottom: 50px; }
    
    .profile-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #f0f2f5;
    }
    
    .siswa-header { text-align: center; margin-bottom: 20px; }
    .siswa-avatar { 
        width: 100px; height: 100px; 
        border-radius: 50%; 
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    .siswa-name { font-weight: 700; font-size: 1.2rem; color: #212529; margin-bottom: 2px; }
    .siswa-nis { color: #6c757d; font-size: 0.9rem; letter-spacing: 1px; }

    .info-row {
        display: flex; justify-content: space-between; padding: 12px 0;
        border-bottom: 1px solid #f8f9fa; font-size: 0.95rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6c757d; }
    .info-val { font-weight: 600; color: #212529; }

    .section-title {
        font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;
        color: #adb5bd; font-weight: 700; margin-bottom: 10px; padding-left: 10px;
    }

    /* Style Guru */
    .guru-card { display: flex; align-items: center; justify-content: space-between; }
    .guru-info { flex: 1; }
    .guru-name { font-weight: 700; font-size: 1rem; color: #000; display: flex; align-items: center; }
    .guru-verified { color: #0095f6; margin-left: 4px; font-size: 0.8rem; }
    .guru-bio { font-size: 0.9rem; color: #333; margin-top: 4px; line-height: 1.4; }
    .guru-link { font-size: 0.9rem; color: #6c757d; margin-top: 5px; display: block; text-decoration: none; }
    .guru-link:hover { text-decoration: underline; }
    .guru-avatar { 
        width: 70px; height: 70px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 1px solid #efefef;
        margin-left: 15px;
    }

    /* Form Password */
    .form-control-custom {
        background: #f8f9fa; border: 1px solid #e9ecef;
        border-radius: 10px; padding: 10px 15px; font-size: 0.9rem;
    }
    .form-control-custom:focus {
        background: #fff; border-color: #0056b3; box-shadow: none;
    }
</style>

<div class="header-simple px-3 py-3 bg-white border-bottom sticky-top mb-3">
    <div class="d-flex align-items-center justify-content-center position-relative">
        <a href="dashboard.php" class="text-dark position-absolute start-0" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard" style="left: 0;"><i class="fas fa-arrow-left fa-lg"></i></a>
        <h6 class="m-0 fw-bold">Profil Akun</h6>
    </div>
</div>

<div class="profile-container px-3 text-center">
    
    <!-- Notifikasi PHP -->
    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success rounded-3 small border-0 shadow-sm mb-3">
            <i class="fas fa-check-circle me-2"></i> <?= $pesan_sukses ?>
        </div>
    <?php endif; ?>

    <?php if ($pesan_error): ?>
        <div class="alert alert-danger rounded-3 small border-0 shadow-sm mb-3">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $pesan_error ?>
        </div>
    <?php endif; ?>

    <!-- BAGIAN 1: SISWA -->
    <div class="section-title">Informasi Siswa</div>
    <div class="profile-card text-start">
        <div class="siswa-header">
            <img src="<?= getAvatar($nama_siswa, 128) ?>" class="siswa-avatar" alt="Profil">
            <div class="siswa-name"><?= htmlspecialchars($nama_siswa) ?></div>
            <div class="siswa-nis">NIS: <?= htmlspecialchars($nis_siswa) ?></div>
        </div>
        
        <div class="mt-4">
            <div class="info-row">
                <span class="info-label">Kelas Saat Ini</span>
                <span class="info-val text-primary"><?= htmlspecialchars($nama_kelas) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Akun</span>
                <span class="info-val text-success"><i class="fas fa-check-circle small"></i> Aktif</span>
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: GANTI PASSWORD -->
    <div class="section-title mt-4">Keamanan Akun</div>
    <div class="profile-card text-start">
        <form action="" method="POST" hx-post="" hx-target="#app-main" hx-swap="innerHTML">
            <input type="hidden" name="aksi_ganti_pass" value="1">
            
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Password Lama</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3 ps-3"><i class="fas fa-lock text-muted small"></i></span>
                    <input type="password" name="pass_lama" class="form-control form-control-custom border-start-0 border-end-0" placeholder="Ketik password lama..." required>
                    <button type="button" class="btn btn-light border rounded-end-3 toggle-password" data-target="pass_lama"><i class="fas fa-eye text-muted small"></i></button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3 ps-3"><i class="fas fa-key text-muted small"></i></span>
                    <input type="password" name="pass_baru" class="form-control form-control-custom border-start-0 border-end-0" placeholder="Minimal 6 karakter" required>
                    <button type="button" class="btn btn-light border rounded-end-3 toggle-password" data-target="pass_baru"><i class="fas fa-eye text-muted small"></i></button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-muted fw-bold">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3 ps-3"><i class="fas fa-check-double text-muted small"></i></span>
                    <input type="password" name="pass_konf" class="form-control form-control-custom border-start-0 border-end-0" placeholder="Ketik ulang password baru" required>
                    <button type="button" class="btn btn-light border rounded-end-3 toggle-password" data-target="pass_konf"><i class="fas fa-eye text-muted small"></i></button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                <i class="fas fa-save me-2"></i> Simpan Password Baru
            </button>
        </form>
    </div>

    <style>
        .toggle-password { transition: all 0.3s ease; }
        .toggle-password:hover { background-color: #e9ecef !important; }
        .toggle-password i { transition: opacity 0.3s ease; }
    </style>

    <script>
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const input = document.querySelector(`input[name="${targetId}"]`);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>

    <!-- BAGIAN 3: GURU (STATIC) -->
    <div class="section-title mt-4">Pengajar Mata Pelajaran</div>
    <div class="profile-card text-start">
        <div class="guru-card">
            <div class="guru-info">
                <div class="guru-name">
                    Ibnu Sina Sudrajat <i class="fas fa-check-circle guru-verified"></i>
                </div>
                <div class="guru-bio">
                    Pengampu Mata Pelajaran Informatika & IPA.
                </div>
                <a href="https://ibnusinasudrajat.netlify.app" target="_blank" class="guru-link">
                    <i class="fas fa-link small me-1"></i> ibnusinasudrajat.netlify.app
                </a>
            </div>
            <img src="../img/guru.webp" class="guru-avatar" alt="Guru" onerror="this.src='https://ui-avatars.com/api/?name=Ibnu+Sina&background=000&color=fff'">
        </div>
        
        <hr class="my-3 opacity-25">
        
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Ingin bertanya seputar materi?</small>
            <a href="ruang_diskusi.php" class="btn btn-sm btn-outline-dark rounded-pill px-3"
               hx-get="ruang_diskusi.php" hx-target="#app-main" hx-push-url="true">Diskusi</a>
        </div>
    </div>

    <div class="text-center mt-4 mb-5">
        <a href="pilih_mapel.php" class="btn btn-danger w-100 py-2 rounded-pill fw-bold">
            <i class="fas fa-sign-out-alt me-2"></i>Ganti Mapel
        </a>
    </div>



<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>