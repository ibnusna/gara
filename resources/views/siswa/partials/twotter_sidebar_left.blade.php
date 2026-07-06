@php
    $active = $active ?? 'beranda';
    $user = auth()->user();
    
    if(!isset($siswa_nama) || !isset($siswa_nis) || !isset($avatar_siswa)) {
        $siswa = \Illuminate\Support\Facades\DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();
        $siswa_nama = $siswa_nama ?? ($siswa ? $siswa->nama : ($user->nama_lengkap ?? session('nama')));
        $siswa_nis = $siswa_nis ?? ($siswa && $siswa->nis ? $siswa->nis : (session('nis') ?? '-'));
        
        if (!function_exists('getConsistentAvatarLocal')) {
            function getConsistentAvatarLocal($name, $uploadedPhoto = null) {
                if ($uploadedPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists('profile_photos/' . $uploadedPhoto)) {
                    return asset('storage/profile_photos/' . $uploadedPhoto) . '?v=' . time();
                }
                $colors = [
                    '1abc9c', '2ecc71', '3498db', '9b59b6', '34495e',
                    '16a085', '27ae60', '2980b9', '8e44ad', '2c3e50',
                    'f1c40f', 'e67e22', 'e74c3c', '95a5a6', 'f39c12',
                    'd35400', 'c0392b', '7f8c8d', '5c6bc0', 'ec407a'
                ];
                $hash = 0;
                $len = strlen($name);
                for ($i = 0; $i < $len; $i++) {
                    $hash = ($hash + ord($name[$i])) % 100000;
                }
                $index = $hash % count($colors);
                $bg = $colors[$index];
                return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=" . $bg . "&color=fff&size=128&bold=true";
            }
        }
        $avatar_siswa = $avatar_siswa ?? getConsistentAvatarLocal($siswa_nama, $user->profile_photo);
    }
@endphp


<aside class="tw-sidebar-left">
    <a href="{{ route('student.dashboard') }}" class="brand" hx-boost="false">
        <img src="{{ \App\Models\AppSetting::getLogo('FARA_BLACK.svg', true) }}" alt="GARA Logo" style="height: 32px;">
        GARA
    </a>
    
    <nav class="tw-nav-menu">
        <a href="{{ route('student.dashboard') }}" class="tw-nav-item {{ $active == 'beranda' ? 'active' : '' }}" hx-boost="false">
            <i class="fas fa-home"></i> Beranda
        </a>
        <a href="{{ route('student.diskusi.index') }}" class="tw-nav-item {{ $active == 'diskusi' ? 'active' : '' }}" hx-boost="false">
            <i class="fas fa-comments"></i> Diskusi
        </a>
        <a href="{{ route('student.notifikasi.index') }}" class="tw-nav-item {{ $active == 'notifikasi' ? 'active' : '' }}" hx-boost="false">
            <i class="far fa-bell"></i> Notifikasi
        </a>
        <a href="{{ route('student.tentang-saya.index') }}" class="tw-nav-item {{ $active == 'profil' ? 'active' : '' }}" hx-boost="false">
            <i class="far fa-user"></i> Profil
        </a>
    </nav>

    <button class="tw-btn-post-large" onclick="if(document.getElementById('trigger-post-modal')) { document.getElementById('trigger-post-modal').click(); } else { window.location.href='{{ route('student.diskusi.index') }}'; }">
        Posting
    </button>

    <div class="tw-user-menu" onclick="window.location.href='{{ route('student.tentang-saya.index') }}'">
        <img src="{{ $avatar_siswa }}" class="avatar" alt="Avatar">
        <div class="tw-user-info">
            <span class="tw-user-name">{{ $siswa_nama }}</span>
            <span class="tw-user-handle">{{ $siswa_nis }}</span>
        </div>
        <i class="fas fa-ellipsis-h" style="color: var(--text-secondary); margin-left: auto;"></i>
    </div>
</aside>
