@extends($layout)







@section('title', 'Ubah Password | GARA')







@push('css')
<style>
    .pwd-card {
        max-width: 480px;
        margin: 30px auto;
        background: #fff;
        border-radius: 14px;
        padding: 28px 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        border: 1px solid #eef0f5;
    }
    .pwd-card .section-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #adb5bd;
        font-weight: 700;
        margin-bottom: 18px;
    }
    .pwd-card .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
    }
    .pwd-input-wrap {
        position: relative;
    }
    .pwd-input-wrap input {
        padding-right: 42px;
        border-radius: 9px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    .pwd-input-wrap input:focus {
        background: #fff;
        border-color: #0d6efd;
        box-shadow: none;
        outline: none;
    }
    .pwd-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #6c757d;
        font-size: 0.85rem;
        line-height: 1;
    }
    .pwd-toggle:hover { color: #343a40; }
    .pwd-submit {
        border-radius: 30px;
        padding: 10px 0;
        font-weight: 700;
        font-size: 0.95rem;
        width: 100%;
        margin-top: 6px;
    }
    .pwd-notice {
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 0.88rem;
        line-height: 1.5;
    }
    
    .pwd-card .form-control { display: block; width: 100%; }
</style>
@endpush

@push('styles')
<style>
    .pwd-card {
        max-width: 480px;
        margin: 30px auto;
        background: #fff;
        border-radius: 14px;
        padding: 28px 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        border: 1px solid #eef0f5;
    }
    .pwd-card .section-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #adb5bd;
        font-weight: 700;
        margin-bottom: 18px;
    }
    .pwd-card .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
    }
    .pwd-input-wrap {
        position: relative;
    }
    .pwd-input-wrap input {
        padding-right: 42px;
        border-radius: 9px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    .pwd-input-wrap input:focus {
        background: #fff;
        border-color: #0d6efd;
        box-shadow: none;
        outline: none;
    }
    .pwd-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #6c757d;
        font-size: 0.85rem;
        line-height: 1;
    }
    .pwd-toggle:hover { color: #343a40; }
    .pwd-submit {
        border-radius: 30px;
        padding: 10px 0;
        font-weight: 700;
        font-size: 0.95rem;
        width: 100%;
        margin-top: 6px;
    }
    .pwd-notice {
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 0.88rem;
        line-height: 1.5;
    }
    .pwd-card .form-control { display: block; width: 100%; }
</style>
@endpush

@section('content')


@if($role === 'siswa')

    
    <div class="header-simple px-3 py-3 bg-white border-bottom sticky-top mb-3">
        <div class="d-flex align-items-center justify-content-center position-relative">
            
            
            <a href="#" onclick="history.back(); return false;"
               id="btn-back-ubah-password"
               class="text-dark position-absolute" style="left: 0;">
                <i class="fas fa-arrow-left fa-lg"></i>
            </a>
            <h6 class="m-0 fw-bold">Ubah Password</h6>
        </div>
    </div>

    <div class="px-3">
        @include('shared._ubah_password_form')
    </div>

@else

    
    @if($role === 'super_admin')
        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="font-size:1.4rem; font-weight:700; color:#212529;">
                            <i class="fas fa-key mr-2" style="color:#0b57d0;"></i>Ubah Password
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">Ubah Password</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('shared._ubah_password_form')
            </div>
        </div>
    @else
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0" style="font-size:1.4rem; font-weight:700; color:#212529;">
                                <i class="fas fa-key mr-2" style="color:#0b57d0;"></i>Ubah Password
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    @php
                                        $dashboardRoutes = [
                                            'guru'        => 'guru.dashboard',
                                            'operator'    => 'operator.dashboard',
                                            'kepsek'      => 'kepsek.dashboard',
                                        ];
                                    @endphp
                                    <a href="{{ route($dashboardRoutes[$role] ?? 'login') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Ubah Password</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container-fluid">
                    @include('shared._ubah_password_form')
                </div>
            </div>
        </div>
    @endif

@endif

@endsection


@push('js')
<script>
(function () {
    document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetName = this.getAttribute('data-target');
            var input = document.querySelector('input[name="' + targetName + '"]');
            var icon  = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
})();
</script>
@endpush

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetName = this.getAttribute('data-target');
            var input = document.querySelector('input[name="' + targetName + '"]');
            var icon  = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
})();
</script>
@endpush
