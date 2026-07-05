@extends('layouts.siswa')

@section('title', 'Dashboard Siswa | GARA')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/root.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
@endpush

@section('content')

    <!-- OVERLAY LOADING -->
    <div id="loading-overlay">
        <div class="spinner-box"></div>
        <div class="loading-text">Memuat Ruang Kompetensi...</div>
        <div class="loading-subtext">Sedang menyiapkan data Kamu</div>
    </div>

    <div class="dashboard-container no-scrollbar">

        <!-- KARTU PELAJAR (Hero Baru) -->
        <div class="student-id-card ripple" onclick="window.location.href='{{ route('student.pilih-mapel') }}'">
            <div class="id-card-content">
                <div class="id-card-header">
                    <span class="id-school-name"><i class="fas fa-university"></i> {{ $sekolah_nama ?? 'GARA' }}</span>
                    <span class="id-tahun-ajaran">{{ $tahun_ajaran ?? '' }}</span>
                </div>
                <div class="id-card-body">
                    <div class="id-info">
                        <h2>{{ session('nama', 'Siswa') }}</h2>
                        <p><i class="fas fa-users"></i> Kelas {{ session('nama_kelas', '-') }}</p>
                        <div class="id-mapel">
                            <i class="fas fa-book"></i> Mapel: {{ session('nama_mapel', '-') }}
                        </div>
                    </div>
                    <div class="id-avatar">
                        @php $avatarUrl = auth()->user()->profile_photo_url; @endphp
                        <img src="{{ $avatarUrl }}" alt="Foto Profil">
                    </div>
                </div>
            </div>
        </div>

        <!-- PENGUMUMAN WIDGET -->
        <div id="pengumuman-container"></div>

        <!-- SECTION: TERAS ILMU (Eksplorasi) -->
        <div class="section-title mt-2">Eksplorasi</div>
        <div class="exploration-grid">

            <!-- Main Features (Besar) -->
            <a href="{{ route('student.materi.index') }}" class="glass-card main-feature ripple" hx-boost="false">
                <div class="icon-box bg-blue"><i class="fas fa-book-reader"></i></div>
                <div class="feature-text">
                    <h4>Ruang Belajar</h4>
                    <p>Materi & Modul</p>
                </div>
            </a>

            {{-- Ruang Diskusi naik ke main-feature (sejajar Ruang Belajar) --}}
            <a href="{{ route('student.diskusi.index') }}" class="glass-card main-feature ripple" hx-boost="false">
                <div class="icon-box bg-emerald"><i class="fas fa-comments"></i></div>
                <div class="feature-text">
                    <h4>Ruang Diskusi</h4>
                    <p>Diskusi & Tanya Jawab</p>
                </div>
            </a>

            <!-- Sub Features (Kecil) — Ruang Tugas turun ke sini -->
            <div class="sub-features-grid">
                <a href="{{ route('student.ruang_kompetensi.index') }}" class="glass-card sub-feature ripple">
                    <div class="icon-box-small bg-rose"><i class="fas fa-laptop-code"></i></div>
                    <span>Ruang Kompetensi</span>
                </a>

                <a href="{{ route('student.ruang-fokus.index') }}" class="glass-card sub-feature ripple" hx-boost="false">
                    <div class="icon-box-small bg-purple"><i class="fas fa-bullseye"></i></div>
                    <span>Ruang Fokus</span>
                </a>

                <a href="{{ route('student.tugas.index') }}" class="glass-card sub-feature ripple" hx-boost="false">
                    <div class="icon-box-small bg-orange"><i class="fas fa-tasks"></i></div>
                    <span>Ruang Tugas</span>
                </a>

                <a href="{{ route('student.ruang-catatan.index') }}" class="glass-card sub-feature ripple" hx-boost="false">
                    <div class="icon-box-small bg-amber"><i class="fas fa-sticky-note"></i></div>
                    <span>Ruang Catatan</span>
                </a>
            </div>
        </div>

        <!-- SECTION: ZONA PRODUKTIF -->
        <div class="section-title mt-4">Produktivitas</div>
        <div class="productivity-grid">

            <!-- Widget Jadwal Sholat -->
            <div class="glass-card productivity-widget ripple">
                <div class="widget-header">
                    <div class="widget-title"><i class="fas fa-mosque" style="color: var(--gara-blue);"></i> Jadwal Sholat
                    </div>
                    <div class="widget-action" id="sholat-date">{{ date('d M Y') }}</div>
                </div>

                <div class="sholat-container">
                    <div id="sholat-location" class="sholat-location">
                        <i class="fas fa-map-marker-alt"></i> Mendeteksi Lokasi...
                    </div>

                    <div class="sholat-times-grid" id="sholat-times-grid">
                        <div class="time-box"><span class="time-name">Subuh</span><span class="time-value"
                                id="time-subuh">--:--</span></div>
                        <div class="time-box"><span class="time-name">Dzuhur</span><span class="time-value"
                                id="time-dzuhur">--:--</span></div>
                        <div class="time-box"><span class="time-name">Ashar</span><span class="time-value"
                                id="time-ashar">--:--</span></div>
                        <div class="time-box"><span class="time-name">Maghrib</span><span class="time-value"
                                id="time-maghrib">--:--</span></div>
                        <div class="time-box"><span class="time-name">Isya</span><span class="time-value"
                                id="time-isya">--:--</span></div>
                    </div>
                </div>
            </div>

            <!-- Widget Brain Warmup -->
            <div class="glass-card productivity-widget ripple">
                <div class="widget-header">
                    <div class="widget-title"><i class="fas fa-brain" style="color: #f59e0b;"></i> Brain Warmup</div>
                    <div class="widget-action"><span class="badge-light">Science & Comp</span></div>
                </div>

                <div class="trivia-container">
                    <div id="trivia-question" class="trivia-question">
                        <div class="skeleton sk-text sk-text-lg mb-2"></div>
                        <div class="skeleton sk-text sk-text-sm mb-4" style="width: 60%"></div>
                    </div>
                    <!-- PENTING: trivia-options disini -->
                    <div id="trivia-options" class="trivia-options"></div>
                </div>
            </div>

        </div>

        <!-- SECTION: JELAJAH ILMU -->
        <div class="section-title mt-4">Jelajah Ilmu</div>
        <div class="external-grid mb-5">
            <a href="https://rumah.pendidikan.go.id/ruang/murid" target="_blank" class="glass-card ext-link-card ripple">
                <i class="fas fa-university ext-icon" style="color: #2563eb;"></i>
                <div class="ext-title">Bank Soal Kemdikbud</div>
            </a>
            <a href="https://buku.kemendikdasmen.go.id/" target="_blank" class="glass-card ext-link-card ripple">
                <i class="fas fa-graduation-cap ext-icon" style="color: #16a34a;"></i>
                <div class="ext-title">E-Book</div>
            </a>
            <a href="https://scholar.google.co.id/" target="_blank" class="glass-card ext-link-card ripple">
                <i class="fab fa-google ext-icon" style="color: #ea4335;"></i>
                <div class="ext-title">Jurnal</div>
            </a>
            <a href="https://www.education.com/resources/games/?gclid=Cj0KCQiA1czLBhDhARIsAIEc7uir5_KtTn7lcO-3b2IpFhlK8su2EReD9JJQqw02ehD0SGUbFKbeIR4aAszcEALw_wcB"
                target="_blank" class="glass-card ext-link-card ripple">
                <i class="fas fa-gamepad ext-icon" style="color: #dc2626;"></i>
                <div class="ext-title">Games</div>
            </a>
            <a href="https://kbbi.web.id/" target="_blank" class="glass-card ext-link-card ripple">
                <i class="fas fa-book ext-icon" style="color: #d97706;"></i>
                <div class="ext-title">KBBI</div>
            </a>
            <a href="https://www.perpusnas.go.id/" target="_blank" class="glass-card ext-link-card ripple">
                <i class="fas fa-book-reader ext-icon" style="color: #9333ea;"></i>
                <div class="ext-title">Perpusnas</div>
            </a>
        </div>

    </div>
@endsection

@push('js')
    <script>
        function showLoading(e, url) {
            e.preventDefault();
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.style.display = 'flex';
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        }

        window.addEventListener('pageshow', function (event) {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.style.display = 'none';
        });

        // Inject Ripple Effect Logic
        document.addEventListener('DOMContentLoaded', () => {
            const rippleElements = document.querySelectorAll('.ripple');
            rippleElements.forEach(element => {
                element.addEventListener('click', function (e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX ? e.clientX - rect.left : rect.width / 2;
                    const y = e.clientY ? e.clientY - rect.top : rect.height / 2;

                    const ink = document.createElement('span');
                    ink.classList.add('ink');

                    const size = Math.max(rect.width, rect.height);
                    ink.style.width = ink.style.height = `${size}px`;
                    ink.style.left = `${x - size / 2}px`;
                    ink.style.top = `${y - size / 2}px`;

                    this.appendChild(ink);
                    setTimeout(() => ink.remove(), 600);
                });
            });
        });
    </script>
    <script src="{{ asset('assets/student/js/dashboard_v2.js') }}?v={{ time() }}"></script>
@endpush