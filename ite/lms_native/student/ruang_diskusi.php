<?php
// GARA - Garuda Akademi
// File: student/ruang_diskusi.php
// Tujuan: Halaman Utama Diskusi Siswa (Twitter Clone Interface) - Standalone Mode
// VERSION: 2.0 - Modal Detail Upgraded (Twitter-Style Full Layout)

require_once '../actions/auth_helper.php';
requireSiswa();
require_once '../components/avatar_helper.php'; // Load helper baru
// Ambil Data Sesi
$nama_mapel = $_SESSION['nama_mapel'] ?? 'Mata Pelajaran';
$nama_kelas = $_SESSION['nama_kelas'] ?? 'Kelas';
$siswa_nama = $_SESSION['nama'];
$siswa_nis  = $_SESSION['nis'];

// DiceBear Miniavs: Style emoji estetik, gender-neutral, clean
$avatar_siswa = "https://api.dicebear.com/9.x/fun-emoji/svg?seed=" . urlencode($siswa_nama);

// Avatar Guru (Statis sesuai brief)
$avatar_guru = "../img/guru.webp"; 
?>
<!DOCTYPE html>
<?php
$page_title = 'Dashboard Siswa | GARA';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>
    
    <!-- Custom Twitter Style -->
    <link rel="stylesheet" href="css/diskusi_style.css?v=<?= time() ?>">
        <link rel="stylesheet" href="css/diss.css?v=<?= time() ?>">
    <!-- STYLING TAMBAHAN UNTUK MODAL DETAIL (TWITTER-STYLE) -->
   
</head>
<body>

    <!-- Data Store untuk JavaScript -->
    <script>
        var CURRENT_USER = {
            id: <?= $_SESSION['user_id'] ?>,
            nama: "<?= htmlspecialchars($siswa_nama) ?>",
            nis: "<?= htmlspecialchars($siswa_nis) ?>",
            avatar: "<?= $avatar_siswa ?>"
        };
        var MAPEL_DATA = {
            id: <?= $_SESSION['mapel_id'] ?>,
            nama: "<?= htmlspecialchars($nama_mapel) ?>"
        };
        // Path gambar guru statis untuk render di JS
        var GURU_STATIC = {
            nama: "Ibnu Sina Sudrajat",
            avatar: "../img/guru.webp",
            verified: true
        };
    </script>

