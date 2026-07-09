/**
 * HTMX Initialization & Global Event Handlers
 * Tujuan: Mengatur behavior HTMX agar serasa SPA (Skeleton loading, No flicker)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Pastikan HTMX sudah load
    if (typeof htmx === 'undefined') {
        console.error('HTMX Library not found!');
        return;
    }

    // KONFIGURASI HTMX
    // Default swap style: innerHTML (mengganti isi #app-main)
    htmx.config.defaultSwapStyle = "innerHTML";

    // GLOBAL FLAG to detect Back Button
    window.isHistoryBack = false;

    // EVENT: BEFORE REQUEST (Saat link diklik, sebelum request dikirim)
    // Kita inject SKELETON agar user langsung melihat respon visual
    document.body.addEventListener('htmx:beforeRequest', function (evt) {
        // 1. PENCEGAHAN ULTIMATE: Check Flag Global dari Popstate
        if (window.isHistoryBack === true) {
            // console.log("Skipping skeleton due to History Back");
            return;
        }

        // 2. PENCEGAHAN HTMX NATIVE FLAG
        if (evt.detail && evt.detail.requestConfig && evt.detail.requestConfig.history) {
            return;
        }

        const target = evt.detail.target;

        // Hanya inject skeleton jika targetnya adalah Main Container (#app-main)
        if (target.id === 'app-main') {
            const trigger = evt.detail.requestConfig.elt;

            // Safety Check Trigger
            if (!trigger) return;

            // Logic Simple: Inject Skeleton
            const skeletonType = trigger.getAttribute('data-skeleton') || 'default';

            // Inject Skeleton HTML
            target.innerHTML = getSkeletonTemplate(skeletonType);
        }
    });

    // EVENT: RESPONSE ERROR
    document.body.addEventListener('htmx:responseError', function (evt) {
        alert("Terjadi kesalahan koneksi atau server error.");
    });

    // EVENT: NAVIGASI URL (Update Bottom Nav Active State secara Client-side)
    document.body.addEventListener('htmx:pushedIntoHistory', function (evt) {
        updateBottomNav(evt.detail.path);
    });

    // GLOBAL LISTENER: Handle Back Button Browser
    window.addEventListener('popstate', function () {
        // Set Flag Global
        window.isHistoryBack = true;

        // Reset flag setelah 1 detik (jaga-jaga)
        setTimeout(() => {
            window.isHistoryBack = false;
        }, 1000);

        // Beri sedikit delay agar URL window terupdate
        setTimeout(() => {
            updateBottomNav(window.location.pathname);
        }, 50);
    });
});

/**
 * Update Active State Bottom Nav
 * Mencocokkan URL saat ini dengan href di footer
 */
function updateBottomNav(fullPath) {
    if (!fullPath) return;

    // Ambil filename saja (contoh: /student/dashboard.php -> dashboard.php)
    let filename = fullPath.split('/').pop().split('?')[0];
    if (filename === '') filename = 'index.php'; // Fallback

    const navLinks = document.querySelectorAll('.bottom-nav .nav-item-link');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        const icon = link.querySelector('i');

        // Reset state
        link.classList.remove('active');

        // Cek kecocokan
        if (href === filename) {
            link.classList.add('active');

            // Ubah icon ke Solid (Filled) active state
            if (icon.classList.contains('fa-bell')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            if (icon.classList.contains('fa-user')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
        } else {
            // Ubah icon ke Outline (Inactive state)
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

/**
 * GENERATOR TEMPLATE SKELETON
 * Mengembalikan HTML string untuk placeholder loading
 */
function getSkeletonTemplate(type) {
    // Base skeleton style (bisa dipindah ke CSS file nanti)
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
        // Generic List (Materi/Tugas)
        html += `
            <div class="sk-line sk-title skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
            <div class="sk-card skeleton-anim"></div>
        `;
    } else {
        // Default Generic
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
