<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($page_title) ? $page_title : 'GARA Student' ?></title>
        <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>"> 
    <link rel="stylesheet" href="css/belajar.css?v=<?= filemtime('css/belajar.css') ?>"> 
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (CDN) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="apple-touch-icon" sizes="180x180" href="..\assets\img\FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="32x32" href="..\assets\img\FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="16x16" href="assets\img\FARA_BLACK.svg">
<link rel="manifest" href="assets\img\FARA_BLACK.svg">
<link rel="shortcut icon" href="assets\img\FARA_BLACK.svg">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-config" content="../../favicon_io/browserconfig.xml">
<meta name="theme-color" content="#ffffff"> 

    <?php
    // INJECT ADDITIONAL CSS (Fix FOUC / Render Blocking)
    if (isset($additional_css)) {
        if (is_array($additional_css)) {
            foreach ($additional_css as $css_file) {
                echo '<link rel="stylesheet" href="' . $css_file . '?v=' . filemtime($css_file) . '">' . PHP_EOL;
            }
        } else {
            echo '<link rel="stylesheet" href="' . $additional_css . '?v=' . filemtime($additional_css) . '">' . PHP_EOL;
        }
    }
    ?>

</head>

    <!-- APP-MODE: Webview Optimizations (Disable Zoom & Selection) -->
    <style>


        /* FIX JELLY EFFECT & NATIVE FEEL */
        html, body {
            overscroll-behavior-y: none; /* Matikan efek jelly/bounce scroll */
            -webkit-tap-highlight-color: transparent; /* Hapus kotak biru saat klik */
            user-select: none; /* Cegah seleksi teks global */
        }
        

    </style>

    <script>
        // Disable Zoom Capabilities (Pinch, Double Tap, Keyboard)
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Disable Double Tap Zoom
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function (event) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);

            // 2. Disable Pinch Zoom
            document.addEventListener('gesturestart', function (e) {
                e.preventDefault();
            });

            // 3. Disable Keyboard Zoom (Ctrl + / - / 0)
            document.addEventListener('keydown', function (e) {
                if (e.ctrlKey && (
                    e.key === '+' || 
                    e.key === '-' || 
                    e.key === '0' || 
                    e.key === '='
                )) {
                    e.preventDefault();
                }
            });

            // 4. Disable Wheel Zoom
            document.addEventListener('wheel', function (e) {
                if (e.ctrlKey) {
                    e.preventDefault();
                }
            }, { passive: false });
        });
    </script>
    <!-- HTMX Library (CDN) -->
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    
    <!-- HTMX Config & Skeleton Logic -->
    <script src="js/htmx_init.js?v=<?= filemtime('js/htmx_init.js') ?>"></script>

</head>
<body>

    <!-- 1. Header Sticky -->
    <div class="app-header">
        <a href="dashboard.php" class="brand-text d-flex align-items-center text-decoration-none">
            <img src="../assets/img/FARA_BLACK.svg" alt="GARA Logo" style="height: 35px; margin-right: 8px;">
            Garuda Akademi
        </a>
    </div>
    
    <!-- MAIN CONTENT WRAPPER (SPA TARGET) -->
    <main id="app-main">
