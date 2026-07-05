<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Ditolak - Garuda Akademi</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f1 100%);
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            animation: slideIn 0.4s ease forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .icon-wrap {
            width: 80px; height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .icon-wrap svg { width: 40px; height: 40px; }
        h1 { font-size: 22px; font-weight: 700; color: #991b1b; margin-bottom: 12px; }
        p  { font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
        .badge {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            margin-bottom: 28px;
        }
        a.btn {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            text-decoration: none;
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        a.btn:hover { background: #b91c1c; }
        .footer { margin-top: 24px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
        </div>
        <div class="badge">403 — AKSES DITOLAK</div>
        <h1>Alamat IP Anda Diblokir</h1>
        <p>
            Permintaan dari alamat IP Anda tidak dapat dilayani oleh sistem
            <strong>Garuda Akademi</strong> karena telah diblokir oleh administrator.<br><br>
            Jika Anda merasa ini adalah kesalahan, hubungi administrator sekolah.
        </p>
        <a href="{{ url('/login') }}" class="btn">Kembali ke Halaman Login</a>
        <p class="footer">Garuda Akademi &mdash; Sistem Manajemen Akademik</p>
    </div>
</body>
</html>
