<?php
// GARA - Garuda Akademi
// File: admin/ruang_materi.php
// Tujuan: Halaman CRUD Materi (RPP) untuk Guru dengan tampilan Tabel AdminLTE.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

// 1. Cek Sesi Guru
requireGuruSession();

// Ambil konteks sesi
$nama_mapel = $_SESSION['nama_mapel'];
$nama_kelas = $_SESSION['nama_kelas'];
$mapel_id   = $_SESSION['mapel_id'];
$kelas_id   = $_SESSION['kelas_id'];

// 2. Ambil Data RPP dari Database
try {
    $stmt = $pdo->prepare("SELECT * FROM rpp_materi WHERE mapel_id = ? AND kelas_id = ? ORDER BY semester ASC, bab ASC, bagian ASC");
    $stmt->execute([$mapel_id, $kelas_id]);
    $materi_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $materi_list = [];
}
?>
<!DOCTYPE html>
<?php
$page_title = 'Ruang Materi (RPP) | GARA Admin';
$active_menu = 'materi';
$use_datatables = true;
require_once 'templates/header.php';
require_once 'templates/sidebar.php';
?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manajemen RPP & Materi</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus-circle"></i> Tambah Materi Baru
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Materi Pembelajaran</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabelMateri" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Materi</th>
                                    <th>Sem</th>
                                    <th>Bab</th>
                                    <th>Bagian</th>
                                    <th>Judul Materi</th>
                                    <th>Link</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($materi_list as $m): ?>
                                <tr>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($m['id_materi']) ?></span></td>
                                    <td><?= $m['semester'] ?></td>
                                    <td><?= $m['bab'] ?></td>
                                    <td><?= $m['bagian'] ?></td>
                                    <td class="font-weight-bold"><?= htmlspecialchars($m['judul_materi']) ?></td>
                                    <td>
                                        <!-- Indikator Ikon Link -->
                                        <?php if($m['link_ppt']) echo '<i class="fas fa-file-powerpoint text-danger mr-1" title="PPT"></i>'; ?>
                                        <?php if($m['link_youtube']) echo '<i class="fab fa-youtube text-danger mr-1" title="YouTube"></i>'; ?>
                                        <?php if($m['link_modul']) echo '<i class="fas fa-file-pdf text-danger mr-1" title="Modul"></i>'; ?>
                                        <?php if($m['link_tugas']) echo '<i class="fas fa-tasks text-success mr-1" title="Tugas"></i>'; ?>
                                        <?php if($m['link_notebook']) echo '<i class="fas fa-book text-warning mr-1" title="Notebook"></i>'; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-edit" 
                                            data-id="<?= $m['id'] ?>"
                                            data-semester="<?= $m['semester'] ?>"
                                            data-bab="<?= $m['bab'] ?>"
                                            data-bagian="<?= $m['bagian'] ?>"
                                            data-judul="<?= htmlspecialchars($m['judul_materi']) ?>"
                                            data-ppt="<?= htmlspecialchars($m['link_ppt']) ?>"
                                            data-youtube="<?= htmlspecialchars($m['link_youtube']) ?>"
                                            data-modul="<?= htmlspecialchars($m['link_modul']) ?>"
                                            data-tugas="<?= htmlspecialchars($m['link_tugas']) ?>"
                                            data-notebook="<?= htmlspecialchars($m['link_notebook']) ?>"
                                        ><i class="fas fa-edit"></i></button>
                                        
                                        <button class="btn btn-sm btn-danger btn-hapus" data-id="<?= $m['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

