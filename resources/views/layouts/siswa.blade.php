<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'GARA Student')</title>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="stylesheet" href="{{ asset('assets/student/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/belajar.css') }}">
    {{-- dashboard_v2.css loaded globally so mesh-bg, font, glass-card styles are always available --}}
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">

    @stack('css')

    
    <style>
        html, body {
            overscroll-behavior-y: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Global mesh-bg support — ensures background is always present */
        .layout-mesh-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            background-color: transparent; /* background grey ada di html element, bukan di sini */
            overflow: hidden;
            pointer-events: none;
        }

        /* Glassmorphic layout header */
        .app-header {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.6) !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06) !important;
            font-family: 'Poppins', sans-serif !important;
        }

        /* Smooth page transition — di-non-aktifkan untuk halaman desktop layout
           karena animation membuat new stacking context yang memecah position:fixed pada sidebar */
        #app-main {
            /* animation: pageEntrance 0.3s cubic-bezier(0.4, 0, 0.2, 1) both; */
            /* Diaktifkan hanya di mobile via class .is-mobile-page */
        }
        .is-mobile-page #app-main {
            animation: pageEntrance 0.3s cubic-bezier(0.4, 0, 0.2, 1) both;
        }
        @keyframes pageEntrance {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
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

            
            
            
            const garaShortcuts = {
                'D': '{{ route('student.dashboard') }}',
                'M': '{{ route('student.materi.index') }}',
                'T': '{{ route('student.tugas.index') }}',
                'F': '{{ route('student.ruang-fokus.index') }}',
                'C': '{{ route('student.ruang-catatan.index') }}',
            };

            document.addEventListener('keydown', function (e) {
                
                if (!e.shiftKey) return;
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
                if (e.ctrlKey || e.altKey || e.metaKey) return;

                const key = e.key.toUpperCase();

                
                if (e.key === 'Escape') {
                    
                    const activeModal = document.querySelector('.modal.show');
                    if (activeModal && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getInstance(activeModal);
                        if (modalInstance) modalInstance.hide();
                    }
                    
                    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                        Swal.close();
                    }
                    return;
                }

                
                if (garaShortcuts[key]) {
                    e.preventDefault();
                    
                    const shortcutNames = { D: 'Dashboard', M: 'Materi', T: 'Tugas', F: 'Ruang Fokus', C: 'Ruang Catatan' };
                    console.log(`⌨️ Shortcut: Shift+${key} → ${shortcutNames[key]}`);
                    window.location.href = garaShortcuts[key];
                }
            });
            
        });
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/htmx/2.0.4/htmx.min.js"></script>

    
    <script src="{{ asset('assets/student/js/htmx_init.js') }}"></script>
    {{-- dashboard_v2.css already loaded globally in <head> above --}}
</head>

<body>

    <!-- GLOBAL MESH BACKGROUND — renders on every student page -->
    <div class="layout-mesh-bg">
        <div class="mesh-blob blob-blue"></div>
        <div class="mesh-blob blob-purple"></div>
        <div class="mesh-blob blob-cyan"></div>
    </div>

    
    <div class="app-header">
        <a href="{{ route('student.dashboard') }}" class="brand-text d-flex align-items-center text-decoration-none">
            










                <span style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 35px;
                height: 35px;
                margin-right: 8px;
                overflow: hidden;
                flex-shrink: 0;
                isolation: isolate;
                border-radius: 0;
                -webkit-transform: translateZ(0);
                transform: translateZ(0);
                will-change: transform;
            ">
                    <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" alt="GARA Logo" width="35" height="35" style="
                        display: block;
                        width: 35px;
                        height: 35px;
                        object-fit: contain;
                        max-width: none;
                        -webkit-transform: translateZ(0);
                        transform: translateZ(0);
                        image-rendering: -webkit-optimize-contrast;
                        image-rendering: crisp-edges;
                    " loading="eager">
                </span>
                Garuda Akademi
        </a>
    </div>

    
    <main id="app-main">
        @yield('content')
    </main>

    @php
        
        
        
        $isWebView = str_contains(request()->userAgent() ?? '', 'GARA_OFFICIAL_APP');
    @endphp

    
    @if(!$isWebView)
        <div class="bottom-nav-spacer" style="height: 80px;"></div>
    @endif

    
    
    @if(!$isWebView)
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
                
                box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.02);
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

        @php
            $current_route = request()->route()->getName();
        @endphp

        <nav class="bottom-nav">
            
            <a href="{{ route('student.dashboard') }}"
                class="nav-item-link {{ $current_route == 'student.dashboard' ? 'active' : '' }}" hx-boost="false">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>

            
            <a href="{{ route('student.notifikasi.index') }}"
                class="nav-item-link {{ $current_route == 'student.notifikasi.index' ? 'active' : '' }}" hx-boost="false">
                <i class="far fa-bell"></i>
                <span>Notifikasi</span>
            </a>

            
            <a href="{{ route('student.tentang-saya.index') }}"
                class="nav-item-link {{ $current_route == 'student.tentang-saya.index' ? 'active' : '' }}" hx-boost="false">
                <i class="far fa-user"></i>
                <span>Akun Saya</span>
            </a>
        </nav>
    @endif

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @yield('modals')
    @stack('js')
</body>

</html>