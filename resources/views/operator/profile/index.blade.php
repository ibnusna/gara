@extends('layouts.operator')
@section('title', 'Tentang Saya | GARA Operator')
@php $active_menu = 'profil'; @endphp
@push('styles')
<style>
    .profile-hero { background: linear-gradient(135deg,#0050CB 0%,#1a73e8 100%); padding: 40px 30px 80px; position: relative; overflow: hidden; }
    .profile-avatar-wrap { position: relative; display: inline-block; cursor: pointer; }
    .profile-avatar-wrap:hover .avatar-overlay { opacity: 1; }
    .profile-avatar { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.9); box-shadow: 0 8px 24px rgba(0,0,0,0.25); }
    .avatar-overlay { position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .25s; color: #fff; font-size: 1.4rem; }
    .profile-card { border-radius: 16px; border: none; box-shadow: 0 2px 16px rgba(0,0,0,0.07); margin-bottom: 20px; }
    .profile-card .card-header { background: transparent; border-bottom: 1px solid #f0f2f5; font-weight: 700; font-size: 0.85rem; letter-spacing: .5px; text-transform: uppercase; color: #6c757d; padding: 16px 20px 12px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f8f9fa; font-size: 0.93rem; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6c757d; }
    .info-val { font-weight: 600; color: #212529; }
    .form-control-profile { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 14px; font-size: 0.92rem; }
    .form-control-profile:focus { border-color: #0b57d0; box-shadow: 0 0 0 3px rgba(11,87,208,0.12); }
    .btn-profile-primary { background: linear-gradient(135deg,#0b57d0,#1a73e8); color: #fff; border: none; border-radius: 10px; padding: 10px 28px; font-weight: 600; font-size: 0.92rem; }
    .btn-profile-primary:hover { opacity: .88; color: #fff; }
    .float-card { margin-top: -55px; position: relative; z-index: 10; }
</style>
@endpush
@section('content')
<div class="content-wrapper">
    <div class="profile-hero text-white text-center">
        <form id="formFoto" action="{{ route('operator.profile.photo') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="profile-avatar-wrap mb-3" onclick="document.getElementById('inputFoto').click()">
                <img id="previewAvatar" src="{{ auth()->user()->profile_photo_url }}" class="profile-avatar" alt="Foto Profil">
                <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
            </div>
            <input type="file" id="inputFoto" name="photo" accept="image/jpg,image/jpeg,image/png" class="d-none">
        </form>
        <h4 class="font-weight-bold mb-1">{{ auth()->user()->nama_lengkap }}</h4>
        <span class="badge badge-light" style="font-size:.8rem; border-radius:20px; padding:4px 14px;">
            <i class="fas fa-user-shield mr-1"></i> Operator
        </span>
    </div>
    <div class="container float-card px-3" style="max-width:660px;">
        @if(session('success'))<div class="alert alert-success border-0 rounded-3 shadow-sm mb-3"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card profile-card">
            <div class="card-header"><i class="fas fa-user-circle mr-2 text-primary"></i>Informasi Akun</div>
            <div class="card-body px-4">
                <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-val">{{ auth()->user()->nama_lengkap }}</span></div>
                <div class="info-row"><span class="info-label">Username</span><span class="info-val text-muted">{{ auth()->user()->username }}</span></div>
                <div class="info-row"><span class="info-label">Role</span><span class="info-val"><span class="badge badge-info" style="border-radius:20px;">Operator</span></span></div>
                <div class="info-row"><span class="info-label">Status</span><span class="info-val text-success"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;"></i> Aktif</span></div>
            </div>
        </div>


    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const inputFoto = document.getElementById('inputFoto');
    const inputFoto2 = document.getElementById('inputFoto2');
    const previewAvatar = document.getElementById('previewAvatar');

    function uploadPhoto(file) {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', '{{ csrf_token() }}');

        previewAvatar.style.opacity = '0.5';

        fetch('{{ route('operator.profile.photo') }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            previewAvatar.style.opacity = '1';
            if(data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false }).then(() => { window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal mengunggah foto.' });
            }
        })
        .catch(error => {
            previewAvatar.style.opacity = '1';
            Swal.fire({ icon: 'error', title: 'Kesalahan', text: 'Terjadi kesalahan sistem.' });
        });
    }

    inputFoto.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => previewAvatar.src = e.target.result;
            reader.readAsDataURL(this.files[0]);
            uploadPhoto(this.files[0]);
        }
    });


</script>
@endpush
