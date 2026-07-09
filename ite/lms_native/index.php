<?php

require_once 'actions/auth_helper.php';
require_once 'config/database.php';
require_once 'config/app_settings.php';

// 2. CEK PERANGKAT: Jika HP biasa (Bukan App Gara) -> BLOKIR 404
enforceAppAccess();

// --- LOGIKA AUTO-LOGIN ---
// Jika browser masih menyimpan "Tiket Masuk" (Cookie/Session) yang valid:
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];

    if ($role === 'super_admin') {
        header("Location: super_admin/dashboard.php");
        exit();
    } elseif ($role === 'operator') {
        header("Location: operator/dashboard.php");
        exit();
    } elseif ($role === 'kepsek') {
        header("Location: kepala_sekolah/dashboard.php");
        exit();
    } elseif ($role === 'guru') {
        header("Location: guru/pilih_sesi.php");
        exit();
    } elseif ($role === 'siswa') {
        header("Location: student/dashboard.php");
        exit();
    }
}
// Jika belum login, biarkan HTML di bawah ini dimuat.
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0056b3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>Garuda Akademi - Login</title>
    <link rel="apple-touch-icon" sizes="180x180" href="favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../../favicon_io/site.webmanifest">
    <link rel="shortcut icon" href="favicon_io/favicon.ico">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="favicon_io/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <!-- Favicon -->
    <link rel="icon" href="assets/img/logo.webp" type="image/webp">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (Poppins untuk kesan Modern App) -->
    <link href="https://fonts.googleapis.com/css/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <!-- Main App Container -->
    <main class="app-container">

        <!-- VIEW 1: WELCOME SCREEN (Halaman Awal) -->
        <section id="welcome-screen" class="screen active">
            <div class="screen-content">
                <div class="brand-display">
                    <img src="assets/img/GARA_WHITE.svg" alt="GARA Logo" class="app-logo animate-in">
                    <h1 class="app-name animate-in delay-1">GARA</h1>
                    <p class="app-tagline animate-in delay-2">
                        <?= htmlspecialchars($sekolah_nama) ?>
                    </p>
                </div>

                <div class="welcome-text animate-in delay-3">
                    <h2>Selamat Datang di<br>Era Belajar Digital</h2>
                    <p>Akses materi, tugas, dan ujian dalam satu genggaman. Cepat, Mudah, Efisien.</p>
                </div>

                <div class="action-area animate-in delay-4">
                    <!-- Indikator Slide (Visual Only sesuai referensi) -->
                    <div class="slide-indicators">
                        <span class="dot"></span>
                        <span class="dot active"></span>
                        <span class="dot"></span>
                    </div>

                    <button id="btn-continue" class="btn-primary full-width">
                        Mulai Akses
                    </button>
                </div>
            </div>
        </section>

        <!-- VIEW 2: LOGIN SCREEN (Form Login) -->
        <section id="login-screen" class="screen">
            <!-- Background Image Container -->
            <div class="bg-layer" style="background-image: url('assets/img/bglogin.jpg');"></div>
            <div class="overlay-layer"></div>

            <div class="screen-content login-layout">
                <div class="login-header">
                    <img src="assets/img/GARA_WHITE.svg" alt="GARA Logo" class="mini-logo">
                    <h3>Masuk Akun</h3>
                    <p>Silakan login untuk melanjutkan</p>
                </div>

                <form id="loginForm" class="app-form">
                    <div class="form-group">
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" id="identifier" name="identifier" class="form-control"
                            placeholder="Username / NIS" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password"
                            required autocomplete="current-password">
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary full-width" id="btnLogin">
                            LOGIN
                        </button>
                    </div>

                </form>
            </div>
        </section>

    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="login.js"></script>

    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let title = 'Akses Ditolak';
                let text = 'Terjadi kesalahan sistem.';
                let icon = 'error';

                <?php if ($_GET['error'] === 'maintenance'): ?>
                    title = 'Masa Perbaikan';
                    text = 'Sistem sedang dalam mode perbaikan (Maintenance Mode). Silakan coba lagi nanti.';
                    icon = 'warning';
                <?php elseif ($_GET['error'] === 'belum_login'): ?>
                    text = 'Anda harus login terlebih dahulu.';
                <?php elseif ($_GET['error'] === 'akses_ditolak'): ?>
                    text = 'Anda tidak memiliki hak akses ke halaman tersebut.';
                <?php endif; ?>

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonColor: '#3085d6'
                });

                // Clean URL
                window.history.replaceState({}, document.title, "index.php");
            });
        </script>
    <?php endif; ?>

</body>

</html>