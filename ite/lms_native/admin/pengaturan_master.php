<?php
// GARA - Garuda Akademi
// File: admin/pengaturan_master.php

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

requireGuru();

// Ambil Data Kelas
$stmtKelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmtKelas->fetchAll();

// Ambil Data Mapel
$stmtMapel = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");
$mapel_list = $stmtMapel->fetchAll();
?>
<!DOCTYPE html>
<?php
$page_title = 'Data Master | GARA Admin';
$use_datatables = true;
require_once 'templates/header_portal.php';
?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0"> Data Master</h1></div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-kelas-link" data-toggle="pill" href="#tab-kelas" role="tab">Data Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-mapel-link" data-toggle="pill" href="#tab-mapel" role="tab">Mata Pelajaran</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    
                    <!-- TAB 1: KELAS -->
                    <div class="tab-pane fade show active" id="tab-kelas" role="tabpanel">
                        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalKelas">
                            <i class="fas fa-plus"></i> Tambah Kelas
                        </button>
                        <table id="tabelKelas" class="table table-bordered table-striped">
                            <thead><tr><th>ID</th><th>Nama Kelas</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($kelas_list as $k): ?>
                                <tr>
                                    <td style="width:10%"><?= $k['id'] ?></td>
                                    <td><?= htmlspecialchars($k['nama_kelas']) ?></td>
                                    <td style="width:20%">
                                        <button class="btn btn-sm btn-warning btn-edit-kelas" data-id="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama_kelas']) ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger btn-hapus" data-type="kelas" data-id="<?= $k['id'] ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TAB 2: MAPEL -->
                    <div class="tab-pane fade" id="tab-mapel" role="tabpanel">
                        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalMapel">
                            <i class="fas fa-plus"></i> Tambah Mapel
                        </button>
                        <table id="tabelMapel" class="table table-bordered table-striped">
                            <thead><tr><th>ID</th><th>Nama Mata Pelajaran</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($mapel_list as $m): ?>
                                <tr>
                                    <td style="width:10%"><?= $m['id'] ?></td>
                                    <td><?= htmlspecialchars($m['nama_mapel']) ?></td>
                                    <td style="width:20%">
                                        <button class="btn btn-sm btn-warning btn-edit-mapel" data-id="<?= $m['id'] ?>" data-nama="<?= htmlspecialchars($m['nama_mapel']) ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger btn-hapus" data-type="mapel" data-id="<?= $m['id'] ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
      </div>
    </div>
  </div>

<?php require_once 'templates/footer_portal.php'; ?>

<!-- Modal Kelas -->
<div class="modal fade" id="modalKelas">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Form Kelas</h5><button class="close" data-dismiss="modal">&times;</button></div>
            <form action="../actions/admin_master_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" value="kelas">
                    <input type="hidden" name="action" value="add" id="actionKelas">
                    <input type="hidden" name="id" id="idKelas">
                    <div class="form-group">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="inputNamaKelas" class="form-control" required placeholder="Contoh: X IPA 1">
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Mapel -->
<div class="modal fade" id="modalMapel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">Form Mata Pelajaran</h5><button class="close" data-dismiss="modal">&times;</button></div>
            <form action="../actions/admin_master_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" value="mapel">
                    <input type="hidden" name="action" value="add" id="actionMapel">
                    <input type="hidden" name="id" id="idMapel">
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" id="inputNamaMapel" class="form-control" required placeholder="Contoh: Matematika Wajib">
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelKelas, #tabelMapel').DataTable({ "responsive": true, "autoWidth": false });

    // --- KELAS ---
    $('[data-target="#modalKelas"]').click(function(){ 
        $('#actionKelas').val('add'); $('#idKelas').val(''); $('#inputNamaKelas').val(''); 
        $('.modal-title').text('Tambah Kelas');
    });
    $('.btn-edit-kelas').click(function(){
        $('#actionKelas').val('update'); 
        $('#idKelas').val($(this).data('id')); 
        $('#inputNamaKelas').val($(this).data('nama'));
        $('.modal-title').text('Edit Kelas');
        $('#modalKelas').modal('show');
    });

    // --- MAPEL ---
    $('[data-target="#modalMapel"]').click(function(){ 
        $('#actionMapel').val('add'); $('#idMapel').val(''); $('#inputNamaMapel').val(''); 
        $('.modal-title').text('Tambah Mapel');
    });
    $('.btn-edit-mapel').click(function(){
        $('#actionMapel').val('update'); 
        $('#idMapel').val($(this).data('id')); 
        $('#inputNamaMapel').val($(this).data('nama'));
        $('.modal-title').text('Edit Mapel');
        $('#modalMapel').modal('show');
    });

    // --- HAPUS GLOBAL ---
    $('.btn-hapus').click(function() {
        const type = $(this).data('type');
        const id = $(this).data('id');
        const msg = type === 'kelas' ? "Semua siswa di kelas ini akan kehilangan data kelasnya!" : "Semua RPP, Tugas, dan Diskusi mapel ini akan terhapus!";
        
        Swal.fire({
            title: 'Hapus Data?', text: msg, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST'; form.action = '../actions/admin_master_manager.php';
                form.innerHTML = `<input type="hidden" name="type" value="${type}"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form); form.submit();
            }
        });
    });

    <?php if(isset($_SESSION['success_message'])): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $_SESSION['success_message'] ?>', timer: 1500, showConfirmButton: false });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['error_message'])): ?>
        Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $_SESSION['error_message'] ?>' });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>
