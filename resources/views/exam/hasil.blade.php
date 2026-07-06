<?php


?>
<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Hasil Ujian - Ruang Asesmen</title>

    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="{{ \App\Models\AppSetting::getLogo('GARA_ICON.svg') }}" type="image/png">
    
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">

    
    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}" />
    <link rel="stylesheet" href="{{ asset('exam/assets/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('exam/assets/hasil.css') }}" />

    
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

    
    <script type="text/javascript" id="MathJax-script" async
        src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
        </script>

    
    <script>
        window.GARA_CONFIG = {
            sekolah_nama: {!! json_encode($sekolahNama) !!},
            sekolah_akreditasi: {!! json_encode($sekolahAkreditasi) !!},
            sekolah_nss: {!! json_encode($sekolahNss) !!},
            sekolah_npsn: {!! json_encode($sekolahNpsn) !!},
            sekolah_alamat: {!! json_encode($sekolahAlamat) !!}
        };
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
</head>

<body>

    <div class="confirmation-layout">

        
        <header class="app-header">
            <div class="header-container">
                <img src="{{ \App\Models\AppSetting::getLogo('FARA_BLACK.svg') }}" alt="Logo Kemendikbud" class="brand-logo">
                <div class="brand-divider">
                    <h1 class="brand-title">
                        RUANG ASESMEN<br>GARUDA AKADEMI
                    </h1>
                </div>

                
                <a href="{{ route('student.dashboard') }}" class="header-logout-btn btn-kembali-beranda"
                    title="Kembali ke Halaman Utama">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        
        <div class="confirmation-card">
            <div class="card-decor-top"></div>

            <div class="card-body">

                
                <div class="result-icon-wrapper">
                    <img src="{{ \App\Models\AppSetting::getLogo('3dlogo.svg') }}" alt="Logo" class="result-logo">
                </div>
                <div class="result-header">
                    <h1 class="result-title">Ujian Selesai!</h1>
                    <p class="result-subtitle">Terima kasih telah mengikuti ujian ini.</p>
                </div>

                
                <div class="score-box">
                    <span class="score-label">Nilai Akhir Kamu</span>
                    <span id="nilaiAkhir" class="score-value">0</span>
                    <div id="kkmStatusBadge" class="kkm-badge-wrapper"></div>
                </div>

                
                <div class="info-group">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span id="hasilNama" class="info-value"><span class="skeleton"
                                style="width: 150px; height: 1em;"></span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kelas / Ruang</span>
                        <span id="hasilKelas" class="info-value"><span class="skeleton"
                                style="width: 80px; height: 1em;"></span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mata Ujian</span>
                        <span id="hasilMapel" class="info-value"><span class="skeleton"
                                style="width: 200px; height: 1em;"></span></span>
                    </div>
                </div>

                
                <div class="stats-grid">
                    <div class="stat-card correct">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <span id="jawabanBenar" class="stat-number">0</span>
                        <span class="stat-label">Jawaban Benar</span>
                    </div>
                    <div class="stat-card wrong">
                        <i class="fas fa-times-circle stat-icon"></i>
                        <span id="jawabanSalah" class="stat-number">0</span>
                        <span class="stat-label">Jawaban Salah</span>
                    </div>
                </div>

                
                <div class="action-buttons">
                    <button id="downloadBuktiBtn" class="btn-action btn-download">
                        <i class="fas fa-file-pdf"></i>
                        <span>Unduh Bukti Ujian</span>
                    </button>

                    <button id="selesaiBtn" class="btn-action btn-finish">
                        <span>Selesai &amp; Keluar</span>
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>

            </div>
        </div>

        
        <div id="analysisCard" class="confirmation-card analysis-card" style="display: none; margin-top: 2rem;">
            <div class="card-body">
                <div class="analysis-header">
                    <div class="analysis-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h2 class="analysis-title">Analisis &amp; Rekomendasi</h2>
                        <p class="analysis-subtitle">Evaluasi performa berdasarkan jawaban yang salah</p>
                    </div>
                </div>

                <div class="analysis-content">
                    
                    <div class="analysis-section">
                        <p id="analysisOpening" class="analysis-text-main"></p>
                    </div>

                    
                    <div id="wrapperWeaknesses" class="analysis-section" style="display: none;">
                        <h3 class="section-label text-red-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Area Perlu Peningkatan
                        </h3>
                        <ul id="listWeaknesses" class="analysis-list warning-list"></ul>
                    </div>

                    
                    <div id="wrapperRecommendations" class="analysis-section">
                        <h3 class="section-label text-blue-600">
                            <i class="fas fa-lightbulb mr-1"></i> Saran Belajar
                        </h3>
                        <ul id="listRecommendations" class="analysis-list info-list"></ul>
                    </div>

                    
                    <div id="aiDeepAnalysis" class="analysis-section" style="margin-top: 2.5rem; display: none;">
                        
                    </div>

                    
                    <div id="wrapperBenar" class="analysis-section" style="display: none; margin-top: 2.5rem;">
                        <h3 class="section-label text-green-700 border-b pb-2 mb-4">
                            ANALISIS JAWABAN BENAR
                        </h3>
                        <div id="listSoalBenar" class="analysis-grid">
                            
                        </div>
                    </div>

                    
                    <div id="wrapperSalah" class="analysis-section" style="display: none; margin-top: 2.5rem;">
                        <h3 class="section-label text-red-700 border-b pb-2 mb-4">
                            ANALISIS JAWABAN SALAH
                        </h3>
                        <div id="listSoalSalah" class="analysis-grid">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div id="anbkModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            
            <div class="modal-header">
                <h3 id="anbkModalTitle" class="modal-title">Konfirmasi</h3>
                <button onclick="document.getElementById('anbkModal').style.display='none'" class="modal-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            
            <div class="modal-body">
                <div id="anbkModalIcon" class="modal-icon-wrapper">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <p id="anbkModalMessage" class="modal-message">Pesan disini</p>
            </div>

            
            <div id="anbkModalButtons" class="modal-footer">
                
            </div>
        </div>
    </div>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    
    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('exam/js/app.js') }}"></script>
    <script src="{{ asset('exam/js/google-apps.js') }}?v=laravel_v1"></script>
    <script src="{{ asset('exam/js/bukti-ujian.js') }}"></script>
    <script type="module" src="{{ asset('exam/js/main.js') }}?v=laravel_v1"></script>

    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nilaiEl = document.getElementById('nilaiAkhir');
            const targetNilai = parseInt(nilaiEl.innerText) || 0;
        });
    </script>

</body>

</html>