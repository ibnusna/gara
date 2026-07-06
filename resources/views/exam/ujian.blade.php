<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GARA - Arena Ujian</title>

    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    
    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}?v={{ time() }}">
    <link rel="icon" href="{{ \App\Models\AppSetting::getLogo('GARA_ICON.svg') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('exam/assets/exam.css') }}?v={{ time() }}" media="(max-width: 768px)">
    <link rel="stylesheet" href="{{ asset('exam/assets/ujian.css') }}?v={{ time() }}" media="(min-width: 769px)">
    <link rel="stylesheet" href="{{ asset('exam/assets/css/camera.css') }}">
    <style>
        .q-image-area {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            gap: 16px;
        }
        .soal-asset-card {
            background: white;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid var(--color-border, #e2e8f0);
            margin-bottom: 0px;
            width: fit-content;
            min-width: 50%;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .soal-asset-card.embed-asset {
            width: 100%;
        }
        .soal-asset-card img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            display: block;
            object-fit: contain;
            cursor: zoom-in;
        }
        @media (min-width: 768px) {
            .soal-asset-card img {
                max-height: 65vh;
            }
        }
        .soal-asset-card iframe {
            width: 100%;
            aspect-ratio: 16 / 9;
            height: auto;
            border-radius: 8px;
            border: none;
            display: block;
        }
        .soal-asset-card audio {
            width: 100%;
            min-width: 250px;
            border-radius: 8px;
            outline: none;
        }
    </style>
</head>

<body class="ready">

    
    
    
    <div id="offlineBanner" class="offline-banner hidden">
        <div class="banner-content">
            <i class="fas fa-wifi-slash"></i>
            <span>KONEKSI PUTUS! SEGERA SAMBUNGKAN LAGI KE INTERNET!.</span>
        </div>
    </div>

    
    
    
    <div id="loadingOverlay" class="overlay-screen skeleton-mode">
        
        <div class="skeleton-header">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div class="skeleton" style="width: 120px; height: 16px;"></div> 
                <div class="skeleton" style="width: 80px; height: 16px;"></div> 
            </div>
            <div class="skeleton" style="width: 100%; height: 32px; border-radius: 8px;"></div>
            
        </div>

        
        <div class="skeleton-body">
            
            <div class="skeleton" style="width: 100px; height: 24px; margin-bottom: 1rem; border-radius:16px;"></div>

            
            <div class="skeleton-q-box">
                <div class="skeleton" style="width: 90%; height: 16px; margin-bottom: 0.5rem;"></div>
                <div class="skeleton" style="width: 95%; height: 16px; margin-bottom: 0.5rem;"></div>
                <div class="skeleton" style="width: 80%; height: 16px; margin-bottom: 1rem;"></div>
                <div class="skeleton" style="width: 100%; height: 150px; border-radius: 8px;"></div>
                
            </div>

            
            <div class="skeleton-opt-box">
                <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                <div class="skeleton" style="width: 70%; height: 16px;"></div>
            </div>
            <div class="skeleton-opt-box">
                <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                <div class="skeleton" style="width: 60%; height: 16px;"></div>
            </div>
            <div class="skeleton-opt-box">
                <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                <div class="skeleton" style="width: 80%; height: 16px;"></div>
            </div>
            <div class="skeleton-opt-box">
                <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                <div class="skeleton" style="width: 50%; height: 16px;"></div>
            </div>
        </div>

        
        <div class="skeleton-footer">
            <div class="skeleton" style="width: 100%; height: 36px; border-radius: 6px;"></div>
            <div class="skeleton" style="width: 100%; height: 36px; border-radius: 6px;"></div>
            <div class="skeleton" style="width: 100%; height: 36px; border-radius: 6px;"></div>
        </div>
    </div>

    
    
    
    <div id="gatewayOverlay" class="overlay-screen hidden">
        <div class="gateway-card">
            <div class="gateway-icon">
                <img src="{{ \App\Models\AppSetting::getLogo('3dlogo.svg') }}" alt="Gateway Icon">
            </div>
            <h2>Mulai Ujian</h2>
            <button id="btnEnterFullscreen" class="btn-gateway">
                Mulai Ujian Sekarang <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    
    
    
    <div id="mainExamContainer" class="hidden">

        
        <header class="exam-header">
            <div class="header-left">
                
                <div class="brand-identity">
                    <img src="{{ \App\Models\AppSetting::getLogo('GARA_WHITE.svg') }}" alt="GARA Logo" class="header-logo"
                        onerror="this.style.display='none'; this.nextElementSibling.style.marginLeft='0';">

                    <div class="title-group">
                        <h1 class="brand-title">Ruang Asesmen</h1>
                        <span class="academy-name">Garuda Akademi</span>
                    </div>
                </div>

                
                <button id="btnToggleSidebar" class="btn-icon-toggle desktop-only" title="Tutup/Buka Navigasi Soal">
                    <i class="fas fa-bars"></i>
                </button>

                
                <div class="header-divider desktop-only"></div>

                
                <div class="exam-info">
                    <span id="mapelName" class="mapel-badge">Memuat Mapel...</span>
                </div>
            </div>

            
            <div class="header-center mobile-only">
                <button id="btnOpenSoalMobile" class="btn-text-icon">
                    <i class="fas fa-th-large"></i> <span>Daftar Soal</span>
                </button>
            </div>

            <div class="header-right">
                <div class="timer-box">
                    <i class="fas fa-stopwatch"></i>
                    <span id="timerDisplay">--:--:--</span>
                </div>
                <div class="user-profile desktop-only">
                    <i class="fas fa-user-circle"></i>
                    <span id="userName">Peserta</span>
                </div>
            </div>
        </header>

        
        <main class="exam-layout">

            
            <aside class="exam-sidebar desktop-only">
                <div class="sidebar-header">
                    NAVIGASI SOAL
                </div>
                <div class="sidebar-content">
                    <div class="nav-grid-container" id="sidebarNavGrid"></div>
                </div>
                <div class="sidebar-footer">
                    <div class="legend-item"><span class="dot green"></span> Dijawab</div>
                    <div class="legend-item"><span class="dot yellow"></span> Ragu</div>
                    <div class="legend-item"><span class="dot gray"></span> Kosong</div>
                </div>
            </aside>

            
            <section class="question-area">
                <div class="question-header">
                    <div class="q-number-badge">
                        Soal No. <span id="displayNoSoal">1</span>
                    </div>
                    <div class="font-controls desktop-only">
                        <button class="btn-font sm">A</button>
                        <button class="btn-font md">A</button>
                        <button class="btn-font lg">A</button>
                    </div>
                </div>

                <div class="question-scroll-area">
                    <div id="questionImageContainer" class="q-image-area hidden"></div>
                    <div id="questionText" class="q-text"></div>
                    <div id="optionsContainer" class="q-options"></div>
                </div>
            </section>

        </main>

        
        <footer class="exam-footer">
            <button id="btnPrev" class="nav-btn secondary">
                <i class="fas fa-chevron-left"></i> <span class="btn-label">Sebelumnya</span>
            </button>

            <div class="ragu-checkbox-wrapper">
                <input type="checkbox" id="checkRagu" class="ragu-input">
                <label for="checkRagu" class="ragu-label-box">
                    <span class="check-icon"><i class="fas fa-check"></i></span>
                    <span>Ragu</span>
                </label>
            </div>

            <button id="btnNext" class="nav-btn primary">
                <span class="btn-label">Selanjutnya</span> <i class="fas fa-chevron-right"></i>
            </button>

            <button id="btnSubmit" class="nav-btn success hidden">
                <span class="btn-label">Selesai</span> <i class="fas fa-flag-checkered"></i>
            </button>
        </footer>
    </div>

    
    <div id="modalDaftarSoal" class="custom-modal hidden">
        <div class="modal-backdrop"></div>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Daftar Soal</h3>
                <button id="btnCloseModalSoal" class="btn-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="nav-grid-container" id="mobileNavGrid"></div>
            </div>
            <div class="modal-footer-legend">
                <div class="legend-item"><span class="dot green"></span> Jawab</div>
                <div class="legend-item"><span class="dot yellow"></span> Ragu</div>
                <div class="legend-item"><span class="dot gray"></span> BelUm</div>
            </div>
        </div>
    </div>

    
    <div id="modalImageZoom" class="custom-modal hidden" style="z-index: 10000;">
        <div class="modal-backdrop" id="backdropImageZoom"></div>
        <div class="modal-card" style="max-width: 90vw; max-height: 90vh; background: transparent; box-shadow: none;">
            <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <button id="btnCloseImageZoom" class="btn-close" style="position: absolute; top: -10px; right: -10px; z-index: 10; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"><i class="fas fa-times"></i></button>
                <img id="zoomedImage" src="" style="max-width: 100%; max-height: 85vh; border-radius: 12px; object-fit: contain; background: white; padding: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
            </div>
        </div>
    </div>

    
    
    

    
    <template id="tpl-pg-biasa">
        <div class="option-card">
            <div class="opt-label">A</div>
            <div class="opt-text">Isi Opsi</div>
        </div>
    </template>

    
    <template id="tpl-pg-kompleks">
        <div class="option-card" style="cursor: pointer;">
            <div class="opt-label" style="background:none; border:none; color:inherit; font-size:1.2rem;">
                <i class="far fa-square checkbox-icon"></i>
            </div>
            <div class="opt-text">Isi Opsi Kompleks</div>
        </div>
    </template>

    
    <template id="tpl-benar-salah-container">
        <div class="bs-container" style="display: flex; flex-direction: column; gap: 16px;"></div>
    </template>

    <template id="tpl-benar-salah-item">
        <div class="bs-card-item"
            style="background: #ffffff; border: 1px solid var(--color-border); border-radius: 12px; padding: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: transform 0.2s;">
            <div class="bs-statement"
                style="margin-bottom: 12px; font-size: 0.95rem; line-height: 1.5; color: var(--color-text-main);">
                
            </div>
            <div class="bs-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                
                <label class="btn-bs-benar"
                    style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 500; border-radius: 8px; cursor: pointer; transition: all 0.2s; user-select: none;">
                    <input type="radio" value="BENAR" style="display:none">
                    <i class="fas fa-check"></i> <span>BENAR</span>
                </label>
                
                <label class="btn-bs-salah"
                    style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 500; border-radius: 8px; cursor: pointer; transition: all 0.2s; user-select: none;">
                    <input type="radio" value="SALAH" style="display:none">
                    <i class="fas fa-times"></i> <span>SALAH</span>
                </label>
            </div>
        </div>
    </template>

    
    <template id="tpl-isian">
        <div class="isian-container">
            <label class="isian-label">Jawaban Kamu (Angka):</label>
            <input type="number" class="isian-input" placeholder="Ketik angka di sini...">
        </div>
    </template>

    
    <template id="tpl-nav-item">
        <div class="nav-item-box">1</div>
    </template>

    <audio id="violationAudio" src="{{ asset('exam/pelanggaran.mp3') }}" preload="auto"></audio>

    

















    
    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('exam/assets/timer.js') }}"></script>
    <script>
        window.__SERVER_TIMESTAMP__ = {{ time() * 1000 }};
        window.__EXAM_API_URL__ = "{{ route('api.exam.student') }}";
        window.__EXAM_DASHBOARD_URL__ = "{{ route('student.dashboard') }}";
        window.__EXAM_URLS__ = {
            login: "{{ route('exam.login') }}",
            summary: "{{ route('exam.summary') }}",
            ujian: "{{ route('exam.ujian') }}",
            hasil: "{{ route('exam.hasil') }}",
            exit: "{{ route('student.dashboard') }}"
        };
        window.BASE_URL = "{{ asset('') }}";

        
        const EXAM_ASSETS_VERSION = "v4";
        if (localStorage.getItem("exam_assets_version") !== EXAM_ASSETS_VERSION) {
            sessionStorage.removeItem("shuffledSoal");
            localStorage.setItem("exam_assets_version", EXAM_ASSETS_VERSION);
            console.log("[CACHE BUSTER] Cleaned stale exam questions structure.");
        }
    </script>
    <script src="{{ asset('exam/js/google-apps.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('exam/js/memory.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('exam/js/protect.js') }}?v={{ time() }}"></script>
    <script type="module" src="{{ asset('exam/js/main.js') }}?v={{ time() }}"></script>
    

    
    <script src="{{ asset('exam/js/network-monitor.js') }}"></script>

    
    <script>
        
        document.addEventListener('contextmenu', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        
        document.addEventListener('selectstart', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        document.addEventListener('dragstart', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        
        document.addEventListener('copy', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        document.addEventListener('cut', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        document.addEventListener('paste', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        
        document.addEventListener('keydown', (e) => {
            const forbiddenKeys = ['u', 's', 'c', 'v', 'x', 'p', 'a', 'shift', 'i', 'j'];

            if (e.key === 'F12' || e.keyCode === 123) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            if (e.altKey || e.key === "Tab" || e.metaKey) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            if (e.ctrlKey || e.metaKey) {
                if (forbiddenKeys.includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        });
    </script>
</body>

</html>