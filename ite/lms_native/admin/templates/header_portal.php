<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? $page_title : 'GARA Admin' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE Theme Style (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- GARA Unified CSS -->
    <link rel="stylesheet" href="css/index.css?v=2.0">
    
    <?php if(isset($use_datatables) && $use_datatables): ?>
        <!-- DataTables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <?php endif; ?>
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/FARA_BLACK.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/FARA_BLACK.svg">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/FARA_BLACK.svg">
    <link rel="shortcut icon" href="../assets/img/FARA_BLACK.svg">
    <meta name="theme-color" content="#ffffff">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white border-bottom-0">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <img src="../assets/img/FARA_BLACK.svg" alt="Logo" style="height: 35px; margin-right: 10px;">
        <span class="brand-text font-weight-bold text-dark">Garuda Akademi</span>
      </a>

      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center">
        <!-- Logic Menu: Jika sedang di halaman pengaturan, munculkan tombol KEMBALI. Jika di Portal, munculkan PENGATURAN -->
        <?php if(isset($is_settings_page) && $is_settings_page): ?>
            <li class="nav-item mr-2">
                <a href="pilih_sesi.php" class="btn btn-default btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Portal
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item mr-2">
                <a href="pengaturan_index.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-cogs mr-1"></i> Pengaturan Sistem
                </a>
            </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <span class="nav-link text-dark">Halo, <b><?= isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Guru' ?></b></span>
        </li>
        <li class="nav-item ml-2">
            <a href="../actions/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </li>
      </ul>
    </div>
  </nav>
  <!-- /.navbar -->
