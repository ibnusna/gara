@extends('layouts.siswa')

@section('title', 'Bab ' . $bab_ke)

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/belajar.css') }}">
@endpush

@section('content')

<div class="page-sub-header">
    <div class="header-left">
        <a href="{{ route('student.materi.index') }}" class="btn-back" hx-boost="false"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Daftar Topik</h1>
            <span class="page-subtitle">{{ session('nama_mapel', 'Mata Pelajaran') }}</span>
        </div>
    </div>
</div>


<div class="subbab-header" style="background: linear-gradient(135deg, rgba(13,110,253,0.85), rgba(10,88,202,0.85)); backdrop-filter: blur(15px); border-bottom-left-radius: 25px; border-bottom-right-radius: 25px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(13,110,253,0.3);">
    <p style="color: rgba(255,255,255,0.9);">BAB {{ $formatNomorBab($bab_ke) }}</p>
    <h2 style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Topik Pembelajaran</h2> 
    <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin-top: 5px;">
        Total {{ count($materi_list) }} Bagian
    </p>
</div>


<div class="materi-list-group glass-card" style="border-radius: 20px; padding: 0; background: rgba(255,255,255,0.65);">
    
    @if (count($materi_list) == 0)
        <div class="p-4 text-center text-muted">
            <i class="fas fa-box-open fa-2x mb-2"></i>
            <p>Belum ada topik di bab ini.</p>
        </div>
    @else

        @foreach($materi_list as $m)
            @php 
                $m = (array) $m; 
                $is_completed = $isMateriCompleted($m['id_materi'] ?? $m['id']); 
            @endphp

            
            <a href="{{ route('student.materi.detail', ['id' => $m['id']]) }}" class="materi-item ripple {{ $is_completed ? 'completed' : '' }}" hx-boost="false" style="background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05);">
                
                
                <div class="status-indicator">
                    @if($is_completed)
                        <i class="fas fa-check"></i>
                    @else
                        <span style="font-size: 0.7rem; font-weight: 700;">{{ $m['bagian'] }}</span>
                    @endif
                </div>

                
                <div class="materi-info">
                    <h4>{{ htmlspecialchars($m['judul_materi']) }}</h4>
                    <span>Bagian ke-{{ $m['bagian'] }}</span>
                    
                    
                    <div style="margin-top: 5px; font-size: 0.75rem; color: #999;">
                        @if($m['link_youtube']) <i class="fab fa-youtube text-danger me-2"></i> @endif
                        @if($m['link_ppt']) <i class="fas fa-file-powerpoint text-warning me-2"></i> @endif
                        @if($m['link_modul']) <i class="fas fa-file-pdf text-primary me-2"></i> @endif
                        @if($m['link_tugas']) <i class="fas fa-tasks text-success me-2"></i> @endif
                    </div>
                </div>

                
                <div style="margin-left: auto; color: #ccc;">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        @endforeach

    @endif

</div>


<div style="height: 50px;"></div>
@endsection
