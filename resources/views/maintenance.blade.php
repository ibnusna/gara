<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - Garuda Akademi</title>
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

        .light.red {
            background: #ff5f56;
            border: 1px solid #e0443e;
        }

        .light.yellow {
            background: #ffbd2e;
            border: 1px solid #dea123;
        }

        .light.green {
            background: #27c93f;
            border: 1px solid #1aab29;
        }

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
            width: 140px;
            height: 140px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 189, 46, 0.1);
            border-radius: 50%;
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
            max-width: 320px;
        }

        @keyframes windowIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
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
            <div class="window-title">Garuda Akademi System</div>
        </header>

        <div class="window-content">
            <div class="illustration-box">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="14.3" y1="14.3" x2="19" y2="19"></line>
                    <line x1="5" y1="19" x2="9.7" y2="14.3"></line>
                    <path d="M14.3 9.7L9.7 14.3"></path>
                    <circle cx="9.5" cy="9.5" r="1.5" fill="#f59e0b"></circle>
                    <circle cx="14.5" cy="14.5" r="1.5" fill="#f59e0b"></circle>
                </svg>
            </div>

            <h1 class="error-title">Sistem Sedang Diperbaiki</h1>
            <p class="error-desc">
                Maaf, saat ini Garuda Akademi sedang dalam masa pemeliharaan rutin
                <strong>(Maintenance Mode)</strong> untuk peningkatan sistem aplikasi.<br><br>
                Silakan coba akses kembali beberapa saat lagi.
            </p>
        </div>
    </main>
</body>

</html>