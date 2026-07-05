@extends('layouts.guru')

@section('title', 'Tentang Saya | GARA Guru')

@php $active_menu = 'profil'; @endphp

@push('styles')
<style>
    .profile-hero {
        background: linear-gradient(135deg, #0050CB 0%, #0b57d0 60%, #1a73e8 100%);
        padding: 40px 30px 80px;
        position: relative;
        overflow: hidden;
    }
    .profile-hero::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .profile-hero::after {
        content: '';
        position: absolute;
        bottom: -30px; left: -30px;
        width: 150px; height: 150px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .profile-avatar-wrap {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .profile-avatar-wrap:hover .avatar-overlay { opacity: 1; }
    .profile-avatar {
        width: 110px; height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,0.9);
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }
    .avatar-overlay {
        position: absolute; inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity .25s;
        color: #fff; font-size: 1.4rem;
    }
    .profile-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        margin-bottom: 20px;
    }
    .profile-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f2f5;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #6c757d;
        padding: 16px 20px 12px;
    }
    .info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
        font-size: 0.93rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6c757d; }
    .info-val { font-weight: 600; color: #212529; }
    .form-control-profile {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        font-size: 0.92rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control-profile:focus {
        border-color: #0b57d0;
        box-shadow: 0 0 0 3px rgba(11,87,208,0.12);
    }
    .btn-profile-primary {
        background: linear-gradient(135deg,#0b57d0,#1a73e8);
        color: #fff; border: none;
        border-radius: 10px; padding: 10px 28px;
        font-weight: 600; font-size: 0.92rem;
        transition: opacity .2s;
    }
    .btn-profile-primary:hover { opacity: .88; color: #fff; }
    .float-card {
        margin-top: -55px;
        position: relative;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    
    <div class="profile-hero text-white text-center">
        <form id="formFoto" action="{{ route('guru.profile.photo') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="profile-avatar-wrap mb-3" onclick="document.getElementById('inputFoto').click()">
                <img id="previewAvatar" src="{{ auth()->user()->profile_photo_url }}" class="profile-avatar" alt="Foto Profil">
                <div class="avatar-overlay">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <input type="file" id="inputFoto" name="photo" accept="image/jpg,image/jpeg,image/png" class="d-none">
        </form>
        <h4 class="font-weight-bold mb-1">{{ auth()->user()->nama_lengkap }}</h4>
        <span class="badge badge-light" style="font-size:.8rem; border-radius:20px; padding:4px 14px; opacity:.9;">
            <i class="fas fa-chalkboard-teacher mr-1"></i> Guru
        </span>
    </div>

    <div class="container float-card px-3" style="max-width:660px;">

        
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-3">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        
        <div class="card profile-card">
            <div class="card-header"><i class="fas fa-user-circle mr-2 text-primary"></i>Informasi Akun</div>
            <div class="card-body px-4">
                <div class="info-row">
                    <span class="info-label">Nama Lengkap</span>
                    <span class="info-val">{{ auth()->user()->nama_lengkap }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Username</span>
                    <span class="info-val text-muted">{{ auth()->user()->username }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role</span>
                    <span class="info-val"><span class="badge badge-primary" style="border-radius:20px;">Guru</span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Akun</span>
                    <span class="info-val text-success"><i class="fas fa-circle" style="font-size:.5rem; vertical-align:middle;"></i> Aktif</span>
                </div>
            </div>
        </div>

        
        <div class="card profile-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <img src="{{ asset('img/image.png') }}" width="16" class="mr-2" style="vertical-align: middle; margin-top: -3px;">
                    Integrasi Google Workspace
                </span>
            </div>
            <div class="card-body px-4">
                <p class="text-muted small mb-3">Hubungkan akun Google Anda untuk mengakses Drive, Docs, dan YouTube langsung dari dalam Gara.</p>
                
                @if($googleConnected)
                    <div class="d-flex align-items-center mb-3 p-3 rounded" style="background:#f1f8e9; border:1px solid #c5e1a5;">
                        <img src="{{ asset('img/image.png') }}" width="32" class="mr-3">
                        <div style="flex:1;">
                            <h6 class="mb-0 font-weight-bold text-success">Terhubung</h6>
                            <span class="small text-muted">{{ $googleEmail ?? 'Akun Google Anda' }}</span>
                        </div>
                    </div>
                    <form action="{{ route('guru.google.disconnect') }}" method="POST" onsubmit="return confirm('Yakin ingin memutus koneksi Google? Anda harus login ulang untuk menggunakan Drive/YouTube di LMS.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100" style="border-radius:10px;">
                            <i class="fas fa-unlink mr-2"></i>Putuskan Koneksi
                        </button>
                    </form>
                @else
                    <div class="d-flex align-items-center mb-3 p-3 rounded bg-light border">
                        <img src="{{ asset('img/image.png') }}" width="32" class="mr-3">
                        <div style="flex:1;">
                            <h6 class="mb-0 font-weight-bold text-secondary">Belum Terhubung</h6>
                            <span class="small text-muted">Akses Google Drive & Docs terbatas sebelum terhubung</span>
                        </div>
                    </div>
                    <a href="{{ route('guru.google.connect') }}" class="btn w-100" style="background:#fff; border:1px solid #dadce0; border-radius:10px; color:#3c4043; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; font-size:0.92rem;">
                        <img src="{{ asset('img/wokspace.png') }}" width="20" class="mr-2"> Hubungkan Google Drive & Workspace
                    </a>
                @endif
            </div>
        </div>



    </div>
</div>
@endsection

@push('scripts')
<script>
    
    document.getElementById('inputFoto').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            
            const reader = new FileReader();
            reader.onload = e => document.getElementById('previewAvatar').src = e.target.result;
            reader.readAsDataURL(file);

            
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            
            document.getElementById('previewAvatar').style.opacity = '0.5';

            
            fetch('{{ route('guru.profile.photo') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('previewAvatar').style.opacity = '1';
                if(data.success) {
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Gagal mengunggah foto.'
                    });
                }
            })
            .catch(error => {
                document.getElementById('previewAvatar').style.opacity = '1';
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan saat mengunggah foto.'
                });
            });
        }
    });


</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
