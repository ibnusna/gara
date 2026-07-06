





<link rel="stylesheet" href="{{ asset('assets/student/css/root.css') }}">


<link rel="manifest" href="{{ asset('manifest.json') }}" crossorigin="use-credentials">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="GARA">


<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180x180.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icons/icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('icons/icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="128x128" href="{{ asset('icons/icon-128x128.png') }}">
<link rel="apple-touch-icon" sizes="96x96"   href="{{ asset('icons/icon-96x96.png') }}">


<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="32x32"   href="{{ asset('icons/icon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16"   href="{{ asset('icons/icon-16x16.png') }}">



<link rel="apple-touch-startup-image" media="screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-1290x2796.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2796x1290.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-1179x2556.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2556x1179.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-750x1334.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-1334x750.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-2048x2732.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2732x2048.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-1668x2388.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2388x1668.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-1640x2360.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2360x1640.png') }}">

<link rel="apple-touch-startup-image" media="screen and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"  href="{{ asset('pwa-assets/splash/splash-1536x2048.png') }}">
<link rel="apple-touch-startup-image" media="screen and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" href="{{ asset('pwa-assets/splash/splash-2048x1536.png') }}">




<style>
    
    .pwa-install-banner {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translate(-50%, 40px);
        width: calc(100% - 32px);
        max-width: 480px;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-2xl);
        padding: 14px 18px;
        box-shadow: var(--shadow-xl);
        z-index: 999999;
        display: flex;
        align-items: center;
        gap: 14px;
        opacity: 0;
        pointer-events: none;
        transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .pwa-install-banner.show {
        transform: translate(-50%, 0);
        opacity: 1;
        pointer-events: auto;
    }
    .pwa-app-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-xl);
        background: var(--color-primary-light);
        border: 1px solid var(--color-border);
        padding: 6px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .pwa-app-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .pwa-info { flex-grow: 1; }
    .pwa-title {
        color: var(--color-text-main);
        font-family: var(--font-family-base);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-bold);
        margin-bottom: 2px;
    }
    .pwa-desc {
        color: var(--color-text-muted);
        font-family: var(--font-family-base);
        font-size: var(--text-xs);
        line-height: 1.4;
    }
    .pwa-actions {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex-shrink: 0;
    }
    .pwa-btn-install {
        background: var(--gradient-primary);
        color: var(--color-text-inverse);
        border: none;
        padding: 8px 16px;
        font-family: var(--font-family-base);
        font-size: var(--text-xs);
        font-weight: var(--font-weight-bold);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all var(--transition-fast);
        box-shadow: var(--shadow-md);
        text-align: center;
    }
    .pwa-btn-install:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
        opacity: 0.92;
    }
    .pwa-btn-close {
        background: transparent;
        color: var(--color-text-muted);
        border: none;
        font-family: var(--font-family-base);
        font-size: var(--text-xs);
        font-weight: var(--font-weight-medium);
        padding: 4px 8px;
        cursor: pointer;
        text-align: center;
        transition: color var(--transition-fast);
    }
    .pwa-btn-close:hover { color: var(--color-text-body); }

    @media (max-width: 768px) {
        .pwa-install-banner { bottom: 84px; }
    }

    
    #ios-pwa-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11, 87, 208, 0.18);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 9999998;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.35s ease;
    }
    #ios-pwa-modal-overlay.show {
        opacity: 1;
        pointer-events: auto;
    }
    #ios-pwa-modal {
        background: var(--color-surface);
        border-radius: var(--radius-2xl) var(--radius-2xl) 0 0;
        padding: 28px 24px 36px;
        width: 100%;
        max-width: 520px;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        box-shadow: var(--shadow-xl);
    }
    #ios-pwa-modal-overlay.show #ios-pwa-modal {
        transform: translateY(0);
    }
    .ios-modal-handle {
        width: 36px;
        height: 4px;
        background: var(--color-border);
        border-radius: var(--radius-full);
        margin: 0 auto 20px;
    }
    .ios-modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }
    .ios-modal-app-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-xl);
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: var(--shadow-md);
        background: var(--color-primary-light);
    }
    .ios-modal-app-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .ios-modal-app-info h3 {
        font-size: var(--text-base);
        font-weight: var(--font-weight-bold);
        color: var(--color-text-main);
        margin: 0 0 2px;
        font-family: var(--font-family-base);
    }
    .ios-modal-app-info p {
        font-size: var(--text-xs);
        color: var(--color-text-muted);
        margin: 0;
        font-family: var(--font-family-base);
    }
    .ios-modal-close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 28px;
        height: 28px;
        border-radius: var(--radius-full);
        background: var(--color-primary-light);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary);
        font-size: var(--text-sm);
        transition: background var(--transition-fast);
    }
    .ios-modal-close-btn:hover { background: var(--color-border); }
    .ios-modal-divider {
        height: 1px;
        background: var(--color-border);
        margin: 0 0 20px;
    }
    .ios-modal-title {
        font-size: var(--text-xs);
        font-weight: var(--font-weight-bold);
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 16px;
        font-family: var(--font-family-base);
    }
    .ios-guide-steps {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .ios-guide-step {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .ios-step-number {
        width: 28px;
        height: 28px;
        border-radius: var(--radius-full);
        background: var(--color-primary-light);
        color: var(--color-primary);
        font-size: var(--text-xs);
        font-weight: var(--font-weight-bold);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-family: var(--font-family-base);
    }
    .ios-step-text {
        font-size: var(--text-sm);
        color: var(--color-text-body);
        line-height: 1.5;
        font-family: var(--font-family-base);
        padding-top: 4px;
    }
    .ios-step-text strong {
        color: var(--color-text-main);
        font-weight: var(--font-weight-bold);
    }
    .ios-step-icon-inline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background: var(--color-primary);
        border-radius: var(--radius-md);
        vertical-align: middle;
        margin: 0 3px;
        flex-shrink: 0;
    }
    .ios-step-icon-inline svg {
        width: 12px;
        height: 12px;
        fill: var(--color-text-inverse);
    }
    .ios-modal-footer-note {
        margin-top: 20px;
        padding: 12px 14px;
        background: var(--color-primary-light);
        border-radius: var(--radius-xl);
        font-size: var(--text-xs);
        color: var(--color-text-muted);
        text-align: center;
        font-family: var(--font-family-base);
        line-height: 1.5;
    }
</style>


<script>
(function () {
    'use strict';

    
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}')
                .then(function (registration) {
                    console.log('[GARA PWA] SW registered, scope:', registration.scope);
                })
                .catch(function (err) {
                    console.error('[GARA PWA] SW registration failed:', err);
                });
        });
    }

    
    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
    var isInStandaloneMode = ('standalone' in window.navigator) && window.navigator.standalone;

    
    var deferredPrompt = null;

    function initializePwaUi() {
        
        if (document.getElementById('pwaInstallBanner')) return;

        
        var bannerDiv = document.createElement('div');
        bannerDiv.id = 'pwaInstallBanner';
        bannerDiv.className = 'pwa-install-banner';
        bannerDiv.innerHTML = `
            <div class="pwa-app-icon">
                <img src="{{ asset('icons/icon-192x192.png') }}" alt="GARA Icon">
            </div>
            <div class="pwa-info">
                <div class="pwa-title">Pasang GARA</div>
                <div class="pwa-desc">Akses materi, tugas & ujian lebih praktis di layar utama.</div>
            </div>
            <div class="pwa-actions">
                <button id="pwaBtnInstall" class="pwa-btn-install">Pasang</button>
                <button id="pwaBtnClose" class="pwa-btn-close">Nanti</button>
            </div>
        `;
        document.body.appendChild(bannerDiv);

        
        var iosOverlay = document.createElement('div');
        iosOverlay.id = 'ios-pwa-modal-overlay';
        iosOverlay.innerHTML = `
            <div id="ios-pwa-modal">
                <div class="ios-modal-handle"></div>
                <button class="ios-modal-close-btn" id="iosModalCloseBtn" aria-label="Tutup">
                    <svg viewBox="0 0 10 10" width="10" height="10" fill="currentColor">
                        <path d="M1 1l8 8M9 1l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="ios-modal-header">
                    <div class="ios-modal-app-icon">
                        <img src="{{ asset('icons/icon-192x192.png') }}" alt="GARA">
                    </div>
                    <div class="ios-modal-app-info">
                        <h3>Pasang GARA di iPhone / iPad</h3>
                        <p>Ikuti 3 langkah mudah di Safari</p>
                    </div>
                </div>
                <div class="ios-modal-divider"></div>
                <div class="ios-modal-title">Cara Menambahkan ke Layar Utama</div>
                <div class="ios-guide-steps">
                    <div class="ios-guide-step">
                        <div class="ios-step-number">1</div>
                        <div class="ios-step-text">
                            Tap tombol <strong>Bagikan</strong>
                            <span class="ios-step-icon-inline">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l-3 3h2v9h2V5h2L12 2zM5 13v7h14v-7h-2v5H7v-5H5z"/></svg>
                            </span>
                            di bagian <strong>bawah</strong> layar Safari (ikon kotak dengan panah ke atas).
                        </div>
                    </div>
                    <div class="ios-guide-step">
                        <div class="ios-step-number">2</div>
                        <div class="ios-step-text">
                            Gulir ke bawah daftar menu yang muncul, lalu cari dan tap
                            <strong>"Tambahkan ke Layar Utama"</strong> (Add to Home Screen).
                        </div>
                    </div>
                    <div class="ios-guide-step">
                        <div class="ios-step-number">3</div>
                        <div class="ios-step-text">
                            Konfirmasi nama aplikasi <strong>"GARA"</strong>, lalu tap tombol
                            <strong>"Tambahkan"</strong> di pojok kanan atas. Selesai! 🎉
                        </div>
                    </div>
                </div>
                <div class="ios-modal-footer-note">
                    💡 Pastikan Anda membuka halaman ini menggunakan <strong>Safari</strong>. Browser lain (Chrome, Firefox) tidak mendukung fitur ini di iOS.
                </div>
            </div>
        `;
        document.body.appendChild(iosOverlay);

        
        var pwaBanner  = document.getElementById('pwaInstallBanner');
        var btnInstall = document.getElementById('pwaBtnInstall');
        var btnClose   = document.getElementById('pwaBtnClose');

        if (btnInstall) {
            btnInstall.addEventListener('click', function () {
                if (!deferredPrompt) return;
                if (pwaBanner) pwaBanner.classList.remove('show');
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function (choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[GARA PWA] User accepted installation.');
                    } else {
                        console.log('[GARA PWA] User dismissed installation.');
                    }
                    deferredPrompt = null;
                });
            });
        }

        if (btnClose) {
            btnClose.addEventListener('click', function () {
                if (pwaBanner) pwaBanner.classList.remove('show');
                localStorage.setItem('gara_pwa_dismissed_at', Date.now().toString());
            });
        }

        
        var iosCloseBtn  = document.getElementById('iosModalCloseBtn');

        if (iosCloseBtn && iosOverlay) {
            iosCloseBtn.addEventListener('click', function () {
                iosOverlay.classList.remove('show');
                localStorage.setItem('gara_ios_guide_dismissed_at', Date.now().toString());
                var nudge = document.getElementById('ios-install-nudge');
                if (nudge) {
                    nudge.classList.remove('show');
                    setTimeout(function () {
                        nudge.style.display = 'none';
                    }, 400);
                }
            });
        }

        if (iosOverlay) {
            iosOverlay.addEventListener('click', function (e) {
                if (e.target === iosOverlay) {
                    iosOverlay.classList.remove('show');
                    localStorage.setItem('gara_ios_guide_dismissed_at', Date.now().toString());
                    var nudge = document.getElementById('ios-install-nudge');
                    if (nudge) {
                        nudge.classList.remove('show');
                        setTimeout(function () {
                            nudge.style.display = 'none';
                        }, 400);
                    }
                }
            });
        }

        
        showAndroidBannerIfEligible();
    }

    function showAndroidBannerIfEligible() {
        if (!deferredPrompt) return;
        var pwaBanner = document.getElementById('pwaInstallBanner');
        if (!pwaBanner) return;

        var dismissedAt = localStorage.getItem('gara_pwa_dismissed_at');
        var now = Date.now();
        var sevenDays = 7 * 24 * 60 * 60 * 1000;

        if (!dismissedAt || (now - parseInt(dismissedAt, 10)) > sevenDays) {
            setTimeout(function () {
                pwaBanner.classList.add('show');
            }, 1500);
        }
    }

    
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        showAndroidBannerIfEligible();
    });

    
    window.addEventListener('appinstalled', function () {
        console.log('[GARA PWA] App successfully installed.');
        var pwaBanner = document.getElementById('pwaInstallBanner');
        if (pwaBanner) pwaBanner.classList.remove('show');
        deferredPrompt = null;
    });

    
    if (isIos && !isInStandaloneMode) {
        var iosDismissedAt = localStorage.getItem('gara_ios_guide_dismissed_at');
        var iosNow = Date.now();
        var sevenDays = 7 * 24 * 60 * 60 * 1000;

        window.GARA_IOS_DETECTED = true;

        if (!iosDismissedAt || (iosNow - parseInt(iosDismissedAt, 10)) > sevenDays) {
            window.garaShowIosModal = function () {
                var overlay = document.getElementById('ios-pwa-modal-overlay');
                if (overlay) overlay.classList.add('show');
            };
        }
    }

    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePwaUi);
    } else {
        initializePwaUi();
    }

}());
</script>

