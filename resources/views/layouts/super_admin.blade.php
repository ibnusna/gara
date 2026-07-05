<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GARA Admin')</title>

    @php
        
        $root_path = '/';
        
        $sekolah_nama = \App\Models\AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'TBD';
    @endphp

    
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link rel="stylesheet"
        href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    
    <link rel="stylesheet" href="{{ $root_path }}helpers/assets/css/index.css?v=2.0">
    <link rel="stylesheet" href="{{ $root_path }}helpers/components/assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="{{ $root_path }}helpers/components/assets/css/glass-style.css?v=3.0">
    <link rel="stylesheet" href="{{ $root_path }}helpers/components/assets/css/glass-dashboard.css?v=3.0">
    <link rel="stylesheet" href="{{ $root_path }}helpers/components/assets/css/modal.css?v=3.0">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <style>
        .small-box {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
    </style>
    @stack('styles')

</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item">
                    <span class="nav-link font-weight-bold text-primary d-flex align-items-center">
                        <img src="{{ asset('assets/img/FARA_BLACK.svg') }}" alt="GARA Logo"
                            style="height: 30px; margin-right: 8px;">
                        Garuda Akademi
                    </span>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                            class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ auth()->user()->profile_photo_url }}"
                            class="img-circle elevation-2" alt="Foto Profil"
                            style="width: 34px; height: 34px; object-fit: cover;">
                    </div>
                    <div class="info">
                        <a href="{{ route('superadmin.profile.index') }}" class="d-block">
                            {{ auth()->user()->nama_lengkap ?? 'Administrator' }}
                        </a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">SYSTEM</li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.settings.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Pengaturan Sistem</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.roles.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.roles.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Role & Permission</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.users.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Admin & Operator</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.audit.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.audit.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Audit Log</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.backup.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.backup.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Database & Backup</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.security.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.security.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Keamanan Sistem</p>
                            </a>
                        </li>

                        <li class="nav-header">LAINNYA</li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.profile.index') }}"
                                class="nav-link {{ request()->routeIs('superadmin.profile.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Tentang Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.password.form') }}"
                                class="nav-link {{ request()->routeIs('superadmin.password.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-key"></i>
                                <p>Ubah Password</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="nav-link text-danger">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

        
        <div class="content-wrapper">
            @yield('content')
        </div>

        
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} {{ $sekolah_nama }} By Garuda Akademi.</strong>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.modal').appendTo('body');
        });

        @if(session('success_message'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session('success_message') }}', timer: 1500, showConfirmButton: false });
        @endif
        @if(session('error_message'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ session('error_message') }}' });
        @endif
    </script>
    @stack('scripts')
    
    <script src="https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/sienna-accessibility.umd.js" async></script>
    <style>
        body > #sienna-widget {
            z-index: 1040 !important;
        }
    </style>
</body>

</html>