<?php







?>


<style>
    
    #loading-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.95); 
        z-index: 9999; 
        display: none; 
        justify-content: center;
        align-items: center;
        flex-direction: column;
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease-out;
    }

    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    
    .spinner-box {
        width: 60px;
        height: 60px;
        margin-bottom: 20px;
        border: 5px solid #e0f2fe;
        border-top: 5px solid #0d6efd; 
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    
    .loading-text {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    
    .loading-subtext {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>


<div id="loading-overlay">
    <div class="spinner-box"></div>
    <div class="loading-text">Memuat Ruang Ujian...</div>
    <div class="loading-subtext">Sedang menyiapkan data Kamu</div>
</div>


<div class="section-label">Teras Ilmu</div>


<div class="main-menu-grid">
    
    
    
    
    <a href="ruang_belajar.php" class="menu-item-v2"
       hx-get="ruang_belajar.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
        <div class="menu-icon-box" style="background: #e0f2fe; color: #0284c7;">
            <i class="fas fa-book-reader"></i>
        </div>
        <span class="menu-label">Ruang Belajar</span>
    </a>

    
    
    <a href="ruang_tugas.php" class="menu-item-v2"
       hx-get="ruang_tugas.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
        <div class="menu-icon-box" style="background: #ffedd5; color: #ea580c;">
            <i class="fas fa-tasks"></i>
        </div>
        <span class="menu-label">Ruang Tugas</span>
    </a>

    
    
    
    <a href="../exam/index.php" class="menu-item-v2"
       hx-get="../exam/index.php" hx-target="#app-main" hx-push-url="true" data-skeleton="default">
        <div class="menu-icon-box" style="background: #fee2e2; color: #dc2626;">
            <i class="fas fa-laptop-code"></i>
        </div>
        <span class="menu-label">Ruang Ujian</span>
    </a>

    

    
    
    <a href="ruang_fokus.php" class="menu-item-v2"
       hx-get="ruang_fokus.php" hx-target="#app-main" hx-push-url="true">
        <div class="menu-icon-box" style="background: #f3e8ff; color: #9333ea;">
            <i class="fas fa-bullseye"></i>
        </div>
        <span class="menu-label">Ruang Fokus</span>
    </a>

    
    
    <a href="ruang_diskusi.php" class="menu-item-v2"
       hx-get="ruang_diskusi.php" hx-target="#app-main" hx-push-url="true" data-skeleton="list">
        <div class="menu-icon-box" style="background: #dcfce7; color: #16a34a;">
            <i class="fas fa-comments"></i>
        </div>
        <span class="menu-label">Ruang Diskusi</span>
    </a>
    
    
    
    <a href="ruang_catatan.php" class="menu-item-v2"
       hx-get="ruang_catatan.php" hx-target="#app-main" hx-push-url="true">
        <div class="menu-icon-box" style="background: #fef3c7; color: #d97706;">
            <i class="fas fa-sticky-note"></i>
        </div>
        <span class="menu-label">Ruang Catatan</span>
    </a>

</div>


<script>
    function showLoading(e, url) {
        
        e.preventDefault();

        
        const overlay = document.getElementById('loading-overlay');
        
        
        overlay.style.display = 'flex';

        
        
        setTimeout(() => {
            window.location.href = url;
        }, 100);
    }
    
    
    window.addEventListener('pageshow', function(event) {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.style.display = 'none';
    });
</script>