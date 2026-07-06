<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GARA Admin')</title>

    
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    
    <link rel="stylesheet" href="{{ asset('assets/css/index.css?v=2.0') }}">
    
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glass-style.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glass-dashboard.css?v=3.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/modal.css?v=3.0') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#ffffff">
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white border-bottom-0">
            <div class="container-fluid px-3 px-md-4">
                <a href="#" class="navbar-brand d-flex align-items-center">
                    <img src="{{ \App\Models\AppSetting::getLogo('FARA_BLACK.svg', true) }}" alt="Logo"
                        style="height: 35px; margin-right: 10px;">
                    <span class="brand-text font-weight-bold text-dark">Garuda Akademi</span>
                </a>

                <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center">
                    <li class="nav-item">
                        @php
                            $guruUser = \DB::connection('mysql_auth')->table('guru')->where('user_id', auth()->id())->first();
                            $guruName = $guruUser ? $guruUser->nama_lengkap : 'Guru';
                        @endphp
                        <span class="nav-link text-dark">Halo, <b>{{ htmlspecialchars($guruName) }}</b></span>
                    </li>
                    <li class="nav-item ml-2">
                        <a href="#" class="btn btn-outline-danger btn-sm"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
        

        @yield('content')

        <footer class="main-footer" style="padding: 15px; font-size: 0.9rem;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <strong style="color: #495057;">Copyright &copy; {{ date('Y') }}
                            {{ \App\Models\AppSetting::where('setting_key', 'sekolah_nama')->value('setting_value') ?? 'Garuda Akademi' }}
                            By <a href="https://garuda-akademi.com" target="_blank"
                                style="color: #0b57d0; text-decoration: none;">Garuda Akademi</a>.</strong> All rights
                        reserved.
                    </div>
                </div>
            </div>
        </footer>
    </div>
    

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/sienna-accessibility.umd.js" async></script>
    <style>
        body>#sienna-widget {
            z-index: 1040 !important;
        }
    </style>
</body>

</html>