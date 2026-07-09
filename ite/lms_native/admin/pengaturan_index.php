<?php
// GARA - Garuda Akademi
// File: admin/pengaturan_index.php
// Tujuan: Menu utama untuk pengaturan sistem global (Siswa, Kode Akses, Master Data).

require_once '../actions/auth_helper.php';

// Cek Login Guru (Tanpa perlu cek sesi kelas, karena ini pengaturan global)
requireGuru();

$page_title = 'Pengaturan Sistem | GARA Admin';
$is_settings_page = true; // Trigger 'Back to Portal' in header
require_once 'templates/header_portal.php';
?>


<div class="gara-container">
    <div class="mb-4">
        <h2>Menu Pengaturan</h2>
        <p class="text-muted">Kelola data master dan konfigurasi sistem Garuda Akademi.</p>
    </div>

    <!-- Grid Menu -->
    <div class="gara-grid cols-4">
        
        <!-- Menu 1: Manajemen Siswa -->
        <a href="pengaturan_siswa.php" class="gara-card interactive text-center">
            <div class="gara-icon-box icon-primary mx-auto mb-3">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4 class="gara-card-title mb-1">Siswa</h4>
            <p class="text-muted small mb-0">Kelola Data Siswa</p>
        </a>


        <!-- Menu 3: Data Master -->
        <a href="pengaturan_master.php" class="gara-card interactive text-center">
            <div class="gara-icon-box icon-danger mx-auto mb-3">
                <i class="fas fa-database"></i>
            </div>
            <h4 class="gara-card-title mb-1">Data Master</h4>
            <p class="text-muted small mb-0">Kelas & Mapel</p>
        </a>

        <!-- Menu 4: Siklus Akademik -->
        <a href="pengaturan_sistem.php" class="gara-card interactive text-center">
            <div class="gara-icon-box icon-success mx-auto mb-3">
                <i class="fas fa-sync-alt"></i>
            </div>
            <h4 class="gara-card-title mb-1">Siklus</h4>
            <p class="text-muted small mb-0">Semester & Kenaikan</p>
        </a>

    </div>

    <!-- Alert Note -->
    <div class="gara-alert gara-alert-warning mt-4">
        <i class="fas fa-info-circle mt-1"></i>
        <div>
            <strong>Catatan Admin</strong><br>
            Perubahan data di sini bersifat global dan akan mempengaruhi semua sesi pembelajaran. Harap berhati-hati saat menghapus data siswa atau kelas.
        </div>
    </div>
</div>


<?php require_once 'templates/footer_portal.php'; ?>
