<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian: {{ $evaluasi->judul }} | In-App Browser</title>

    <link rel="stylesheet" href="{{ asset('assets/student/css/root.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin: 0 !important;
            padding: 0 !important;
            overscroll-behavior-y: none;
        }

        
        #setup-view {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(248, 250, 252, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(8px);
        }

        .setup-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            text-align: center;
            max-width: 450px;
            width: 90%;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background: #e0f2fe;
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .exam-title-setup {
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .exam-desc-setup {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .btn-start {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn-start:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }

        
        #warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(220, 38, 38, 0.95);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10000;
            text-align: center;
            padding: 20px;
        }

        #warning-overlay i {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .btn-fullscreen-return {
            background: white;
            color: #dc2626;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        
        #exam-header {
            height: 60px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            z-index: 10;
        }

        .header-hidden {
            transform: translateY(-100%);
            position: absolute;
            width: 100%;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-text-exam {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .timer-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 6px 15px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            margin-left: 10px;
        }

        .timer-icon {
            color: #64748b;
        }

        .timer-text {
            font-family: monospace;
            font-weight: bold;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .timer-warning {
            color: #dc2626;
            border-color: #fca5a5;
            background: #fef2f2;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .action-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-action:hover {
            background: #e2e8f0;
        }

        .btn-danger {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        .btn-danger:hover {
            background: #fee2e2;
        }

        .btn-icon-only {
            padding: 6px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border: none;
            cursor: pointer;
            color: #475569;
        }

        .btn-icon-only:hover {
            background: #e2e8f0;
        }

        
        #restore-header-btn {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            font-size: 1rem;
            font-family: monospace;
            font-weight: bold;
            padding: 6px 20px;
            border-radius: 0 0 16px 16px;
            border: 1px solid #e2e8f0;
            border-top: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            z-index: 50;
            display: none;
            backdrop-filter: blur(4px);
            align-items: center;
            gap: 8px;
            transition: transform 0.2s ease, background 0.2s;
        }

        #restore-header-btn:hover {
            transform: translateX(-50%) translateY(2px);
            background: white;
        }

        #restore-header-btn.timer-warning-chip {
            color: #dc2626 !important;
            animation: pulse 1s infinite;
        }

        
        .iframe-container {
            flex: 1;
            position: relative;
            background: white;
            width: 100%;
            transition: none;
        }

        #exam-iframe {
            width: 100%;
            height: 100%;
            border: none;
            position: relative;
            z-index: 5;
            display: block;
        }

        #loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e0f2fe;
            border-top: 4px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 600px) {
            .brand-text-exam span {
                display: none;
            }

            .btn-action span {
                display: none;
            }

            .action-section {
                gap: 5px;
            }

            #exam-header {
                padding: 0 10px;
            }
        }
    </style>
</head>

