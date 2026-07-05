<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Data - Ruang Asesmen</title>

    
    <link rel="icon" href="{{ asset('exam/img/GARA_ICON.svg') }}" type="image/png">

    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    
    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}">
    <link rel="stylesheet" href="{{ asset('exam/assets/header.css') }}">
    <link rel="stylesheet" href="{{ asset('exam/assets/summary.css') }}">

    
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>

    <div id="mainLayout" class="confirmation-layout">

        
        <header class="app-header">
            <div class="header-container">
                <img src="{{ asset('exam/img/FARA_BLACK (1).svg') }}" alt="Logo Kemendikbud" class="brand-logo">
                <div class="brand-divider">
                    <h1 class="brand-title">
                        RUANG UJIAN<br>GARUDA AKADEMI
                    </h1>
                </div>

                
                <a href="{{ route('student.dashboard') }}" class="header-logout-btn btn-kembali-beranda"
                    title="Kembali ke Halaman Utama">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        
        
        
        <div id="loadingOverlay" class="loading-overlay" style="display: flex;">
            <div class="confirmation-card skeleton-card">
                
                <div class="card-header skeleton-header-bg">
                    <div class="skeleton"
                        style="width: 120px; height: 16px; margin-bottom: 8px; background: rgba(255,255,255,0.3);">
                    </div>
                    <div class="skeleton" style="width: 250px; height: 32px; background: rgba(255,255,255,0.4);"></div>
                </div>

                <div class="card-body">
                    
                    <div class="student-data-grid">
                        <div class="data-group">
                            <div class="skeleton" style="width: 100px; height: 12px; margin-bottom: 8px;"></div>
                            <div class="skeleton" style="width: 100%; height: 24px;"></div>
                        </div>
                        <div class="data-group">
                            <div class="skeleton" style="width: 120px; height: 12px; margin-bottom: 8px;"></div>
                            <div class="skeleton" style="width: 100%; height: 24px;"></div>
                        </div>
                        <div class="data-group">
                            <div class="skeleton" style="width: 90px; height: 12px; margin-bottom: 8px;"></div>
                            <div class="skeleton" style="width: 80%; height: 24px;"></div>
                        </div>
                        <div class="data-group">
                            <div class="skeleton" style="width: 110px; height: 12px; margin-bottom: 8px;"></div>
                            <div class="skeleton" style="width: 90%; height: 24px;"></div>
                        </div>
                    </div>

                    
                    <div class="time-allocation-box" style="border:none; background: #f8fafc;">
                        <div class="skeleton" style="width: 150px; height: 12px; margin-bottom: 8px;"></div>
                        <div class="skeleton" style="width: 100px; height: 36px;"></div>
                    </div>

                    
                    <div class="rules-box" style="border:none; background: #f8fafc; margin-top: 1rem;">
                        <div class="skeleton" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div style="flex:1">
                            <div class="skeleton" style="width: 40%; height: 14px; margin-bottom: 8px;"></div>
                            <div class="skeleton" style="width: 90%; height: 12px; margin-bottom: 4px;"></div>
                            <div class="skeleton" style="width: 80%; height: 12px;"></div>
                        </div>
                    </div>

                    
                    <div class="skeleton" style="width: 100%; height: 60px; border-radius: 1rem; margin-top: 1rem;">
                    </div>
                </div>
            </div>
        </div>

        
        <div class="confirmation-card">

            <header class="card-header">
                <div class="header-pattern"></div>

                <div class="header-content">
                    <div class="header-left">
                        <p class="school-label">Garuda Akademi</p>
                        <h1 class="page-title">
                            Konfirmasi Peserta
                        </h1>
                    </div>
                </div>
            </header>

            
            <div class="card-body">

                
                <div class="student-data-grid">

                    <div class="data-group">
                        <label class="data-label">Nomor Induk Siswa</label>
                        <div id="summaryNIS" class="data-value"><span class="skeleton"
                                style="width: 100px; height: 1.25rem;"></span></div>
                    </div>

                    <div class="data-group">
                        <label class="data-label">Nama Lengkap</label>
                        <div id="summaryNama" class="data-value"><span class="skeleton"
                                style="width: 180px; height: 1.25rem;"></span></div>
                    </div>

                    <div class="data-group">
                        <label class="data-label">Kelas</label>
                        <div id="summaryKelas" class="data-value"><span class="skeleton"
                                style="width: 80px; height: 1.25rem;"></span></div>
                    </div>

                    <div class="data-group">
                        <label class="data-label">Topik Ujian</label>
                        <div id="summaryMapel" class="data-value"><span class="skeleton"
                                style="width: 150px; height: 1.25rem;"></span></div>
                    </div>

                    
                    <div class="time-allocation-box">
                        <label class="time-label">Alokasi Waktu Pengerjaan</label>
                        <div class="time-value">
                            <i class="far fa-clock"></i>
                            <span id="summaryWaktu">0</span>
                            <span>Menit</span>
                        </div>
                    </div>

                </div>

                
                <div class="rules-box">
                    <i class="fas fa-info-circle rules-icon"></i>
                    <div class="rules-content">
                        <span class="rules-title">Ketentuan Ujian:</span>
                        <ul class="rules-list">
                            <li>Pastikan koneksi internet stabil.</li>
                            <li>Dilarang Joki/Mencontek!.</li>
                            <li>Dilarang menggunakan aplikasi lain selama ujian.</li>
                            <li>Berdoalah sebelum memulai.</li>
                        </ul>
                    </div>
                </div>

                
                <button id="startUjianBtn" class="btn-start-exam">
                    <span>MULAI MENGERJAKAN</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

            </div>
        </div>
    </div>

    
    
    
    <div id="anbkModal"
        class="fixed inset-0 z-[100] hidden bg-black/50 backdrop-blur-sm items-center justify-center p-4 font-sans"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full overflow-hidden transform transition-all scale-100">
            
            <div class="bg-[#0b57d0] px-6 py-3 flex justify-between items-center"
                style="background-color: #0b57d0; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="anbkModalTitle" class="text-white font-bold text-lg"
                    style="color: white; font-weight: bold; margin: 0;">Konfirmasi</h3>
                <button onclick="document.getElementById('anbkModal').style.display='none'"
                    class="text-blue-200 hover:text-white transition-colors"
                    style="background:none; border:none; color: #bfdbfe; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            
            <div class="p-6 text-center" style="padding: 24px; text-align: center;">
                <div id="anbkModalIcon" class="mb-4 text-4xl text-[#f39c12]"
                    style="margin-bottom: 16px; font-size: 2.5rem; color: #f39c12;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <p id="anbkModalMessage" class="text-gray-700 text-base leading-relaxed mb-2"
                    style="color: #374151; margin-bottom: 8px;">Pesan disini</p>
            </div>

            
            <div id="anbkModalButtons" class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100"
                style="background-color: #f9fafb; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f3f4f6;">
                
            </div>
        </div>
    </div>

    
    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('exam/js/app.js') }}"></script>
    <script>
        window.__EXAM_API_URL__ = "{{ route('api.exam.student') }}";
        
        window.__EXAM_DASHBOARD_URL__ = "{{ route('student.dashboard') }}";
        window.__EXAM_URLS__ = {
            login: "{{ route('exam.login') }}",
            summary: "{{ route('exam.summary') }}",
            ujian: "{{ route('exam.ujian') }}",
            hasil: "{{ route('exam.hasil') }}",
            exit: "{{ route('student.dashboard') }}"
        };
    </script>
    <script src="{{ asset('exam/js/google-apps.js') }}?v=laravel_v1"></script>
    <script type="module" src="{{ asset('exam/js/main.js') }}?v=laravel_v1"></script>
    <script src="{{ asset('exam/js/protect.js') }}"></script>

    
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