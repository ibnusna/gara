<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Koneksi Terputus - GARA</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }

        .mac-window {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
            opacity: 0;
            transform: scale(0.95);
            animation: windowIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
        }

        .window-header {
            height: 44px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            padding: 0 16px;
            background: linear-gradient(to bottom, #ffffff, #f9fafb);
            border-radius: 12px 12px 0 0;
        }

        .traffic-lights {
            display: flex;
            gap: 8px;
        }

        .light {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .light.red { background: #ff5f56; border: 1px solid #e0443e; }
        .light.yellow { background: #ffbd2e; border: 1px solid #dea123; }
        .light.green { background: #27c93f; border: 1px solid #1aab29; }

        .window-title {
            flex-grow: 1;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            margin-left: -48px;
        }

        .window-content {
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .illustration-box {
            width: 120px;
            height: 120px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(11, 87, 208, 0.1);
            border-radius: 50%;
            position: relative;
        }

        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(11, 87, 208, 0.1);
            animation: pulse 2s infinite ease-in-out;
        }

        .main-icon {
            color: #0b57d0;
            z-index: 1;
        }

        h1.error-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }

        p.error-desc {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .btn-retry {
            background: #0b57d0;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(11, 87, 208, 0.2);
        }

        .btn-retry:hover {
            background: #08429e;
            transform: translateY(-1px);
        }

        @keyframes windowIn {
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.8; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        
        .spin-icon { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <main class="mac-window">
        <header class="window-header">
            <div class="traffic-lights">
                <div class="light red"></div>
                <div class="light yellow"></div>
                <div class="light green"></div>
            </div>
            <div class="window-title">GARA Network Monitor</div>
        </header>

        <div class="window-content">
            <div class="illustration-box">
                <div class="pulse-ring"></div>
                <svg class="main-icon" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                    <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
                    <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
                    <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
                    <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                    <line x1="12" y1="20" x2="12.01" y2="20"></line>
                </svg>
            </div>

            <h1 class="error-title">Koneksi Terputus</h1>
            <p class="error-desc">
                Sepertinya Anda tidak terhubung ke internet. Halaman ini akan <strong>otomatis dimuat ulang</strong> saat koneksi kembali.
            </p>

            <button class="btn-retry" id="btnRetry">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg> 
                Coba Hubungkan Ulang
            </button>
        </div>
    </main>

    <script>
        const btnRetry = document.getElementById('btnRetry');
        
        const syncSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>`;
        const loadSvg = `<svg class="spin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>`;
        const checkSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

        function checkNetwork() {
            btnRetry.innerHTML = loadSvg + ' Memeriksa Koneksi...';

            fetch('{{ asset("favicon.ico") }}', { method: 'HEAD', cache: 'no-store' })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        showOfflineFeedback();
                    }
                })
                .catch(() => showOfflineFeedback());
        }

        function showOfflineFeedback() {
            setTimeout(() => {
                btnRetry.innerHTML = syncSvg + ' Coba Hubungkan Ulang';
            }, 1000);
        }

        btnRetry.addEventListener('click', checkNetwork);

        window.addEventListener('online', () => {
            btnRetry.innerHTML = checkSvg + ' Terhubung! Memuat ulang...';
            btnRetry.style.background = '#10b981';
            setTimeout(() => window.location.reload(), 500);
        });
    </script>
</body>
</html>