<?php require_once 'templates/footer.php'; ?>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Materi Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="../actions/rpp_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" class="form-control" required>
                                    <option value="1">1 (Ganjil)</option>
                                    <option value="2">2 (Genap)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bab Ke-</label>
                                <input type="number" name="bab" class="form-control" required min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bagian (Pertemuan)</label>
                                <input type="number" name="bagian" class="form-control" required min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Judul Materi</label>
                                <input type="text" name="judul_materi" class="form-control" required placeholder="Contoh: Sistem Pencernaan Manusia">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6>Tautan Sumber Belajar (Opsional)</h6>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><i class="fas fa-file-powerpoint text-danger"></i> Link PPT</label>
                        <div class="col-sm-9"><input type="url" name="link_ppt" class="form-control" placeholder="https://..."></div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><i class="fab fa-youtube text-danger"></i> Link YouTube</label>
                        <div class="col-sm-9"><input type="url" name="link_youtube" class="form-control" placeholder="https://..."></div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><i class="fas fa-file-pdf text-danger"></i> Link Modul PDF</label>
                        <div class="col-sm-9"><input type="url" name="link_modul" class="form-control" placeholder="https://..."></div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><i class="fas fa-tasks text-success"></i> Link Tugas</label>
                        <div class="col-sm-9"><input type="url" name="link_tugas" class="form-control" placeholder="https://..."></div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><i class="fas fa-book text-warning"></i> Link Notebook</label>
                        <div class="col-sm-9"><input type="url" name="link_notebook" class="form-control" placeholder="https://..."></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Materi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="../actions/rpp_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" id="edit_semester" class="form-control" required>
                                    <option value="1">1 (Ganjil)</option>
                                    <option value="2">2 (Genap)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bab Ke-</label>
                                <input type="number" name="bab" id="edit_bab" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bagian</label>
                                <input type="number" name="bagian" id="edit_bagian" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Judul Materi</label>
                                <input type="text" name="judul_materi" id="edit_judul" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6>Edit Tautan Sumber Belajar</h6>
                    <div class="form-group row"><label class="col-sm-3"><i class="fas fa-file-powerpoint text-danger"></i> Link PPT</label><div class="col-sm-9"><input type="url" name="link_ppt" id="edit_ppt" class="form-control"></div></div>
                    <div class="form-group row"><label class="col-sm-3"><i class="fab fa-youtube text-danger"></i> YouTube</label><div class="col-sm-9"><input type="url" name="link_youtube" id="edit_youtube" class="form-control"></div></div>
                    <div class="form-group row"><label class="col-sm-3"><i class="fas fa-file-pdf text-danger"></i> Modul</label><div class="col-sm-9"><input type="url" name="link_modul" id="edit_modul" class="form-control"></div></div>
                    <div class="form-group row"><label class="col-sm-3"><i class="fas fa-tasks text-success"></i> Tugas</label><div class="col-sm-9"><input type="url" name="link_tugas" id="edit_tugas" class="form-control"></div></div>
                    <div class="form-group row"><label class="col-sm-3"><i class="fas fa-book text-warning"></i> Notebook</label><div class="col-sm-9"><input type="url" name="link_notebook" id="edit_notebook" class="form-control"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Init DataTable
    $('#tabelMateri').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" }
    });

    // 2. Handle Edit Button Click
    $('.btn-edit').click(function() {
        // Ambil data dari atribut tombol
        const id = $(this).data('id');
        const sem = $(this).data('semester');
        const bab = $(this).data('bab');
        const bag = $(this).data('bagian');
        const judul = $(this).data('judul');
        
        // Isi form modal edit
        $('#edit_id').val(id);
        $('#edit_semester').val(sem);
        $('#edit_bab').val(bab);
        $('#edit_bagian').val(bag);
        $('#edit_judul').val(judul);
        $('#edit_ppt').val($(this).data('ppt'));
        $('#edit_youtube').val($(this).data('youtube'));
        $('#edit_modul').val($(this).data('modul'));
        $('#edit_tugas').val($(this).data('tugas'));
        $('#edit_notebook').val($(this).data('notebook'));

        // Tampilkan modal
        $('#modalEdit').modal('show');
    });

    // 3. Handle Delete Button Click
    $('.btn-hapus').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Materi?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form dinamis untuk submit POST delete
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../actions/rpp_manager.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'delete';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;

                form.appendChild(inputAction);
                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
