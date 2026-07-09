<?php
// GARA - Garuda Akademi
// File: admin/ruang_tugas.php (V9 - File Submission Support)
// Update: Memperbaiki monitoring untuk mendukung Download File vs Link

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
requireGuruSession();

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$nama_mapel = $_SESSION['nama_mapel'];
$nama_kelas = $_SESSION['nama_kelas'];

// --- LOGIKA DATA ---
try {
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM tugas_pengumpulan tp WHERE tp.tugas_id = t.id) as total_dikumpulkan
            FROM tugas t 
            WHERE t.mapel_id = ? AND t.kelas_id = ? 
            ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mapel_id, $kelas_id]);
    $all_tugas = $stmt->fetchAll();
} catch (PDOException $e) { $all_tugas = []; }

$tugas_aktif = array_filter($all_tugas, function($t) { return $t['status'] === 'aktif'; });
$tugas_draft = array_filter($all_tugas, function($t) { return $t['status'] === 'draft'; });

// Quick Stats
$total_aktif = count($tugas_aktif);
$total_arsip = count($tugas_draft);
$total_partisipasi = 0;
foreach($tugas_aktif as $t) $total_partisipasi += $t['total_dikumpulkan'];

$page_title = 'Manajemen Tugas';
require_once 'templates/header.php';
require_once 'templates/sidebar.php';
?>

<!-- Load CSS Khusus -->
<link rel="stylesheet" href="tugas_admin_style.css?v=<?= time() ?>">
<style>
    /* Styling Khusus untuk Modal Monitoring */
    .col-catatan {
        white-space: pre-wrap; /* Agar enter/baris baru terbaca */
        word-break: break-word; /* Agar kata panjang turun ke bawah */
        min-width: 250px; /* Lebar minimum agar nyaman dibaca */
        font-size: 0.9rem;
        line-height: 1.4;
        color: #333;
    }
    .note-late {
        display: inline-block;
        background: #ffebee;
        color: #c62828;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        margin-bottom: 6px;
        border: 1px solid #ffcdd2;
    }
    .link-submission {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none !important;
        font-weight: 500;
    }
</style>

