@extends('layouts.siswa')

@section('title', 'Pemberitahuan | GARA')

@push('css')
    <style>
        .notif-list {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 12px 100px 12px; /* padding sisi agar card tidak menempel layar */
        }

        .notif-item {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 12px 14px;
            border-bottom: none; /* pakai margin bukan border agar gap antar card */
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            transition: background 0.2s, transform 0.15s;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .notif-item:active,
        .notif-item:hover {
            background: rgba(255,255,255,1);
            transform: scale(0.99);
        }

        .icon-box {
            width: 42px;
            height: 42px;
            min-width: 42px; /* Cegah icon mengecil */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-tugas {
            background-color: #e8f5e9;
            color: #28a745;
        }

        .icon-diskusi {
            background-color: #e3f2fd;
            color: #0d6efd;
        }

        .icon-materi {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .icon-kompetensi {
            background-color: #f8d7da;
            color: #dc3545;
        }

        .notif-content {
            flex: 1;
            min-width: 0; /* Wajib: agar flex child bisa shrink dengan benar */
            text-align: left;
            overflow: hidden;
        }

        .notif-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 3px;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-desc {
            font-size: 0.8rem;
            color: #6c757d;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .notif-desc span.badge {
            flex-shrink: 0;
            font-size: 0.65rem;
            padding: 2px 8px;
        }

        .notif-time {
            font-size: 0.72rem;
            color: #adb5bd;
            margin-top: 2px;
            white-space: nowrap;
        }

        .notif-unread-dot {
            width: 8px;
            height: 8px;
            min-width: 8px;
            background: #dc3545;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .header-simple {
            background: white;
            padding: 14px 20px;
            border-bottom: 1px solid #e4e6eb;
            position: sticky;
            top: 0;
            z-index: 100;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/student/css/diskusi_desktop.css') }}?v={{ time() }}">
@endpush

@section('content')


<div class="tw-layout-wrapper">
    
    @include('siswa.partials.twotter_sidebar_left', ['active' => 'notifikasi'])

    
    <main class="tw-main-feed">
        <div class="header-simple px-3 py-3 border-bottom sticky-top glass-card-strong" style="margin: 0 0 16px 0; border-radius: 0;">
            <div class="d-flex align-items-center justify-content-center position-relative">
                <a href="{{ route('student.dashboard') }}" class="text-dark position-absolute start-0" style="left: 0;" hx-boost="false">
                    <i class="fas fa-arrow-left fa-lg"></i>
                </a>
                <h6 class="m-0 fw-bold">Aktivitas Terbaru</h6>
            </div>
        </div>

        <div class="notif-list">
            @if (empty($notifikasi))
                <div class="text-center py-5 text-muted">
                    <i class="far fa-bell-slash fa-3x mb-3 text-light-gray" style="color: #dee2e6;"></i>
                    <p>Belum ada pemberitahuan baru.</p>
                </div>
            @else
                @foreach ($notifikasi as $n)
                    @php
                        $icon = 'fas fa-bell';
                        $bg_cls = 'icon-diskusi'; 
                        $text = 'Pemberitahuan';

                        if ($n->tipe == 'tugas') {
                            $icon = 'fas fa-clipboard-list';
                            $bg_cls = 'icon-tugas';
                            $text = 'Tugas baru diberikan';
                        } elseif ($n->tipe == 'diskusi') {
                            $icon = 'fas fa-comments';
                            $bg_cls = 'icon-diskusi';
                            $text = 'Topik diskusi baru';
                        } elseif ($n->tipe == 'materi') {
                            $icon = 'fas fa-book-open';
                            $bg_cls = 'icon-materi';
                            $text = 'Materi baru ditambahkan';
                        } elseif ($n->tipe == 'kompetensi') {
                            $icon = 'fas fa-file-signature';
                            $bg_cls = 'icon-kompetensi';
                            $text = 'Ujian / Evaluasi baru';
                        }

                        $link = route('student.notifikasi.redirect', [
                            'type' => $n->tipe,
                            'id' => $n->id,
                            'mapel_id' => $n->mapel_id
                        ]);
                    @endphp
                    <a href="{{ $link }}" class="notif-item glass-card ripple" hx-boost="false" style="border-radius: 16px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.4);">
                        <div class="icon-box {{ $bg_cls }}">
                            <i class="{{ $icon }}"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-desc">
                                <span>{{ $text }}</span>
                                @if($n->nama_mapel)
                                    <span class="badge bg-secondary">{{ $n->nama_mapel }}</span>
                                @endif
                            </div>
                            <div class="notif-title">{{ htmlspecialchars($n->judul) }}</div>
                            <div class="notif-time">{{ date('d M Y, H:i', strtotime($n->created_at)) }}</div>
                        </div>
                        @if(strtotime($n->created_at) > strtotime('-1 day'))
                            <div class="notif-unread-dot"></div>
                        @endif
                    </a>
                @endforeach
            @endif
        </div>
    </main>

    
    @include('siswa.partials.twotter_sidebar_right')
</div>

@endsection