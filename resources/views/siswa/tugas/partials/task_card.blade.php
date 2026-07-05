@php
    $icon_color = ($type == 'selesai') ? 'green' : (($type == 'terlewat') ? 'red' : 'blue');
    $deadline_ts = $t['batas_waktu'] ? strtotime($t['batas_waktu']) : null;
    $deadline_str = $deadline_ts ? date('d M, H:i', $deadline_ts) : 'Tanpa Batas';
    $is_urgent = ($type == 'aktif' && $deadline_ts && ($deadline_ts - time() < 86400));
@endphp

<div id="card-tugas-{{ $t['id'] }}" class="app-card glass-card {{ $type == 'terlewat' ? 'border-danger-soft bg-danger-soft' : '' }}" style="border-radius: 20px; margin-bottom: 16px;">
    <div class="card-header-flex">
        <div class="icon-box {{ $icon_color }}" id="icon-box-{{ $t['id'] }}">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="card-info">
            <div class="task-title">{{ htmlspecialchars($t['judul']) }}</div>
            
            @if ($type == 'aktif' && $deadline_ts)
                @php $iso_date = date('Y-m-d H:i:s', $deadline_ts); @endphp
                <div class="task-deadline urgent countdown-timer" data-deadline="{{ $iso_date }}">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            @else
                <div class="task-deadline">
                    <i class="far fa-clock"></i> Deadline: {{ $deadline_str }}
                </div>
            @endif
        </div>
    </div>

    @if (!empty($t['deskripsi']))
        <div class="task-desc-preview">{{ \Illuminate\Support\Str::limit(strip_tags($t['deskripsi']), 100) }}</div>
    @endif

    @if ($type == 'selesai' && !empty($t['feedback_guru']))
        <div class="w-100 p-2 mb-3 bg-light small" style="border-radius: 10px; border-left: 3px solid var(--primary-color);">
            <div class="d-flex align-items-center mb-1 text-primary font-weight-bold">
                <i class="fas fa-comment-dots me-2"></i> Feedback Guru
            </div>
            <div class="font-italic text-secondary" style="line-height: 1.4;">"{{ $t['feedback_guru'] }}"</div>
        </div>
    @endif

    <div class="card-footer-flex">
        @if (!empty($t['link_lampiran']))
            <button class="btn-app outline ripple" onclick="openLampiran('{{ htmlspecialchars($t['judul']) }}', '{{ $t['link_lampiran'] }}')">
                <i class="fas fa-paperclip me-1"></i> Soal
            </button>
        @else
            <div></div>
        @endif

        @if ($type == 'aktif')
            @if ($t['allow_upload'] == 1)
                <button class="btn-app primary" onclick="openSubmitForm({{ $t['id'] }}, false)">Kumpulkan</button>
            @else
                <button id="btn-read-{{ $t['id'] }}" class="btn-app primary" onclick="markAsRead({{ $t['id'] }})">
                    <i class="fas fa-check-double me-2"></i> Sudah dibaca
                </button>
            @endif
        @elseif ($type == 'selesai')
            @if (isset($t['nilai']) && $t['nilai'] !== null && $t['nilai'] !== '')
                <span class="status-pill score"><i class="fas fa-star text-warning me-1"></i> Nilai: <strong class="ms-1">{{ floatval($t['nilai']) }}</strong></span>
            @else
                @php 
                    $is_locked_by_deadline = ($deadline_ts && time() > $deadline_ts); 
                    $safe_metode = htmlspecialchars($t['metode'] ?? 'link');
                @endphp

                @if ($is_locked_by_deadline)
                    <button class="btn-app disabled" disabled><i class="fas fa-lock me-1"></i> Terkunci</button>
                @elseif (isset($t['allow_upload']) && $t['allow_upload'] == 0)
                    <button class="btn-app disabled" disabled><i class="fas fa-check-double me-1"></i> Telah Dibaca</button>
                @else
                    <button class="btn-app outline" onclick="openSubmitForm({{ $t['id'] }}, false, '{{ $t['link_pengumpulan'] }}', '{{ htmlspecialchars($t['catatan_siswa'] ?? '') }}', '{{ $safe_metode }}')">Lihat / Edit</button>
                @endif
            @endif
        @elseif ($type == 'terlewat')
            @if ($t['is_auto_close'] == 1)
                <button class="btn-app disabled" disabled><i class="fas fa-lock me-1"></i> Ditutup</button>
            @else
                <button class="btn-app" style="background:#ed8936; color:white;" onclick="openSubmitForm({{ $t['id'] }}, true)">Susulan</button>
            @endif
        @endif
    </div>
</div>
