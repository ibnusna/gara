<?php
// Deteksi halaman saat ini untuk menentukan menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

    <!-- CLOSE MAIN CONTENT -->
    </main>

    <!-- SPACE KOSONG DI BAWAH AGAR KONTEN TIDAK TERTUTUP NAVBAR -->
    <div style="height: 80px;"></div>

    <!-- BOTTOM NAVIGATION BAR (KHUSUS SISWA) -->
    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #ffffff;
            border-top: 1px solid #dbdbdb;
            display: flex;
            justify-content: space-around; /* Agar jarak antar menu rata */
            align-items: center;
            padding: 12px 0;
            z-index: 1050; /* Pastikan di atas elemen lain */
            box-shadow: 0 -1px 5px rgba(0,0,0,0.02);
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #8e8e8e; /* Warna abu-abu untuk menu tidak aktif */
            transition: all 0.2s ease-in-out;
            padding: 8px 16px;
            border-radius: 20px; /* Bentuk Pill */
        }

        .nav-item-link i {
            font-size: 1.5rem; /* Ukuran ikon */
            display: block;
        }

        .nav-item-link span {
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 8px;
            display: none; /* Sembunyikan teks secara default */
        }

        /* STYLE SAAT MENU AKTIF (PILL STYLE) */
        .nav-item-link.active {
            background-color: #e7f3ff; /* Latar biru muda lembut */
            color: #0d6efd; /* Warna biru utama */
        }

        .nav-item-link.active i {
            font-size: 1.4rem; /* Sedikit lebih kecil agar proporsional dengan teks */
        }

        .nav-item-link.active span {
            display: inline-block; /* Tampilkan teks saat aktif */
        }

        /* Efek Hover Halus */
        .nav-item-link:hover {
            background-color: #f8f9fa;
        }
        .nav-item-link.active:hover {
            background-color: #dbeafe;
        }
    </style>

    <nav class="bottom-nav">
        <!-- 1. HOME (DASHBOARD) -->
        <!-- 1. HOME (DASHBOARD) -->
        <a href="dashboard.php" class="nav-item-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>"
           hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard">
            <i class="<?= ($current_page == 'dashboard.php') ? 'fas' : 'fas' ?> fa-home"></i>
            <span>Beranda</span>
        </a>

        <!-- 2. NOTIFIKASI -->
        <!-- 2. NOTIFIKASI -->
        <a href="notifikasi.php" class="nav-item-link <?= ($current_page == 'notifikasi.php') ? 'active' : '' ?>"
           hx-get="notifikasi.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
            <i class="<?= ($current_page == 'notifikasi.php') ? 'fas' : 'far' ?> fa-bell"></i>
            <span>Notifikasi</span>
        </a>

        <!-- 3. INFO AKUN (TENTANG SAYA) -->
        <!-- 3. INFO AKUN (TENTANG SAYA) -->
        <a href="tentangsaya.php" class="nav-item-link <?= ($current_page == 'tentangsaya.php') ? 'active' : '' ?>"
           hx-get="tentangsaya.php" hx-target="#app-main" hx-push-url="true">
            <i class="<?= ($current_page == 'tentangsaya.php') ? 'fas' : 'far' ?> fa-user"></i>
            <span>Akun Saya</span>
        </a>
    </nav>

    <!-- Bootstrap JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CORE NAVIGATION ENGINE (SPA) -->
    <!-- CORE NAVIGATION ENGINE (SPA) - DISABLED (REPLACED BY HTMX) -->
    <!-- <script src="js/navigation_core.js?v=<?= time() ?>"></script> -->

    <script>
    // Global Script
    </script>
</body>
</html>