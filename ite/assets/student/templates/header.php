<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($page_title) ? $page_title : 'GARA Student' ?></title>
    <?php $root_path = isset($up_one) ? $up_one . '../' : '../'; ?>
        <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>"> 
    <link rel="stylesheet" href="css/belajar.css?v=<?= filemtime('css/belajar.css') ?>"> 
    
    <link href="<?= $root_path ?>components/cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="<?= $root_path ?>components/cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="<?= $root_path ?>components/fonts.googleapis.com/css/family_Inter_wght_400_600_700_display_swap.css" rel="stylesheet">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $root_path ?>logo/FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $root_path ?>logo/FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $root_path ?>logo/FARA_BLACK.svg">
<link rel="manifest" href="<?= $root_path ?>manifest.json">
<link rel="shortcut icon" href="<?= $root_path ?>logo/FARA_BLACK.svg">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-config" content="../<?= $root_path ?>favicon_io/browserconfig.xml">
<meta name="theme-color" content="#ffffff"> 

    <?php
    
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

    
    <style>


        
        html, body {
            overscroll-behavior-y: none; 
            -webkit-tap-highlight-color: transparent; 
            user-select: none; 
        }
        

    </style>

    <script>
        
        document.addEventListener('DOMContentLoaded', () => {
            
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function (event) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);

            
            document.addEventListener('gesturestart', function (e) {
                e.preventDefault();
            });

            
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

            
            document.addEventListener('wheel', function (e) {
                if (e.ctrlKey) {
                    e.preventDefault();
                }
            }, { passive: false });
        });
    </script>
    
    <script src="<?= $root_path ?>components/unpkg.com/htmx.org@2.0.4"></script>
    
    
    <script src="js/htmx_init.js?v=<?= filemtime('js/htmx_init.js') ?>"></script>

</head>
<body>

    
    <div class="app-header">
        <a href="dashboard.php" class="brand-text d-flex align-items-center text-decoration-none">
            <img src="<?= $root_path ?>logo/FARA_BLACK.svg" alt="GARA Logo" style="height: 35px; margin-right: 8px;">
            Garuda Akademi
        </a>
    </div>
    
    
    <main id="app-main">
