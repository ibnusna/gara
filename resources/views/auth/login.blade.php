<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0056b3">
    <title>Garuda Akademi</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
    
    <link rel="stylesheet" href="{{ asset('assets/login.css') }}">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" integrity="sha384-dCW5imOdApH6OwpFau8cZNKjqVbJYnCA5q+8YsMYP3XwXKsV6Jfz1u6MZLnXaBsS" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        
        .mini-logo {
            display: block !important;
            margin: 0 auto 15px auto !important;
        }

        
        #ios-install-nudge {
            display: none;
            
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(40px);
            width: calc(100% - 32px);
            max-width: 400px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 14px 16px;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            -webkit-tap-highlight-color: transparent;
            z-index: 9999;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
            opacity: 0;
            pointer-events: none;
        }

        #ios-install-nudge.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        #ios-install-nudge:active {
            background: rgba(30, 41, 59, 0.95);
            transform: translateX(-50%) scale(0.98);
        }

        .ios-nudge-inner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-right: 20px; 
        }

        .ios-nudge-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .ios-nudge-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ios-nudge-text {
            flex: 1;
            text-align: left;
        }

        .ios-nudge-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            font-family: 'Inter', sans-serif;
            margin-bottom: 2px;
            text-align: left;
        }

        .ios-nudge-desc {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            font-family: 'Inter', sans-serif;
            text-align: left;
            line-height: 1.3;
        }

        .ios-nudge-arrow {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
            flex-shrink: 0;
            margin-left: auto;
        }

        
        .ios-nudge-close {
            position: absolute;
            top: 10px;
            right: 12px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .ios-nudge-close:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        
        .btn-primary.btn-locked {
            background: #4b5563 !important;
            opacity: 0.65 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }

        .form-control:disabled {
            background: rgba(10, 20, 38, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            color: rgba(255, 255, 255, 0.3) !important;
            cursor: not-allowed !important;
        }

        
        #gara-autologin-overlay {
            position: fixed;
            inset: 0;
            background: #0b0f19;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        #gara-autologin-overlay .al-logo {
            width: 72px;
            height: 72px;
            animation: al-pulse 1.4s ease-in-out infinite;
        }

        #gara-autologin-overlay .al-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid rgba(255, 255, 255, 0.15);
            border-top-color: #0056b3;
            border-radius: 50%;
            animation: al-spin 0.8s linear infinite;
        }

        #gara-autologin-overlay .al-text {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.04em;
        }

        @keyframes al-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes al-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(0.93);
            }
        }
    </style>
</head>

