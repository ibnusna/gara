@extends('layouts.kepsek', ['active_menu' => 'kinerja_guru'])

@section('title', 'Kinerja Guru | Kepala Sekolah')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts_vendor')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">
                    <i class="fas fa-chalkboard-teacher mr-2" style="color:#1a3c6e"></i>Pantau Kinerja Guru
                </h1>
                <p class="text-muted mb-0">
                    Monitoring kepatuhan upload materi, pemberian tugas, dan aktivitas forum.
                    <span class="badge badge-warning ml-1"><i class="fas fa-eye mr-1"></i>Read-Only</span>
                </p>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">

                
                <div class="card ks-section-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Perbandingan Upload Materi vs Tugas vs Soal Tervalidasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position:relative; height:260px;">
                            <canvas id="kinerjaChart"></canvas>
                        </div>
                        <div id="chartEmpty" class="text-center text-muted mt-3" style="display:none">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>Belum ada data untuk ditampilkan.
                        </div>
                    </div>
                </div>

                
                <div class="card ks-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Tabel Kinerja Guru</h3>
                        <span class="badge badge-secondary">Hanya Pantau — Tidak dapat diedit</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tblKinerja" class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Guru</th>
                                        <th title="Jumlah kelas unik yang diajarkan">
                                            <i class="fas fa-school mr-1"></i>Kelas Diajar
                                        </th>
                                        <th title="Jumlah file materi/RPP yang diupload">
                                            <i class="fas fa-book mr-1"></i>Materi Diupload
                                        </th>
                                        <th title="Jumlah tugas yang diberikan ke siswa">
                                            <i class="fas fa-tasks mr-1"></i>Tugas Diberikan
                                        </th>
                                        <th title="Soal yang sudah disetujui Kepsek">
                                            <i class="fas fa-check-circle mr-1 text-success"></i>Soal Validated
                                        </th>
                                        <th title="Thread diskusi di forum oleh guru ini">
                                            <i class="fas fa-comments mr-1"></i>Forum Aktif
                                        </th>
                                        <th>Indikator Kepatuhan</th>
                                    </tr>
                                </thead>
                                <tbody id="tblBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts_custom')
    <script>
        $(document).ready(function () {
            fetch('{{ route('api.kepsek.kinerja') }}')
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        $('#tblBody').html('<tr><td colspan="8" class="text-danger text-center">Gagal memuat data: ' + (res.message || '') + '</td></tr>');
                        return;
                    }
                    const data = res.data;
                    if (!data.length) {
                        $('#tblBody').html('<tr><td colspan="8" class="text-center text-muted">Belum ada data guru.</td></tr>');
                        document.getElementById('chartEmpty').style.display = 'block';
                        return;
                    }

                    
                    let html = '';
                    data.forEach((g, i) => {
                        
                        const hasMateri = g.jumlah_materi > 0;
                        const hasTugas = g.jumlah_tugas > 0;
                        const score = (hasMateri ? 50 : 0) + (hasTugas ? 30 : 0) + (g.jumlah_forum > 0 ? 20 : 0);
                        const barColor = score >= 70 ? '#16a34a' : score >= 40 ? '#d97706' : '#dc2626';
                        const scoreLabel = score >= 70 ? 'Baik' : score >= 40 ? 'Cukup' : 'Perlu Perhatian';

                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>
                                <strong>${g.nama_lengkap}</strong><br>
                                <small class="text-muted">${g.nip !== '-' ? 'NIP: ' + g.nip : 'NIP: -'}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info px-2">${g.jumlah_kelas}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge px-2" style="background:${hasMateri ? '#2563eb' : '#9ca3af'};color:#fff">
                                    ${g.jumlah_materi}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge px-2" style="background:${hasTugas ? '#16a34a' : '#9ca3af'};color:#fff">
                                    ${g.jumlah_tugas}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success px-2">${g.soal_validated}</span>
                                ${g.soal_submitted > 0 ? `<span class="badge badge-warning px-1 ml-1" title="${g.soal_submitted} soal menunggu">+${g.soal_submitted}</span>` : ''}
                            </td>
                            <td class="text-center">
                                <span class="badge px-2" style="background:${g.jumlah_forum > 0 ? '#7c3aed' : '#9ca3af'};color:#fff">
                                    ${g.jumlah_forum}
                                </span>
                            </td>
                            <td style="min-width:140px">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="progress flex-grow-1 mr-2" style="height:8px;border-radius:4px">
                                        <div class="progress-bar" style="width:${score}%;background:${barColor};border-radius:4px"></div>
                                    </div>
                                    <small style="color:${barColor};font-weight:600;white-space:nowrap">${score}% <em style="font-weight:400">${scoreLabel}</em></small>
                                </div>
                            </td>
                        </tr>`;
                    });

                    $('#tblBody').html(html);
                    $('#tblKinerja').DataTable({
                        pageLength: 15,
                        responsive: true,
                        language: {
                            search: 'Cari:',
                            emptyTable: 'Belum ada data',
                            zeroRecords: 'Data tidak ditemukan',
                            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ guru',
                            paginate: { previous: 'Prev', next: 'Next' }
                        }
                    });

                    
                    const names = data.map(g => g.nama_lengkap.split(' ')[0]);
                    new Chart(document.getElementById('kinerjaChart'), {
                        type: 'bar',
                        data: {
                            labels: names,
                            datasets: [
                                {
                                    label: 'Materi Diupload',
                                    data: data.map(g => g.jumlah_materi),
                                    backgroundColor: 'rgba(37,99,235,0.75)',
                                    borderRadius: 5,
                                },
                                {
                                    label: 'Tugas Diberikan',
                                    data: data.map(g => g.jumlah_tugas),
                                    backgroundColor: 'rgba(22,163,74,0.75)',
                                    borderRadius: 5,
                                },
                                {
                                    label: 'Soal Validated',
                                    data: data.map(g => g.soal_validated),
                                    backgroundColor: 'rgba(124,58,237,0.75)',
                                    borderRadius: 5,
                                },
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                })
                .catch(err => {
                    $('#tblBody').html('<tr><td colspan="8" class="text-danger text-center">Error: ' + err.message + '</td></tr>');
                });
        });
    </script>
@endpush