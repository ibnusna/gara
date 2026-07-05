@extends('layouts.kepsek', ['active_menu' => 'dashboard'])

@section('title', 'Executive Dashboard | Kepala Sekolah')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-tachometer-alt mr-2" style="color:#1a3c6e"></i>Executive Dashboard
                        </h1>
                        <p class="text-muted mb-0">
                            Selamat datang, {{ auth()->user()->nama_lengkap ?? 'Kepala Sekolah' }}.
                            Pantau aktivitas sekolah secara real-time.
                        </p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i><span id="liveClock">--:--:--</span> WIB
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                
                <div class="row" id="widgetRow">
                    <div class="col-12 text-center py-4" id="widgetLoader">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="text-muted mt-2">Memuat data...</p>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card ks-section-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-line mr-2"></i>Tren Aktivitas LMS — 7 Hari Terakhir
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height:280px;">
                                    <canvas id="aktivitasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card ks-section-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-2"></i>Kelengkapan Perangkat Guru
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="position:relative; height:240px;">
                                    <canvas id="perangkatPieChart"></canvas>
                                </div>
                                <div id="perangkatPieLegend" class="mt-2 text-center small"></div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card ks-section-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-alt mr-2"></i>Jadwal Ujian Mendatang
                                </h3>
                                <a href="{{ route('kepsek.ujian_nilai') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-external-link-alt mr-1"></i>Lihat Semua
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Mata Pelajaran</th>
                                                <th>Kelas</th>
                                                <th>Tanggal</th>
                                                <th>Jam</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="jadwalTable">
                                            <tr>
                                                <td colspan="5" class="text-center py-3">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card ks-section-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-globe mr-2" style="color:#2563eb;"></i>
                                    Akses Cepat Layanan Nasional
                                </h3>
                                <span class="badge badge-light text-muted" style="font-size:0.72rem;">
                                    <i class="fas fa-external-link-alt mr-1"></i>Portal Resmi Kemendikdasmen
                                </span>
                            </div>
                            <div class="card-body pb-2">

                                
                                <div class="mb-3">
                                    <p class="mb-2"
                                        style="color:#6b7280;letter-spacing:0.08em;font-size:0.72rem;text-transform:uppercase;font-weight:600;">
                                        <i class="fas fa-layer-group mr-1"></i>Superaplikasi &amp; Manajemen Sekolah
                                    </p>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://rumah.pendidikan.go.id" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#2563eb;">
                                                <div class="ks-portal-icon-wrap">
                                                    <i class="fas fa-house-chimney-user"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Rumah Pendidikan</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(37,99,235,0.12);color:#2563eb;">Superaplikasi</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Platform ekosistem pendidikan resmi
                                                        Kemendikdasmen</p>
                                                    <div class="ks-portal-footer">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://rumah.pendidikan.go.id/ruang-gtk" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#2563eb;">
                                                <div class="ks-portal-icon-wrap">
                                                    <i class="fas fa-chalkboard-user"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Ruang GTK</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(37,99,235,0.12);color:#2563eb;">Superaplikasi</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Pengembangan guru &amp; tenaga kependidikan di
                                                        Rumah Pendidikan</p>
                                                    <div class="ks-portal-footer">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://kspstendik.kemendikdasmen.go.id" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#2563eb;">
                                                <div class="ks-portal-icon-wrap">
                                                    <i class="fas fa-id-badge"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">KSPSTENDIK</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(37,99,235,0.12);color:#2563eb;">Superaplikasi</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Layanan kepangkatan &amp; status tenaga
                                                        kependidikan</p>
                                                    <div class="ks-portal-footer">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-3">
                                    <p class="mb-2"
                                        style="color:#6b7280;letter-spacing:0.08em;font-size:0.72rem;text-transform:uppercase;font-weight:600;">
                                        <i class="fas fa-layer-group mr-1"></i>Data &amp; Administrasi
                                    </p>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://dapo.kemendikdasmen.go.id" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#16a34a;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(22,163,74,0.1);color:#16a34a;">
                                                    <i class="fas fa-database"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Dapodik</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(22,163,74,0.12);color:#16a34a;">Data
                                                            Pokok</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Data Pokok Pendidikan — referensi data
                                                        nasional</p>
                                                    <div class="ks-portal-footer" style="color:#16a34a;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://belajar.id" target="_blank" rel="noopener noreferrer"
                                                class="ks-portal-card" style="--cat-color:#16a34a;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(22,163,74,0.1);color:#16a34a;">
                                                    <i class="fas fa-envelope-open-text"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Belajar.id</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(22,163,74,0.12);color:#16a34a;">Akun
                                                            Resmi</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Akun pembelajaran resmi GTK &amp; siswa
                                                        Kemendikdasmen</p>
                                                    <div class="ks-portal-footer" style="color:#16a34a;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://portal.simpkb.id" target="_blank" rel="noopener noreferrer"
                                                class="ks-portal-card" style="--cat-color:#16a34a;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(22,163,74,0.1);color:#16a34a;">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">SIMPKB</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(22,163,74,0.12);color:#16a34a;">PKB
                                                            Guru</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Sistem informasi pengembangan keprofesian
                                                        berkelanjutan</p>
                                                    <div class="ks-portal-footer" style="color:#16a34a;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-2">
                                    <p class="mb-2"
                                        style="color:#6b7280;letter-spacing:0.08em;font-size:0.72rem;text-transform:uppercase;font-weight:600;">
                                        <i class="fas fa-layer-group mr-1"></i>Kebijakan &amp; Regulasi
                                    </p>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://gtk.kemendikdasmen.go.id" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#7c3aed;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(124,58,237,0.1);color:#7c3aed;">
                                                    <i class="fas fa-sitemap"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Dirjen GTK</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(124,58,237,0.12);color:#7c3aed;">Kebijakan</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Direktorat Jenderal Guru &amp; Tenaga
                                                        Kependidikan resmi</p>
                                                    <div class="ks-portal-footer" style="color:#7c3aed;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://jdih.kemdikbud.go.id" target="_blank" rel="noopener noreferrer"
                                                class="ks-portal-card" style="--cat-color:#7c3aed;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(124,58,237,0.1);color:#7c3aed;">
                                                    <i class="fas fa-scale-balanced"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">JDIH</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(124,58,237,0.12);color:#7c3aed;">Hukum</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Jaringan Dokumentasi &amp; Informasi Hukum
                                                        Kemendikbud</p>
                                                    <div class="ks-portal-footer" style="color:#7c3aed;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <a href="https://www.kemendikdasmen.go.id" target="_blank"
                                                rel="noopener noreferrer" class="ks-portal-card"
                                                style="--cat-color:#7c3aed;">
                                                <div class="ks-portal-icon-wrap"
                                                    style="background:rgba(124,58,237,0.1);color:#7c3aed;">
                                                    <i class="fas fa-landmark"></i>
                                                </div>
                                                <div class="ks-portal-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="ks-portal-name">Kemendikdasmen</span>
                                                        <span class="ks-portal-badge"
                                                            style="background:rgba(124,58,237,0.12);color:#7c3aed;">Resmi</span>
                                                    </div>
                                                    <p class="ks-portal-desc">Situs resmi Kementerian Pendidikan Dasar &amp;
                                                        Menengah</p>
                                                    <div class="ks-portal-footer" style="color:#7c3aed;">
                                                        <span>Buka Portal</span>
                                                        <i class="fas fa-arrow-right ml-1"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                

            </div>
        </section>
    </div>