<div class="content-wrapper">
    <!-- Header Page -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 text-dark" style="font-weight: 800; letter-spacing: -0.5px;">Ruang Tugas</h1>
                <p class="text-muted m-0 small">Mapel: <strong><?= htmlspecialchars($nama_mapel) ?></strong> &bull; Kelas: <strong><?= htmlspecialchars($nama_kelas) ?></strong></p>
            </div>
            <button class="btn btn-primary shadow-sm rounded-pill px-4 py-2 font-weight-bold" data-toggle="modal" data-target="#modalTambah">
                <i class="fas fa-plus mr-2"></i> Buat Tugas Baru
            </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        
        <!-- Quick Stats Header -->
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <div class="quick-stat-card">
                    <div class="quick-stat-icon"><i class="fas fa-rocket"></i></div>
                    <div class="quick-stat-info">
                        <h4><?= $total_aktif ?></h4>
                        <span>Tugas Aktif</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="quick-stat-card">
                    <div class="quick-stat-icon" style="background:#fff3cd; color:#ffc107;"><i class="fas fa-users"></i></div>
                    <div class="quick-stat-info">
                        <h4><?= $total_partisipasi ?></h4>
                        <span>Total Pengumpulan</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="quick-stat-card">
                    <div class="quick-stat-icon" style="background:#e2e6ea; color:#6c757d;"><i class="fas fa-archive"></i></div>
                    <div class="quick-stat-info">
                        <h4><?= $total_arsip ?></h4>
                        <span>Arsip / Draft</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Tugas Modern -->
        <div class="card card-modern">
            <ul class="nav nav-tabs nav-tabs-modern" id="tugas-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-aktif-link" data-toggle="pill" href="#tab-aktif" role="tab">
                        Tugas Aktif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-arsip-link" data-toggle="pill" href="#tab-arsip" role="tab">
                        Arsip / Draft
                    </a>
                </li>
            </ul>

            <div class="card-body p-0">
                <div class="tab-content">
                    
                    <!-- KONTEN TAB 1: AKTIF -->
                    <div class="tab-pane fade show active" id="tab-aktif">
                        <?php if(empty($tugas_aktif)): ?>
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80" class="mb-3 opacity-50" style="filter: grayscale(100%);">
                                <h6 class="text-muted font-weight-bold">Belum ada tugas aktif.</h6>
                                <p class="text-muted small">Klik tombol "Buat Tugas Baru" di pojok kanan atas.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-modern datatable-init">
                                    <thead>
                                        <tr>
                                            <th style="width: 45%;">Info Tugas & Deskripsi</th>
                                            <th style="width: 25%;">Deadline</th>
                                            <th style="width: 15%;" class="text-center">Progres</th>
                                            <th style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($tugas_aktif as $t): ?>
                                        <tr>
                                            <!-- Kolom 1 -->
                                            <td>
                                                <span class="task-title"><?= htmlspecialchars($t['judul']) ?></span>
                                                <div class="task-desc">
                                                    <?= htmlspecialchars(substr($t['deskripsi'], 0, 90)) ?><?= strlen($t['deskripsi']) > 90 ? '...' : '' ?>
                                                </div>
                                                <?php if($t['link_lampiran']): ?>
                                                    <a href="<?= htmlspecialchars($t['link_lampiran']) ?>" target="_blank" class="badge badge-light text-primary border mt-2 px-2 py-1">
                                                        <i class="fas fa-paperclip mr-1"></i> Lampiran Soal
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Kolom 2 -->
                                            <td>
                                                <?php if($t['batas_waktu']): ?>
                                                    <div class="badge-modern deadline">
                                                        <i class="far fa-clock"></i>
                                                        <span><?= date('d M Y, H:i', strtotime($t['batas_waktu'])) ?></span>
                                                    </div>
                                                    <?php if($t['is_auto_close']): ?>
                                                        <div class="small text-danger mt-1 font-weight-bold ml-1">
                                                            <i class="fas fa-lock mr-1"></i> Auto-Close
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="badge-modern safe">
                                                        <i class="fas fa-infinity"></i>
                                                        <span>Tanpa Batas</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Kolom 3 -->
                                            <td class="text-center">
                                                <?php if($t['allow_upload']): ?>
                                                    <div class="stat-number"><?= $t['total_dikumpulkan'] ?></div>
                                                    <div class="stat-label">Siswa</div>
                                                    <button class="btn btn-xs btn-outline-primary rounded-pill mt-2 px-3 btn-monitor" 
                                                            data-id="<?= $t['id'] ?>" 
                                                            data-judul="<?= htmlspecialchars($t['judul']) ?>">
                                                        Lihat Jawaban
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small"><i class="fas fa-info-circle"></i> Info Only</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Kolom 4 -->
                                            <td class="text-center">
                                                <div class="btn-action-group">
                                                    <button class="btn-action edit btn-edit" data-tugas='<?= json_encode($t) ?>' title="Edit">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <form action="../actions/tugas_manager.php" method="POST" onsubmit="return confirm('Arsipkan tugas ini?')">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                        <input type="hidden" name="current_status" value="aktif">
                                                        <button type="submit" class="btn-action archive" title="Arsipkan">
                                                            <i class="fas fa-box-archive"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn-action delete btn-hapus" data-id="<?= $t['id'] ?>" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- KONTEN TAB 2: ARSIP -->
                    <div class="tab-pane fade" id="tab-arsip">
                        <div class="p-4 text-center bg-light border-bottom text-muted small">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Draft adalah tugas yang disimpan tapi <strong>tidak terlihat</strong> oleh siswa.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern datatable-init">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Info Tugas (Draft)</th>
                                        <th style="width: 30%;">Tanggal Dibuat</th>
                                        <th style="width: 20%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tugas_draft as $t): ?>
                                    <tr style="background: #fafafa;">
                                        <td>
                                            <span class="task-title text-muted"><?= htmlspecialchars($t['judul']) ?></span>
                                            <div class="task-desc text-muted small">
                                                <?= htmlspecialchars(substr($t['deskripsi'], 0, 60)) ?>...
                                            </div>
                                        </td>
                                        <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                                        <td class="text-center">
                                            <div class="btn-action-group">
                                                <form action="../actions/tugas_manager.php" method="POST">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="current_status" value="draft">
                                                    <button type="submit" class="btn-action restore" title="Publikasikan">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                </form>
                                                <button class="btn-action edit btn-edit" data-tugas='<?= json_encode($t) ?>'><i class="fas fa-pen"></i></button>
                                                <button class="btn-action delete btn-hapus" data-id="<?= $t['id'] ?>"><i class="fas fa-trash"></i></button>
                                            </div>
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
    </section>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white pb-0 border-bottom-0">
                <h5 class="modal-title font-weight-bold" id="modalTitle">Buat Tugas Baru</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="../actions/tugas_manager.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="form-group mb-4">
                        <label class="small text-uppercase text-muted font-weight-bold">Judul Tugas</label>
                        <input type="text" name="judul" id="inputJudul" class="form-control form-control-lg" placeholder="Contoh: Tugas Harian 1" required>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="small text-uppercase text-muted font-weight-bold m-0">Instruksi / Deskripsi</label>
                            <span class="small text-primary"><i class="fas fa-magic mr-1"></i> Template Cepat</span>
                        </div>
                        <div class="magic-chips-container">
                            <span class="desc-chip" onclick="addDesc('Kerjakan dengan teliti dan jujur. Jawaban yang terindikasi menyontek akan diberi nilai 0.')">
                                <i class="fas fa-shield-alt"></i> Aturan Ketat
                            </span>
                            <span class="desc-chip" onclick="addDesc('Silakan rangkum materi dari video/dokumen terlampir. Tulis di buku catatan lalu foto.')">
                                <i class="fas fa-book-reader"></i> Rangkuman
                            </span>
                            <span class="desc-chip" onclick="addDesc('Upload bukti pengerjaan dalam format PDF atau JPG.')">
                                <i class="fas fa-file-upload"></i> Format Upload
                            </span>
                        </div>
                        <textarea name="deskripsi" id="inputDeskripsi" class="form-control" rows="5" placeholder="Ketik instruksi di sini..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-uppercase text-muted font-weight-bold">Link Lampiran (Opsional)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text bg-light border-right-0"><i class="fas fa-link"></i></span></div>
                                    <input type="url" name="link_lampiran" id="inputLink" class="form-control border-left-0" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-uppercase text-muted font-weight-bold">Batas Waktu</label>
                                <input type="datetime-local" name="batas_waktu" id="inputBatas" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mt-2 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold m-0" style="font-size: 0.9rem;">Wajib Upload?</h6>
                                <small class="text-muted">Siswa harus mengirim file/link</small>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="checkAllowUpload" name="allow_upload" value="1" checked>
                                <label class="custom-control-label" for="checkAllowUpload"></label>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold m-0 text-danger" style="font-size: 0.9rem;">Auto Close?</h6>
                                <small class="text-muted">Tolak jawaban setelah deadline</small>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="checkAutoClose" name="is_auto_close" value="1">
                                <label class="custom-control-label" for="checkAutoClose"></label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Monitoring (Fixed UI) -->
