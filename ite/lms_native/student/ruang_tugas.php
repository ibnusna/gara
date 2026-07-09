<?php
// GARA - Garuda Akademi
// File: student/ruang_tugas.php (V3 - Logic Status & Auto Close)
// Update: Sinkronisasi Tab Terlewat, Timer Realtime, dan Logika Auto Close

require_once '../actions/auth_helper.php';
require_once '../config/database.php';

// 1. KUNCI ZONA WAKTU (WAJIB SAMA DENGAN API)
date_default_timezone_set('Asia/Jakarta');

requireSiswa();

$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];
$siswa_id = $_SESSION['user_id'];
$nama_mapel = $_SESSION['nama_mapel'] ?? 'Mata Pelajaran';

// --- HELPER LOCAL ---
function getYoutubeEmbedIdLocal($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($pattern, $url, $match)) return $match[1];
    return false;
}
function isGoogleDriveLinkLocal($url) {
    return (strpos($url, 'drive.google.com') !== false || strpos($url, 'docs.google.com') !== false);
}

// --- LOGIKA UTAMA (PEMISAHAN STATUS) ---
try {
    // Ambil semua tugas aktif (bukan draft)
    // [UPDATE] Added tp.metode to selection
    $sql = "SELECT t.*, 
            tp.id as submission_id, tp.link_pengumpulan, tp.nilai, tp.catatan_siswa, tp.dikumpulkan_pada, tp.feedback_guru, tp.metode
            FROM tugas t
            LEFT JOIN tugas_pengumpulan tp ON t.id = tp.tugas_id AND tp.siswa_id = ?
            WHERE t.mapel_id = ? AND t.kelas_id = ? AND t.status = 'aktif'
            ORDER BY t.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$siswa_id, $mapel_id, $kelas_id]);
    $all_tugas = $stmt->fetchAll();

    $tugas_aktif = [];
    $tugas_selesai = [];
    $tugas_terlewat = [];
    $current_time = time(); // Waktu Server Saat Ini

    foreach ($all_tugas as $t) {
        $deadline_ts = $t['batas_waktu'] ? strtotime($t['batas_waktu']) : null;
        $is_submitted = !empty($t['submission_id']);

        if ($is_submitted) {
            // 1. STATUS SELESAI (Sudah kumpul)
            $tugas_selesai[] = $t;
        } else {
            // Belum kumpul, cek waktu
            if ($deadline_ts && $current_time > $deadline_ts) {
                // 2. STATUS TERLEWAT (Belum kumpul & Lewat Deadline)
                // Nanti di UI dibedakan tombolnya berdasarkan is_auto_close
                $tugas_terlewat[] = $t;
            } else {
                // 3. STATUS AKTIF (Belum kumpul & Masih ada waktu)
                $tugas_aktif[] = $t;
            }
        }
    }
} catch (PDOException $e) {
    $all_tugas = [];
}

$page_title = 'Ruang Tugas';

// Load Specific CSS
$additional_css = 'tugas_style.css';

if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<?php if (is_ajax_request()): ?>
    <!-- Inject CSS for Partial Render -->
    <link rel="stylesheet" href="tugas_style.css?v=<?= filemtime('tugas_style.css') ?>">