@endsection

@push('scripts_custom')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        
        
        
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent =
                now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);

        
        
        
        document.addEventListener('DOMContentLoaded', function () {
            fetch('{{ route('api.kepsek.stats') }}')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    renderWidgets(data);
                    renderChart(data.rekap_chart);
                    renderPerangkatPie(data.perangkat_pie); 
                    return fetch('{{ route('api.kepsek.ujian') }}');
                })
                .then(r => r ? r.json() : null)
                .then(ujianData => {
                    if (ujianData && ujianData.success) renderJadwalTable(ujianData.jadwals);
                })
                .catch(err => console.error('Dashboard error:', err));
        });

        
        function renderPerangkatPie(pie) {
            if (!pie) return;
            const ctx = document.getElementById('perangkatPieChart');
            if (!ctx) return;
            const { lengkap, kurang } = pie;
            const total = lengkap + kurang;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Lengkap', 'Perlu Perbaikan'],
                    datasets: [{
                        data: [lengkap, kurang],
                        backgroundColor: ['#16a34a', '#dc2626'],
                        hoverOffset: 6,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });
            document.getElementById('perangkatPieLegend').innerHTML =
                `<span style="color:#16a34a"><b>${lengkap}</b> Lengkap</span>
                 &nbsp;|&nbsp;
                 <span style="color:#dc2626"><b>${kurang}</b> Perlu Perbaikan</span>
                 <br><small class="text-muted">Total: ${total} guru</small>`;
        }

        function renderWidgets(d) {
            const widgets = [
                { icon: 'fa-chalkboard-teacher', color: 'info', val: d.total_guru, label: 'Total Guru', href: '{{ route('kepsek.kinerja_guru') }}' },
                { icon: 'fa-user-graduate', color: 'success', val: d.total_siswa, label: 'Akademik & Presensi', href: '{{ route('kepsek.laporan_akademik') }}#tabPresensi' },
                { icon: 'fa-file-alt', color: 'warning', val: d.pending_soal, label: 'Laporan Eksekutif', href: '{{ route('kepsek.kinerja_guru') }}' },
                { icon: 'fa-calendar-check', color: 'primary', val: d.ujian_hari_ini, label: 'Ujian Hari Ini', href: '{{ route('kepsek.ujian_nilai') }}' },
            ];

            let html = '';
            widgets.forEach(w => {
                html += `<div class="col-lg-3 col-sm-6">
                        <a href="${w.href}" style="text-decoration:none">
                        <div class="small-box bg-${w.color} kepsek-stat-card">
                            <div class="inner">
                                <h3>${w.val}</h3>
                                <p>${w.label}</p>
                            </div>
                            <div class="icon"><i class="fas ${w.icon}"></i></div>
                            <div class="small-box-footer">Lihat Detail &nbsp;<i class="fas fa-arrow-circle-right"></i></div>
                        </div>
                        </a></div>`;
            });
            const wl = document.getElementById('widgetLoader');
            if (wl) wl.remove();
            document.getElementById('widgetRow').innerHTML = html;
        }

        
        
        
        function renderChart(rekap) {
            const ctx = document.getElementById('aktivitasChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: rekap.labels,
                    datasets: [{
                        label: 'Total Interaksi Siswa',
                        data: rekap.values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        tension: 0.4,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 5,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        
        
        
        function renderJadwalTable(jadwals) {
            const tbody = document.getElementById('jadwalTable');
            if (!jadwals || jadwals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-calendar-times mr-1"></i>Belum ada jadwal ujian mendatang.</td></tr>';
                return;
            }
            const statusMap = { berlangsung: 'badge-berlangsung', mendatang: 'badge-mendatang', selesai_hari_ini: 'badge-selesai' };
            let html = '';
            jadwals.slice(0, 10).forEach(j => {
                const cls = statusMap[j.status_ujian] || 'badge-secondary';
                html += `<tr>
                        <td><strong>${j.mapel}</strong></td>
                        <td><span class="badge badge-light">${j.kelas}</span></td>
                        <td>${j.tanggal_ujian}</td>
                        <td>${j.jam_mulai.slice(0, 5)}–${j.jam_selesai.slice(0, 5)}</td>
                        <td><span class="badge ${cls} text-white px-2 py-1">${j.status_ujian.toUpperCase().replace('_', ' ')}</span></td>
                    </tr>`;
            });
            tbody.innerHTML = html;
        }
    </script>
@endpush