<body>

    
    <div id="setup-view">
        <div class="setup-card">
            <div class="icon-circle"><i class="fas fa-shield-alt"></i></div>
            <h1 class="exam-title-setup">{{ $evaluasi->judul }}</h1>
            <p class="exam-desc-setup">
                Waktu pengerjaan: <strong>{{ $evaluasi->waktu_menit }} Menit</strong>.<br>
                Ujian ini menerapkan sistem <strong>Fullscreen Wajib</strong> untuk mencegah kecurangan. Jika Anda
                keluar dari layar penuh, sistem akan mencatat pelanggaran.
            </p>
            <button id="btnMulaiSekarang" class="btn-start">
                <i class="fas fa-play"></i> Mulai Ujian Sekarang
            </button>
            <a href="{{ route('student.ruang_kompetensi.index') }}" class="btn-action"
                style="margin-top: 15px; justify-content: center; border: none; background: transparent; color: #64748b;">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    
    <div id="warning-overlay">
        <i class="fas fa-exclamation-triangle"></i>
        <h1 style="font-size: 2rem; margin-bottom: 10px;">PELANGGARAN TERDETEKSI!</h1>
        <p style="font-size: 1.1rem; max-width: 600px;">
            Anda telah keluar dari mode layar penuh (Fullscreen). Tindakan ini tercatat sebagai indikasi kecurangan.
            Silakan kembali ke mode layar penuh untuk melanjutkan ujian.
        </p>
        <button id="btnKembaliFullscreen" class="btn-fullscreen-return">Kembali ke Ujian (Fullscreen)</button>
    </div>

    
    
    <button id="restore-header-btn" onclick="toggleHeader()" title="Tampilkan Navigasi" style="display: none;">
        <i class="fas fa-clock"></i> <span id="chip-timer-display">00:00:00</span>
    </button>

    <div id="exam-header">
        <div class="brand-section">
            <div class="brand-text-exam">
                <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" alt="GARA Logo"
                    style="height: 35px; margin-right: 8px;">
                <span>Garuda Akademi</span>
            </div>
            <div class="timer-box">
                <i class="fas fa-clock timer-icon"></i>
                <span id="timer-display" class="timer-text">00:00:00</span>
            </div>
        </div>

        <div class="action-section">
            <button onclick="reloadIframe()" class="btn-action" title="Muat Ulang Halaman">
                <i class="fas fa-sync-alt"></i> <span>Reload</span>
            </button>

            <button onclick="endExamPrompt()" class="btn-action btn-danger" title="Akhiri Ujian">
                <i class="fas fa-sign-out-alt"></i> <span>Keluar</span>
            </button>

            
            <button onclick="toggleHeader()" class="btn-icon-only" title="Sembunyikan Navigasi">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
    </div>

    <div class="iframe-container">
        <div id="loading-indicator">
            <div class="spinner"></div>
            <p style="color: #64748b; font-weight: 500;">Memuat soal ujian...</p>
        </div>
        <iframe id="exam-iframe" src="about:blank" allow="autoplay; camera; microphone; fullscreen"></iframe>
    </div>

    
    <audio id="violationAudio" src="{{ asset('exam/pelanggaran.mp3') }}" preload="auto"></audio>

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        
        const examId = "{{ $evaluasi->id }}";
        const examUrl = "{{ $evaluasi->link_evaluasi }}";
        const examMinutes = parseInt("{{ $evaluasi->waktu_menit }}");
        const dashboardUrl = "{{ route('student.ruang_kompetensi.index') }}";

        const storageKey = `gara_exam_${examId}_end`;

        
        window.__SERVER_TIMESTAMP__ = {{ time() * 1000 }};
        const serverOffset = window.__SERVER_TIMESTAMP__ - Date.now();

        function getNow() {
            return Date.now() + serverOffset;
        }

        
        let endTime = localStorage.getItem(storageKey) ? parseInt(localStorage.getItem(storageKey), 10) : null;

        
        if (endTime && endTime <= getNow()) {
            localStorage.removeItem(storageKey);
            endTime = null;
        }

        
        if (endTime) {
            document.addEventListener('DOMContentLoaded', () => {
                const descEl = document.querySelector('.exam-desc-setup');
                const btnEl = document.getElementById('btnMulaiSekarang');
                if (descEl) {
                    descEl.innerHTML = 'Sesi ujian Anda sedang berjalan. Silakan klik <strong>Lanjutkan Ujian</strong> untuk masuk kembali ke ruang ujian dan melanjutkan pengerjaan.';
                }
                if (btnEl) {
                    btnEl.innerHTML = '<i class="fas fa-play"></i> Lanjutkan Ujian';
                }
            });
        }

        
        const setupView = document.getElementById('setup-view');
        const examIframe = document.getElementById('exam-iframe');
        const timerDisplay = document.getElementById('timer-display');
        const chipTimerDisplay = document.getElementById('chip-timer-display');
        const examHeader = document.getElementById('exam-header');
        const restoreHeaderBtn = document.getElementById('restore-header-btn');
        const loadingIndicator = document.getElementById('loading-indicator');
        const warningOverlay = document.getElementById('warning-overlay');
        const btnMulaiSekarang = document.getElementById('btnMulaiSekarang');
        const btnKembaliFullscreen = document.getElementById('btnKembaliFullscreen');

        let timerInterval;
        let remainingSeconds = 0;
        let isExamActive = false;
        let isForcedExit = false;

        
        let violationCount = 3;
        const violationAudio = document.getElementById('violationAudio');

        function handleCheatingAttempt(reason) {
            if (!isExamActive || isForcedExit) return;

            violationCount--;

            if (violationAudio) {
                violationAudio.play().catch(e => console.log("Audio play failed:", e));
            }

            if (violationCount <= 0) {
                isForcedExit = true;
                clearInterval(timerInterval);
                exitFullscreen();
                localStorage.removeItem(storageKey);

                Swal.fire({
                    icon: "error",
                    title: "Batas Pelanggaran Tercapai!",
                    text: `Anda telah melakukan pelanggaran berulang kali. Ujian Anda akan dihentikan.`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Tutup'
                }).then(() => {
                    window.location.href = dashboardUrl;
                });
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Peringatan Keamanan!",
                    html: `Alasan: <strong>${reason}</strong>.<br>Jangan meninggalkan halaman ujian.<br><br>Sisa kesempatan: <strong>${violationCount}</strong> kali.`,
                    confirmButtonText: "Saya Mengerti",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    if (violationAudio) {
                        violationAudio.pause();
                        violationAudio.currentTime = 0;
                    }
                    
                    enterFullscreen();
                });
            }
        }

        function enterFullscreen() {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(err => console.log(err));
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen().catch(err => console.log(err));
            }
        }

        btnMulaiSekarang.addEventListener('click', async () => {
            try {
                if (document.documentElement.requestFullscreen) {
                    await document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    await document.documentElement.webkitRequestFullscreen();
                }

                setupView.style.display = 'none';
                startExam();
            } catch (err) {
                Swal.fire({
                    title: 'Gagal Fullscreen',
                    text: `Gagal mengakses mode fullscreen: ${err.message}`,
                    icon: 'error',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });

        btnKembaliFullscreen.addEventListener('click', async () => {
            try {
                if (document.documentElement.requestFullscreen) {
                    await document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    await document.documentElement.webkitRequestFullscreen();
                }
            } catch (err) {
                console.error("Gagal fullscreen:", err);
            }
        });

        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

        function handleFullscreenChange() {
            if (isExamActive && !isForcedExit && !document.fullscreenElement && !document.webkitFullscreenElement) {
                warningOverlay.style.display = 'flex';
                handleCheatingAttempt("Keluar dari mode layar penuh");
            } else {
                warningOverlay.style.display = 'none';
            }
        }

        
        
        document.addEventListener("visibilitychange", () => {
            if (isExamActive && !isForcedExit && document.visibilityState === "hidden") {
                handleCheatingAttempt("Meninggalkan Halaman Ujian (Buka Aplikasi Lain / Ganti Tab)");
            }
        });

        
        window.addEventListener("resize", () => {
            if (!isExamActive || isForcedExit) return;
            const threshold = 160;
            if (
                window.outerWidth - window.innerWidth > threshold ||
                window.outerHeight - window.innerHeight > threshold
            ) {
                handleCheatingAttempt("Developer Tools Terdeteksi");
            }
        });

        
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
                handleCheatingAttempt("Membuka Developer Tools");
                return false;
            }

            if (e.altKey || e.key === "Tab" || e.metaKey) {
                e.preventDefault();
                e.stopPropagation();
                handleCheatingAttempt("Tombol Terlarang Ditekan");
                return false;
            }

            if (e.ctrlKey || e.metaKey) {
                if (forbiddenKeys.includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleCheatingAttempt("Shortcut Terlarang Ditekan");
                    return false;
                }
            }
        });

        examIframe.onload = function () {
            if (examIframe.src !== 'about:blank') {
                loadingIndicator.style.display = 'none';
            }
        };

        function startExam() {
            isExamActive = true;
            loadingIndicator.style.display = 'flex';
            examIframe.src = examUrl;

            
            if (!endTime) {
                endTime = getNow() + (examMinutes * 60 * 1000);
                localStorage.setItem(storageKey, endTime);
            }

            updateTimeRemaining();
            updateTimerDisplay();

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                updateTimeRemaining();
                updateTimerDisplay();

                if (remainingSeconds === 60) {
                    timerDisplay.parentElement.classList.add('timer-warning');
                    restoreHeaderBtn.classList.add('timer-warning-chip');
                }

                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.parentElement.classList.remove('timer-warning');
                    restoreHeaderBtn.classList.remove('timer-warning-chip');
                    isForcedExit = true;
                    exitFullscreen();
                    localStorage.removeItem(storageKey);

                    Swal.fire({
                        title: 'Waktu Habis!',
                        text: 'Sesi ujian telah berakhir.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#0d6efd',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = dashboardUrl;
                    });
                }
            }, 1000);
        }

        function updateTimeRemaining() {
            remainingSeconds = Math.max(0, Math.floor((endTime - getNow()) / 1000));
        }

        function updateTimerDisplay() {
            const h = Math.floor(remainingSeconds / 3600);
            const m = Math.floor((remainingSeconds % 3600) / 60);
            const s = remainingSeconds % 60;
            const format = (num) => String(num).padStart(2, '0');
            const timeString = `${format(h)}:${format(m)}:${format(s)}`;

            timerDisplay.textContent = timeString;
            chipTimerDisplay.textContent = timeString; 
        }

        function reloadIframe() {
            if (isExamActive) {
                loadingIndicator.style.display = 'flex';
                
                examIframe.src = examIframe.src;
            }
        }

        function toggleHeader() {
            if (examHeader.classList.contains('header-hidden')) {
                
                examHeader.classList.remove('header-hidden');
                restoreHeaderBtn.style.display = 'none';
            } else {
                
                examHeader.classList.add('header-hidden');
                restoreHeaderBtn.style.display = 'flex';
            }
        }

        function endExamPrompt() {
            Swal.fire({
                title: 'Akhiri Ujian?',
                text: "Yakin ingin menyelesaikan ujian dan keluar ruangan? Waktu dan sesi Anda akan dihentikan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isForcedExit = true;
                    clearInterval(timerInterval);
                    exitFullscreen();
                    localStorage.removeItem(storageKey);
                    window.location.href = dashboardUrl;
                }
            });
        }

        function exitFullscreen() {
            if (document.exitFullscreen && document.fullscreenElement) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen && document.webkitFullscreenElement) {
                document.webkitExitFullscreen();
            }
        }
    </script>
</body>

</html>