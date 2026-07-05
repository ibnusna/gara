<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GARA LMS - Installation Wizard</title>

    <link rel="icon" href="{{ asset('exam/img/GARA_ICON.svg') }}" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}">
    <link rel="stylesheet" href="{{ asset('exam/assets/header.css') }}">
    <link rel="stylesheet" href="{{ asset('exam/assets/summary.css') }}">

    <style>
        /* Specific adjustments for installer */
        .page-title {
            font-size: 1.5rem !important;
        }

        .account-data-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .account-group {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .account-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #0b57d0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .account-detail {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .account-detail span {
            color: #64748b;
            font-weight: 500;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-primary-full {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #0b57d0 0%, #08429e 100%);
            color: #fff;
            border: none;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(11, 87, 208, 0.2);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary-full:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11, 87, 208, 0.3);
        }

        /* Transmission Loading Animation */
        .transmission-loader {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .transmission-loader .tower {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #0b57d0, #38bdf8);
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 0 20px rgba(11, 87, 208, 0.6);
            animation: pulse-tower 2s infinite alternate;
        }
        .transmission-loader .wave {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid #38bdf8;
            border-radius: 50%;
            animation: transmit 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
            opacity: 0;
            box-shadow: inset 0 0 10px rgba(56, 189, 248, 0.3), 0 0 10px rgba(56, 189, 248, 0.3);
        }
        .transmission-loader .wave:nth-child(2) { animation-delay: 0.6s; }
        .transmission-loader .wave:nth-child(3) { animation-delay: 1.2s; }
        
        @keyframes transmit {
            0% { transform: scale(0.2); opacity: 0.9; border-width: 4px; }
            100% { transform: scale(1.5); opacity: 0; border-width: 1px; }
        }
        @keyframes pulse-tower {
            0% { transform: scale(0.9); box-shadow: 0 0 10px rgba(11, 87, 208, 0.4); }
            100% { transform: scale(1.1); box-shadow: 0 0 25px rgba(56, 189, 248, 0.8); }
        }

        @media print {
            body { background: none; }
            .app-header, .action-buttons { display: none !important; }
            .confirmation-card { box-shadow: none; border: none; width: 100%; max-width: none; }
            .account-group { border: 1px solid #000; break-inside: avoid; }
        }
    </style>
</head>

<body>
    <div id="mainLayout" class="confirmation-layout">
        
        <header class="app-header">
            <div class="header-container">
                <img src="{{ asset('exam/img/FARA_BLACK (1).svg') }}" alt="Logo GARA" class="brand-logo" onerror="this.src='{{ asset('assets/img/FARA_BLACK.svg') }}'">
                <div class="brand-divider">
                    <h1 class="brand-title">
                    INSTALASI<br>GARUDA AKADEMI
                    </h1>
                </div>
            </div>
        </header>

        
        <div id="loadingOverlay" class="loading-overlay" style="display: none; align-items: center; justify-content: center; flex-direction: column; min-height: 300px;">
            <div class="transmission-loader">
                <div class="tower"></div>
                <div class="wave"></div>
                <div class="wave"></div>
                <div class="wave"></div>
            </div>
            <h3 style="color: #0b57d0; font-weight: 700; margin-top: 1.5rem; letter-spacing: 0.5px;">Memproses Instalasi...</h3>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.5rem;" id="loading-msg">Membangun struktur database sistem</p>
        </div>

        
        <div id="setup-phase">
            <div class="confirmation-card">
                <header class="card-header">
                    <div class="header-pattern"></div>
                    <div class="header-content">
                        <div class="header-left">
                            <p class="school-label">Garuda Akademi</p>
                            <h1 class="page-title">Setup Database Sistem</h1>
                        </div>
                    </div>
                </header>

                <div class="card-body">
                    <div class="rules-box" style="margin-bottom: 1.5rem; border-color: #f59e0b; background-color: #fffbeb;">
                        <i class="fas fa-exclamation-triangle rules-icon" style="color: #d97706;"></i>
                        <div class="rules-content">
                            <span class="rules-title" style="color: #b45309;">Database belum terkonfigurasi!</span>
                            <ul class="rules-list" style="color: #92400e;">
                                <li>Sistem mendeteksi bahwa database bawaan belum terisi.</li>
                                <li>Silakan klik tombol di bawah untuk membuat skema master secara otomatis.</li>
                            </ul>
                        </div>
                    </div>

                    <button id="btn-run-setup" class="btn-start-exam" onclick="runSetup()">
                        <span>MULAI KONFIGURASI DATABASE</span>
                        <i class="fas fa-database"></i>
                    </button>
                </div>
            </div>
        </div>

        
        <div id="success-phase" style="display: none;">
            <div class="confirmation-card">
                <header class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="header-pattern"></div>
                    <div class="header-content">
                        <div class="header-left">
                            <p class="school-label" style="color: #d1fae5;">Garuda Akademi</p>
                            <h1 class="page-title">Instalasi Sukses!</h1>
                        </div>
                        <i class="fas fa-check-double" style="font-size: 2.5rem; color: #fff; opacity: 0.8;"></i>
                    </div>
                </header>

                <div class="card-body">
                    <div class="rules-box" id="success-msg" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-info-circle rules-icon"></i>
                        <div class="rules-content">
                            <span class="rules-title">Informasi Akun Default:</span>
                            <ul class="rules-list">
                                <li>Database berhasil dibuat dengan aman.</li>
                                <li>Harap simpan atau catat informasi akun dummy di bawah ini.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="account-data-grid">
                        <div class="account-group">
                            <label class="account-label">Super Admin (Utama)</label>
                            <div class="account-detail"><span>Username:</span> <strong>admin</strong></div>
                            <div class="account-detail"><span>Password:</span> <strong>admin123</strong></div>
                        </div>
                        <div class="account-group">
                            <label class="account-label">Operator Sekolah</label>
                            <div class="account-detail"><span>Username:</span> <strong>operator</strong></div>
                            <div class="account-detail"><span>Password:</span> <strong>password123</strong></div>
                        </div>
                        <div class="account-group">
                            <label class="account-label">Kepala Sekolah</label>
                            <div class="account-detail"><span>Username:</span> <strong>kepsek</strong></div>
                            <div class="account-detail"><span>Password:</span> <strong>admingara776</strong></div>
                        </div>
                        <div class="account-group">
                            <label class="account-label">Akun Guru</label>
                            <div class="account-detail"><span>Username:</span> <strong>guru</strong></div>
                            <div class="account-detail"><span>Password:</span> <strong>password123</strong></div>
                        </div>
                        <div class="account-group">
                            <label class="account-label">Akun Siswa</label>
                            <div class="account-detail"><span>Username:</span> <strong>siswa</strong></div>
                            <div class="account-detail"><span>Password:</span> <strong>password123</strong></div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-secondary" onclick="downloadPDF()">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <button class="btn-secondary" id="btn-copy-info" onclick="copyAccountInfo()">
                            <i class="far fa-copy"></i> Copy Teks
                        </button>
                        <a href="{{ route('login') }}" class="btn-primary-full">
                            <span>MASUK KE HALAMAN LOGIN</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function runSetup() {
            const setupPhase = document.getElementById('setup-phase');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingMsg = document.getElementById('loading-msg');

            setupPhase.style.display = 'none';
            loadingOverlay.style.display = 'flex';
            
            console.log("-> Menginisialisasi setup database GARA...");

            const msgs = ['Membangun skema tabel...', 'Menyiapkan data dummy...', 'Memfinalisasi pengaturan transmisi...'];
            let msgIdx = 0;
            const msgInterval = setInterval(() => {
                msgIdx = (msgIdx + 1) % msgs.length;
                if(loadingMsg) loadingMsg.innerText = msgs[msgIdx];
            }, 2000);

            fetch('{{ route("install.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': ''
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.message || 'Setup gagal dieksekusi.');
                    });
                }
                return response.json();
            })
            .then(data => {
                clearInterval(msgInterval);
                if (data.success) {
                    console.log("[CONSOLE OUTPUT]\n", data.output || 'Setup selesai dengan sukses.');
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                        document.getElementById('success-phase').style.display = 'block';
                    }, 500);
                } else {
                    throw new Error(data.message || 'Setup command did not complete successfully.');
                }
            })
            .catch(error => {
                clearInterval(msgInterval);
                console.error("[FATAL ERROR DETECTED]\n", error.message);
                loadingOverlay.style.display = 'none';
                setupPhase.style.display = 'block';
                alert("Setup Gagal: Cek DevTools Console (F12) untuk detail error.");
            });
        }

        function copyAccountInfo() {
            const infoText = `GARA LMS - Informasi Akun Default\n\n` +
                             `[Super Admin]\nUsername: admin\nPassword: admin123\n\n` +
                             `[Operator Sekolah]\nUsername: operator\nPassword: password123\n\n` +
                             `[Kepala Sekolah]\nUsername: kepsek\nPassword: admingara776\n\n` +
                             `[Guru]\nUsername: guru\nPassword: password123\n\n` +
                             `[Siswa]\nUsername: siswa\nPassword: password123`;
            
            const copyBtn = document.getElementById('btn-copy-info');
            
            navigator.clipboard.writeText(infoText).then(() => {
                const originalHtml = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
                copyBtn.style.background = '#d1fae5';
                copyBtn.style.color = '#059669';
                copyBtn.style.borderColor = '#34d399';
                
                setTimeout(() => {
                    copyBtn.innerHTML = originalHtml;
                    copyBtn.style.background = '';
                    copyBtn.style.color = '';
                    copyBtn.style.borderColor = '';
                }, 2000);
            }).catch(err => {
                alert('Gagal menyalin info ke clipboard.');
            });
        }

        function downloadPDF() {
            document.getElementById('success-msg').style.display = 'none';
            window.print();
            setTimeout(() => {
                document.getElementById('success-msg').style.display = 'flex';
            }, 500);
        }
    </script>
</body>
</html>