<div class="modal fade" id="modalMonitor" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title font-weight-bold">Monitoring: <span id="monitorJudul" class="text-primary"></span></h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pt-0">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <h3 class="font-weight-bold mb-0 text-dark" id="statTotal">0</h3>
                            <small class="text-muted text-uppercase font-weight-bold">Total Siswa</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded text-center border" style="background: #e6fffa; border-color:#b2f5ea!important;">
                            <h3 class="font-weight-bold mb-0 text-success" id="statSudah">0</h3>
                            <small class="text-muted text-uppercase font-weight-bold">Sudah Kumpul</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded text-center border" style="background: #fff5f5; border-color:#feb2b2!important;">
                            <h3 class="font-weight-bold mb-0 text-danger" id="statBelum">0</h3>
                            <small class="text-muted text-uppercase font-weight-bold">Belum Kumpul</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered small" style="min-width: 800px;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 20%;">Nama Siswa</th>
                                <th style="width: 10%;">NIS</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 15%;">Waktu Kirim</th>
                                <th style="width: 10%;">Lampiran</th>
                                <!-- KOLOM CATATAN LEBIH LEBAR -->
                                <th style="width: 35%;">Catatan Siswa</th>
                            </tr>
                        </thead>
                        <tbody id="monitorTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

<!-- Script Logic -->
<script>
function addDesc(text) {
    const box = document.getElementById('inputDeskripsi');
    const current = box.value;
    box.value = current ? current + "\n\n" + text : text;
}

