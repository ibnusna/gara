@extends('layouts.kepsek', ['active_menu' => 'ujian_nilai'])

@section('title', 'Monitoring Ujian & Nilai | Kepala Sekolah')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }

        .status-berlangsung {
            background-color: #10b981;
            color: white;
        }

        .status-mendatang {
            background-color: #3b82f6;
            color: white;
        }

        .status-selesai {
            background-color: #6b7280;
            color: white;
        }
    </style>
@endpush

@push('scripts_vendor')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">
                    <i class="fas fa-chart-bar mr-2" style="color:#1a3c6e"></i>Monitoring Ujian & Nilai
                </h1>
                <p class="text-muted mb-0">Pantau pelaksanaan ujian hari ini dan distribusi nilai akhir siswa.</p>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">


                
                <div class="card ks-section-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Jadwal Pelaksanaan Ujian</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Mata Pelajaran</th>
                                        <th>Kelas</th>
                                        <th>Jenis</th>
                                        <th>Tanggal</th>
                                        <th>Pukul</th>
                                        <th>Durasi</th>
                                        <th>Mode Submit</th>
                                        <th>Status Pelaksanaan</th>
                                    </tr>
                                </thead>
                                <tbody id="jadwalBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i>
                                            Memuat jadwal...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="card ks-section-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Distribusi & Rata-rata Nilai Asesmen
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tblDistribusi" class="table table-bordered table-sm table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Kelas</th>
                                        <th>Jenis Ujian</th>
                                        <th>Jml Peserta</th>
                                        <th>Rata-rata Kelas</th>
                                        <th>Nilai Tertinggi</th>
                                        <th>Nilai Terendah</th>
                                    </tr>
                                </thead>
                                <tbody id="distribusiBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i>
                                            Memuat data...</td>
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
        let isLocked = false;

        $(document).ready(function () {
            loadData();
        });

        function loadData() {
            fetch('{{ route('api.kepsek.ujian') }}')
                .then(r => r.json())
                .then(res => {
                    if (!res.success) throw new Error(res.message);

                    
                    renderJadwal(res.jadwals);

                    
                    renderDistribusi(res.distribusi);
                })
                .catch(err => {
                    Swal.fire('Error', 'Gagal memuat data: ' + err.message, 'error');
                    $('#jadwalBody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat.</td></tr>');
                });
        }


        
        function renderJadwal(list) {
            if (!list || !list.length) {
                $('#jadwalBody').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><br>Tidak ada jadwal ujian dalam 90 hari terakhir.</td></tr>');
                return;
            }

            const statusMap = {
                'berlangsung': { cls: 'status-berlangsung', text: 'Berlangsung' },
                'mendatang': { cls: 'status-mendatang', text: 'Mendatang' },
                'selesai_hari_ini': { cls: 'status-selesai', text: 'Selesai Hari Ini' },
                'selesai': { cls: 'status-selesai', text: 'Selesai' },
            };

            let html = '';
            list.forEach(j => {
                const badge = statusMap[j.status_ujian] || { cls: 'badge-secondary', text: j.status_ujian };
                html += `<tr>
                            <td><strong>${j.mapel}</strong></td>
                            <td><span class="badge badge-light px-2">${j.kelas}</span></td>
                            <td><code>${j.jenis_asesmen}</code></td>
                            <td>${j.tanggal_ujian}</td>
                            <td>${j.jam_mulai.slice(0, 5)} – ${j.jam_selesai.slice(0, 5)}</td>
                            <td>${j.durasi} <small>menit</small></td>
                            <td><span class="badge ${j.mode_submit === 'SERENTAK' ? 'badge-danger' : 'badge-info'}">${j.mode_submit}</span></td>
                            <td><span class="badge ${badge.cls} status-badge">${badge.text.toUpperCase()}</span></td>
                        </tr>`;
            });
            $('#jadwalBody').html(html);
        }

        
        function renderDistribusi(list) {
            if ($.fn.DataTable.isDataTable('#tblDistribusi')) {
                $('#tblDistribusi').DataTable().destroy();
            }

            if (!list || !list.length) {
                $('#distribusiBody').html('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data nilai asesmen.</td></tr>');
                return;
            }

            let html = '';
            list.forEach((d, i) => {
                const rata = parseFloat(d.rata_rata);
                const rataColor = rata >= 75 ? '#16a34a' : rata >= 60 ? '#d97706' : '#dc2626';

                html += `<tr>
                            <td>${i + 1}</td>
                            <td><strong>${d.mapel}</strong></td>
                            <td><span class="badge badge-light">${d.kelas}</span></td>
                            <td>${d.jenis_asesmen}</td>
                            <td class="text-center">${d.jumlah_peserta} <small>siswa</small></td>
                            <td class="text-center font-weight-bold" style="color:${rataColor}; font-size:1.1rem">${d.rata_rata}</td>
                            <td class="text-center text-success">${d.tertinggi}</td>
                            <td class="text-center text-danger">${d.terendah}</td>
                        </tr>`;
            });

            $('#distribusiBody').html(html);
            $('#tblDistribusi').DataTable({
                pageLength: 10,
                responsive: true,
                language: { search: 'Cari:', emptyTable: 'Belum ada data nilai.' }
            });
        }
    </script>
@endpush