<body>
    


    @if(!empty($autoLoginPending))
        <div id="gara-autologin-overlay">
            <img src="{{ \App\Models\AppSetting::getLogo('GARA_WHITE.svg', true) }}" alt="GARA" class="al-logo">
            <div class="al-spinner"></div>
            <span class="al-text">Mengautentikasi sesi Anda...</span>
        </div>
    @endif
    <main class="app-container">
        
        <section id="welcome-screen" class="screen active">
            <div class="screen-content">
                <div class="brand-display">
                    <img src="{{ \App\Models\AppSetting::getLogo('GARA_WHITE.svg', true) }}" alt="GARA Logo" class="app-logo animate-in">
                    <h1 class="app-name animate-in delay-1">GARA</h1>
                    <p class="app-tagline animate-in delay-2">
                        {{ $sekolah_nama ?? 'Garuda Akademi' }}
                    </p>
                </div>
                
                <div id="ios-install-nudge" role="button" tabindex="0" aria-label="Cara pasang GARA di iPhone">
                    <button type="button" class="ios-nudge-close" aria-label="Tutup Banner">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="ios-nudge-inner">
                        <div class="ios-nudge-icon">
                            <img src="{{ asset('icons/icon-192x192.png') }}" alt="GARA">
                        </div>
                        <div class="ios-nudge-text">
                            <div class="ios-nudge-title">📲 Pasang GARA di iPhone Anda</div>
                            <div class="ios-nudge-desc">Tap untuk panduan Add to Home Screen</div>
                        </div>
                        <div class="ios-nudge-arrow"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>

                <div class="welcome-text animate-in delay-3" id="welcome-slide">
                    <h2 id="welcome-slide-title">Selamat Datang di<br>Era Belajar Digital</h2>
                    <p id="welcome-slide-description">Akses materi, tugas, dan ujian dalam satu genggaman. Cepat, Mudah,
                        Efisien.</p>
                </div>
                <div class="action-area animate-in delay-4">
                    <div class="slide-indicators" id="slideIndicators">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                    <button id="btn-continue" class="btn-primary full-width">
                        Mulai Akses
                    </button>
                </div>
            </div>
        </section>

        
        <section id="login-screen" class="screen">
            <div class="bg-layer" style="background-image: url('{{ asset('assets/img/bglogin.jpg') }}');"></div>
            <div class="overlay-layer"></div>
            <div class="screen-content login-layout">
                <div class="login-header">
                    <img src="{{ \App\Models\AppSetting::getLogo('GARA_WHITE.svg', false) }}" alt="GARA Logo" class="mini-logo">
                    <h3>Masuk Akun</h3>
                    <p>Silakan login untuk melanjutkan</p>
                </div>
                <form id="loginForm" class="app-form">
                    <div id="lockout-alert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171; padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px; text-align: center; font-family: 'Inter', sans-serif;">
                        Terlalu banyak percobaan login.
                    </div>
                    <div class="form-group">
                        <div class="input-icon"><i class="fas fa-user"></i></div>
                        <input type="text" id="identifier" name="identifier" class="form-control"
                            placeholder="Username / NIS" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <div class="input-icon"><i class="fas fa-lock"></i></div>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password"
                            required autocomplete="current-password">
                        <button type="button" class="toggle-password"><i class="fas fa-eye-slash"></i></button>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary full-width" id="btnLogin">LOGIN</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btnContinue = document.getElementById('btn-continue');
            const welcomeScreen = document.getElementById('welcome-screen');
            const loginScreen = document.getElementById('login-screen');
            const identifierInput = document.getElementById('identifier');
            const slideTitle = document.getElementById('welcome-slide-title');
            const slideDescription = document.getElementById('welcome-slide-description');
            const slideDots = document.querySelectorAll('#slideIndicators .dot');

            const welcomeSlides = [
                {
                    title: 'Selamat Datang di<br>Era Belajar Digital',
                    description: 'Akses materi, tugas, dan ujian dalam satu genggaman. Cepat, Mudah, Efisien.'
                },
                {
                    title: 'Temukan Cara Belajar Lebih Praktis',
                    description: 'Buka akses pembelajaran interaktif untuk semua mata pelajaran kapan saja, di mana saja.'
                },
                {
                    title: 'Belajar Lebih Seru & Terarah',
                    description: 'Pantau kemajuanmu dengan mudah dan raih prestasi bersama Garuda Akademi.'
                }
            ];

            let currentSlideIndex = 0;

            function activateSlide(index) {
                const slide = welcomeSlides[index];
                slideTitle.innerHTML = slide.title;
                slideDescription.textContent = slide.description;

                slideDots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('active', dotIndex === index);
                });
            }

            function startSlideAutoPlay() {
                setInterval(() => {
                    currentSlideIndex = (currentSlideIndex + 1) % welcomeSlides.length;
                    activateSlide(currentSlideIndex);
                }, 2000);
            }

            activateSlide(currentSlideIndex);
            startSlideAutoPlay();

            if (btnContinue) {
                btnContinue.addEventListener('click', function () {
                    welcomeScreen.classList.remove('active');
                    welcomeScreen.classList.add('slide-out-left');
                    loginScreen.classList.add('active', 'slide-in-right');
                    setTimeout(() => identifierInput.focus(), 300);
                });
            }

            let lockoutInterval = null;

            function startLockoutCountdown(seconds) {
                const btnLogin = document.getElementById('btnLogin');
                const identifierInput = document.getElementById('identifier');
                const passwordInput = document.getElementById('password');
                const lockoutAlert = document.getElementById('lockout-alert');

                if (!btnLogin) return;

                
                if (lockoutInterval) clearInterval(lockoutInterval);

                
                if (!localStorage.getItem('gara_login_lockout_until')) {
                    const lockoutUntil = Date.now() + (seconds * 1000);
                    localStorage.setItem('gara_login_lockout_until', lockoutUntil);
                }

                
                if (lockoutAlert) lockoutAlert.style.display = 'block';

                
                if (identifierInput) identifierInput.disabled = true;
                if (passwordInput) passwordInput.disabled = true;
                btnLogin.disabled = true;
                btnLogin.classList.add('btn-locked');

                function updateButtonText() {
                    if (seconds <= 0) {
                        clearInterval(lockoutInterval);
                        localStorage.removeItem('gara_login_lockout_until');
                        if (lockoutAlert) lockoutAlert.style.display = 'none';
                        if (identifierInput) identifierInput.disabled = false;
                        if (passwordInput) passwordInput.disabled = false;
                        btnLogin.disabled = false;
                        btnLogin.classList.remove('btn-locked');
                        btnLogin.innerHTML = 'LOGIN';
                        return;
                    }

                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    const timeString = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

                    btnLogin.innerHTML = 'Coba Lagi Dalam ' + timeString;
                    seconds--;
                }

                updateButtonText();
                lockoutInterval = setInterval(updateButtonText, 1000);
            }

            
            const savedLockoutUntil = localStorage.getItem('gara_login_lockout_until');
            if (savedLockoutUntil) {
                const remainingSeconds = Math.ceil((parseInt(savedLockoutUntil, 10) - Date.now()) / 1000);
                if (remainingSeconds > 0) {
                    startLockoutCountdown(remainingSeconds);
                } else {
                    localStorage.removeItem('gara_login_lockout_until');
                }
            }

            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const btnLogin = document.getElementById('btnLogin');
                    const originalText = btnLogin.innerHTML;
                    btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    btnLogin.disabled = true;

                    const formData = new FormData(this);

                    fetch('{{ route('login.post') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                window.location.href = data.redirect;
                            } else {
                                if (data.lockout) {
                                    startLockoutCountdown(data.penalty_seconds);
                                } else {
                                    Swal.fire({
                                        title: 'Gagal',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonColor: '#3085d6'
                                    });
                                    btnLogin.innerHTML = originalText;
                                    btnLogin.disabled = false;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error Server',
                                text: 'Terjadi kesalahan komunikasi dengan server.',
                                icon: 'error'
                            });
                            btnLogin.innerHTML = originalText;
                            btnLogin.disabled = false;
                        });
                });
            }

            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }

            @if(session('error_message'))
                Swal.fire({
                    title: 'Akses Ditolak',
                    text: '{{ session('error_message') }}',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            @endif

            
            (function () {
                var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
                var isStandalone = ('standalone' in window.navigator) && window.navigator.standalone;

                if (isIos && !isStandalone) {
                    var iosDismissedAt = localStorage.getItem('gara_ios_guide_dismissed_at');
                    var sevenDays = 7 * 24 * 60 * 60 * 1000;

                    if (!iosDismissedAt || (Date.now() - parseInt(iosDismissedAt, 10)) > sevenDays) {
                        
                        var nudge = document.getElementById('ios-install-nudge');
                        if (nudge) {
                            nudge.style.display = 'block';
                            
                            setTimeout(function () {
                                nudge.classList.add('show');
                            }, 50);

                            
                            var closeBtn = nudge.querySelector('.ios-nudge-close');
                            if (closeBtn) {
                                closeBtn.addEventListener('click', function (e) {
                                    e.stopPropagation(); 
                                    nudge.classList.remove('show');
                                    localStorage.setItem('gara_ios_guide_dismissed_at', Date.now().toString());
                                    setTimeout(function () {
                                        nudge.style.display = 'none';
                                    }, 400);
                                });
                            }
                        }

                        
                        nudge.addEventListener('click', function () {
                            var overlay = document.getElementById('ios-pwa-modal-overlay');
                            if (overlay) overlay.classList.add('show');
                        });
                        nudge.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                var overlay = document.getElementById('ios-pwa-modal-overlay');
                                if (overlay) overlay.classList.add('show');
                            }
                        });
                    }
                }
            }());
        });
    </script>
</body>

</html>