$(document).ready(function() {
    if ($.fn.DataTable) {
        $('.datatable-init').DataTable({
            "responsive": true, 
            "autoWidth": false,
            "language": { "search": "", "searchPlaceholder": "Cari tugas..." },
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    }

    $('[data-target="#modalTambah"]').click(function() {
        $('#formAction').val('add'); $('#editId').val(''); 
        $('#inputJudul').val(''); $('#inputDeskripsi').val(''); 
        $('#inputLink').val(''); $('#inputBatas').val('');
        $('#checkAutoClose').prop('checked', false); 
        $('#checkAllowUpload').prop('checked', true);
        $('#modalTitle').text('Buat Tugas Baru');
    });

    $(document).on('click', '.btn-edit', function() {
        const t = $(this).data('tugas');
        $('#formAction').val('update');
        $('#editId').val(t.id);
        $('#inputJudul').val(t.judul);
        $('#inputDeskripsi').val(t.deskripsi);
        $('#inputLink').val(t.link_lampiran);
        if(t.batas_waktu) $('#inputBatas').val(t.batas_waktu.replace(' ', 'T'));
        $('#checkAutoClose').prop('checked', t.is_auto_close == 1);
        $('#checkAllowUpload').prop('checked', t.allow_upload == 1);
        $('#modalTitle').text('Edit Tugas');
        $('#modalTambah').modal('show');
    });

    // --- LOGIC MONITORING (FIXED) ---
    $(document).on('click', '.btn-monitor', function() {
        const id = $(this).data('id');
        const judul = $(this).data('judul');
        $('#monitorJudul').text(judul);
        $('#monitorTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat jawaban siswa...</p></td></tr>');
        $('#modalMonitor').modal('show');
        
        $.ajax({
            url: '../actions/get_submission_detail.php', 
            type: 'GET', 
            data: { tugas_id: id }, 
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#statTotal').text(res.rekap.total); 
                    $('#statSudah').text(res.rekap.sudah); 
                    $('#statBelum').text(res.rekap.belum);
                    
                    let rows = '';
                    res.siswa.forEach(s => {
                        let statusHtml = s.status === 'sudah' ? '<span class="badge badge-success px-3 py-2">Selesai</span>' : '<span class="badge badge-danger px-3 py-2">Belum</span>';
                        
                        // [UPDATE] Logic File vs Link
                        let linkHtml = '-';
                        if (s.status === 'sudah' && s.link) {
                            if (s.metode === 'file') {
                                // Tampilkan Tombol Download
                                linkHtml = `<a href="../uploads/tugas/${s.link}" target="_blank" class="btn btn-sm btn-info shadow-sm" style="border-radius:20px;">
                                                <i class="fas fa-file-download mr-1"></i> File
                                            </a>`;
                            } else {
                                // Tampilkan Tombol Link
                                linkHtml = `<a href="${s.link}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius:20px;">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka
                                            </a>`;
                            }
                        }
                        
                        // LOGIC FIX: Penanganan Catatan Siswa
                        // Menggunakan fallback aman untuk mengambil data catatan
                        let rawNote = s.catatan || s.catatan_siswa || ''; 
                        let catatanDisplay = '';

                        if(rawNote) {
                            // Cek apakah ada tag terlambat
                            if(rawNote.includes('[TERLAMBAT')) {
                                // Parsing Regex untuk memisahkan tag terlambat dan pesan asli
                                catatanDisplay = rawNote.replace(/\[TERLAMBAT(.*?)\]/g, '<span class="note-late"><i class="fas fa-exclamation-circle mr-1"></i> Terlambat $1</span><br>');
                            } else {
                                catatanDisplay = rawNote;
                            }
                        } else {
                            catatanDisplay = '<span class="text-muted small font-italic">- Tidak ada catatan -</span>';
                        }

                        rows += `<tr>
                                    <td class="font-weight-bold text-dark">${s.nama}</td>
                                    <td>${s.nis}</td>
                                    <td>${statusHtml}</td>
                                    <td>${s.waktu}</td>
                                    <td class="text-center">${linkHtml}</td>
                                    <td class="col-catatan">${catatanDisplay}</td>
                                 </tr>`;
                    });
                    $('#monitorTableBody').html(rows);
                } else {
                     $('#monitorTableBody').html(`<tr><td colspan="6" class="text-center text-danger py-3">Gagal memuat data: ${res.message}</td></tr>`);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error);
                $('#monitorTableBody').html('<tr><td colspan="6" class="text-center text-danger py-3">Terjadi kesalahan koneksi server.</td></tr>');
            }
        });
    });

    $(document).on('click', '.btn-hapus', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Tugas?', 
            text: 'Data pengumpulan siswa juga akan terhapus!', 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#dc3545', 
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((r) => {
            if(r.isConfirmed) {
                const f = document.createElement('form'); f.method = 'POST'; f.action = '../actions/tugas_manager.php';
                f.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(f); f.submit();
            }
        });
    });

    <?php if(isset($_SESSION['success_message'])): ?>
        const Toast = Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 3000});
        Toast.fire({icon: 'success', title: '<?= $_SESSION['success_message'] ?>'});
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
});
</script>