<div class="app-header">
    <div class="header-left">
        <a href="dashboard.php" class="btn-back" hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Ruang Diskusi</h1>
            <span class="page-subtitle"><?= htmlspecialchars($_SESSION['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
        </div>
    </div>
</div>

    <div class="container-fluid p-0">

        <!-- PULL TO REFRESH SPINNER -->
        <div id="ptr-spinner" style="height:0; overflow:hidden; transition: height 0.2s; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center; border-bottom:1px solid transparent;">
            <div id="ptr-content" style="display:flex; align-items:center; gap:8px; color:var(--text-secondary); font-size:14px; font-weight:600;">
                <i class="fas fa-arrow-down" id="ptr-icon" style="transition: transform 0.2s;"></i>
                <i class="fas fa-circle-notch fa-spin" id="ptr-loading" style="display:none; color:var(--color-primary);"></i>
                <span id="ptr-text">Tarik untuk menyegarkan</span>
            </div>
        </div>

        <!-- INPUT TRIGGER AREA (Top) -->
        <div class="input-area">
            <img src="<?= $avatar_siswa ?>" alt="Profil" class="avatar">
            <div class="input-wrapper">
                <div class="fake-input" id="trigger-post-modal">Apa yang sedang terjadi?</div>
            </div>
            <button class="icon-btn" id="trigger-image-modal" style="color:var(--color-primary)">
                <i class="fa-regular fa-image"></i>
            </button>
        </div>

        <!-- FEED CONTAINER -->
        <div id="feed-container">
            <!-- Loading State Awal -->
            <div style="padding:40px; text-align:center; color:var(--text-secondary);">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i>
                <p style="margin-top:10px;">Memuat diskusi...</p>
            </div>
        </div>

        <!-- LOAD MORE BUTTON -->
        <div class="load-more-container" id="load-more-area" style="display:none;">
            <button class="btn-load-more" id="btn-load-more">Muat lebih banyak</button>
        </div>

        <!-- Spacer Bottom -->
        <div style="height: 80px;"></div>

    </div>

    <!-- MODAL POSTING (Overlay) - Create Thread -->
    <div class="modal-overlay" id="modal-post">
        <div class="modal-box">
            <div class="modal-header">
                <button class="btn-close" id="btn-close-modal"><i class="fa-solid fa-xmark"></i></button>
                <button class="btn-post" id="btn-submit-post">Posting</button>
            </div>
            
            <div class="modal-body">
                <img src="<?= $avatar_siswa ?>" class="avatar" style="width:40px;height:40px;">
                <div class="modal-input-area">
                    <form id="form-create-thread">
                        <input type="hidden" name="action" value="create_thread">
                        <input type="text" name="judul" placeholder="Judul (Opsional)" 
                               style="width:100%; border:none; outline:none; font-weight:700; font-size:16px; margin-bottom:5px; display:none;" id="input-judul">
                        
                        <textarea name="isi_konten" id="post-textarea" class="auto-textarea" 
                                  placeholder="Apa yang sedang terjadi?"></textarea>
                        
                        <!-- Preview Gambar Upload -->
                        <div class="image-preview-container" id="preview-container">
                            <img id="img-preview" src="">
                            <button type="button" class="btn-remove-image" id="btn-remove-img">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" name="media" id="file-input" accept="image/*" hidden>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <div class="action-icons">
                    <button class="icon-btn" onclick="document.getElementById('file-input').click()">
                        <i class="fa-regular fa-image"></i>
                    </button>
                    <button class="icon-btn" style="opacity:0.5; cursor:default;"><i class="fa-solid fa-list-ul"></i></button>
                    <button class="icon-btn" style="opacity:0.5; cursor:default;"><i class="fa-regular fa-face-smile"></i></button>
                    <button class="icon-btn" style="opacity:0.5; cursor:default;"><i class="fa-regular fa-calendar"></i></button>
                </div>
                
                <div style="font-size:12px; color:var(--text-secondary);" id="char-count"></div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL DETAIL THREAD (VIEW & REPLY) - UPGRADED -->
    <!-- ============================================ -->
    <div class="modal-overlay" id="modal-detail">
        <div class="modal-box">
            <!-- Header Modal Detail -->
            <div class="modal-header">
                <button class="btn-close" id="btn-close-detail">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <h3>Postingan</h3>
            </div>

            <!-- Scrollable Content -->
            <div class="modal-scroll-content" id="modal-scroll-content">
                
                <!-- PTR SPINNER MODAL -->
                <div id="ptr-spinner-modal" style="height:0; overflow:hidden; transition: height 0.2s; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center; border-bottom:1px solid transparent;">
                    <div style="display:flex; align-items:center; gap:8px; color:var(--text-secondary); font-size:14px; font-weight:600;">
                        <i class="fas fa-arrow-down" id="ptr-icon-modal" style="transition: transform 0.2s;"></i>
                        <i class="fas fa-circle-notch fa-spin" id="ptr-loading-modal" style="display:none; color:var(--color-primary);"></i>
                    </div>
                </div>

                <!-- SECTION 1: MAIN POST (Original Thread) -->
                <div class="detail-main-post" id="detail-main-post">
                    <!-- Loading Spinner -->
                    <div class="loading-spinner">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <p>Memuat postingan...</p>
                    </div>
                </div>

                <!-- SECTION 2: REPLY INPUT (Sticky) -->
                <div class="detail-reply-input" id="detail-reply-input">
                    <img src="<?= $avatar_siswa ?>" class="reply-input-avatar" alt="Avatar Anda">
                    <div class="reply-input-wrapper" style="flex-direction: column; align-items: stretch;">
                        <div style="display: flex; gap: 0px; width: 100%;">
                            <input type="text" class="reply-input-field" id="input-reply-text" placeholder="Posting balasan Anda">
                            <div class="reply-action-icons">

                                </button>
                            </div>
                            <button class="reply-submit-btn" id="btn-submit-reply">Balas</button>
                        </div>
                        <!-- Preview Image untuk Reply -->
                        <div class="reply-image-preview" id="reply-image-preview">
                            <img id="reply-img-preview" src="" alt="Preview">
                            <button type="button" class="reply-remove-image" id="btn-remove-reply-img">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Hidden File Input untuk Reply -->
                    <input type="file" id="reply-file-input" accept="image/*" hidden>
                </div>

                <!-- SECTION 3: REPLIES LIST -->
                <div class="detail-replies-list" id="detail-replies-list">
                    <!-- Loading Spinner -->
                    <div class="loading-spinner">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <p>Memuat balasan...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL EDIT (Reused for Edit Thread & Reply) -->
    <div class="modal-overlay" id="modal-edit">
        <div class="modal-box">
            <div class="modal-header">
                <button class="btn-close" onclick="$('#modal-edit').removeClass('active')"><i class="fa-solid fa-xmark"></i></button>
                <h3 style="font-size:16px; font-weight:700; margin:0;">Edit</h3>
                <button class="btn-post active" id="btn-save-edit">Simpan</button>
            </div>
            <div class="modal-body">
                <div class="modal-input-area">
                    <input type="hidden" id="edit-type" value=""> <!-- 'thread' or 'reply' -->
                    <input type="hidden" id="edit-id" value="">
                    <textarea id="edit-textarea" class="auto-textarea" style="min-height:150px;" placeholder="Edit konten..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- LIBRARY JQUERY (WAJIB ADA) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SCRIPT UTAMA -->
    <script src="js/diskusi_app.js?v=<?= time() ?>"></script>
<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>
</body>
</html>