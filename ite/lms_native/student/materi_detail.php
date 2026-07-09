<?php
// GARA - Garuda Akademi
// File: student/materi_detail.php
// Tujuan: LEVEL 3 - Halaman belajar fokus (Video Player + Material Resources)
// Revisi: Full Youtube Style (Header Hidden saat ada video).

require_once '../actions/auth_helper.php';
require_once '../config/database.php';
require_once '../actions/func_helper.php';

// 1. Cek Akses
requireSiswa();

// 2. Ambil ID Materi
if (!isset($_GET['id'])) {
    header("Location: ruang_belajar.php");
    exit();
}

$id_materi_db = $_GET['id'];
$mapel_id     = $_SESSION['mapel_id'];
$kelas_id     = $_SESSION['kelas_id'];

// 3. Query Data Materi
$stmt = $pdo->prepare("SELECT * FROM rpp_materi WHERE id = ? AND mapel_id = ? AND kelas_id = ?");
$stmt->execute([$id_materi_db, $mapel_id, $kelas_id]);
$materi = $stmt->fetch();

if (!$materi) {
    echo "Materi tidak ditemukan atau Anda tidak memiliki akses.";
    exit();
}

// 4. Tandai Selesai (Auto-Progress)
markMateriAsCompleted($materi['id_materi']);

// 5. Persiapan Data Tampilan
$youtube_id = getYoutubeEmbedId($materi['link_youtube']);
$has_video  = !empty($youtube_id);

$page_title = $materi['judul_materi'];

