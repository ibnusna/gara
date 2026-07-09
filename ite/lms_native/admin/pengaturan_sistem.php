<?php
// GARA - Garuda Akademi
// File: admin/pengaturan_sistem.php
// Tujuan: Control Panel untuk Ganti Semester & Kenaikan Kelas.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

requireGuru();

// Ambil Status Semester Saat Ini
$semester_aktif = '1'; // Default
try {
    $stmt = $pdo->query("SELECT setting_value FROM app_settings WHERE setting_key = 'semester_aktif'");
    $row = $stmt->fetch();
    if ($row) $semester_aktif = $row['setting_value'];
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Siklus Akademik | GARA Admin</title>
    <!-- Dependencies -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
    <div class="container">
      <a href="pengaturan_index.php" class="navbar-brand">
        <span class="brand-text font-weight-bold text-dark"><i class="fas fa-cogs text-primary mr-1"></i> Pengaturan</span>
      </a>
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        <li class="nav-item">
            <a href="pengaturan_index.php" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Menu
            </a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0"> Siklus Akademik</h1></div>
        </div>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i> 
            <strong>Area Berbahaya!</strong> Perubahan di sini mempengaruhi seluruh sistem dan data siswa. Harap berhati-hati.
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container">
        
        <!-- STATUS SAAT INI -->
        <div class="card card-primary card-outline">
            <div class="card-body text-center">
                <h3>Semester Aktif Saat Ini:</h3>
                <h1 class="display-4 font-weight-bold text-primary">SEMESTER <?= $semester_aktif ?></h1>
                <p class="text-muted">Tahun Ajaran Berjalan</p>
            </div>
        </div>

        <div class="row">
            <!-- 1. GANTI SEMESTER -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i> Pergantian Semester</h3>
                    </div>
                    <div class="card-body">
                        <p>Fitur ini digunakan saat pertengahan tahun ajaran (misal dari Ganjil ke Genap).</p>
                        <ul>
                            <li>Materi/RPP <strong>TIDAK</strong> dihapus.</li>
                            <li>Data Diskusi & Pengumpulan Tugas <strong>AKAN DIHAPUS</strong> (Reset).</li>
                            <li>Siswa tetap di kelas yang sama.</li>
                        </ul>
                        <hr>
                        <?php if($semester_aktif == '1'): ?>
                            <button class="btn btn-block btn-outline-info" onclick="confirmSemester(2)">
                                Pindah ke Semester 2 <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-block btn-outline-info" onclick="confirmSemester(1)">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Semester 1
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. KENAIKAN KELAS -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title"><i class="fas fa-level-up-alt mr-2"></i> Kenaikan Kelas (Akhir Tahun)</h3>
                    </div>
                    <div class="card-body">
                        <p>Fitur ini digunakan saat <strong>Tahun Ajaran Baru</strong> dimulai.</p>
                        <ul class="text-danger small font-weight-bold">
                            <li>Siswa Kelas 9 akan DIHAPUS PERMANEN (Lulus).</li>
                            <li>Siswa Kelas 8 naik ke Kelas 9.</li>
                            <li>Siswa Kelas 7 naik ke Kelas 8.</li>
                            <li>Kelas 7 akan KOSONG (Siap diisi siswa baru).</li>
                            <li>Semester otomatis reset ke 1.</li>
                        </ul>
                        <hr>
                        <button class="btn btn-block btn-danger" onclick="confirmNaikKelas()">
                            <i class="fas fa-exclamation-circle mr-1"></i> PROSES KENAIKAN KELAS
                        </button>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="main-footer">
    <div class="container"><strong>Copyright &copy; <?= date('Y') ?> Garuda Akademi.</strong></div>
  </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Konfirmasi Ganti Semester
    function confirmSemester(target) {
        Swal.fire({
            title: 'Ganti ke Semester ' + target + '?',
            text: "Data diskusi dan pengumpulan tugas siswa akan dibersihkan untuk memulai semester baru.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            confirmButtonText: 'Ya, Ganti Semester',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                postAction('ganti_semester', {semester_tujuan: target});
            }
        });
    }

    // Konfirmasi Naik Kelas (Extra Warning)
    function confirmNaikKelas() {
        Swal.fire({
            title: 'YAKIN PROSES KENAIKAN KELAS?',
            html: "Ini akan <strong>MENGHAPUS SISWA KELAS 9</strong> dan memindahkan kelas 7 & 8.<br>Tindakan ini tidak dapat dibatalkan!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'YA, SAYA PAHAM RISIKONYA',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Double Confirm
                Swal.fire({
                    title: 'Konfirmasi Terakhir',
                    text: 'Ketik "NAIK" untuk melanjutkan.',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Proses'
                }).then((res2) => {
                    if (res2.value === 'NAIK') {
                        postAction('naik_kelas', {});
                    } else if (res2.isConfirmed) {
                        Swal.fire('Batal', 'Kode konfirmasi salah.', 'error');
                    }
                });
            }
        });
    }

    function postAction(actionName, data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/system_reset_manager.php';
        
        const inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = actionName;
        form.appendChild(inputAction);

        for (const key in data) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }

    <?php if(isset($_SESSION['success_message'])): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $_SESSION['success_message'] ?>' });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['error_message'])): ?>
        Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $_SESSION['error_message'] ?>' });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</script>
</body>
</html>