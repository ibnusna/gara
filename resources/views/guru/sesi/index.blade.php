<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pilih Sesi Mengajar | GARA Guru</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/FARA_BLACK.svg') }}">

    <style>
        :root {
            --gt-primary:       #0050CB;
            --gt-primary-hover: #003FA4;
            --gt-primary-dim:   #B3C5FF;
            --gt-primary-light: #DAE1FF;
            --gt-primary-surf:  #EEF2FF;
            --gt-bg:            #F3F4F5;
            --gt-surface:       #FFFFFF;
            --gt-surface-low:   #F8F9FA;
            --gt-text:          #191C1D;
            --gt-text-2:        #424656;
            --gt-text-muted:    #727687;
            --gt-border:        #C2C6D8;
            --gt-border-light:  #E1E3E4;
            --gt-danger:        #EF4444;
            --gt-danger-bg:     #FEF2F2;
            --gt-warning-bg:    #FFFBEB;
            --gt-warning-bd:    #FDE68A;
            --gt-shadow-sm:     0 2px 8px rgba(0,0,0,0.06);
            --gt-shadow-md:     0 6px 24px rgba(0,0,0,0.1);
            --gt-shadow-primary:0 8px 24px rgba(0, 80, 203, 0.2);
            --gt-radius-sm:     8px;
            --gt-radius-md:     12px;
            --gt-radius-lg:     16px;
            --gt-radius-full:   9999px;
            --gt-font:          'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --gt-transition:    0.22s cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--gt-font);
            background: var(--gt-bg);
            color: var(--gt-text);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        
        .gt-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px 40px;
        }

        
        .gt-topbar {
            width: 100%;
            max-width: 1100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            margin-bottom: 4px;
        }

        .gt-topbar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .gt-topbar .brand img {
            height: 28px;
            width: 28px;
            object-fit: contain;
        }

        .gt-topbar .brand-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--gt-primary);
            letter-spacing: -0.03em;
        }

        .gt-topbar .brand-name span { color: var(--gt-text-muted); font-weight: 500; }

        
        .gt-container {
            width: 100%;
            max-width: 1100px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        
        .gt-welcome {
            background: linear-gradient(135deg, #0050CB 0%, #316BE5 70%, #5B8EFF 100%);
            border-radius: var(--gt-radius-lg);
            padding: 28px 28px 24px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--gt-shadow-primary);
        }

        .gt-welcome::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }

        .gt-welcome::after {
            content: '';
            position: absolute;
            bottom: -20px; left: 40px;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            pointer-events: none;
        }

        .gt-welcome .gt-salut {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.7);
            margin-bottom: 6px;
            position: relative;
        }

        .gt-welcome .gt-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.04em;
            margin-bottom: 8px;
            line-height: 1.2;
            position: relative;
        }

        .gt-welcome .gt-sub {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
            position: relative;
            line-height: 1.6;
        }

        
        .gt-section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gt-text-muted);
            margin-bottom: 24px;
            padding-left: 2px;
        }

        
        .gt-kelas-group {
            margin-bottom: 40px;
        }

        .gt-kelas-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--gt-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--gt-primary-light);
            padding-bottom: 10px;
        }

        
        .gt-sesi-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 20px;
            margin-bottom: 0;
        }

        @media (min-width: 768px) {
            .gt-sesi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .gt-sesi-form { display: block; }

        
        .gt-sesi-card {
            background: var(--gt-surface);
            border: 1.5px solid var(--gt-border-light);
            border-radius: var(--gt-radius-lg);
            height: auto;
            min-height: 140px;
            display: flex;
            align-items: stretch;
            cursor: pointer;
            transition: all var(--gt-transition);
            text-decoration: none;
            color: inherit;
            box-shadow: var(--gt-shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .gt-sesi-card:hover {
            border-color: var(--gt-primary);
            box-shadow: var(--gt-shadow-md);
            transform: translateY(-4px);
        }

        .gt-sesi-card:active {
            transform: scale(0.98) translateY(0);
        }

        
        .gt-sesi-bar {
            width: 12px;
            transition: all var(--gt-transition);
            flex-shrink: 0;
        }

        .gt-sesi-card:hover .gt-sesi-bar {
            width: 16px;
        }

        .gt-sesi-bar.icon-primary { background: linear-gradient(to bottom, #0050CB, #1a73e8); }
        .gt-sesi-bar.icon-success { background: linear-gradient(to bottom, #10b981, #059669); }
        .gt-sesi-bar.icon-info    { background: linear-gradient(to bottom, #06b6d4, #0891b2); }
        .gt-sesi-bar.icon-warning { background: linear-gradient(to bottom, #f59e0b, #d97706); }

        
        .gt-sesi-body {
            flex: 1;
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }

        .gt-sesi-header-text {
            min-width: 0;
        }

        .gt-sesi-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--gt-text);
            letter-spacing: -0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .gt-sesi-sub {
            font-size: 13px;
            color: var(--gt-text-muted);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gt-sesi-footer-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }

        .gt-sesi-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: var(--gt-radius-full);
            background: var(--gt-primary-surf);
            color: var(--gt-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .gt-sesi-arrow {
            color: var(--gt-border);
            font-size: 14px;
            transition: all var(--gt-transition);
        }

        .gt-sesi-card:hover .gt-sesi-arrow {
            color: var(--gt-primary);
            transform: translateX(4px);
        }

        
        .gt-empty {
            text-align: center;
            padding: 48px 24px;
            color: var(--gt-text-muted);
        }

        .gt-empty-icon {
            width: 72px;
            height: 72px;
            background: var(--gt-surface-low);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--gt-border);
            margin: 0 auto 20px;
        }

        .gt-empty h5 {
            font-size: 16px;
            font-weight: 700;
            color: var(--gt-text-2);
            margin-bottom: 8px;
        }

        .gt-empty p {
            font-size: 13px;
            line-height: 1.7;
            max-width: 280px;
            margin: 0 auto;
        }

        
        .gt-alert {
            padding: 14px 18px;
            border-radius: var(--gt-radius-md);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .gt-alert-warning {
            background: var(--gt-warning-bg);
            border: 1px solid var(--gt-warning-bd);
            color: #92400E;
        }

        .gt-alert-danger {
            background: var(--gt-danger-bg);
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        
        .gt-logout-area {
            margin-top: auto;
            padding-top: 32px;
            text-align: center;
        }

        .gt-btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: var(--gt-danger-bg);
            color: var(--gt-danger);
            border-radius: var(--gt-radius-full);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #FECACA;
            transition: all var(--gt-transition);
        }

        .gt-btn-logout:hover {
            background: #FEE2E2;
            color: #DC2626;
            transform: translateY(-1px);
        }

        
        @media (max-width: 767px) {
            .gt-welcome { padding: 22px 20px 20px; }
            .gt-welcome .gt-name { font-size: 1.35rem; }
            .gt-sesi-card { padding: 0; height: 110px; }
            .gt-kelas-title { font-size: 1rem; }
        }
    </style>
</head>

<body>
    <div class="gt-page">

        
        <div class="gt-topbar">
            <a href="#" class="brand">
                <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" alt="GARA">
                <span class="brand-name">Garuda <span>Akademi</span></span>
            </a>
            <span style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--gt-text-muted);">
                <i class="fas fa-chalkboard-teacher" style="color:var(--gt-primary);margin-right:4px;"></i>
                Portal Guru
            </span>
        </div>

        
        <div class="gt-container">

            @if(session('warning'))
                <div class="gt-alert gt-alert-warning">
                    <i class="fas fa-exclamation-triangle" style="margin-top:2px;flex-shrink:0;"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="gt-alert gt-alert-danger">
                    <i class="fas fa-times-circle" style="margin-top:2px;flex-shrink:0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            
            <div class="gt-welcome">
                <div class="gt-salut">Selamat Datang</div>
                <div class="gt-name">Halo, {{ explode(' ', $guruName)[0] }}! 👋</div>
                <div class="gt-sub">Silakan pilih mata pelajaran dan kelas<br>yang ingin Anda kelola hari ini.</div>
            </div>

            
            <div class="gt-section-label">
                <i class="fas fa-book-open" style="margin-right:6px;"></i>Kelas Mengajar Anda
            </div>

            
            @if ($daftarSesi->isNotEmpty())
                @foreach ($daftarSesi->groupBy('subjudul') as $kelasLabel => $sesiList)
                    <div class="gt-kelas-group">
                        <div class="gt-kelas-title">
                            <i class="fas fa-graduation-cap" style="color: var(--gt-primary);"></i>
                            <span>{{ $kelasLabel }}</span>
                        </div>
                        <div class="gt-sesi-grid">
                            @foreach ($sesiList as $sesi)
                                <form action="{{ route('guru.sesi.store') }}" method="POST" class="gt-sesi-form">
                                    @csrf
                                    <input type="hidden" name="mapel_id" value="{{ $sesi->mapel_id }}">
                                    <input type="hidden" name="kelas_id" value="{{ $sesi->kelas_id }}">

                                    <div class="gt-sesi-card" onclick="this.parentNode.submit()" role="button" tabindex="0">
                                        
                                        <div class="gt-sesi-bar {{ $sesi->bg }}"></div>
                                        
                                        
                                        <div class="gt-sesi-body">
                                            <div class="gt-sesi-header-text">
                                                <div class="gt-sesi-name" title="{{ $sesi->judul }}">{{ $sesi->judul }}</div>
                                                <div class="gt-sesi-sub">{{ $sesi->subjudul }}</div>
                                            </div>
                                            
                                            <div class="mt-2 mb-2 d-flex flex-wrap gap-1" style="gap: 4px;">
                                                @php
                                                    $isOngoing = false;
                                                    $jadwals = $jadwalMap[$sesi->kelas_id][$sesi->mapel_id] ?? [];
                                                @endphp
                                                @forelse($jadwals as $j)
                                                    @php
                                                        $active = ($j->hari == $hariIni && $waktuSekarang >= $j->jam_mulai && $waktuSekarang <= $j->jam_selesai);
                                                        if($active) $isOngoing = true;
                                                    @endphp
                                                    <span style="font-size:10px; font-weight:600; padding:3px 8px; border-radius:12px; 
                                                        background: {{ $active ? 'rgba(16, 185, 129, 0.15)' : 'rgba(0, 80, 203, 0.08)' }}; 
                                                        color: {{ $active ? '#059669' : 'var(--gt-primary)' }}; 
                                                        border: 1px solid {{ $active ? 'rgba(16, 185, 129, 0.3)' : 'rgba(0, 80, 203, 0.15)' }};
                                                        backdrop-filter: blur(4px);">
                                                        @if($active) <i class="fas fa-circle fa-beat text-success mr-1" style="font-size:8px;"></i> @endif
                                                        {{ $j->hari }}, {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                                    </span>
                                                @empty
                                                    <span style="font-size:10px; font-weight:600; padding:3px 8px; border-radius:12px; background:var(--gt-surface-low); color:var(--gt-text-muted); border:1px dashed var(--gt-border);">
                                                        <i class="fas fa-calendar-times mr-1"></i>Belum Terjadwal
                                                    </span>
                                                @endforelse
                                            </div>

                                            <div class="gt-sesi-footer-details">
                                                <span class="gt-sesi-badge {{ $isOngoing ? 'bg-success text-white' : '' }}">
                                                    {{ $isOngoing ? 'SEDANG BERLANGSUNG' : 'Masuk Sesi' }} <i class="fas fa-sign-in-alt ml-1" style="font-size:0.7rem;"></i>
                                                </span>
                                                <span class="gt-sesi-arrow">
                                                    <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="gt-empty">
                    <div class="gt-empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h5>Belum ada jadwal mengajar</h5>
                    <p>Hubungi Operator atau Kurikulum untuk mendapatkan penugasan mengajar.</p>
                </div>
            @endif

            
            <div class="gt-logout-area">
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="gt-btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Keluar Akun
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>

        </div>
    </div>
</body>

</html>