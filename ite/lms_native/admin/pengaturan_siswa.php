<?php
// GARA - Garuda Akademi
// File: admin/pengaturan_siswa.php
// Tujuan: Manajemen CRUD Siswa & Fitur Reset Password.

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

requireGuru();

// Ambil Data Siswa + Nama Kelas
try {
    $sql = "SELECT s.*, k.nama_kelas 
            FROM siswa s 
            JOIN kelas k ON s.kelas_id = k.id 
            ORDER BY k.nama_kelas ASC, s.nama ASC";
    $stmt = $pdo->query($sql);
    $siswa_list = $stmt->fetchAll();

    // Ambil Data Kelas untuk Dropdown Modal
    $stmtKelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmtKelas->fetchAll();
} catch (PDOException $e) {
    $siswa_list = [];
    $kelas_list = [];
}
?>
<!DOCTYPE html>
<?php
$page_title = 'Manajemen Siswa | GARA Admin';
$use_datatables = true;
require_once 'templates/header_portal.php';
?>

  <div class="content-wrapper">
    <!-- Header Halaman -->
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"> Manajemen Siswa</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalSiswa">
                <i class="fas fa-user-plus"></i> Tambah Siswa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Konten Utama -->
    <div class="content">
      <div class="container">
        <div class="card card-outline card-info">
            <div class="card-body">
                <table id="tabelSiswa" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 15%">NIS</th>
                            <th>Nama Lengkap</th>
                            <th style="width: 15%">Kelas</th>
                            <th style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($siswa_list as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['nis']) ?></td>
                            <td><?= htmlspecialchars($s['nama']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($s['nama_kelas']) ?></span></td>
                            <td>
                                <!-- Tombol Edit -->
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="<?= $s['id'] ?>"
                                    data-nis="<?= htmlspecialchars($s['nis']) ?>"
                                    data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                    data-kelas="<?= $s['kelas_id'] ?>"
                                    title="Edit Data"
                                ><i class="fas fa-edit"></i></button>
                                
                                <!-- Tombol Reset Password (FITUR BARU) -->
                                <button class="btn btn-sm btn-secondary btn-reset" 
                                    data-id="<?= $s['id'] ?>"
                                    data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                    title="Reset Password ke Default (gara17)"
                                ><i class="fas fa-key"></i></button>
                                
                                <!-- Tombol Hapus -->
                                <button class="btn btn-sm btn-danger btn-hapus" 
                                    data-id="<?= $s['id'] ?>"
                                    title="Hapus Siswa"
                                >
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
    </div>
  </div>

<?php require_once 'templates/footer_portal.php'; ?>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalSiswa">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Form Data Siswa</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="../actions/admin_siswa_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="form-group">
                        <label>NIS (Nomor Induk Siswa)</label>
                        <input type="number" name="nis" id="inputNis" class="form-control" required placeholder="Contoh: 22051">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" id="inputNama" class="form-control" required placeholder="Nama Siswa">
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" id="inputKelas" class="form-control" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach($kelas_list as $k): ?>
                                <option value="<?= $k['id'] ?>">Kelas <?= $k['nama_kelas'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-light border small text-muted mt-3">
                        <i class="fas fa-info-circle"></i> Password default untuk siswa baru adalah <b>gara17</b>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    var table = $('#tabelSiswa').DataTable({
        "responsive": true,
        "autoWidth": false,
        "pageLength": 10 // Menampilkan 10 baris per halaman
    });

    // 1. Logic Tombol Tambah (Tetap sama karena di luar tabel)
    $('[data-target="#modalSiswa"]').click(function() {
        $('#formAction').val('add');
        $('#editId').val('');
        $('#inputNis').val('');
        $('#inputNama').val('');
        $('#inputKelas').val('');
        $('.modal-title').text('Tambah Siswa Baru');
    });

    // --- PERBAIKAN: MENGGUNAKAN EVENT DELEGATION ---
    // Gunakan .on('click', 'selector', ...) pada parent element (tbody)
    // agar tombol di halaman 2, 3, dst tetap bisa diklik.

    // 2. Logic Tombol Edit (FIXED)
    $('#tabelSiswa tbody').on('click', '.btn-edit', function() {
        $('#formAction').val('update');
        $('#editId').val($(this).data('id'));
        $('#inputNis').val($(this).data('nis'));
        $('#inputNama').val($(this).data('nama'));
        $('#inputKelas').val($(this).data('kelas'));
        $('.modal-title').text('Edit Data Siswa');
        $('#modalSiswa').modal('show');
    });

    // 3. Logic Tombol Hapus (FIXED)
    $('#tabelSiswa tbody').on('click', '.btn-hapus', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Siswa?', 
            text: "Data nilai dan tugas siswa ini juga akan terhapus permanen!",
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#d33', 
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST'; form.action = '../actions/admin_siswa_manager.php';
                form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form); form.submit();
            }
        });
    });

    // 4. Logic Tombol Reset Password (FIXED)
    $('#tabelSiswa tbody').on('click', '.btn-reset', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        Swal.fire({
            title: 'Reset Password?',
            text: `Password untuk siswa "${nama}" akan dikembalikan ke default: gara17`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../actions/admin_siswa_manager.php';
                form.innerHTML = `<input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // 5. Auto-Alert Handler (Menangkap pesan dari URL)
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('msg');

    if (status && msg) {
        Swal.fire({
            icon: status === 'success' ? 'success' : 'error',
            title: status === 'success' ? 'Berhasil' : 'Gagal',
            text: msg, 
            showConfirmButton: false,
            timer: 2000
        });
        window.history.replaceState(null, null, window.location.pathname);
    }
});
</script>
</body>
</html>