<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Ujian - {{ $sekolahNama ?? 'Garuda Akademi' }}</title>
    <link rel="icon" href="{{ asset('exam/img/GARA_ICON.svg') }}" type="image/png">

    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    
    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}">

    <style>
        
        body {
            background-color: var(--color-bg-body);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: var(--space-4);
            
            background-image: radial-gradient(var(--color-border) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 380px; 
            animation: fadeIn var(--transition-normal);
        }

        .login-card {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03), 0 0 0 1px rgba(0,0,0,0.02);
            padding: var(--space-6) var(--space-6) var(--space-8) var(--space-6);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
        }

        .brand-header {
            text-align: center;
            margin-bottom: var(--space-6);
        }

        .brand-logo-container {
            width: 64px; 
            height: 64px;
            margin: 0 auto var(--space-3) auto;
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
        }

        .brand-logo {
            width: 36px; 
            height: 36px;
            object-fit: contain;
        }

        .brand-title {
            font-size: var(--text-lg); 
            font-weight: var(--font-weight-bold);
            color: var(--color-text-main);
            margin-bottom: 2px;
            letter-spacing: -0.01em;
        }

        .brand-subtitle {
            font-size: var(--text-xs); 
            font-weight: var(--font-weight-medium);
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        
        .token-group {
            margin-bottom: var(--space-5);
        }

        .token-label {
            display: block;
            text-align: center;
            color: var(--color-text-body);
            font-size: var(--text-sm);
            font-weight: var(--font-weight-medium);
            margin-bottom: var(--space-2);
        }

        .token-input-wrapper {
            position: relative;
        }

        .token-icon {
            position: absolute;
            top: 50%;
            left: var(--space-3);
            transform: translateY(-50%);
            color: var(--color-icon-inactive);
            font-size: var(--text-base);
            transition: color var(--transition-fast);
        }

        .token-input {
            width: 100%;
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: var(--space-3) var(--space-3) var(--space-3) 2.5rem;
            font-size: var(--text-xl); 
            font-weight: var(--font-weight-bold);
            color: var(--color-text-main);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 4px; 
            transition: all var(--transition-fast);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }

        .token-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        .token-input:focus ~ .token-icon {
            color: var(--color-primary);
        }

        .token-input::placeholder {
            color: var(--color-text-muted);
            opacity: 0.4;
            letter-spacing: 1px;
            font-weight: var(--font-weight-regular);
        }

        
        .btn-masuk {
            width: 100%;
            background: var(--color-primary);
            color: var(--color-text-inverse);
            border: none;
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            font-size: var(--text-base);
            font-weight: var(--font-weight-bold);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            cursor: pointer;
        }

        .btn-masuk:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%);
        }

        .btn-masuk:active {
            transform: translateY(0);
        }

        
        .back-nav {
            text-align: center;
            margin-top: var(--space-6);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--color-text-muted);
            font-size: var(--text-sm);
            font-weight: var(--font-weight-medium);
            text-decoration: none;
            transition: color var(--transition-fast);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            background-color: transparent;
        }

        .back-link:hover {
            color: var(--color-primary);
            background-color: var(--color-primary-light);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">

            <div class="brand-header">
                <div class="brand-logo-container">
                    
                    <img src="{{ asset('exam/img/GARA_ICON.svg') }}"
                        onerror="this.src='{{ asset('assets/img/logo.png') }}'" alt="Logo" class="brand-logo">
                </div>
                <h1 class="brand-title">Ruang Asesmen</h1>
                <div class="brand-subtitle">{{ $sekolahNama ?? 'GARUDA AKADEMI' }}</div>
            </div>

            <form id="loginForm">
                
                <input type="hidden" id="inputNIS" value="{{ $nisPreFill }}">

                <div class="token-group">
                    <label class="token-label">Masukkan Token Ujian Anda</label>
                    <div class="token-input-wrapper">
                        <input type="text" id="inputToken" class="token-input" placeholder="TOKEN" maxlength="5"
                            autocomplete="off" autofocus>
                        <i class="fas fa-key token-icon"></i>
                    </div>
                </div>

                <button type="button" id="btnLogin" class="btn-masuk">
                    <span>Mulai Ujian</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

        </div>

        <div class="back-nav">
            @auth
                <a href="{{ route('student.pilih-mapel') }}" class="back-link">
                    <i class="fas fa-chevron-left"></i> Kembali ke Pilih Mapel
                </a>
            @else
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-chevron-left"></i> Kembali ke Login
                </a>
            @endauth
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    
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

        
        document.getElementById('inputToken').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        });

        
        document.getElementById('inputToken').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btnLogin').click();
            }
        });
    </script>

    <script src="{{ asset('exam/js/google-apps.js') }}?v=laravel_v1"></script>
    <script type="module" src="{{ asset('exam/js/main.js') }}?v=laravel_v1"></script>

</body>

</html>