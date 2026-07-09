




document.addEventListener('DOMContentLoaded', () => {
    
    if (typeof htmx === 'undefined') {
        console.error('HTMX Library not found!');
        return;
    }

    
    
    htmx.config.defaultSwapStyle = "innerHTML";

    
    window.isHistoryBack = false;

    
    
    
    document.body.addEventListener('htmx:beforeSwap', function (evt) {
        
        if (document.getElementById('app-fokus-wrapper')) {
            
            
            
            window._garaFokusShouldStop = true;
            console.log('🍅 Ruang Fokus: Pomodoro tick loop stopped before HTMX swap');
        }
    });

    
    
    
    document.body.addEventListener('htmx:afterSwap', function (evt) {
        
        window._garaFokusShouldStop = false;

        
        if (document.getElementById('sholat-times-grid')) {
            if (typeof loadJadwalSholat === 'function') {
                loadJadwalSholat();
                console.log('🕌 Jadwal Sholat widget re-bound after HTMX swap');
            }
        }

        
        if (document.getElementById('trivia-question')) {
            if (typeof loadTrivia === 'function') {
                loadTrivia();
                console.log('🎯 Trivia widget re-bound after HTMX swap');
            }
        }

        
        if (document.getElementById('pengumuman-container')) {
            if (typeof loadPengumuman === 'function') {
                loadPengumuman();
            }
        }

        
        if (document.getElementById('bab-list-container')) {
            if (typeof initMateriSearch === 'function') {
                
                setTimeout(initMateriSearch, 50);
            }
        }
    });

    
    
    document.body.addEventListener('htmx:beforeRequest', function (evt) {
        
        if (window.isHistoryBack === true) {
            
            return;
        }

        
        if (evt.detail && evt.detail.requestConfig && evt.detail.requestConfig.history) {
            return;
        }

        const target = evt.detail.target;

        
        if (target.id === 'app-main') {
            const trigger = evt.detail.requestConfig.elt;

            
            if (!trigger) return;

            
            const skeletonType = trigger.getAttribute('data-skeleton') || 'default';

            
            target.innerHTML = getSkeletonTemplate(skeletonType);
        }
    });

    
    document.body.addEventListener('htmx:responseError', function (evt) {
        alert("Terjadi kesalahan koneksi atau server error.");
    });

    
    document.body.addEventListener('htmx:pushedIntoHistory', function (evt) {
        updateBottomNav(evt.detail.path);
    });

    
    window.addEventListener('popstate', function () {
        
        window.isHistoryBack = true;

        
        setTimeout(() => {
            window.isHistoryBack = false;
        }, 1000);

        
        setTimeout(() => {
            updateBottomNav(window.location.pathname);
        }, 50);
    });
});





function updateBottomNav(fullPath) {
    if (!fullPath) return;

    
    let filename = fullPath.split('/').pop().split('?')[0];
    if (filename === '') filename = 'index.php'; 

    const navLinks = document.querySelectorAll('.bottom-nav .nav-item-link');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        const icon = link.querySelector('i');

        
        link.classList.remove('active');

        
        if (href === filename) {
            link.classList.add('active');

            
            if (icon.classList.contains('fa-bell')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            if (icon.classList.contains('fa-user')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
        } else {
            
            if (icon.classList.contains('fa-bell')) {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
            if (icon.classList.contains('fa-user')) {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
    });
}





function getSkeletonTemplate(type) {
    
    const skeletonClass = "skeleton-box";

    let html = `
    <style>
        .skeleton-anim {
            animation: skeleton-loading 1.5s infinite linear;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .sk-card { background: #fff; border-radius: 12px; padding: 15px; margin-bottom: 15px; border: 1px solid #efefef; }
        .sk-line { height: 16px; margin-bottom: 10px; border-radius: 4px; }
        .sk-title { height: 24px; width: 60%; margin-bottom: 15px; }
        .sk-img { width: 100%; height: 180px; border-radius: 8px; margin-bottom: 10px; }
    </style>
    <div class="container-fluid pt-3 fade-in">
    `;

    if (type === 'dashboard') {
        html += `
            <!-- Hero Skeleton -->
            <div class="sk-card skeleton-anim" style="height: 120px;"></div>
            <!-- Menu Grid Skeleton -->
            <div class="row g-2 mb-3">
                <div class="col-3"><div class="sk-card skeleton-anim" style="height: 80px;"></div></div>
                <div class="col-3"><div class="sk-card skeleton-anim" style="height: 80px;"></div></div>
                <div class="col-3"><div class="sk-card skeleton-anim" style="height: 80px;"></div></div>
                <div class="col-3"><div class="sk-card skeleton-anim" style="height: 80px;"></div></div>
            </div>
            <!-- List Content Skeleton -->
            <div class="sk-card skeleton-anim" style="height: 100px;"></div>
        `;
    } else if (type === 'list') {
        
        html += `
            <div class="sk-line sk-title skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
        `;
    } else {
        
        html += `
            <div class="sk-card">
                <div class="sk-line sk-title skeleton-anim"></div>
                <div class="sk-line skeleton-anim" style="width: 100%"></div>
                <div class="sk-line skeleton-anim" style="width: 90%"></div>
                <div class="sk-line skeleton-anim" style="width: 80%"></div>
            </div>
             <div class="sk-card">
                <div class="sk-line skeleton-anim" style="width: 100%"></div>
                <div class="sk-line skeleton-anim" style="width: 90%"></div>
            </div>
        `;
    }

    html += `</div>`;
    return html;
}
