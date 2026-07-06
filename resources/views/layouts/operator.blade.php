<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GARA Admin')</title>

    
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
    </style>
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
                        <i class="fas fa-user-shield mr-1" style="color:#0b57d0;"></i>
                        Operator
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
                        <img src="{{ auth()->user()->profile_photo_url }}" class="img-circle elevation-2"
                            alt="Foto Profil" style="width: 34px; height: 34px; object-fit: cover;">
                    </div>
                    <div class="info">
                        <a href="{{ route('operator.profile.index') }}" class="d-block">
                            {{ auth()->user()->nama_lengkap ?? 'Operator' }}
                        </a>
                    </div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @php
                            $activeMenu = $active_menu ?? '';
                            $activeSubMenu = $active_submenu ?? '';
                        @endphp
                        <li class="nav-item">
                            <a href="{{ route('operator.dashboard') }}"
                                class="nav-link {{ $activeMenu == 'dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">MANAJEMEN USER</li>
                        <li class="nav-item">
                            <a href="{{ route('operator.users.index') }}"
                                class="nav-link {{ $activeMenu == 'users' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Data Guru & Siswa</p>
                            </a>
                        </li>

                        <li class="nav-header">ASESMEN</li>
                        <li class="nav-item {{ $activeMenu == 'ujian' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $activeMenu == 'ujian' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Asesmen <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('operator.asesmen.dashboard') }}"
                                        class="nav-link {{ $activeSubMenu == 'asesmen_dashboard' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard Asesmen</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('operator.asesmen.jadwal') }}"
                                        class="nav-link {{ $activeSubMenu == 'jadwal_ujian' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jadwal Ujian</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('operator.asesmen.hasil') }}"
                                        class="nav-link {{ $activeSubMenu == 'hasil_ujian' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Hasil Ujian</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-header">AKADEMIK</li>
                        <li class="nav-item {{ $activeMenu == 'mapping' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $activeMenu == 'mapping' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                <p>Mapping & Penugasan <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('operator.mapping.curriculum') }}"
                                        class="nav-link {{ $activeSubMenu == 'curriculum' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kurikulum Kelas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('operator.mapping.jadwal') }}"
                                        class="nav-link {{ $activeSubMenu == 'jadwal_pelajaran' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jadwal Pelajaran</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('operator.mapping.competency') }}"
                                        class="nav-link {{ $activeSubMenu == 'competency' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kompetensi Guru</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('operator.mapping.assignments') }}"
                                        class="nav-link {{ $activeSubMenu == 'assignments' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Penugasan Guru</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('operator.master.index') }}"
                                class="nav-link {{ $activeMenu == 'master' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Data Master (Mapel/Kelas)</p>
                            </a>
                        </li>

                        <li class="nav-header">AKUN</li>
                        <li class="nav-item">
                            <a href="{{ route('operator.profile.index') }}"
                                class="nav-link {{ $activeMenu == 'profil' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Tentang Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('operator.password.form') }}"
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
    
    <script src="https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/sienna-accessibility.umd.js" async></script>
    <style>
        body>#sienna-widget {
            z-index: 1040 !important;
        }
    </style>
</body>

</html>