<?php endif; ?>
<!-- Header App -->
<div class="app-header">
    <div class="header-left">
        <a href="dashboard.php" class="btn-back" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Ruang Tugas</h1>
            <span class="page-subtitle"><?= htmlspecialchars($_SESSION['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
        </div>
    </div>
</div>
<!-- Load CSS External -->
<link rel="stylesheet" href="tugas_style.css?v=<?= filemtime('tugas_style.css') ?>">

<!-- OVERRIDE STYLE & ANIMATION -->
<style>
    /* Styling Tab Terlewat agar lebih dramatis */
    .border-danger-soft { border: 1px solid #feb2b2; }
    .bg-danger-soft { background-color: #fff5f5; }
    
    .btn-susulan {
        background-color: #ed8936; /* Orange */
        color: white;
        border: none;
        transition: all 0.2s;
    }
    .btn-susulan:hover { background-color: #dd6b20; color: white; transform: translateY(-2px); }

    .countdown-timer {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #e53e3e;
        font-size: 0.9rem;
    }
    
    /* Toast Container Fix */
    .app-toast-container {
        position: fixed; bottom: 30px; left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #323232; color: white;
        padding: 12px 24px; border-radius: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 9999; display: flex; align-items: center; gap: 12px;
        opacity: 0; transition: all 0.4s;
    }
    .app-toast-container.show { transform: translateX(-50%) translateY(0); opacity: 1; }
    .app-toast-success .app-toast-icon { color: #2ecc71; }
    .app-toast-error .app-toast-icon { color: #e74c3c; }
</style>

<!-- 1. STICKY TABS -->
<div class="tabs-container">
    <div class="tab-item active" onclick="switchTab('aktif', this)">
        Tugas Baru 
        <?php if(count($tugas_aktif) > 0): ?>
            <span id="badge-aktif" class="badge bg-primary rounded-pill ms-1"><?= count($tugas_aktif) ?></span>
        <?php endif; ?>
    </div>
    <div class="tab-item" onclick="switchTab('selesai', this)">
        Selesai
    </div>
    <div class="tab-item" onclick="switchTab('terlewat', this)">
        Terlewat
        <?php if(count($tugas_terlewat) > 0): ?>
            <span id="badge-terlewat" class="badge bg-danger rounded-pill ms-1"><?= count($tugas_terlewat) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="container py-3" style="max-width: 700px; padding-bottom: 100px;">

    <!-- TAB 1: TUGAS AKTIF -->
    <div id="tab-aktif" class="tab-content active">
        <?php if(empty($tugas_aktif)): ?>
            <div class="empty-state">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="Empty">
                <p>Tidak ada tugas aktif. Kerja bagus!</p>
            </div>
        <?php else: ?>
            <?php foreach($tugas_aktif as $t): renderTaskCard($t, 'aktif'); endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB 2: SELESAI -->
    <div id="tab-selesai" class="tab-content">
        <?php if(empty($tugas_selesai)): ?>
            <div class="empty-state">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486803.png" alt="Empty">
                <p>Belum ada riwayat tugas.</p>
            </div>
        <?php else: ?>
            <?php foreach($tugas_selesai as $t): renderTaskCard($t, 'selesai'); endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB 3: TERLEWAT -->
    <div id="tab-terlewat" class="tab-content">
        <?php if(empty($tugas_terlewat)): ?>
            <div class="empty-state">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty">
                <p>Tidak ada tugas terlewat. Pertahankan!</p>
            </div>
        <?php else: ?>
            <div class="alert alert-warning small mb-3">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Tugas di sini sudah melewati batas waktu.
            </div>
            <?php foreach($tugas_terlewat as $t): renderTaskCard($t, 'terlewat'); endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- BOTTOM SHEET: DOCUMENT VIEWER -->
<div id="backdrop-view" class="offcanvas-backdrop" onclick="closeSheetView()"></div>
<div id="sheet-view" class="bottom-sheet">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
        <h5 id="viewTitle" class="sheet-title m-0">Lampiran</h5>
        <div class="d-flex gap-2">
            <a id="btnExternalOpen" href="#" target="_blank" class="btn-icon-circle primary"><i class="fas fa-external-link-alt"></i></a>
            <button class="btn-icon-circle" onclick="closeSheetView()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="sheet-content" id="viewBody" style="background:black; height: 100%;"></div>
</div>

<!-- BOTTOM SHEET: SUBMIT FORM -->
<div id="backdrop-form" class="offcanvas-backdrop" onclick="closeSheetForm()"></div>
<div id="sheet-form" class="bottom-sheet" style="height: auto; max-height: 90vh;">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
        <h5 class="sheet-title m-0">Kumpulkan Tugas</h5>
        <button class="btn-icon-circle" onclick="closeSheetForm()"><i class="fas fa-times"></i></button>
    </div>
    <div class="sheet-content" style="background: white; padding: 20px;">
        <form id="form-submit-tugas" enctype="multipart/form-data">
            <input type="hidden" name="action" value="submit_tugas">
            <input type="hidden" name="tugas_id" id="input_tugas_id">
            
            <!-- Warning jika Susulan -->
            <div id="late-warning" class="alert alert-danger py-2 small d-none mb-3">
                <i class="fas fa-clock me-1"></i> Anda mengumpulkan terlambat. Sistem akan mencatat waktu keterlambatan.
            </div>

            <!-- PILIH METODE -->
            <div class="sheet-form-group mb-3">
                <label class="sheet-form-label mb-2">Metode Pengumpulan</label>
                <div class="d-flex gap-3">
                    <label class="radio-card active" onclick="toggleMetode('link')">
                        <input type="radio" name="metode" value="link" checked onchange="toggleMetode('link')">
                        <i class="fas fa-link"></i> Link / Tautan
                    </label>
                    <label class="radio-card" onclick="toggleMetode('file')">
                        <input type="radio" name="metode" value="file" onchange="toggleMetode('file')">
                        <i class="fas fa-file-upload"></i> Upload File
                    </label>
                </div>
            </div>

            <!-- INPUT LINK -->
            <div id="group-link" class="sheet-form-group">
                <label class="sheet-form-label">Link Hasil Pekerjaan</label>
                <input type="url" name="link_pengumpulan" id="input_link" class="sheet-form-input" placeholder="https://...">
                <div class="form-text mt-2"><i class="fas fa-info-circle"></i> Pastikan link bersifat Publik (Google Drive/Docs).</div>
            </div>

            <!-- INPUT FILE -->
            <div id="group-file" class="sheet-form-group d-none">
                <label class="sheet-form-label">Upload File (Max 5MB)</label>
                <div class="file-upload-box">
                    <input type="file" name="file_upload" id="input_file" accept=".pdf, .doc, .docx, .ppt, .pptx, .jpg, .jpeg, .png, .webp">
                    <div class="file-content">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                        <p class="m-0 small text-muted">Klik untuk pilih file</p>
                        <p class="m-0 x-small text-muted mt-1">PDF, Word, PPT, Gambar</p>
                    </div>
                </div>
                <div id="file-name-display" class="mt-2 small text-primary font-weight-bold"></div>
            </div>

            <div class="sheet-form-group">
                <label class="sheet-form-label">Catatan Tambahan</label>
                <textarea name="catatan" id="input_catatan" class="sheet-form-input" rows="3" placeholder="Pesan untuk guru..."></textarea>
            </div>

            <button type="submit" id="btn-submit-final" class="btn-app primary btn-app-block mb-3">
                <i class="fas fa-paper-plane me-2"></i> Kirim Tugas
            </button>
            
            <!-- Tombol Batal untuk Revisi (Hanya muncul jika sudah kumpul & diizinkan) -->
            <button type="button" id="btn-delete-sub" class="btn btn-outline-danger btn-block w-100 d-none" onclick="deleteSubmission()">
                <i class="fas fa-trash me-2"></i> Batalkan Pengumpulan
            </button>
        </form>
    </div>
</div>

<!-- TOAST -->
<div id="appToast" class="app-toast-container">
    <i class="fas fa-check-circle app-toast-icon"></i>
    <span id="toastMessage">Berhasil disimpan</span>
</div>

<script>
    // --- 1. COUNTDOWN TIMER REALTIME ---
    function updateTimers() {
        const now = new Date().getTime();
        document.querySelectorAll('.countdown-timer').forEach(el => {
            const deadline = new Date(el.dataset.deadline).getTime();
            const distance = deadline - now;

            if (distance < 0) {
                // WAKTU HABIS!
                el.innerHTML = "Waktu Habis!";
                el.closest('.app-card').classList.add('opacity-50');
                // Jika ingin reload otomatis agar pindah tab:
                if (!el.dataset.reloaded) {
                    el.dataset.reloaded = "true";
                    setTimeout(() => location.reload(), 2000); // Reload setelah 2 detik
                }
            } else {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                let timeStr = "";
                if(days > 0) timeStr += days + "h ";
                timeStr += hours + "j " + minutes + "m " + seconds + "d";
                el.innerHTML = '<i class="far fa-clock me-1"></i> ' + timeStr;
            }
        });
    }
    // Update setiap detik
    setInterval(updateTimers, 1000);
    updateTimers(); // Jalankan langsung

    // --- 2. LOGIC TAB & FORM, FILE TOGGLE ---
    function switchTab(tabName, element) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        element.classList.add('active');
    }

    function toggleMetode(val) {
        // Update visual radio cards
        document.querySelectorAll('.radio-card').forEach(el => el.classList.remove('active'));
        const selectedRadio = document.querySelector(`input[name="metode"][value="${val}"]`);
        if(selectedRadio) selectedRadio.closest('.radio-card').classList.add('active');
        
        // Toggle input groups
        if(val === 'link') {
            document.getElementById('group-link').classList.remove('d-none');
            document.getElementById('group-file').classList.add('d-none');
            document.getElementById('input_link').required = true;
            document.getElementById('input_file').value = ''; // Reset file logic if needed, but keeping it simple
        } else {
            document.getElementById('group-link').classList.add('d-none');
            document.getElementById('group-file').classList.remove('d-none');
            document.getElementById('input_link').required = false;
        }
    }
    
    // File Input Listener to show name
    document.getElementById('input_file').addEventListener('change', function(){
        const name = this.files[0] ? this.files[0].name : '';
        document.getElementById('file-name-display').innerText = name ? 'File: ' + name : '';
        
        // Client side size validation
        if(this.files[0] && this.files[0].size > 5 * 1024 * 1024) {
            alert('File terlalu besar! Maksimal 5MB.');
            this.value = '';
            document.getElementById('file-name-display').innerText = '';
        }
    });

    function openSubmitForm(id, isLateMode = false, existingLink = '', existingNote = '', existingMetode = 'link') {
        document.getElementById('input_tugas_id').value = id;
        document.getElementById('input_catatan').value = existingNote;
        
        // Set Metode Logic
        // Jika existingMetode kosong (default), anggap link
        if(!existingMetode) existingMetode = 'link'; 
        
        // Trigger click/toggle
        const radio = document.querySelector(`input[name="metode"][value="${existingMetode}"]`);
        if(radio) {
            radio.checked = true;
            toggleMetode(existingMetode);
        }

        if(existingMetode === 'link') {
            document.getElementById('input_link').value = existingLink;
            document.getElementById('file-name-display').innerText = '';
        } else {
            document.getElementById('input_link').value = '';
            document.getElementById('file-name-display').innerText = 'File saat ini: ' + existingLink; // Show filename
        }
        
        // Handling Tampilan jika Terlambat
        const warning = document.getElementById('late-warning');
        const btn = document.getElementById('btn-submit-final');
        const btnDel = document.getElementById('btn-delete-sub');

        if (isLateMode) {
            warning.classList.remove('d-none');
            btn.classList.remove('primary');
            btn.classList.add('btn-susulan'); // Warna Orange
            btn.innerHTML = '<i class="fas fa-history me-2"></i> Kirim Susulan';
        } else {
            warning.classList.add('d-none');
            btn.classList.remove('btn-susulan');
            btn.classList.add('primary');
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Kirim Tugas';
        }

        // Logic Tombol Hapus (Hanya jika sudah pernah kumpul)
        if(existingLink) {
            btnDel.classList.remove('d-none');
        } else {
            btnDel.classList.add('d-none');
        }

        document.getElementById('backdrop-form').classList.add('show');
        document.getElementById('sheet-form').classList.add('show');
    }

    function closeSheetForm() {
        document.getElementById('backdrop-form').classList.remove('show');
        document.getElementById('sheet-form').classList.remove('show');
    }

    // --- 3. AJAX SUBMIT ---
    document.getElementById('form-submit-tugas').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(this);

        fetch('../actions/tugas_siswa_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                closeSheetForm();
                showToast(data.message, 'success');
                
                // INSTANT UPDATE: Pindahkan kartu tanpa reload
                const tugasId = document.getElementById('input_tugas_id').value;
                const linkVal = data.data.link; // Use return from server (filename or link)
                const catVal = data.data.catatan;
                const metodeVal = data.data.metode;
                
                moveCardToCompleted(tugasId, {
                    link: linkVal,
                    catatan: catVal,
                    metode: metodeVal
                });

            } else {
                showToast(data.message, 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal terhubung ke server', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    // --- 4. AJAX DELETE ---
    function deleteSubmission() {
        if(!confirm('Apakah Anda yakin ingin membatalkan pengumpulan? Anda harus mengumpulkan ulang.')) return;

        const tugasId = document.getElementById('input_tugas_id').value;
        const formData = new FormData();
        formData.append('action', 'delete_submission');
        formData.append('tugas_id', tugasId);

        fetch('../actions/tugas_siswa_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                closeSheetForm();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        });
    }

    // --- 5. LAMPIRAN ---
    function openLampiran(title, url) {
        const viewBody = document.getElementById('viewBody');
        document.getElementById('viewTitle').innerText = title;
        document.getElementById('btnExternalOpen').href = url;
        
        let embedUrl = url;
        // Simple embed logic
        if(url.includes('youtube') || url.includes('youtu.be')) {
            const id = url.match(/(?:v=|youtu\.be\/)([^&]+)/);
            if(id) embedUrl = `https://www.youtube.com/embed/${id[1]}`;
        } else if (url.includes('drive.google.com') || url.includes('docs.google.com')) {
             embedUrl = url.replace('/view', '/preview');
        }

        viewBody.innerHTML = `<iframe src="${embedUrl}" style="width:100%; height:100%; border:none;"></iframe>`;
        document.getElementById('backdrop-view').classList.add('show');
        document.getElementById('sheet-view').classList.add('show');
    }

    function closeSheetView() {
        document.getElementById('backdrop-view').classList.remove('show');
        document.getElementById('sheet-view').classList.remove('show');
        document.getElementById('viewBody').innerHTML = '';
    }

    function showToast(msg, type) {
        const t = document.getElementById('appToast');
        document.getElementById('toastMessage').innerText = msg;
        t.className = 'app-toast-container show ' + (type === 'error' ? 'app-toast-error' : 'app-toast-success');
        setTimeout(() => t.classList.remove('show'), 3000);
    }
    // --- 6. DOM MANIPULATION (INSTANT MOVE) ---
    function moveCardToCompleted(tugasId, data) {
        const card = document.getElementById('card-tugas-' + tugasId);
        if(!card) return; // Should not happen

        // [NEW] 0. Update Badge Asal
        const parentId = card.parentElement.id; // tab-aktif or tab-terlewat
        let badgeId = '';
        if(parentId === 'tab-aktif') badgeId = 'badge-aktif';
        if(parentId === 'tab-terlewat') badgeId = 'badge-terlewat';

        if(badgeId) {
            const badge = document.getElementById(badgeId);
            if(badge) {
                let count = parseInt(badge.innerText);
                if(count > 1) {
                    badge.innerText = count - 1;
                } else {
                    badge.remove();
                }
            }
        }

        // 1. Pindah ke Tab Selesai
        const tabSelesai = document.getElementById('tab-selesai');
        const emptyState = tabSelesai.querySelector('.empty-state');
        if(emptyState) emptyState.remove(); // Hapus empty state jika ada

        tabSelesai.prepend(card); // Pindah ke paling atas

        // 2. Update Tombol Aksi
        const footer = card.querySelector('.card-footer-flex');
        // Hapus tombol kumpul/susulan lama
        const oldBtn = footer.querySelector('button.primary') || footer.querySelector('button[style*="background:#ed8936"]'); // Primary or Susulan Orange
        if(oldBtn) oldBtn.remove();
        
        // Buat tombol baru "Lihat / Edit"
        if (!data) data = {};
        const rawLink = (data.link === null || data.link === undefined) ? '' : String(data.link);
        const rawCat = (data.catatan === null || data.catatan === undefined) ? '' : String(data.catatan);
        
        const safeLink = rawLink.replace(/"/g, '&quot;');
        const safeCat = rawCat.replace(/"/g, '&quot;').replace(/\n/g, ' '); 
        const safeMetode = data.metode ? data.metode : 'link'; // safe default

        const newBtn = document.createElement('button');
        newBtn.className = 'btn-app outline';
        newBtn.innerHTML = 'Lihat / Edit';
        newBtn.onclick = function() { 
            openSubmitForm(tugasId, false, rawLink, rawCat, safeMetode); 
        };
        footer.appendChild(newBtn);

        // 3. Update Icon Warna (Blue/Red -> Green)
        const iconBox = document.getElementById('icon-box-' + tugasId);
        if(iconBox) {
            iconBox.className = 'icon-box green';
        }
        
        // 4. (Done above - Badge Update)
        
        // 5. Pindah View ke Tab Selesai
        const tabItemSelesai = document.querySelectorAll('.tab-item')[1]; // Index 1 is Selesai
        switchTab('selesai', tabItemSelesai);

        // 6. Cek Empty State di Tab Asal (Aktif/Terlewat)
        const sourceTab = document.getElementById(parentId);
        const remainingCards = sourceTab.querySelectorAll('.app-card');
        
        if (remainingCards.length === 0) {
            // Inject Empty State HTML
            let emptyHtml = '';
            if (parentId === 'tab-aktif') {
                emptyHtml = `
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="Empty">
                    <p>Tidak ada tugas aktif. Kerja bagus!</p>
                </div>`;
            } else if (parentId === 'tab-terlewat') {
                emptyHtml = `
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty">
                    <p>Tidak ada tugas terlewat. Pertahankan!</p>
                </div>`;
            }
            sourceTab.innerHTML = emptyHtml;
        }
    }

    // --- 7. MARK AS READ (Informasi Only) ---
    function markAsRead(tugasId) {
        const btn = document.getElementById('btn-read-' + tugasId);
        const originalHtml = btn.innerHTML;
        
        // Konfirmasi Sederhana (Optional, kalau mau langsung bisa hapus ini)
        // if(!confirm('Tandai tugas ini sebagai sudah dibaca?')) return;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'submit_tugas');
        formData.append('tugas_id', tugasId);
        formData.append('metode', 'manual');
        formData.append('catatan', ''); // Kosongkan saja

        fetch('../actions/tugas_siswa_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                showToast('Tugas ditandai sudah dibaca', 'success');
                
                // Move to Completed
                moveCardToCompleted(tugasId, {
                    link: '-', 
                    catatan: 'Sudah dibaca',
                    metode: 'manual'
                });

            } else {
                showToast(data.message, 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal terhubung ke server', 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

</script>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>

<?php
// --- FUNGSI RENDER KARTU TUGAS (PHP) ---
function renderTaskCard($t, $type) {
    // Tentukan Warna & Icon
    $icon_color = ($type == 'selesai') ? 'green' : (($type == 'terlewat') ? 'red' : 'blue');
    
    // Format Waktu
    $deadline_ts = $t['batas_waktu'] ? strtotime($t['batas_waktu']) : null;
    $deadline_str = $deadline_ts ? date('d M, H:i', $deadline_ts) : 'Tanpa Batas';
    
    // Cek Urgent (Kurang dari 24 jam)
    $is_urgent = ($type == 'aktif' && $deadline_ts && ($deadline_ts - time() < 86400));
    
    echo '<div id="card-tugas-'.$t['id'].'" class="app-card '.($type == 'terlewat' ? 'border-danger-soft bg-danger-soft' : '').'">';
    echo '<div class="card-header-flex">';
        echo '<div class="icon-box '.$icon_color.'" id="icon-box-'.$t['id'].'"><i class="fas fa-clipboard-list"></i></div>';
        echo '<div class="card-info">';
            echo '<div class="task-title">'.htmlspecialchars($t['judul']).'</div>';
            
            // Logic Tampilan Waktu
            if ($type == 'aktif' && $deadline_ts) {
                // Tampilkan Countdown Timer
                // Kita oper batas waktu ke data-attribute untuk JS
                $iso_date = date('Y-m-d H:i:s', $deadline_ts);
                echo '<div class="task-deadline urgent countdown-timer" data-deadline="'.$iso_date.'">';
                    echo '<i class="fas fa-spinner fa-spin"></i> Loading...';
                echo '</div>';
            } else {
                echo '<div class="task-deadline">';
                    echo '<i class="far fa-clock"></i> Deadline: '.$deadline_str;
                echo '</div>';
            }
            
        echo '</div>';
    echo '</div>';

    if (!empty($t['deskripsi'])) {
        echo '<div class="task-desc-preview">'.substr(strip_tags($t['deskripsi']), 0, 100).'...</div>';
    }

    echo '<div class="card-footer-flex">';
        
        // Tombol Lampiran
        if (!empty($t['link_lampiran'])) {
            echo '<button class="btn-app outline" onclick="openLampiran(\''.htmlspecialchars($t['judul']).'\', \''.$t['link_lampiran'].'\')">';
            echo '<i class="fas fa-paperclip me-1"></i> Soal';
            echo '</button>';
        } else { echo '<div></div>'; }

        // Tombol Aksi Utama
        if ($type == 'aktif') {
            if ($t['allow_upload'] == 1) {
                echo '<button class="btn-app primary" onclick="openSubmitForm('.$t['id'].', false)">Kumpulkan</button>';
            } else {
                // TUGAS INFORMASI (Hanya baca)
                echo '<button id="btn-read-'.$t['id'].'" class="btn-app primary" onclick="markAsRead('.$t['id'].')"><i class="fas fa-check-double me-2"></i> Sudah dibaca</button>';
            }
        } 
        elseif ($type == 'selesai') {
            if (isset($t['nilai']) && $t['nilai'] !== null) {
                echo '<div class="status-pill score"><i class="fas fa-star"></i> Nilai: '.$t['nilai'].'</div>';
            } else {
                // Mode Edit/Lihat
                // [UPDATE] Check Lock Deadline
                $is_locked_by_deadline = ($deadline_ts && time() > $deadline_ts);

                if ($is_locked_by_deadline) {
                     echo '<button class="btn-app disabled" disabled><i class="fas fa-lock me-1"></i> Terkunci</button>';
                } else {
                     // Pass metode parameter
                     $safe_metode = htmlspecialchars($t['metode'] ?? 'link');
                     echo '<button class="btn-app outline" onclick="openSubmitForm('.$t['id'].', false, \''.$t['link_pengumpulan'].'\', \''.htmlspecialchars($t['catatan_siswa'] ?? '').'\', \''.$safe_metode.'\')">Lihat / Edit</button>';
                }
            }
        } 
        elseif ($type == 'terlewat') {
            // LOGIKA PENTING: AUTO CLOSE CHECK
            if ($t['is_auto_close'] == 1) {
                // Auto Close ON: Tombol Mati
                echo '<button class="btn-app disabled" disabled><i class="fas fa-lock me-1"></i> Ditutup</button>';
            } else {
                // Auto Close OFF: Tombol Nyala (Susulan)
                echo '<button class="btn-app" style="background:#ed8936; color:white;" onclick="openSubmitForm('.$t['id'].', true)">Susulan</button>';
            }
        }
        
    echo '</div></div>';
}
?>