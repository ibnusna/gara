@extends('layouts.siswa')

@section('title', 'Materi Belajar')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/belajar.css') }}?v={{ time() }}">
@endpush

@push('js')
    
    <script src="{{ asset('assets/student/js/search_filter.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', initMateriSearch);
        
        document.body.addEventListener('htmx:afterSwap', function(e) {
            if (document.getElementById('bab-list-container')) initMateriSearch();
        });
    </script>
@endpush

@section('content')

    <div class="page-sub-header">
        <div class="header-left">
            <a href="{{ route('student.dashboard') }}" class="btn-back" hx-boost="false"><i
                    class="fas fa-arrow-left"></i></a>
            <div>
                <h1 class="page-title">Ruang Belajar</h1>
                <span class="page-subtitle">{{ session('nama_mapel', 'Mata Pelajaran') }}</span>
            </div>
        </div>
    </div>

    
    
    <div class="bab-list-container" id="bab-list-container">
        

        @if (empty($list_bab))
            <div class="empty-state text-center py-5">
                <img src="{{ \App\Models\AppSetting::getLogo('3dlogo.svg') }}" alt="Empty"
                    style="width: 80px; opacity: 0.5; margin-bottom: 20px;">
                <h5 class="text-muted">Belum ada materi</h5>
                <p class="text-muted small">Guru belum mengupload materi untuk pelajaran ini.</p>
            </div>
        @else

            @foreach($list_bab as $b)
                @php $b = (array) $b; @endphp
                
                
                <a href="{{ route('student.materi.bab', ['bab' => $b['bab']]) }}" class="card-bab glass-card ripple" hx-boost="false" style="border-radius: 20px; margin-bottom: 12px; display: flex; text-decoration: none; align-items: center; padding: 16px;">

                    
                    <div class="bab-number-box">
                        <span class="bab-label" style="opacity:0.8;">BAB</span>
                        <span class="bab-digit">{{ $formatNomorBab($b['bab']) }}</span>
                    </div>

                    
                    <div class="bab-info">
                        <div class="bab-meta">
                            <span>SEMESTER {{ $b['semester'] }}</span>
                            <span>&bull;</span>
                            <span>{{ $b['total_topik'] }} Topik</span>
                        </div>

                        
                        <h3 class="bab-title">
                            {{ htmlspecialchars($b['nama_bab_otomatis'] ?? 'Bab ' . $b['bab']) }}
                        </h3>

                        
                        <div class="progress-mini">
                            <div class="progress-bar-fill" style="width: 0%"></div>
                        </div>
                    </div>

                    
                    <div class="bab-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            @endforeach

        @endif

    </div>

    
    <div style="height: 50px;"></div>
@endsection