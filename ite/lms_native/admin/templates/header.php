<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? $page_title : 'GARA Admin' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables (Optional per page, but included here for consistency if needed or handle conditionally) -->
    <?php if(isset($use_datatables) && $use_datatables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <?php endif; ?>
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="apple-touch-icon" sizes="180x180" href="..\assets\img\FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="32x32" href="..\assets\img\FARA_BLACK.svg">
<link rel="icon" type="image/png" sizes="16x16" href="assets\img\FARA_BLACK.svg">
<link rel="manifest" href="assets\img\FARA_BLACK.svg">
<link rel="shortcut icon" href="assets\img\FARA_BLACK.svg">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-config" content="../../favicon_io/browserconfig.xml">
<meta name="theme-color" content="#ffffff"> 

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
                <span class="nav-link font-weight-bold text-primary d-flex align-items-center">
                    <img src="../assets/img/FARA_BLACK.svg" alt="GARA Logo" style="height: 30px; margin-right: 8px;">
                    Garuda Akademi
                </span>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link text-danger" href="../actions/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