// Load header bawaan
// Load header bawaan
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<!-- STYLE KHUSUS: Full Focus Mode -->
<link rel="stylesheet" href="css/yt_desktop.css" media="(min-width: 992px)">
<style>
    <?php if ($has_video): ?>
    /* LOGIKA 1: Sembunyikan Header Sepenuhnya jika ada video (Full Youtube Feel) - HANYA DI MOBILE */
    @media (max-width: 991.98px) {
        .app-header {
            display: none !important;
        }
    }
    
    /* Pastikan Video Wrapper mentok ke atas */
    .video-wrapper {
        margin-top: 0;
    }
    <?php else: ?>
    /* Jika TIDAK ada video, kita sembunyikan navigasi kanan header agar tetap clean */
    .app-header .btn-back,
    .app-header a[href*="logout"],
    .app-header .header-right,
    .app-header .fa-sign-out-alt,
    .app-header .page-title,
    .app-header .page-subtitle { 
        display: none !important; 
    }
    
    .app-header .brand-wrapper, 
    .app-header .brand-text,
    .app-header a[href="dashboard.php"] {
        display: flex !important;
        pointer-events: auto; 
    }
    <?php endif; ?>

    /* SHEET ACTIONS (Tombol Close & External di Modal) */
    .sheet-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .btn-sheet-action {
        background: #f0f2f5;
        border: none;
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #555;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none; /* Untuk tag <a> */
    }
    .btn-sheet-action:hover { background: #e4e6eb; color: #333; }
    .btn-sheet-action.close { color: #dc3545; background: #ffebeb; }
</style>

<!-- CONTAINER UTAMA (Desktop Layout Wrapper) -->
<div class="desktop-layout-container <?= !$has_video ? 'no-video' : '' ?>">

    <!-- KOLOM KIRI: VIDEO (Hanya jika ada video) -->
    <?php if ($has_video): ?>
    <div class="desktop-video-column">
        <!-- CONTAINER VIDEO (LEVEL 3 - ATAS) -->
        <!-- Logic IF moved to parent -->
            <div class="video-wrapper">
                <div class="video-container">
                    <iframe 
                        id="yt-player"
                        src="https://www.youtube.com/embed/<?= $youtube_id ?>?rel=0&modestbranding=1&playsinline=1&controls=1&showinfo=0" 
                        allowfullscreen 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen">
                    </iframe>

                    <!-- MASKING COVER -->
                    <div class="video-cover" id="videoCover" 
                         style="background-image: url('https://img.youtube.com/vi/<?= $youtube_id ?>/maxresdefault.jpg');">
                        <div class="play-button-circle">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Logic ENDIF moved to parent -->
    </div>
    <?php endif; ?>

    <!-- KOLOM KANA: MATERI & DESKRIPSI -->
    <div class="desktop-content-column">
        <!-- BODY KONTEN (LEVEL 3 - BAWAH) -->
        <div class="content-body" style="min-height: 80vh; <?= !$has_video ? 'margin-top: 0; border-radius: 0;' : '' ?>">
            
            <!-- Judul Materi -->
            <span class="badge bg-light text-primary mb-2">
                BAB <?= formatNomorBab($materi['bab']) ?> - Bagian <?= $materi['bagian'] ?>
            </span>
            <h1 class="materi-title-lg"><?= htmlspecialchars($materi['judul_materi']) ?></h1>

            <div class="materi-desc">
                <p>Silakan pelajari materi ini dan akses bahan ajar melalui menu di bawah ini.</p>
            </div>

            <!-- GRID TOMBOL SUMBER DAYA -->
            <div class="resource-grid">
                
                <!-- 1. PPT (Modal) -->
                <?php if (!empty($materi['link_ppt'])): ?>
                    <?php 
                        $link_ppt = $materi['link_ppt'];
                        $is_gdrive_ppt = isGoogleDriveLink($link_ppt);
                    ?>
                    <button onclick="openResource('Slide PPT', '<?= $link_ppt ?>', <?= $is_gdrive_ppt ? 'true' : 'false' ?>)" 
                       class="btn-resource">
                        <i class="fas fa-file-powerpoint text-warning"></i>
                        <span>Slide PPT</span>
                    </button>
                <?php endif; ?>

                <!-- 2. E-MODUL (Modal) -->
                <?php if (!empty($materi['link_modul'])): ?>
                    <?php 
                        $link_modul = $materi['link_modul'];
                        $is_gdrive_modul = isGoogleDriveLink($link_modul);
                    ?>
                    <button onclick="openResource('E-Modul', '<?= $link_modul ?>', <?= $is_gdrive_modul ? 'true' : 'false' ?>)" 
                       class="btn-resource" style="background: #f0f8ff; border-color: #cce5ff;">
                        <i class="fas fa-file-pdf text-primary"></i>
                        <span>Baca Modul</span>
                    </button>
                <?php endif; ?>

                <!-- 3. CATATAN / NOTEBOOK (Tetap Tab Baru / Opsional) -->
                <?php if (!empty($materi['link_notebook'])): ?>
                    <a href="<?= $materi['link_notebook'] ?>" target="_blank" class="btn-resource">
                        <i class="fas fa-book-open text-info"></i>
                        <span>Tanya AI</span>
                    </a>
                <?php endif; ?>

                <!-- 4. TUGAS (Modal) -->
                <?php if (!empty($materi['link_tugas'])): ?>
                    <?php 
                        $link_tugas = $materi['link_tugas'];
                        $is_gdrive_tugas = isGoogleDriveLink($link_tugas);
                    ?>
                    <button onclick="openResource('Modul Utama', '<?= $link_tugas ?>', <?= $is_gdrive_tugas ? 'true' : 'false' ?>)" 
                       class="btn-resource">
                        <i class="fas fa-book text-success"></i>
                        <span>E-Book</span>
                    </button>
                <?php endif; ?>

            </div>

            <!-- TOMBOL KEMBALI (DI BAWAH) -->
            <a href="ruang_belajar_bab.php?bab=<?= $materi['bab'] ?>" class="btn-back-bottom"
               hx-get="ruang_belajar_bab.php?bab=<?= $materi['bab'] ?>" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Topik
            </a>
            
        </div>
    </div>

</div>

<!-- OFFCANVAS / BOTTOM SHEET VIEWER -->
<div id="backdrop" class="offcanvas-backdrop" onclick="closeSheet()"></div>
<div id="bottomSheet" class="bottom-sheet">
    <div class="sheet-header">
        <h5 id="sheetTitle" class="sheet-title">Document Viewer</h5>
        <div class="sheet-actions">
            <!-- Tombol Buka di Tab Baru -->
            <a id="sheetExternalLink" href="#" target="_blank" class="btn-sheet-action" title="Buka di Tab Baru">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <!-- Tombol Tutup -->
            <button class="btn-sheet-action close" onclick="closeSheet()" title="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="sheet-content" id="sheetBody">
        <!-- Iframe akan di-inject disini lewat JS -->
    </div>
</div>

<!-- SCRIPT PENGENDALI UI -->
<?php 
if (is_ajax_request()): ?>
    <!-- Inject CSS for Partial Render -->
    <link rel="stylesheet" href="css/belajar.css?v=<?= filemtime('css/belajar.css') ?>">
    <link rel="stylesheet" href="css/yt_desktop.css" media="(min-width: 992px)">
<?php endif; ?>

<script>
// Logic Masking Video (Updated for HTMX)
function initVideoPlayer() {
    const cover = document.getElementById('videoCover');
    const iframe = document.getElementById('yt-player');
    
    if(cover && iframe) {
        cover.addEventListener('click', function() {
            this.style.display = 'none';
            let src = iframe.src;
            if (src.indexOf('?') > -1) {
                iframe.src = src + "&autoplay=1";
            } else {
                iframe.src = src + "?autoplay=1";
            }
        });
    }
}

// Run immediately for HTMX swap
initVideoPlayer();
// Also run on DOMContentLoaded just in case of full reload
document.addEventListener('DOMContentLoaded', initVideoPlayer);

function openResource(title, url, isGDrive) {
    const backdrop = document.getElementById('backdrop');
    const sheet = document.getElementById('bottomSheet');
    const sheetTitle = document.getElementById('sheetTitle');
    const sheetBody = document.getElementById('sheetBody');
    const sheetExternalLink = document.getElementById('sheetExternalLink');

    // Set Judul & Link External
    sheetTitle.innerText = title;
    sheetExternalLink.href = url; // Update link tombol 'Buka di Tab Baru'

    // Tentukan URL Embed untuk Iframe
    let embedUrl = url;
    if (isGDrive) {
        embedUrl = "https://docs.google.com/viewer?url=" + encodeURIComponent(url) + "&embedded=true";
        if(url.includes('drive.google.com') && url.includes('/view')) {
            embedUrl = url.replace('/view', '/preview');
        }
    }

    // Inject Iframe
    sheetBody.innerHTML = `<iframe src="${embedUrl}" allow="autoplay"></iframe>`;

    // Tampilkan Sheet
    backdrop.classList.add('show');
    sheet.classList.add('show');
    document.body.style.overflow = 'hidden'; 
}

function closeSheet() {
    const backdrop = document.getElementById('backdrop');
    const sheet = document.getElementById('bottomSheet');
    const sheetBody = document.getElementById('sheetBody');

    backdrop.classList.remove('show');
    sheet.classList.remove('show');
    document.body.style.overflow = ''; 

    // Hapus iframe agar beratnya hilang
    setTimeout(() => {
        sheetBody.innerHTML = '';
    }, 300);
}
</script>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php'; 
}
?>