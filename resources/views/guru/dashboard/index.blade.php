@extends('layouts.guru')

@section('title', 'Dashboard ' . session('nama_mapel') . ' | GARA')

@php
    $active_menu = 'dashboard';
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard Guru</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                
                <div class="gt-welcome-banner" style="
                                    border-radius: 20px;
                                    padding: 28px 36px;
                                    margin-bottom: 28px;
                                    position: relative;
                                    overflow: hidden;
                                ">
                    
                    <div
                        style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;">
                    </div>
                    <div
                        style="position:absolute;bottom:-20px;right:120px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;">
                    </div>

                    <div
                        style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;">
                        <div>
                            <p
                                style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 4px;letter-spacing:0.05em;text-transform:uppercase;">
                                Selamat Datang</p>
                            <h2 style="color:#fff;font-size:1.7rem;font-weight:700;margin:0 0 6px;font-family:'Plus Jakarta Sans',sans-serif;" class="gt-welcome-name">
                                {{ auth()->user()->nama_lengkap ?? auth()->user()->name ?? auth()->user()->username ?? 'Guru' }}
                            </h2>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
                                <span
                                    style="background:rgba(255,255,255,0.18);color:#fff;border-radius:20px;padding:4px 14px;font-size:0.82rem;font-weight:600;">
                                    <i class="fas fa-book-open mr-1"></i> {{ session('nama_mapel') }}
                                </span>
                                <span
                                    style="background:rgba(255,255,255,0.12);color:#fff;border-radius:20px;padding:4px 14px;font-size:0.82rem;">
                                    <i class="fas fa-school mr-1"></i> {{ session('nama_kelas') }}
                                </span>
                            </div>
                        </div>
                        <div id="dashClock"
                            style="color:rgba(255,255,255,0.9);font-size:1.4rem;font-weight:700;letter-spacing:0.06em;text-align:right;">
                            <div id="dashTime" style="font-size:2rem;"></div>
                            <div id="dashDate" style="font-size:0.78rem;opacity:0.8;font-weight:400;"></div>
                        </div>
                    </div>
                </div>

                
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0" style="border-radius: 16px;">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h5 style="font-weight:700;color:#1e293b;"><i
                                        class="fas fa-layer-group mr-2 text-primary"></i> Menu Utama Kelas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    @php
                                        $menus = [
                                            ['href' => route('guru.absensi.create'), 'icon' => 'fas fa-clipboard-user', 'label' => 'Absensi', 'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)'],
                                            ['href' => route('guru.absensi.rekap'), 'icon' => 'fas fa-calendar-check', 'label' => 'Rekap Absensi', 'color' => '#14b8a6', 'bg' => 'rgba(20,184,166,0.1)'],
                                            ['href' => route('guru.agenda.index'), 'icon' => 'fas fa-calendar-alt', 'label' => 'Agenda', 'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.1)'],
                                            ['href' => route('guru.ruang_kompetensi.index'), 'icon' => 'fas fa-laptop-code', 'label' => 'Ruang Kompetensi', 'color' => '#f43f5e', 'bg' => 'rgba(244,63,94,0.1)'],
                                            ['href' => route('guru.ruang_tugas.index'), 'icon' => 'fas fa-upload', 'label' => 'Ruang Tugas', 'color' => '#ec4899', 'bg' => 'rgba(236,72,153,0.1)'],
                                            ['href' => route('guru.tugas.create'), 'icon' => 'fas fa-edit', 'label' => 'Input Tugas', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'],
                                            ['href' => route('guru.rekap.index'), 'icon' => 'fas fa-chart-bar', 'label' => 'Rekap Nilai', 'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.1)'],
                                            ['href' => route('guru.diskusi'), 'icon' => 'fas fa-comments', 'label' => 'Diskusi', 'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)'],
                                        ];
                                    @endphp

                                    @foreach($menus as $m)
                                        <div class="col-6 col-md-4 col-lg-2 mb-3">
                                            <a href="{{ $m['href'] }}" class="text-decoration-none">
                                                <div class="guru-quick-item p-3"
                                                    style="border-radius: 14px; background: {{ $m['bg'] }}; border: 1px solid {{ $m['color'] }}30; transition: all 0.3s ease;">
                                                    <i class="{{ $m['icon'] }} mb-2"
                                                        style="font-size: 2rem; color: {{ $m['color'] }};"></i>
                                                    <div style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                                                        {{ $m['label'] }}
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="row mb-4">
                    
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0" style="border-radius: 16px; height: 100%;">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 style="font-weight:700;color:#1e293b;"><i class="fas fa-chart-pie mr-2"
                                        style="color:#10b981;"></i> Rasio Kehadiran (Semester Ini)</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                @if(array_sum($absensiStats) > 0)
                                    <div style="position: relative; height: 250px; width: 100%;">
                                        <canvas id="attendanceChart"></canvas>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-folder-open mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <p>Belum ada data absensi tercatat.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0" style="border-radius: 16px; height: 100%;">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 style="font-weight:700;color:#1e293b;"><i
                                        class="fas fa-chart-line mr-2 text-primary"></i> Rata-Rata Nilai Tugas Siswa</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                @if(count($tugasStats) > 0)
                                    <div style="position: relative; height: 250px; width: 100%;">
                                        <canvas id="averageGradesChart"></canvas>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-chart-bar mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <p>Belum ada data nilai tugas tercatat.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="row">
                    
                    <div class="col-12">
                        <div class="card shadow-sm border-0" style="border-radius: 16px;">
                            <div class="card-header bg-white border-bottom-0 pt-4">
                                <h5 style="font-weight:700;color:#1e293b;"><i class="fas fa-bullhorn mr-2"
                                        style="color:#dc2626;"></i> Broadcast Pengumuman Kelas</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('guru.pengumuman.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                <input type="text" name="judul" class="form-control"
                                                    placeholder="Judul pengumuman menarik..." required
                                                    value="{{ $pengumuman->judul ?? '' }}"
                                                    style="border-radius:10px;border:1px solid rgba(11,87,208,0.2);font-size:0.95rem;">
                                            </div>
                                            <div class="form-group mb-0">
                                                <textarea name="isi" class="form-control" rows="3"
                                                    placeholder="Tuliskan pesan / notifikasi untuk seluruh siswa di kelas {{ session('nama_kelas') }}..."
                                                    required
                                                    style="border-radius:10px;border:1px solid rgba(11,87,208,0.2);font-size:0.95rem;resize:none;">{{ $pengumuman->isi ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-flex flex-column justify-content-center">
                                            <button type="submit" class="btn btn-primary btn-block mb-3"
                                                style="border-radius:10px;font-weight:600; padding: 12px;">
                                                <i class="fas fa-paper-plane mr-1"></i> Publikasikan Sekarang
                                            </button>

                                            @if($pengumuman)
                                                <div class="text-center">
                                                    <small class="text-muted d-block mb-2"><i
                                                            class="fas fa-check-circle text-success"></i> Aktif sejak:
                                                        {{ \Carbon\Carbon::parse($pengumuman->updated_at)->format('d M y - H:i') }}</small>
                                                    <a href="#"
                                                        onclick="event.preventDefault(); document.getElementById('delete-pengumuman').submit();"
                                                        class="text-danger text-sm text-decoration-none">
                                                        <i class="fas fa-trash"></i> Hapus Pengumuman Saat Ini
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                @if($pengumuman)
                                    <form id="delete-pengumuman" action="{{ route('guru.pengumuman.destroy') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    @push('styles')
        <style>
            .guru-quick-item:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
                background: #fff !important;
            }
        </style>
    @endpush

    @push('scripts')
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            
            function updateDashClock() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                const el = document.getElementById('dashTime');
                const el2 = document.getElementById('dashDate');
                if (el) el.textContent = timeStr;
                if (el2) el2.textContent = dateStr + ' WIB';
            }
            updateDashClock();
            setInterval(updateDashClock, 1000);

            
            document.addEventListener('DOMContentLoaded', function () {

                
                @if(array_sum($absensiStats) > 0)
                    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
                    new Chart(ctxAttendance, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)'],
                            datasets: [{
                                data: [{{ $absensiStats['H'] }}, {{ $absensiStats['S'] }}, {{ $absensiStats['I'] }}, {{ $absensiStats['A'] }}],
                                backgroundColor: [
                                    '#10b981', 
                                    '#f59e0b', 
                                    '#0ea5e9', 
                                    '#ef4444'  
                                ],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { family: "'Inter', sans-serif" }
                                    }
                                }
                            },
                            cutout: '65%'
                        }
                    });
                @endif

                    
                    @if(count($tugasStats) > 0)
                        const ctxGrades = document.getElementById('averageGradesChart').getContext('2d');
                        new Chart(ctxGrades, {
                            type: 'bar',
                            data: {
                                labels: [
                                    @foreach($tugasStats as $ts)
                                        'Tugas {{ $ts->tugas_ke }}',
                                    @endforeach
                                                                                        ],
                                datasets: [{
                                    label: 'Rata-Rata Nilai',
                                    data: [
                                        @foreach($tugasStats as $ts)
                                            {{ number_format((float) $ts->rata_rata, 2, '.', '') }},
                                        @endforeach
                                                                                            ],
                                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                                    borderColor: '#2563eb',
                                    borderWidth: 1,
                                    borderRadius: { topLeft: 6, topRight: 6 }
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] },
                                        ticks: { font: { family: "'Inter', sans-serif" } }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { family: "'Inter', sans-serif" } }
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            title: function (context) {
                                                let titles = [
                                                    @foreach($tugasStats as $ts)
                                                        '{{ addslashes(str_replace("\n", "", $ts->pokok_bahasan)) }}',
                                                    @endforeach
                                                                                                        ];
                                                return titles[context[0].dataIndex];
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    @endif

                                            });
        </script>
    @endpush
@endsection