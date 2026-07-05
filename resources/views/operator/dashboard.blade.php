@extends('layouts.operator', ['page_title' => 'Dashboard Operator | Garuda Akademi', 'active_menu' => 'dashboard'])

@section('title', 'Dashboard Operator | Garuda Akademi')

@section('content')
    
    <div class="content-wrapper">

        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="font-size:1.35rem; font-weight:700; color:#1e293b;">
                            <i class="fas fa-tachometer-alt mr-2 text-primary"></i>Dashboard Operator
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right mb-0" style="background:transparent; padding:0;">
                            <li class="breadcrumb-item"><a href="#" style="color:#0b57d0;">Home</a></li>
                            <li class="breadcrumb-item active" style="color:#64748b;">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        
        <section class="content pb-4">
            <div class="container-fluid">

                
                <div class="row mb-4">
                    <div class="col-12">
                        <div style="
                                    background: linear-gradient(135deg, #052d6e 0%, #0b57d0 60%, #1a73e8 100%);
                                    border-radius: 18px;
                                    padding: 28px 32px;
                                    color: #fff;
                                    position: relative;
                                    overflow: hidden;
                                    box-shadow: 0 8px 30px rgba(11,87,208,0.35);
                                    border: 1px solid rgba(255,255,255,0.15);
                                ">
                            
                            <div style="position:absolute;top:-60px;right:-40px;width:220px;height:220px;
                                        border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>
                            <div style="position:absolute;bottom:-50px;left:40%;width:160px;height:160px;
                                        border-radius:50%;background:rgba(66,133,244,0.15);pointer-events:none;"></div>

                            <div style="position:relative;z-index:2;">
                                <span style="display:inline-flex;align-items:center;gap:6px;
                                            background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);
                                            border-radius:99px;padding:4px 14px;font-size:0.75rem;font-weight:600;
                                            margin-bottom:10px;">
                                    <i class="fas fa-user-shield"></i> Operator Sistem
                                </span>
                                <h2 style="font-size:1.55rem;font-weight:800;margin:0 0 6px;">
                                    Selamat Datang, Operator! 👋
                                </h2>
                                <p style="margin:0;opacity:0.8;font-size:0.9rem;">
                                    Kelola seluruh data sistem Garuda Akademi dari panel terpusat ini.
                                </p>
                                <p style="margin:8px 0 0;opacity:0.55;font-size:0.78rem;">
                                    <i class="far fa-clock mr-1"></i>
                                    <span id="liveClock"></span>
                                </p>
                            </div>

                            <i class="fas fa-cogs" style="position:absolute;right:28px;top:50%;
                                        transform:translateY(-50%);font-size:100px;opacity:0.06;z-index:1;
                                        pointer-events:none;"></i>
                        </div>
                    </div>
                </div>

                
                <div class="row mb-4">

                    
                    <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ route('operator.users.index') }}" style="text-decoration:none;">
                            <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                        border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                        box-shadow:0 4px 16px rgba(11,87,208,0.1);padding:20px;
                                        transition:all .3s ease;position:relative;overflow:hidden;">
                                <div
                                    style="position:absolute;top:0;left:0;right:0;height:3px;
                                            background:linear-gradient(90deg,#0b57d0,#4285f4);border-radius:16px 16px 0 0;">
                                </div>
                                <div style="display:flex;align-items:center;gap:14px;">
                                    <div style="width:52px;height:52px;border-radius:13px;flex-shrink:0;
                                                background:linear-gradient(135deg,#0b57d0,#4285f4);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:1.3rem;
                                                box-shadow:0 4px 12px rgba(11,87,208,0.35);">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:#64748b;font-weight:700;
                                                    text-transform:uppercase;letter-spacing:.5px;">Pengguna</div>
                                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b;line-height:1.1;">
                                            Guru & Siswa
                                        </div>
                                    </div>
                                </div>
                                <div
                                    style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(11,87,208,.08);
                                            display:flex;align-items:center;gap:5px;color:#0b57d0;font-size:0.8rem;font-weight:600;">
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> Kelola User
                                </div>
                            </div>
                        </a>
                    </div>

                    
                    <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ route('operator.mapping.assignments') }}" style="text-decoration:none;">
                            <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                        border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                        box-shadow:0 4px 16px rgba(20,184,166,0.12);padding:20px;
                                        transition:all .3s ease;position:relative;overflow:hidden;">
                                <div
                                    style="position:absolute;top:0;left:0;right:0;height:3px;
                                            background:linear-gradient(90deg,#0891b2,#06b6d4);border-radius:16px 16px 0 0;">
                                </div>
                                <div style="display:flex;align-items:center;gap:14px;">
                                    <div style="width:52px;height:52px;border-radius:13px;flex-shrink:0;
                                                background:linear-gradient(135deg,#0891b2,#06b6d4);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:1.3rem;
                                                box-shadow:0 4px 12px rgba(6,182,212,0.35);">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:#64748b;font-weight:700;
                                                    text-transform:uppercase;letter-spacing:.5px;">Akademik</div>
                                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b;line-height:1.1;">
                                            Mapping
                                        </div>
                                    </div>
                                </div>
                                <div
                                    style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(8,145,178,.08);
                                            display:flex;align-items:center;gap:5px;color:#0891b2;font-size:0.8rem;font-weight:600;">
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> Penugasan Guru
                                </div>
                            </div>
                        </a>
                    </div>

                    
                    <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ route('operator.master.index') }}" style="text-decoration:none;">
                            <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                        border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                        box-shadow:0 4px 16px rgba(5,150,105,0.1);padding:20px;
                                        transition:all .3s ease;position:relative;overflow:hidden;">
                                <div
                                    style="position:absolute;top:0;left:0;right:0;height:3px;
                                            background:linear-gradient(90deg,#059669,#10b981);border-radius:16px 16px 0 0;">
                                </div>
                                <div style="display:flex;align-items:center;gap:14px;">
                                    <div style="width:52px;height:52px;border-radius:13px;flex-shrink:0;
                                                background:linear-gradient(135deg,#059669,#10b981);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:1.3rem;
                                                box-shadow:0 4px 12px rgba(16,185,129,0.35);">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:#64748b;font-weight:700;
                                                    text-transform:uppercase;letter-spacing:.5px;">Referensi</div>
                                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b;line-height:1.1;">
                                            Master
                                        </div>
                                    </div>
                                </div>
                                <div
                                    style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(5,150,105,.08);
                                            display:flex;align-items:center;gap:5px;color:#059669;font-size:0.8rem;font-weight:600;">
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> Kelas & Mapel
                                </div>
                            </div>
                        </a>
                    </div>

                    
                    <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                        <a href="{{ route('operator.asesmen.dashboard') }}" style="text-decoration:none;">
                            <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                        border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                        box-shadow:0 4px 16px rgba(245,158,11,0.1);padding:20px;
                                        transition:all .3s ease;position:relative;overflow:hidden;">
                                <div
                                    style="position:absolute;top:0;left:0;right:0;height:3px;
                                            background:linear-gradient(90deg,#d97706,#f59e0b);border-radius:16px 16px 0 0;">
                                </div>
                                <div style="display:flex;align-items:center;gap:14px;">
                                    <div style="width:52px;height:52px;border-radius:13px;flex-shrink:0;
                                                background:linear-gradient(135deg,#d97706,#f59e0b);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:1.3rem;
                                                box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:#64748b;font-weight:700;
                                                    text-transform:uppercase;letter-spacing:.5px;">Ujian</div>
                                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b;line-height:1.1;">
                                            Asesmen
                                        </div>
                                    </div>
                                </div>
                                <div
                                    style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(217,119,6,.08);
                                            display:flex;align-items:center;gap:5px;color:#d97706;font-size:0.8rem;font-weight:600;">
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> Ruang Asesmen
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

                
                <div class="row mb-4">

                    
                    <div class="col-lg-8 mb-3">
                        <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                box-shadow:0 4px 16px rgba(11,87,208,0.1);padding:22px;">

                            <h6 style="font-size:0.9rem;font-weight:700;color:#1e293b;
                                    margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                                <span style="width:28px;height:28px;border-radius:7px;
                                        background:linear-gradient(135deg,#0b57d0,#4285f4);
                                        display:inline-flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:0.8rem;">
                                    <i class="fas fa-th-large"></i>
                                </span>
                                Akses Cepat
                            </h6>

                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
                                @php
                                    $menus = [
                                        ['href' => route("operator.master.index"), 'icon' => 'fa-users-cog', 'label' => 'Guru & Siswa', 'color' => '#0b57d0'],
                                        ['href' => route("operator.mapping.curriculum"), 'icon' => 'fa-sitemap', 'label' => 'Kurikulum', 'color' => '#0891b2'],
                                        ['href' => route("operator.mapping.competency"), 'icon' => 'fa-award', 'label' => 'Kompetensi', 'color' => '#7c3aed'],
                                        ['href' => route("operator.mapping.assignments"), 'icon' => 'fa-tasks', 'label' => 'Penugasan', 'color' => '#059669'],
                                        ['href' => route("operator.master.index"), 'icon' => 'fa-book-open', 'label' => 'Data Kelas', 'color' => '#0b57d0'],
                                        ['href' => route("operator.asesmen.dashboard"), 'icon' => 'fa-file-alt', 'label' => 'Asesmen', 'color' => '#d97706'],
                                        ['href' => route("operator.asesmen.jadwal"), 'icon' => 'fa-question-circle', 'label' => 'Ujian', 'color' => '#64748b'],
                                        ['href' => route("operator.master.index"), 'icon' => 'fa-graduation-cap', 'label' => 'Mata Pelajaran', 'color' => '#dc2626'],
                                    ];
                                @endphp
                                @foreach($menus as $m)
                                    <a href="{{ $m['href'] }}" class="quick-access-btn" style="
                                                display:flex;flex-direction:column;align-items:center;justify-content:center;
                                                background:rgba(255,255,255,0.5);border:1px solid rgba(255,255,255,0.7);
                                                border-radius:12px;padding:16px 8px 12px;text-decoration:none;
                                                transition:all .25s ease;text-align:center;gap:8px;
                                                position:relative;overflow:hidden;">
                                        <i class="fas {{ $m['icon'] }}" style="font-size:1.4rem;color:{{ $m['color'] }};
                                                          transition:transform .25s ease;"></i>
                                        <span style="font-size:0.75rem;font-weight:600;color:#334155;line-height:1.2;">
                                            {{ $m['label'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-4 mb-3">
                        <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                box-shadow:0 4px 16px rgba(11,87,208,0.1);padding:22px;height:100%;">

                            <h6 style="font-size:0.9rem;font-weight:700;color:#1e293b;
                                    margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                                <span style="width:28px;height:28px;border-radius:7px;
                                        background:linear-gradient(135deg,#0b57d0,#4285f4);
                                        display:inline-flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:0.8rem;">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                Info Sistem
                            </h6>

                            
                            @php
                                $infos = [
                                    ['icon' => 'fa-server', 'label' => 'Server', 'value' => 'Online', 'pill_color' => 'rgba(34,197,94,.12)', 'pill_text_color' => '#16a34a'],
                                    ['icon' => 'fa-calendar-alt', 'label' => 'Tanggal', 'value' => date('d/m/Y'), 'pill_color' => '', 'pill_text_color' => ''],
                                    ['icon' => 'fa-database', 'label' => 'Database', 'value' => 'Terhubung', 'pill_color' => 'rgba(34,197,94,.12)', 'pill_text_color' => '#16a34a'],
                                    ['icon' => 'fa-user-shield', 'label' => 'Role', 'value' => 'Operator', 'pill_color' => 'rgba(11,87,208,.1)', 'pill_text_color' => '#0b57d0'],
                                    ['icon' => 'fa-code', 'label' => 'PHP', 'value' => phpversion(), 'pill_color' => '', 'pill_text_color' => ''],
                                ];
                            @endphp
                            @foreach($infos as $info)
                                <div style="display:flex;justify-content:space-between;align-items:center;
                                            padding:9px 12px;background:rgba(255,255,255,0.4);border-radius:9px;
                                            margin-bottom:7px;font-size:0.82rem;">
                                    <span style="color:#64748b;font-weight:500;display:flex;align-items:center;gap:8px;">
                                        <i class="fas {{ $info['icon'] }}"
                                            style="color:#0b57d0;width:14px;text-align:center;"></i>
                                        {{ $info['label'] }}
                                    </span>
                                    @if($info['pill_color'])
                                        <span style="background:{{ $info['pill_color'] }};color:{{ $info['pill_text_color'] }};
                                                        padding:2px 10px;border-radius:99px;font-size:0.72rem;font-weight:700;">
                                            {{ $info['value'] }}
                                        </span>
                                    @else
                                        <span style="font-weight:700;color:#1e293b;font-size:0.8rem;">
                                            {{ $info['value'] }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                            
                            <div
                                style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(11,87,208,0.08);display:flex;flex-direction:column;gap:7px;">
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    style="display:block;text-align:center;padding:8px 14px;
                                           background:rgba(239,68,68,0.08);color:#dc2626;
                                           border:1px solid rgba(239,68,68,0.2);border-radius:9px;
                                           font-size:0.82rem;font-weight:600;text-decoration:none;">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                
                <div class="row">
                    <div class="col-12">
                        <div style="background:rgba(255,255,255,0.6);backdrop-filter:blur(14px);
                                border:1px solid rgba(255,255,255,0.55);border-radius:16px;
                                box-shadow:0 4px 16px rgba(11,87,208,0.1);padding:22px;">

                            <h6 style="font-size:0.9rem;font-weight:700;color:#1e293b;
                                    margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                                <span style="width:28px;height:28px;border-radius:7px;
                                        background:linear-gradient(135deg,#0b57d0,#4285f4);
                                        display:inline-flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:0.8rem;">
                                    <i class="fas fa-map-signs"></i>
                                </span>
                                Panduan Pengelolaan Sistem
                            </h6>

                            <div class="row">
                                @php
                                    $guides = [
                                        [
                                            'border' => '#0b57d0',
                                            'icon' => 'fa-users',
                                            'title' => 'Manajemen User',
                                            'color' => '#0b57d0',
                                            'desc' => 'Tambah, edit, hapus dan reset password akun <b>Guru</b> serta <b>Siswa</b>. Kelola hak akses sistem pembelajaran.'
                                        ],
                                        [
                                            'border' => '#14b8a6',
                                            'icon' => 'fa-chalkboard-teacher',
                                            'title' => 'Mapping Akademik',
                                            'color' => '#0d9488',
                                            'desc' => 'Atur penugasan guru pada mata pelajaran dan kelas. Kelola kurikulum dan kompetensi berdasarkan standar akademik.'
                                        ],
                                        [
                                            'border' => '#f59e0b',
                                            'icon' => 'fa-cogs',
                                            'title' => 'Data Master & Siklus',
                                            'color' => '#d97706',
                                            'desc' => 'Kelola referensi data kelas dan mata pelajaran. Atur siklus sistem untuk periode pembelajaran aktif.'
                                        ],
                                    ];
                                @endphp
                                @foreach($guides as $g)
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div style="padding:16px 18px;border-radius:12px;
                                                    background:rgba(255,255,255,0.35);
                                                    border-left:3px solid {{ $g['border'] }};height:100%;">
                                            <h6
                                                style="font-weight:700;color:{{ $g['color'] }};margin-bottom:8px;font-size:0.9rem;">
                                                <i class="fas {{ $g['icon'] }} mr-2"></i>{{ $g['title'] }}
                                            </h6>
                                            <p style="font-size:0.82rem;color:#64748b;margin:0;line-height:1.6;">
                                                {!! $g['desc'] !!}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    @push('scripts')
        <script>
            (function () {
                function updateClock() {
                    var el = document.getElementById('liveClock');
                    if (!el) return;
                    var now = new Date();
                    el.textContent = now.toLocaleDateString('id-ID', {
                        weekday: 'long', year: 'numeric', month: 'long',
                        day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                }
                updateClock();
                setInterval(updateClock, 30000);
            })();

            
            document.querySelectorAll('.quick-access-btn').forEach(function (el) {
                el.addEventListener('mouseenter', function () {
                    this.style.background = 'rgba(255,255,255,0.88)';
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = '0 8px 20px rgba(11,87,208,0.15)';
                    this.style.borderColor = 'rgba(11,87,208,0.2)';
                    var icon = this.querySelector('i');
                    if (icon) icon.style.transform = 'scale(1.2) rotate(-5deg)';
                });
                el.addEventListener('mouseleave', function () {
                    this.style.background = 'rgba(255,255,255,0.5)';
                    this.style.transform = '';
                    this.style.boxShadow = '';
                    this.style.borderColor = 'rgba(255,255,255,0.7)';
                    var icon = this.querySelector('i');
                    if (icon) icon.style.transform = '';
                });
            });
        </script>
    @endpush
@endsection