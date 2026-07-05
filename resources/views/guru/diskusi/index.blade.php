@extends('layouts.guru')

@section('title', 'Diskusi Kelas | ' . htmlspecialchars($namaMapel))

@php
    $active_menu = 'diskusi';

    if (!function_exists('getConsistentAvatar')) {
        function getConsistentAvatar($name, $uploadedPhoto = null)
        {
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

@php
    
    $sidebarKelasId = session('kelas_id');
    $sidebarMapelId = session('mapel_id');
    $authDb = config('database.connections.mysql_auth.database');
    $appsDb = config('database.connections.mysql_apps.database');

    $totalSiswa = \Illuminate\Support\Facades\DB::connection('mysql_auth')
        ->table('siswa')->where('kelas_id', $sidebarKelasId)->count();

    $totalThread = \Illuminate\Support\Facades\DB::connection('mysql_apps')
        ->table('diskusi_threads')
        ->where('mapel_id', $sidebarMapelId)->where('kelas_id', $sidebarKelasId)
        ->where('status', 'aktif')->count();

    $totalReply = \Illuminate\Support\Facades\DB::connection('mysql_apps')
        ->table('diskusi_replies as r')
        ->join($appsDb . '.diskusi_threads as t', 'r.thread_id', '=', 't.id')
        ->where('t.mapel_id', $sidebarMapelId)->where('t.kelas_id', $sidebarKelasId)
        ->count();

    $namaKelas = \Illuminate\Support\Facades\DB::connection('mysql_auth')
        ->table('kelas')->where('id', $sidebarKelasId)->value('nama_kelas');
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
        <div class="gd-page-layout">
        <div class="gd-app-container">

            
            <div class="gd-header">
                <div>
                    <h1>Diskusi Kelas</h1>
                    <span>{{ htmlspecialchars($namaMapel) }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('guru.diskusi.arsip') }}" class="btn btn-sm btn-light rounded-pill" style="border:1px solid #eff3f4; font-weight:600; color:#0f1419;">Arsip</a>
                    <button class="btn btn-sm btn-primary rounded-pill" style="font-weight:700;" data-toggle="modal" data-target="#modalPostAdmin">
                        <i class="fas fa-plus mr-1"></i> Topik
                    </button>
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
                @if($threads->isEmpty())
                    <div class="gd-empty">
                        <h5>Belum ada diskusi aktif.</h5>
                        <p>Buat topik baru untuk memulai interaksi kelas.</p>
                    </div>
                @else
                    @foreach($threads as $t)
                        @php
                            $isGuru   = ($t->role_pembuat === 'guru');
                            $rawNama  = $t->nama_siswa ?? 'Siswa Tidak Dikenal';
                            $nisSiswa = $t->nis_siswa ?? '000000';
                            $nama     = $isGuru ? (auth()->user()->nama_lengkap ?? 'Guru Pengajar') : htmlspecialchars($rawNama);
                            $username = $isGuru ? '@guru' : '@' . htmlspecialchars($nisSiswa);
                            $avatar   = $isGuru
                                        ? getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo)
                                        : getConsistentAvatar($rawNama, $t->user_photo);
                            $isPinned    = ($t->is_pinned == 1);
                            $replyCount  = count($t->replies);
                        @endphp

                        <div class="gd-feed-item" data-id="{{ $t->id }}">

                            
                            <img src="{{ $avatar }}" class="gd-avatar" alt="Avatar">

                            
                            <div class="gd-post-body">

                                
                                @if($isPinned)
                                    <div class="gd-pinned-badge"><i class="fas fa-thumbtack fa-xs"></i> Disematkan</div>
                                @endif

                                
                                <div class="gd-post-header">
                                    <div class="gd-user-meta">
                                        <span class="gd-name">{{ $nama }}</span>
                                        @if($isGuru)
                                            <span class="gd-verified"><i class="fas fa-check-circle"></i></span>
                                        @endif
                                        <span class="gd-username">{{ $username }}</span>
                                        <span class="gd-time">· {{ \App\Http\Controllers\Guru\DiskusiController::formatWaktuRelatif($t->created_at) }}</span>
                                    </div>
                                    <div class="dropdown" style="position:relative;">
                                        <button class="gd-menu-btn" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right gd-dropdown-menu">
                                            <form action="{{ route('guru.diskusi.toggle_pin', $t->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="current_pin" value="{{ $t->is_pinned }}">
                                                <button class="dropdown-item" type="submit">
                                                    <i class="fas fa-thumbtack mr-2 text-warning"></i>{{ $isPinned ? 'Lepas Pin' : 'Sematkan' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('guru.diskusi.toggle_status', $t->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="current_status" value="aktif">
                                                <button class="dropdown-item" type="submit">
                                                    <i class="fas fa-archive mr-2 text-primary"></i>Arsipkan
                                                </button>
                                            </form>
                                            @if($isGuru)
                                                <button class="dropdown-item text-info" onclick="openEditModal({{ $t->id }}, `{{ addslashes($t->isi_konten) }}`, `{{ $t->media_path ? (Str::startsWith($t->media_path, 'http') ? $t->media_path : asset('lms/' . $t->media_path)) : '' }}`)">
                                                    <i class="fas fa-edit mr-2"></i>Edit
                                                </button>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('guru.diskusi.destroy', $t->id) }}" method="POST" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger btn-delete">
                                                    <i class="fas fa-trash mr-2"></i>Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="gd-post-text">{!! nl2br(htmlspecialchars(trim($t->isi_konten))) !!}</div>

                                
                                @if($t->media_path)
                                    <div class="gd-post-media">
                                        @if(Str::startsWith($t->media_path, 'http'))
                                            <img src="{{ $t->media_path }}" onclick="openFullscreenImage(this.src);" alt="media">
                                        @else
                                            <img src="{{ asset('lms/' . $t->media_path) }}" onclick="openFullscreenImage(this.src);" alt="media">
                                        @endif
                                    </div>
                                @endif

                                
                                <div class="gd-post-actions">
                                    <button class="gd-action-btn" onclick="toggleComments({{ $t->id }})">
                                        <span class="gd-action-icon"><i class="far fa-comment"></i></span>
                                        <span>{{ $replyCount }}</span>
                                    </button>
                                </div>

                                
                                <div id="gd-comments-{{ $t->id }}" class="gd-replies-section" style="display:none;">
                                    @if($replyCount === 0)
                                        <p style="color:#536471; font-size:14px; text-align:center; margin:10px 0;">Belum ada balasan.</p>
                                    @else
                                        @foreach($t->replies as $r)
                                            @php
                                                $isGuruR    = ($r->role_pembuat === 'guru');
                                                $rawNamaR   = $r->nama_siswa ?? 'Siswa Tidak Dikenal';
                                                $nisR       = $r->nis_siswa ?? '000000';
                                                $rNama      = $isGuruR ? (auth()->user()->nama_lengkap ?? 'Guru Pengajar') : htmlspecialchars($rawNamaR);
                                                $rUsername  = $isGuruR ? '@guru' : '@' . htmlspecialchars($nisR);
                                                $rAvatar    = $isGuruR
                                                              ? getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo)
                                                              : getConsistentAvatar($rawNamaR, $r->user_photo);
                                            @endphp
                                            <div class="gd-reply-item">
                                                <img src="{{ $rAvatar }}" class="gd-reply-avatar" alt="avatar">
                                                <div class="gd-reply-body">
                                                    <div class="gd-reply-meta">
                                                        <span class="gd-reply-name">{{ $rNama }}</span>
                                                        @if($isGuruR)<span class="gd-verified" style="font-size:12px;"><i class="fas fa-check-circle"></i></span>@endif
                                                        <span class="gd-reply-handle">{{ $rUsername }}</span>
                                                        <span class="gd-reply-time">· {{ \App\Http\Controllers\Guru\DiskusiController::formatWaktuRelatif($r->created_at) }}</span>
                                                        <form action="{{ route('guru.diskusi.destroy_reply', $r->id) }}" method="POST" class="delete-form d-inline float-right ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="btn btn-link text-danger p-0 m-0 btn-delete" style="font-size:12px;" title="Hapus Balasan">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="gd-reply-text">{!! nl2br(htmlspecialchars(trim($r->isi_balasan))) !!}</div>
                                                    @if($r->media_path)
                                                        <div class="gd-reply-media">
                                                            @if(Str::startsWith($r->media_path, 'http'))
                                                                <img src="{{ $r->media_path }}" onclick="openFullscreenImage(this.src);" alt="media">
                                                            @else
                                                                <img src="{{ asset('lms/' . $r->media_path) }}" onclick="openFullscreenImage(this.src);" alt="media">
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    
                                    <form action="{{ route('guru.diskusi.reply', $t->id) }}" method="POST" class="gd-reply-form">
                                        @csrf
                                        <img src="{{ getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo) }}" class="gd-reply-form-avatar" alt="me">
                                        <input type="text" name="isi_balasan" class="gd-reply-input" placeholder="Balas postingan ini..." required>
                                        <button type="submit" class="gd-reply-submit">Balas</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div style="height:80px;"></div>
        </div>

        
        <aside class="gd-sidebar-right">

            
            <div class="gd-sb-search">
                <div class="gd-sb-search-inner">
                    <i class="fas fa-search"></i>
                    <input type="text" id="gd-search-input" placeholder="Cari diskusi..." autocomplete="off">
                </div>
            </div>

            
            <div class="gd-widget">
                <div class="gd-widget-title">Ruang Diskusi</div>
                <div class="gd-widget-body">
                    <div class="gd-stat-grid">
                        <div class="gd-stat-item">
                            <span class="gd-stat-num">{{ $totalSiswa }}</span>
                            <span class="gd-stat-label">Siswa</span>
                        </div>
                        <div class="gd-stat-item">
                            <span class="gd-stat-num">{{ $totalThread }}</span>
                            <span class="gd-stat-label">Topik</span>
                        </div>
                        <div class="gd-stat-item">
                            <span class="gd-stat-num">{{ $totalReply }}</span>
                            <span class="gd-stat-label">Balasan</span>
                        </div>
                    </div>
                </div>
                <div class="gd-widget-footer">
                    <i class="fas fa-school mr-1"></i>
                    Kelas {{ $namaKelas ?? '-' }} &mdash; {{ htmlspecialchars($namaMapel) }}
                </div>
            </div>

            
            <div class="gd-widget">
                <div class="gd-widget-title">Aksi Cepat</div>
                <div class="gd-widget-body" style="padding:8px 0;">
                    <button class="gd-quick-btn" data-toggle="modal" data-target="#modalPostAdmin">
                        <i class="fas fa-plus-circle"></i> Buat Topik Baru
                    </button>
                    <a href="{{ route('guru.diskusi.arsip') }}" class="gd-quick-btn">
                        <i class="fas fa-archive"></i> Lihat Arsip
                    </a>
                </div>
            </div>

            <div class="gd-sb-footer">&copy; {{ date('Y') }} Garuda Akademi</div>
        </aside>

        </div>
    </div>

    
    <div class="modal fade" id="modalEditAdmin" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <button type="button" class="close" data-dismiss="modal"><i class="fas fa-times text-dark"></i></button>
                </div>
                <div class="modal-body pt-0 px-3 pb-3">
                    <form id="formEditAdmin" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="d-flex">
                            <div class="mr-3">
                                <img src="{{ getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo) }}" class="rounded-circle" width="44" height="44" style="object-fit:cover;">
                            </div>
                            <div style="flex:1;">
                                <textarea name="isi_konten" id="edit_isi_konten" class="form-control border-0 p-0" rows="4" style="resize:none; font-size:1.05rem; box-shadow:none; background:transparent;" required></textarea>

                                <div id="editPreviewContainer" class="gd-image-preview-wrap" style="display:none;">
                                    <button type="button" class="gd-btn-remove-image" id="btnEditRemoveImage"><i class="fas fa-times"></i></button>
                                    <img id="editImagePreview" src="" alt="Preview">
                                </div>
                                <input type="hidden" name="google_image_url" id="edit_google_image_url">
                                <input type="hidden" name="remove_media" id="edit_remove_media" value="0">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3">
                            <div class="d-flex align-items-center">
                                <label class="btn text-primary rounded-circle mb-0 p-2" style="cursor:pointer;" title="Upload Lokal">
                                    <i class="far fa-image fa-lg"></i>
                                    <input type="file" id="editFileInput" name="media" accept="image/*" hidden>
                                </label>
                                @php
                                    $hasGoogle = \App\Integrations\Google\GoogleOAuthService::class;
                                    $isGoogleConnected = false;
                                    if(class_exists($hasGoogle)) {
                                        $gService = new \App\Integrations\Google\GoogleOAuthService();
                                        $isGoogleConnected = !is_null($gService->getConnectedStatus(auth()->id()));
                                    }
                                @endphp
                                <button type="button" class="btn text-primary rounded-circle mb-0 mr-2 p-2"
                                    onclick="openDrivePicker('images', true)"
                                    @if(!$isGoogleConnected) disabled title="Hubungkan akun Google di Profil terlebih dahulu" @endif>
                                    <i class="fab fa-google-drive fa-lg"></i>
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalPostAdmin" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <button type="button" class="close" data-dismiss="modal"><i class="fas fa-times text-dark"></i></button>
                </div>
                <div class="modal-body pt-0 px-3 pb-3">
                    <form action="{{ route('guru.diskusi.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex">
                            <div class="mr-3">
                                <img src="{{ getConsistentAvatar(auth()->user()->nama_lengkap ?? 'Guru Pengajar', auth()->user()->profile_photo) }}" class="rounded-circle" width="44" height="44" style="object-fit:cover;">
                            </div>
                            <div style="flex:1;">
                                <textarea name="isi_konten" class="form-control border-0 p-0" rows="4"
                                    placeholder="Apa yang ingin didiskusikan hari ini?"
                                    style="resize:none; font-size:1.05rem; box-shadow:none; background:transparent;" required></textarea>

                                <div id="previewContainer" class="gd-image-preview-wrap">
                                    <button type="button" class="gd-btn-remove-image" id="btnRemoveImage"><i class="fas fa-times"></i></button>
                                    <img id="imagePreview" src="" alt="Preview">
                                </div>
                                <input type="hidden" name="google_image_url" id="google_image_url">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3">
                            <div class="d-flex align-items-center">
                                <label class="btn text-primary rounded-circle mb-0 p-2" style="cursor:pointer;" title="Upload Lokal">
                                    <i class="far fa-image fa-lg"></i>
                                    <input type="file" id="fileInput" name="media" accept="image/*" hidden>
                                </label>
                                @php
                                    $hasGoogle = \App\Integrations\Google\GoogleOAuthService::class;
                                    $isGoogleConnected = false;
                                    if(class_exists($hasGoogle)) {
                                        $gService = new \App\Integrations\Google\GoogleOAuthService();
                                        $isGoogleConnected = !is_null($gService->getConnectedStatus(auth()->id()));
                                    }
                                @endphp
                                <button type="button" class="btn text-primary rounded-circle mb-0 mr-2 p-2"
                                    onclick="openDrivePicker('images')"
                                    @if(!$isGoogleConnected) disabled title="Hubungkan akun Google di Profil terlebih dahulu" @endif>
                                    <i class="fab fa-google-drive fa-lg"></i>
                                </button>
                                <div class="custom-control custom-switch d-inline-block">
                                    <input type="checkbox" class="custom-control-input" id="pinSwitch" name="is_pinned" value="1">
                                    <label class="custom-control-label small pt-1 text-secondary" for="pinSwitch">Pin</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Posting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openEditModal(id, isi, mediaPath) {
            $('#formEditAdmin').attr('action', '/guru/diskusi/' + id);
            $('#edit_isi_konten').val(isi);
            $('#editFileInput').val('');
            $('#edit_google_image_url').val('');
            $('#edit_remove_media').val('0');
            
            if (mediaPath && mediaPath !== '') {
                $('#editImagePreview').attr('src', mediaPath);
                $('#editPreviewContainer').show();
            } else {
                $('#editPreviewContainer').hide();
                $('#editImagePreview').attr('src', '');
            }

            $('#modalEditAdmin').modal('show');
        }

        function toggleComments(id) {
            $('#gd-comments-' + id).slideToggle('fast');
        }

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Hapus Diskusi?',
                text: "Topik ini beserta seluruh balasannya akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        $('form').not('.delete-form').on('submit', function() {
            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Proses...');
        });

        document.getElementById('fileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('btnRemoveImage').addEventListener('click', function() {
            document.getElementById('fileInput').value = '';
            document.getElementById('google_image_url').value = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('imagePreview').src = '';
        });

        document.getElementById('editFileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editImagePreview').src = e.target.result;
                    document.getElementById('editPreviewContainer').style.display = 'block';
                    document.getElementById('edit_remove_media').value = '0';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('btnEditRemoveImage').addEventListener('click', function() {
            document.getElementById('editFileInput').value = '';
            document.getElementById('edit_google_image_url').value = '';
            document.getElementById('editPreviewContainer').style.display = 'none';
            document.getElementById('editImagePreview').src = '';
            document.getElementById('edit_remove_media').value = '1';
        });

        function openFullscreenImage(src) {
            if ($('#gara-fullscreen-modal').length === 0) {
                $('body').append(`
                    <div id="gara-fullscreen-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;background:rgba(0,0,0,0.75);backdrop-filter:blur(10px);align-items:center;justify-content:center;cursor:pointer;">
                        <button style="position:absolute;top:20px;right:20px;background:rgba(0,0,0,0.5);border:none;color:white;width:44px;height:44px;border-radius:50%;font-size:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:1000000;">
                            <i class="fas fa-times"></i>
                        </button>
                        <img id="gara-fullscreen-image" src="" style="max-width:95vw;max-height:95vh;object-fit:contain;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.5);transition:transform 0.3s cubic-bezier(0.175,0.885,0.32,1.275);">
                    </div>
                `);
                $('#gara-fullscreen-modal').click(function() { $(this).fadeOut(200); });
            }
            $('#gara-fullscreen-image').attr('src', src).css('transform', 'scale(0.9)');
            $('#gara-fullscreen-modal').css('display', 'flex').hide().fadeIn(200, function() {
                $('#gara-fullscreen-image').css('transform', 'scale(1)');
            });
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('gd-search-input');
            if (!searchInput) return;
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.gd-feed-item').forEach(function(item) {
                    if (!q) { item.style.display = ''; return; }
                    const name     = (item.querySelector('.gd-name')?.textContent || '').toLowerCase();
                    const username = (item.querySelector('.gd-username')?.textContent || '').toLowerCase();
                    const text     = (item.querySelector('.gd-post-text')?.textContent || '').toLowerCase();
                    item.style.display = (name.includes(q) || username.includes(q) || text.includes(q)) ? '' : 'none';
                });
            });
        });
    </script>
    <script src="{{ asset('assets/js/gara-picker.js') }}?v={{ time() }}"></script>
    <script>
        let pickerImages = null;
        let isEditMode = false;
        function openDrivePicker(mode, isEdit = false) {
            isEditMode = isEdit;
            if (!pickerImages) {
                pickerImages = new GaraPicker({
                    developerKey: '{{ config('services.google.developer_key') }}',
                    appId: '{{ config('services.google.app_id') }}',
                    tokenUrl: '{{ route('guru.google.picker_token') }}',
                    mode: mode,
                    onSelect: function(data) {
                        Swal.fire({ title: 'Mengunduh Gambar...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                        const formData = new FormData();
                        formData.append('file_id', data.id);
                        formData.append('file_name', data.name);
                        formData.append('_token', '{{ csrf_token() }}');
                        fetch('{{ route('guru.diskusi.download_media') }}', { method: 'POST', body: formData })
                            .then(r => r.json())
                            .then(res => {
                                if (res.status === 'success') {
                                    if (isEditMode) {
                                        document.getElementById('edit_google_image_url').value = res.media_path;
                                        document.getElementById('editImagePreview').src = res.url;
                                        document.getElementById('editPreviewContainer').style.display = 'block';
                                        document.getElementById('edit_remove_media').value = '0';
                                    } else {
                                        document.getElementById('google_image_url').value = res.media_path;
                                        document.getElementById('imagePreview').src = res.url;
                                        document.getElementById('previewContainer').style.display = 'block';
                                    }
                                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Gambar siap!', showConfirmButton: false, timer: 2500 });
                                } else { Swal.fire('Error', res.message, 'error'); }
                            })
                            .catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
                    }
                });
            }
            pickerImages.open();
        }
    </script>
@endpush