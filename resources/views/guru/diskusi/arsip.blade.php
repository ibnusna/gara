@extends('layouts.guru')

@section('title', 'Arsip Diskusi | ' . htmlspecialchars($namaMapel))

@php
    $active_menu = 'diskusi';

    if (!function_exists('getConsistentAvatar')) {
        function getConsistentAvatar($name, $uploadedPhoto = null) {
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
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/guru_diskusi.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
        body.guru-workspace, 
        body.guru-workspace .content-wrapper.gd-content-wrapper {
            background-color: #ffffff !important;
            background: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper gd-content-wrapper">
        <div class="gd-app-container">

            
            <div class="gd-header">
                <div>
                    <h1>Arsip Diskusi</h1>
                    <span>Diskusi yang disembunyikan dari siswa</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('guru.diskusi') }}" class="btn btn-sm btn-light rounded-pill" style="border:1px solid #eff3f4; font-weight:600; color:#0f1419;">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            @if(session('success'))
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{!! session('success') !!}", showConfirmButton: false, timer: 3000 });
                    });
                </script>
            @endif

            
            <div id="gd-feed-container">
                @if($archives->isEmpty())
                    <div class="gd-empty">
                        <i class="fas fa-box-open fa-3x mb-3" style="opacity:0.2;"></i>
                        <h5>Tidak ada arsip.</h5>
                        <p>Diskusi yang diarsipkan akan muncul di sini.</p>
                    </div>
                @else
                    @foreach($archives as $t)
                        @php
                            $isGuru   = ($t->role_pembuat === 'guru');
                            $rawNama  = $t->nama_siswa ?? 'Siswa Tidak Dikenal';
                            $nisSiswa = $t->nis_siswa ?? '000000';
                            $nama     = $isGuru ? (auth()->user()->nama_lengkap ?? 'Guru Pengajar') : htmlspecialchars($rawNama);
                            $username = $isGuru ? '@guru' : '@' . htmlspecialchars($nisSiswa);
                            $avatar   = $isGuru
                                        ? getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo)
                                        : getConsistentAvatar($rawNama, $t->user_photo);
                        @endphp

                        <div class="gd-feed-item" data-id="{{ $t->id }}" style="opacity: 0.75;">

                            
                            <img src="{{ $avatar }}" class="gd-avatar" style="filter:grayscale(100%);" alt="Avatar">

                            
                            <div class="gd-post-body">

                                
                                <div class="gd-post-header">
                                    <div class="gd-user-meta">
                                        <span class="gd-name">{{ $nama }}</span>
                                        @if($isGuru)
                                            <span class="gd-verified"><i class="fas fa-check-circle"></i></span>
                                        @endif
                                        <span class="gd-username">{{ $username }}</span>
                                        <span class="gd-time">· {{ \App\Http\Controllers\Guru\DiskusiController::formatWaktuRelatif($t->created_at) }}</span>
                                        <span class="badge badge-secondary" style="font-size:0.62rem; align-self:center;">ARSIP</span>
                                    </div>
                                    <div class="dropdown" style="position:relative;">
                                        <button class="gd-menu-btn" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right gd-dropdown-menu">
                                            <form action="{{ route('guru.diskusi.toggle_status', $t->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="current_status" value="draft">
                                                <button class="dropdown-item" type="submit">
                                                    <i class="fas fa-history mr-2 text-success"></i>Pulihkan
                                                </button>
                                            </form>
                                            <form action="{{ route('guru.diskusi.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus permanen? Tindakan ini tidak bisa dibatalkan.');">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit">
                                                    <i class="fas fa-trash mr-2"></i>Hapus Permanen
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="gd-post-text text-muted">{!! nl2br(htmlspecialchars(trim($t->isi_konten))) !!}</div>

                                
                                <div class="gd-post-actions" style="margin-top:10px;">
                                    <span class="gd-action-btn" style="cursor:default;">
                                        <span class="gd-action-icon"><i class="far fa-comment"></i></span>
                                        <span>{{ $t->jumlah_balasan }} Balasan tersimpan</span>
                                    </span>
                                </div>

                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div style="height:80px;"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush