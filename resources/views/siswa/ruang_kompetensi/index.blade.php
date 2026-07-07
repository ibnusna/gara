@extends('layouts.siswa')

@section('title', 'Ruang Kompetensi | GARA')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/root.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/ujian.css') }}">
@endpush

@section('content')

    <div class="page-sub-header">
        <div class="header-left">
            <a href="{{ route('student.dashboard') }}" class="btn-back"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h1 class="page-title">Ruang Kompetensi</h1>
                <span class="page-subtitle">{{ session('nama_mapel', 'Modul Evaluasi Harian') }}</span>
            </div>
        </div>
    </div>

    <div class="dashboard-container">

        @if($evaluasi->isEmpty())
            <div class="empty-state animate-up glass-card" style="border-radius: 20px; padding: 30px; text-align: center;">
                <i class="fas fa-box-open" style="font-size: 2rem; color: #ccc; margin-bottom: 10px;"></i>
                <h5 class="text-dark font-weight-bold">Belum Ada Evaluasi Aktif</h5>
                <p class="text-muted">Saat ini belum ada jadwal evaluasi untuk mata pelajaran ini. Silakan kembali lagi nanti.</p>
            </div>
        @else

            <div class="section-label">Ujian Tersedia</div>
            <div>
                @foreach($evaluasi as $eval)
                    <div class="exam-card animate-up glass-card" style="border-radius: 20px; padding: 20px; margin-bottom: 15px;">
                        <div class="exam-header">
                            <div class="exam-info">
                                <h4>{{ $eval->judul }}</h4>
                                <div class="exam-badges">
                                    <span class="badge-item badge-time"><i class="far fa-clock"></i> {{ $eval->waktu_menit }}
                                        Menit</span>
                                    @if($eval->guru)
                                        <span class="badge-item bg-light text-dark text-nowrap"><i class="far fa-user"></i>
                                            {{ $eval->guru->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <button
                            onclick="confirmExam('{{ route('student.ruang_kompetensi.ujian', $eval->id) }}', '{{ $eval->waktu_menit }}')"
                            class="btn-start-exam">
                            <i class="fas fa-play me-2"></i> Mulai Ujian
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('js')
    <script>
        function confirmExam(url, waktu) {
            Swal.fire({
                title: 'Mulai Ujian?',
                html: `Ujian ini akan berlangsung selama <strong>${waktu} Menit</strong>.<br>Halaman ujian akan ditampilkan di mode <strong>Layar Penuh (Fullscreen)</strong>.<br>Apakah Kamu siap?`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Mulai Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
@endpush