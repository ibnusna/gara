<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GARA Guru')</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    @stack('styles')
    @if(isset($use_datatables) && $use_datatables)
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    @endif

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/glass-style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/glass-dashboard.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/modal.css?v=3.0') }}">

    
    <link rel="stylesheet" href="{{ asset('helpers/assets/css/teacher-theme.css?v=2.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/assets/css/teacher-sidebar.css?v=2.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/assets/css/teacher-components.css?v=2.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/assets/css/teacher-pages.css?v=2.0') }}">

    
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#0b57d0">
    <style>
        
        .main-sidebar {
            padding-top: 12px !important;
        }
        .sidebar-collapse .main-sidebar {
            padding-top: 15px !important;
        }
        .sidebar-collapse .main-sidebar .user-panel {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            justify-content: center !important;
            border-bottom: none !important;
        }
        .sidebar-collapse .main-sidebar .user-panel .info,
        .sidebar-collapse .main-sidebar .sesi-aktif-box,
        .sidebar-collapse .main-sidebar .nav-header {
            display: none !important;
        }

        
        body.guru-workspace {
            background-color: #F4F7FC !important;
        }

        body.guru-workspace .main-sidebar {
            background-color: #0050CB !important;
        }

        
        body.guru-workspace .text-center img {
            margin-left: auto;
            margin-right: auto;
        }

        
        .dt-container {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .dt-container .row:first-child {
            margin-bottom: 18px;
            align-items: center;
        }

        .dt-container .row:last-child {
            margin-top: 18px;
            align-items: center;
        }

        
        .dt-length label,
        .dt-search label {
            font-weight: 600;
            color: #475569;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }

        .dt-input {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 8px 16px !important;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            background-color: #fff !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
            box-shadow: none !important;
            font-weight: 500;
        }

        .dt-input:focus {
            border-color: #0b57d0 !important;
            box-shadow: 0 0 0 3px rgba(11, 87, 208, 0.15) !important;
        }

        select.dt-input {
            padding-right: 32px !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 10px center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
        }

        .dt-search {
            text-align: right;
        }

        .dt-search label {
            justify-content: flex-end;
        }

        .dt-search input {
            width: 240px !important;
        }

        
        .dt-info {
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 600;
        }

        
        .dt-paging {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .dt-paging-button {
            background: #fff !important;
            border: 1.5px solid #e2e8f0 !important;
            color: #334155 !important;
            padding: 8px 16px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            transition: all 0.2s ease-in-out !important;
            outline: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .dt-paging-button:hover:not(.disabled):not(.active):not(.current) {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
            text-decoration: none !important;
        }

        .dt-paging-button.active,
        .dt-paging-button.current {
            background: linear-gradient(135deg, #0b57d0 0%, #2563eb 100%) !important;
            border-color: #0b57d0 !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(11, 87, 208, 0.25) !important;
        }

        .dt-paging-button.disabled {
            background: #f8fafc !important;
            border-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.7;
        }
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed sidebar-dark-primary guru-workspace">
    <div class="wrapper">

        
        <nav class="main-header navbar navbar-expand navbar-white navbar-light"
            style="border-bottom:1px solid rgba(0,0,0,0.08);">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-flex align-items-center">
                    <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" alt="GARA Logo"
                        style="height:28px; margin-right:8px; opacity:0.85">
                    <span class="font-weight-bold" style="color:#0b57d0; font-size:0.95rem;">Garuda Akademi</span>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center mr-3">
                    <span class="text-muted" style="font-size:0.82rem;">
                        <i class="fas fa-chalkboard-teacher mr-1" style="color:#0b57d0;"></i>
                        Guru
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger font-weight-bold" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-md-inline ml-1">Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>

        
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <div class="sidebar">
                @php
                    $guruUser = \DB::connection('mysql_auth')->table('guru')->where('user_id', auth()->id())->first();
                    $guruName = $guruUser ? $guruUser->nama_lengkap : (auth()->user()->nama_lengkap ?? 'Guru');
                @endphp
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ auth()->user()->profile_photo_url }}" class="img-circle elevation-2"
                            alt="Foto Profil" style="width: 34px; height: 34px; object-fit: cover;">
                    </div>
                    <div class="info">
                        <a href="{{ route('guru.profile.index') }}" class="d-block">{{ $guruName }}</a>
                    </div>
                </div>
                
                @if(session('kelas_id') && session('mapel_id'))
                    <div class="p-2 mb-3 mx-2 rounded text-center sesi-aktif-box"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <div style="font-size: 0.75rem; color: #aaa; text-transform: uppercase;">Sesi Aktif</div>
                        <div style="font-size: 0.85rem; font-weight: bold; color: #fff;">{{ session('nama_mapel') }}</div>
                        <div style="font-size: 0.80rem; color: #e0e0e0;"><i class="fas fa-school mr-1"></i>
                            {{ session('nama_kelas') }}</div>
                    </div>
                @endif
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @php
                            $activeMenu = $active_menu ?? '';
                        @endphp
                        <li class="nav-item">
                            <a href="{{ route('guru.dashboard') }}"
                                class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if(session('kelas_id') && session('mapel_id'))
                            
                            @php
                                $isAkademikActive = in_array($activeMenu, ['materi', 'diskusi', 'ruang_tugas', 'ruang_kompetensi']);
                            @endphp
                            <li class="nav-item has-treeview {{ $isAkademikActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $isAkademikActive ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chalkboard"></i>
                                    <p>
                                        Akademik & Kelas
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview"
                                    style="margin-left: 10px; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 5px;">
                                    <li class="nav-item">
                                        <a href="{{ route('guru.materi') }}"
                                            class="nav-link {{ $activeMenu == 'materi' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Ruang Materi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.diskusi') }}"
                                            class="nav-link {{ $activeMenu == 'diskusi' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Ruang Diskusi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.ruang_tugas.index') }}"
                                            class="nav-link {{ $activeMenu == 'ruang_tugas' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Ruang Tugas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.ruang_kompetensi.index') }}"
                                            class="nav-link {{ $activeMenu == 'ruang_kompetensi' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Ruang Kompetensi</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            
                            @php
                                $isPenilaianActive = in_array($activeMenu, ['ujian', 'tugas', 'rekap', 'absensi', 'rekap_absensi', 'agenda']);
                            @endphp
                            <li class="nav-item has-treeview {{ $isPenilaianActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $isPenilaianActive ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>
                                        Penilaian & Laporan
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview"
                                    style="margin-left: 10px; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 5px;">
                                    <li class="nav-item">
                                        <a href="{{ route('guru.ujian') }}"
                                            class="nav-link {{ $activeMenu == 'ujian' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Ruang Asesmen</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.tugas.create') }}"
                                            class="nav-link {{ $activeMenu == 'tugas' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Input Nilai Tugas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.rekap.index') }}"
                                            class="nav-link {{ $activeMenu == 'rekap' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Rekap Tugas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.absensi.create') }}"
                                            class="nav-link {{ $activeMenu == 'absensi' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Input Absensi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.absensi.rekap') }}"
                                            class="nav-link {{ $activeMenu == 'rekap_absensi' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Rekap Absensi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('guru.agenda.index') }}"
                                            class="nav-link {{ $activeMenu == 'agenda' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size:0.6rem;"></i>
                                            <p>Agenda Mengajar</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <li class="nav-header">LAINNYA</li>
                        <li class="nav-item">
                            <a href="{{ route('guru.profile.index') }}"
                                class="nav-link {{ $activeMenu == 'profil' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Tentang Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('guru.sesi.index') }}"
                                class="nav-link bg-secondary {{ $activeMenu == 'sesi' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>Ganti Kelas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('guru.password.form') }}"
                                class="nav-link {{ $activeMenu == 'ubah_password' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-key"></i>
                                <p>Ubah Password</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        
        @yield('content')

        
        <footer class="main-footer" style="padding: 15px; font-size: 0.9rem;">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <strong style="color: #495057;">Copyright &copy; {{ date('Y') }}
                        {{ \App\Models\AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'Garuda Akademi' }}
                        By <a href="https://garuda-akademi.com" target="_blank"
                            style="color: #0b57d0; text-decoration: none;">Garuda Akademi</a>.</strong> All rights
                    reserved.
                </div>
            </div>
        </footer>

    </div>
    

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts_vendor')

    @if(isset($use_datatables) && $use_datatables)
        
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    @endif

    
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js">
    </script>


    <script>
        $(document).ready(function () {
            
            $('.modal').appendTo('body');
        });
    </script>

    @stack('scripts')
    
    <script src="https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/sienna-accessibility.umd.js" async></script>
    <style>
        body>#sienna-widget {
            z-index: 1040 !important;
        }
    </style>
</body>

</html>