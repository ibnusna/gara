<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pilih Mata Pelajaran | GARA</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    
    <style>
        :root {
            --primary: #0056b3;
            --secondary: #6c757d;
            --bg-light: #f4f6f9;
            --card-bg: #ffffff;
            --text-dark: #333333;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        .container-pilih {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .header-section {
            margin-top: 40px;
            margin-bottom: 30px;
        }

        .welcome-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .sub-text {
            color: var(--secondary);
            font-size: 0.95rem;
        }

        .mapel-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        
        .mapel-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .mapel-card:active {
            transform: scale(0.98);
        }

        .mapel-card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 86, 179, 0.2);
        }

        .mapel-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .mapel-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(0, 86, 179, 0.1);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .mapel-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .mapel-arrow {
            color: #ced4da;
        }

        
        .logout-area {
            margin-top: auto;
            padding-top: 40px;
            text-align: center;
        }

        .btn-logout {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(220, 53, 69, 0.1);
        }

        .btn-logout:hover {
            background: rgba(220, 53, 69, 0.2);
        }

        @keyframes jam-pulse {
            0%, 100% { box-shadow: 0 0 0 3px rgba(16,185,129,0.25), 0 2px 8px rgba(16,185,129,0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(16,185,129,0.12), 0 2px 12px rgba(16,185,129,0.4); }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
</head>

<body>

    <!-- THE MESH BACKGROUND (Glassmorphism effect support) -->
    <div class="mesh-bg">
        <div class="mesh-blob blob-blue"></div>
        <div class="mesh-blob blob-purple"></div>
        <div class="mesh-blob blob-cyan"></div>
    </div>

    <div class="container-pilih" style="position: relative; z-index: 2;">
        <div class="header-section glass-card-strong" style="padding: 20px; border-radius: 20px;">
            <div class="welcome-text">Halo, {{ explode(' ', $nama_siswa)[0] }}! 👋</div>
            <div class="sub-text">Kamu berada di Kelas <b>{{ $kelas_siswa }}</b>.<br>Pilih pelajaran
                untuk memulai.</div>
            <div style="margin-top: 8px; font-size: 0.78rem; color: #0056b3; font-weight: 600; letter-spacing: 0.03em;">
                <i class="fas fa-school" style="margin-right: 4px;"></i>{{ $sekolah_nama }}
            </div>
        </div>

        <div class="mapel-grid">

            @if ($gateSiswaOpen)
                <a href="{{ route('exam.login') }}" class="mapel-card ripple"
                    style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); color: white; border: none; box-shadow: 0 8px 20px rgba(255,107,107,0.35); border-radius: 20px;">
                    <div class="mapel-info">
                        <div class="mapel-icon" style="background: rgba(255,255,255,0.25); color: white;">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div>
                            <div class="mapel-name" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.15); font-size: 1.25rem;">
                                RUANG ASESMEN
                            </div>
                            <div
                                style="font-size: 0.85rem; font-weight: 500; opacity: 0.95; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                <span
                                    style="width: 8px; height: 8px; background: #fff; border-radius: 50%; display: inline-block; box-shadow: 0 0 5px #fff;"></span>
                                Ujian Telah Dibuka!
                            </div>
                        </div>
                    </div>
                    <div class="mapel-arrow" style="color: white; font-size: 1.5rem; opacity: 0.9;">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            @endif

            @if ($mapel_list->isNotEmpty())
                @foreach ($mapel_list as $mapel)
                    
                    <form action="{{ route('student.set-mapel') }}" method="POST" class="mapel-form">
                        @csrf
                        <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">

                        @php
                            $isOngoing = false;
                            $jadwals = $jadwalMap[$mapel->id] ?? [];
                            foreach($jadwals as $j) {
                                if($j->hari == $hariIni && $waktuSekarang >= $j->jam_mulai && $waktuSekarang <= $j->jam_selesai) {
                                    $isOngoing = true;
                                    break;
                                }
                            }
                        @endphp

                        <div class="mapel-card glass-card ripple" onclick="this.parentNode.submit()" style="border-radius: 20px; {{ $isOngoing ? 'border-color: #10b981; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);' : '' }}">
                            <div class="mapel-info" style="flex: 1;">
                                <div class="mapel-icon" style="{{ $isOngoing ? 'background: rgba(16, 185, 129, 0.1); color: #059669;' : '' }}">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="mapel-name" style="{{ $isOngoing ? 'color: #059669;' : '' }}">
                                        {{ $mapel->nama_mapel }}
                                    </div>
                                    {{-- Jadwal: redesigned with active-day contrast and pulsing now-indicator --}}
                                    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 5px;">
                                        @forelse($jadwals as $j)
                                            @php
                                                $isToday  = ($j->hari == $hariIni);
                                                $active   = ($isToday && $waktuSekarang >= $j->jam_mulai && $waktuSekarang <= $j->jam_selesai);
                                            @endphp
                                            @if($active)
                                                <span style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:5px 11px;border-radius:50px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 0 0 3px rgba(16,185,129,0.25),0 2px 8px rgba(16,185,129,0.3);animation:jam-pulse 2s infinite;">
                                                    <span style="width:7px;height:7px;background:#fff;border-radius:50%;display:inline-block;box-shadow:0 0 6px rgba(255,255,255,0.9);flex-shrink:0;"></span>
                                                    {{ $j->hari }}, {{ substr($j->jam_mulai,0,5) }}&ndash;{{ substr($j->jam_selesai,0,5) }}
                                                    &nbsp;<span style="font-size:9px;font-weight:800;opacity:0.9;letter-spacing:0.04em;">SEKARANG</span>
                                                </span>
                                            @elseif($isToday)
                                                <span style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:50px;background:rgba(16,185,129,0.12);color:#059669;border:1.5px solid rgba(16,185,129,0.4);">
                                                    <i class="fas fa-calendar-check" style="font-size:9px;"></i>
                                                    {{ $j->hari }}, {{ substr($j->jam_mulai,0,5) }}&ndash;{{ substr($j->jam_selesai,0,5) }}
                                                </span>
                                            @else
                                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;padding:3px 9px;border-radius:50px;background:rgba(0,86,179,0.07);color:var(--primary);border:1px solid rgba(0,86,179,0.15);">
                                                    <i class="far fa-clock" style="font-size:9px;"></i>
                                                    {{ $j->hari }}, {{ substr($j->jam_mulai,0,5) }}&ndash;{{ substr($j->jam_selesai,0,5) }}
                                                </span>
                                            @endif
                                        @empty
                                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;padding:3px 9px;border-radius:50px;background:var(--bg-light);color:var(--secondary);border:1px dashed #ccc;">
                                                <i class="fas fa-calendar-times" style="font-size:9px;"></i>Belum Terjadwal
                                            </span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="mapel-arrow" style="display: flex; flex-direction: column; align-items: flex-end;">
                                <i class="fas fa-chevron-right" style="{{ $isOngoing ? 'color: #059669;' : '' }}"></i>
                                @if($isOngoing)
                                    <span style="font-size:9px; font-weight:700; color:#059669; text-transform:uppercase; margin-top:4px;">Sedang Belajar</span>
                                @endif
                            </div>
                        </div>
                    </form>
                @endforeach
            @else
                <div class="text-center" style="color: #999; padding: 20px;">
                    Belum ada mata pelajaran tersedia.
                </div>
            @endif
        </div>

        <div class="logout-area">
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Keluar Akun
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>

</body>

</html>