<?php $root_path = isset($up_one) ? $up_one . '../' : '../'; ?>
<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>

    
    </main>

    
    <div style="height: 80px;"></div>

    
    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #ffffff;
            border-top: 1px solid #dbdbdb;
            display: flex;
            justify-content: space-around; 
            align-items: center;
            padding: 12px 0;
            z-index: 1050; 
            box-shadow: 0 -1px 5px rgba(0,0,0,0.02);
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #8e8e8e; 
            transition: all 0.2s ease-in-out;
            padding: 8px 16px;
            border-radius: 20px; 
        }

        .nav-item-link i {
            font-size: 1.5rem; 
            display: block;
        }

        .nav-item-link span {
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 8px;
            display: none; 
        }

        
        .nav-item-link.active {
            background-color: #e7f3ff; 
            color: #0d6efd; 
        }

        .nav-item-link.active i {
            font-size: 1.4rem; 
        }

        .nav-item-link.active span {
            display: inline-block; 
        }

        
        .nav-item-link:hover {
            background-color: #f8f9fa;
        }
        .nav-item-link.active:hover {
            background-color: #dbeafe;
        }
    </style>

    <nav class="bottom-nav">
        
        
        <a href="dashboard.php" class="nav-item-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>"
           hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true" data-skeleton="dashboard">
            <i class="<?= ($current_page == 'dashboard.php') ? 'fas' : 'fas' ?> fa-home"></i>
            <span>Beranda</span>
        </a>

        
        
        <a href="notifikasi.php" class="nav-item-link <?= ($current_page == 'notifikasi.php') ? 'active' : '' ?>"
           hx-get="notifikasi.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
            <i class="<?= ($current_page == 'notifikasi.php') ? 'fas' : 'far' ?> fa-bell"></i>
            <span>Notifikasi</span>
        </a>

        
        
        <a href="tentangsaya.php" class="nav-item-link <?= ($current_page == 'tentangsaya.php') ? 'active' : '' ?>"
           hx-get="tentangsaya.php" hx-target="#app-main" hx-push-url="true">
            <i class="<?= ($current_page == 'tentangsaya.php') ? 'fas' : 'far' ?> fa-user"></i>
            <span>Akun Saya</span>
        </a>
    </nav>

    
    <script src="<?= $root_path ?>components/cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    
    <script src="<?= $root_path ?>components/cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    
    
    

    
    <script src="js/tracker.js?v=<?= time() ?>"></script>

    <script>
    
    </script>
</body>
</html>