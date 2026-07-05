
@php
    if(!isset($guru_nama) || !isset($avatar_guru)) {
        $kelas_id = session('kelas_id');
        $mapel_id = session('mapel_id');
        
        if($kelas_id && $mapel_id) {
            $guruInfo = \Illuminate\Support\Facades\DB::connection('mysql_apps')->selectOne("
                SELECT u.id as user_id, g.nama_lengkap, u.profile_photo 
                FROM teaching_assignments ta 
                JOIN " . config('database.connections.mysql_auth.database') . ".guru g ON ta.teacher_id = g.id 
                JOIN " . config('database.connections.mysql_auth.database') . ".users u ON g.user_id = u.id 
                WHERE ta.class_id = ? AND ta.subject_id = ?
            ", [$kelas_id, $mapel_id]);
            
            $guru_nama = $guruInfo ? $guruInfo->nama_lengkap : 'Guru Pengajar';
            if (!function_exists('getConsistentAvatarRight')) {
                function getConsistentAvatarRight($name, $uploadedPhoto = null) {
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
            $avatar_guru = getConsistentAvatarRight($guru_nama, $guruInfo ? $guruInfo->profile_photo : null);
        }
    }
@endphp
<aside class="tw-sidebar-right custom-scrollbar">
    <div class="tw-search-bar">
        <div class="tw-search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" class="tw-search-input" placeholder="Cari di diskusi...">
        </div>
    </div>

    
    @if(isset($guru_nama) && isset($avatar_guru))
    <div class="tw-widget">
        <div class="tw-widget-title">Guru Pengajar</div>
        <div class="tw-widget-item" style="display: flex; align-items: center; gap: 12px; cursor: default;">
            <img src="{{ $avatar_guru }}" class="avatar" alt="Guru">
            <div style="flex: 1; overflow: hidden;">
                <div class="tw-user-name" style="display: flex; align-items: center; gap: 4px;">
                    {{ $guru_nama }}
                    <i class="fas fa-check-circle" style="color: var(--color-primary); font-size: 14px;"></i>
                </div>
                <div class="tw-user-handle">Pengajar Utama</div>
            </div>
        </div>
    </div>
    @endif

    
    <div class="tw-widget" id="desktop-pengumuman-widget" style="display: none;">
        <div class="tw-widget-title">Pengumuman Mapel</div>
        <div class="tw-widget-item" style="cursor: default;">
            <div class="tw-widget-meta" style="margin-bottom: 4px;">
                <span style="color: var(--color-primary); font-weight: 600;" id="pengumuman-judul"></span>
            </div>
            <div class="tw-widget-content" style="font-size: 14px; font-weight: 400; color: var(--text-secondary);" id="pengumuman-isi">
            </div>
        </div>
    </div>
    
    <div style="font-size: 13px; color: var(--text-secondary); padding: 0 16px; margin-top: 16px;">
        <p>&copy; {{ date('Y') }} Garuda Akademi. All rights reserved.</p>
    </div>
</aside>

<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth >= 768 && typeof $ !== 'undefined') {
            $.ajax({
                url: '{{ route('api.student.pengumuman') }}',
                method: 'GET',
                success: function(response) {
                    if (response.found && response.data) {
                        $('#pengumuman-judul').text(response.data.judul);
                        $('#pengumuman-isi').text(response.data.isi);
                        $('#desktop-pengumuman-widget').show();
                    }
                },
                error: function(err) {
                    console.error('Gagal mengambil pengumuman', err);
                }
            });
            
            
            $('.tw-search-input').on('input', function() {
                var query = $(this).val().toLowerCase();
                if(query === '') {
                    $('.feed-item').show();
                } else {
                    $('.feed-item').each(function() {
                        var content = $(this).find('.post-text').text().toLowerCase();
                        var name = $(this).find('.name').text().toLowerCase();
                        var username = $(this).find('.username').text().toLowerCase();
                        
                        if(content.indexOf(query) > -1 || name.indexOf(query) > -1 || username.indexOf(query) > -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });
        }
    });
</script>
