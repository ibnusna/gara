<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kepala Sekolah | Garuda Akademi')</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    @stack('styles')

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/glass-style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/glass-dashboard.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('helpers/components/assets/css/modal.css?v=3.0') }}">

    
    <style>
        .ks-section-card {
            border-top: 3px solid #1a3c6e;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        
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

        .badge-submitted {
            background: #d97706;
            color: #fff;
        }

        .badge-draft {
            background: #6b7280;
            color: #fff;
        }

        .badge-validated {
            background: #16a34a;
            color: #fff;
        }

        


        .ks-portal-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            border-top: 3px solid var(--cat-color, #2563eb);
            background: #fff;
            text-decoration: none !important;
            color: inherit;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            height: 100%;
            min-height: 110px;
        }

        .ks-portal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: var(--cat-color, #2563eb);
            text-decoration: none !important;
            color: inherit;
        }

        .ks-portal-icon-wrap {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .ks-portal-body {
            flex: 1;
            min-width: 0;
        }

        .ks-portal-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #111827;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ks-portal-desc {
            font-size: 0.76rem;
            color: #6b7280;
            margin: 2px 0 6px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ks-portal-badge {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .ks-portal-footer {
            display: flex;
            align-items: center;
            font-size: 0.76rem;
            font-weight: 600;
            color: #2563eb;
            margin-top: 2px;
        }

        .ks-portal-footer .fa-arrow-right {
            transition: transform 0.15s ease;
        }

        .ks-portal-card:hover .ks-portal-footer .fa-arrow-right {
            transform: translateX(4px);
        }

        
        .badge-berlangsung {
            background: #16a34a;
        }

        .badge-mendatang {
            background: #2563eb;
        }

        .badge-selesai {
            background: #6b7280;
        }
    </style>

    
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#0b57d0">

</head>

<body class="hold-transition sidebar-mini layout-fixed sidebar-dark-primary">
    <div class="wrapper">

        
        <nav class="main-header navbar navbar-expand navbar-light"
            style="border-bottom:1px solid rgba(255,255,255,0.3);">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-flex align-items-center">
                    <img src="{{ \App\Models\AppSetting::getLogo('FARA_BLACK.svg', true) }}" alt="GARA Logo"
                        style="height:28px; margin-right:8px; opacity:0.85">
                    <span class="font-weight-bold" style="color:#0b57d0; font-size:0.95rem;">Garuda Akademi</span>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center mr-3">
                    <span class="text-muted" style="font-size:0.82rem;">
                        <i class="fas fa-user-tie mr-1" style="color:#0b57d0;"></i>
                        Kepala Sekolah
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
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://ui-avatars.com/api/?name=Kepala+Sekolah&background=random"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info"><a href="#"
                            class="d-block">{{ auth()->user()->nama_lengkap ?? 'Kepala Sekolah' }}</a></div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @php
                            $activeMenu = $active_menu ?? '';
                        @endphp

                        <li class="nav-item">
                            <a href="{{ route('kepsek.dashboard') }}"
                                class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Executive Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">MONITORING AKADEMIK</li>

                        <li class="nav-item">
                            <a href="{{ route('kepsek.kinerja_guru') }}"
                                class="nav-link {{ $activeMenu == 'kinerja_guru' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                <p>Kinerja Guru</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('kepsek.ujian_nilai') }}"
                                class="nav-link {{ $activeMenu == 'ujian_nilai' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Monitoring Ujian & Nilai</p>
                            </a>
                        </li>

                        <li class="nav-header">REPORTING</li>

                        <li class="nav-item">
                            <a href="{{ route('kepsek.laporan_akademik') }}"
                                class="nav-link {{ $activeMenu == 'laporan_akademik' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Laporan Eksekutif</p>
                            </a>
                        </li>

                        <li class="nav-header">LAYANAN NASIONAL</li>

                        
                        <li class="nav-item {{ $activeMenu == 'portal_nasional' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $activeMenu == 'portal_nasional' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-globe"></i>
                                <p>
                                    Layanan & Portal Nasional
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">

                                
                                <li class="nav-item">
                                    <span class="nav-link"
                                        style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;color:rgba(255,255,255,0.35);cursor:default;padding-top:10px;padding-bottom:2px;">
                                        <i class="fas fa-layer-group mr-1" style="font-size:0.65rem;"></i>Superaplikasi
                                        & Sekolah
                                    </span>
                                </li>

                                <li class="nav-item">
                                    <a href="https://rumah.pendidikan.go.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-house-chimney-user mr-1 text-info" style="width:14px;"></i>
                                            Rumah Pendidikan
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://rumah.pendidikan.go.id/ruang-gtk" target="_blank"
                                        rel="noopener noreferrer" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-chalkboard-user mr-1 text-info" style="width:14px;"></i>
                                            Ruang GTK
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://kspstendik.kemendikdasmen.go.id" target="_blank"
                                        rel="noopener noreferrer" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-id-badge mr-1 text-info" style="width:14px;"></i>
                                            KSPSTENDIK
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                
                                <li class="nav-item">
                                    <span class="nav-link"
                                        style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;color:rgba(255,255,255,0.35);cursor:default;padding-top:10px;padding-bottom:2px;">
                                        <i class="fas fa-layer-group mr-1" style="font-size:0.65rem;"></i>Data &
                                        Administrasi
                                    </span>
                                </li>

                                <li class="nav-item">
                                    <a href="https://dapo.kemendikdasmen.go.id" target="_blank"
                                        rel="noopener noreferrer" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-database mr-1 text-success" style="width:14px;"></i>
                                            Dapodik
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://belajar.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-envelope-open-text mr-1 text-success"
                                                style="width:14px;"></i>
                                            Belajar.id
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://portal.simpkb.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-graduation-cap mr-1 text-success" style="width:14px;"></i>
                                            SIMPKB
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                
                                <li class="nav-item">
                                    <span class="nav-link"
                                        style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;color:rgba(255,255,255,0.35);cursor:default;padding-top:10px;padding-bottom:2px;">
                                        <i class="fas fa-layer-group mr-1" style="font-size:0.65rem;"></i>Kebijakan &
                                        Regulasi
                                    </span>
                                </li>

                                <li class="nav-item">
                                    <a href="https://gtk.kemendikdasmen.go.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-sitemap mr-1 text-warning" style="width:14px;"></i>
                                            Dirjen GTK
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://jdih.kemdikbud.go.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-scale-balanced mr-1 text-warning" style="width:14px;"></i>
                                            JDIH
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="https://www.kemendikdasmen.go.id" target="_blank" rel="noopener noreferrer"
                                        class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            <i class="fas fa-landmark mr-1 text-warning" style="width:14px;"></i>
                                            Kemendikdasmen
                                            <i class="fas fa-external-link-alt ml-1"
                                                style="font-size:0.6rem;opacity:0.5;"></i>
                                        </p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-header">AKUN</li>
                        <li class="nav-item">
                            <a href="{{ route('kepsek.password.form') }}"
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

        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }}
                {{ \App\Models\AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'Garuda Akademi' }}
                By Garuda Akademi.</strong>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    
    @stack('scripts_vendor')
    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.modal').appendTo('body');
        });

        @if (session('success_message'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '{!! addslashes(session('success_message')) !!}', timer: 1500, showConfirmButton: false });
        @endif
        @if (session('error_message'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: '{!! addslashes(session('error_message')) !!}' });
        @endif
    </script>

    @stack('scripts_custom')
    
    <script src="https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/sienna-accessibility.umd.js" async></script>
    <style>
        body>#sienna-widget {
            z-index: 1040 !important;
        }
    </style>
</body>

</html>