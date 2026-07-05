@extends('layouts.siswa')

@section('title', 'Tentang Saya | GARA')

@push('css')
    <style>
        .profile-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 0 4px 60px 4px; /* padding sisi minimal, agar card tidak menempel tepi pada HP kecil */
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 24px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f2f5;
        }

        .siswa-header {
            text-align: center;
            margin-bottom: 20px; /* jarak lebih besar antara header info dan kartu info */
            padding: 12px 0 4px 0;
        }

        .siswa-avatar {
            display: block;
            margin: 0 auto 16px auto;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            object-fit: cover;
        }

        .siswa-name {
            font-weight: 700;
            font-size: 1.15rem;
            color: #212529;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .siswa-nis {
            color: #6c757d;
            font-size: 0.875rem;
            letter-spacing: 1px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #f8f9fa;
            font-size: 0.92rem;
            gap: 8px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            flex-shrink: 0;
        }

        .info-val {
            font-weight: 600;
            color: #212529;
            text-align: right;
        }

        .section-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #adb5bd;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 12px;
            padding-left: 4px;
        }

        
        .guru-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .guru-info {
            flex: 1;
            min-width: 0;
        }

        .guru-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #000;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .guru-verified {
            color: #0095f6;
            margin-left: 4px;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .guru-bio {
            font-size: 0.875rem;
            color: #333;
            margin-top: 6px;
            line-height: 1.5;
        }

        .guru-link {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
            text-decoration: none;
        }

        .guru-link:hover {
            text-decoration: underline;
        }

        .guru-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #efefef;
            margin-left: 15px;
        }

        
        .form-control-custom {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .form-control-custom:focus {
            background: #fff;
            border-color: #0d6efd;
            box-shadow: none;
        }

        .toggle-password {
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            background-color: #e9ecef !important;
        }

        .toggle-password i {
            transition: opacity 0.3s ease;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/diskusi_desktop.css') }}?v={{ time() }}">
@endpush

@section('content')



<div class="tw-layout-wrapper">
    
    @include('siswa.partials.twotter_sidebar_left', ['active' => 'profil'])

    
    <main class="tw-main-feed">
        
        <div class="header-simple px-3 py-3 border-bottom sticky-top glass-card-strong" style="margin: 0 0 20px 0; border-radius: 0;">
            <div class="d-flex align-items-center justify-content-center position-relative">
                <a href="{{ route('student.dashboard') }}" class="text-dark position-absolute start-0" style="left: 0;" hx-boost="false">
                    <i class="fas fa-arrow-left fa-lg"></i>
                </a>
                <h6 class="m-0 fw-bold">Profil Akun</h6>
            </div>
        </div>

        <div class="profile-container px-3">

            
            @if(session('success'))
                <div class="alert alert-success rounded-3 small border-0 shadow-sm mb-3">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-3 small border-0 shadow-sm mb-3">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-3 small border-0 shadow-sm mb-3">
                    <ul class="mb-0 text-start">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            
            <div class="section-title text-start">Informasi Siswa</div>
            <div class="profile-card text-start glass-card ripple" style="border-radius: 24px;">
                <div class="siswa-header">
                    @php
                        $avatarUrl = auth()->user()->profile_photo_url;
                    @endphp
                    <img src="{{ $avatarUrl }}" class="siswa-avatar" alt="Profil">
                    <div class="siswa-name">{{ $nama_siswa }}</div>
                    <div class="siswa-nis">NIS: {{ $nis_siswa }}</div>
                </div>

                <div class="mt-4">
                    <div class="info-row">
                        <span class="info-label">Kelas Saat Ini</span>
                        <span class="info-val text-primary">{{ $nama_kelas }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status Akun</span>
                        <span class="info-val text-success"><i class="fas fa-check-circle small"></i> Aktif</span>
                    </div>
                </div>
            </div>

            
            <div class="section-title text-start mt-4">Keamanan Akun</div>

            
            @if($is_default_password)
                <div class="alert border-0 shadow-sm rounded-3 mb-3 text-start small"
                    style="background:#fff8e1; border-left:4px solid #ffb300 !important; border-radius:12px;">
                    <i class="fas fa-exclamation-circle me-2" style="color:#ffb300;"></i>
                    <strong>Password bawaan terdeteksi.</strong> Segera ubah password Anda untuk keamanan akun.
                </div>
            @endif

            <div class="profile-card text-start glass-card ripple" style="border-radius: 24px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold" style="font-size:0.95rem;">Ubah Password</div>
                        <div class="text-muted small mt-1">
                            @if($is_default_password)
                                Anda menggunakan password bawaan sistem
                            @else
                                Terakhir diubah secara manual
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('student.password.form') }}"
                        class="btn btn-sm btn-primary rounded-pill px-3" hx-boost="false">
                        <i class="fas fa-key me-1"></i> Ubah
                    </a>
                </div>
            </div>

            
            <div class="section-title text-start mt-4">Informasi Sekolah</div>
            <div class="profile-card text-start glass-card ripple" style="border-radius: 24px;">
                <div class="guru-card">
                    <div class="guru-info">
                        <div class="guru-name">
                            {{ $nama_sekolah }} <i class="fas fa-check-circle guru-verified"></i>
                        </div>
                        <div class="guru-bio" style="font-weight: 500; color: #495057;">
                            Tahun Ajaran: <span class="text-primary">{{ $tahun_ajaran }}</span>
                        </div>
                        <div class="guru-bio" style="font-weight: 500; color: #495057;">
                            Semester Saat Ini: <span class="text-primary">{{ $semester }}</span>
                        </div>
                    </div>
                    <div
                        style="width: 60px; height: 60px; border-radius: 12px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; font-size: 1.5rem; margin-left: 15px;">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 mb-5">
                <a href="{{ route('student.pilih-mapel') }}" class="btn btn-primary w-100 py-2 fw-bold mb-3 ripple"
                    hx-boost="false" style="border-radius: 16px;">
                    <i class="fas fa-exchange-alt me-2"></i>Ganti Mapel
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-bold ripple" style="border-radius: 16px;">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </form>
            </div>

        </div>
    </main>

    
    @include('siswa.partials.twotter_sidebar_right')
</div>

@endsection

@push('js')
    <script>
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const input = document.querySelector(`input[name="${targetId}"]`);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
@endpush