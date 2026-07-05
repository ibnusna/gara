@extends('layouts.kepsek', ['active_menu' => 'laporan_akademik'])

@section('title', 'Laporan Eksekutif | Kepala Sekolah')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        .dt-buttons {
            margin-bottom: 15px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dt-buttons .btn {
            border-radius: 20px;
            font-weight: 600;
            padding: 4px 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        
        @media print {
            .main-sidebar,
            .main-header,
            .content-header,
            .dt-buttons,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_info,
            .dataTables_paginate,
            nav,
            .navbar,
            .sidebar,
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            table {
                width: 100% !important;
                font-size: 10px;
            }
            th, td {
                padding: 4px 6px !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0"><i class="fas fa-file-alt mr-2" style="color:#1a3c6e"></i>Laporan Eksekutif Formal</h1>
                <p class="text-muted mb-0">Laporan cetak berstandar dengan kop surat dan lembar pengesahan otomatis. <span
                        class="badge badge-warning ml-1">Read-Only</span></p>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card ks-section-card">
                    <div class="card-header p-0" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <ul class="nav nav-tabs" id="reportTabs" style="border-bottom: 0;">
                            <li class="nav-item m-1">
                                <a class="nav-link active px-4 py-2" data-toggle="tab" href="#tabJurnal"><i
                                        class="fas fa-book-open mr-1"></i>Jurnal Mengajar</a>
                            </li>
                            <li class="nav-item m-1">
                                <a class="nav-link px-4 py-2" data-toggle="tab" href="#tabPerangkat"><i
                                        class="fas fa-folder-open mr-1"></i>Kelengkapan Perangkat</a>
                            </li>
                            <li class="nav-item m-1">
                                <a class="nav-link px-4 py-2" data-toggle="tab" href="#tabPresensi"><i
                                        class="fas fa-users mr-1"></i>Rekap Presensi & Akademik</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="tabJurnal">
                                <h5 class="mb-3 text-primary"><i class="fas fa-clipboard-list mr-2"></i>Laporan Supervisi
                                    Jurnal Mengajar Guru</h5>
                                <div class="table-responsive">
                                    <table id="tblJurnal" class="table table-bordered table-striped w-100 text-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Hari/Tanggal</th>
                                                <th>Nama Guru</th>
                                                <th>Kelas & Mapel</th>
                                                <th>Pokok Bahasan / Kegiatan</th>
                                                <th>Catatan Pelaksanaan</th>
                                                <th>Siswa Absen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($jurnalMengajar as $idx => $jm)
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($jm->tanggal)->translatedFormat('l, d F Y') }}<br><small
                                                            class="text-muted">{{ $jm->jam }}</small></td>
                                                    <td><strong>{{ $jm->nama_guru }}</strong><br><small class="text-muted">NIP:
                                                            {{ $jm->nip ?? '-' }}</small></td>
                                                    <td><span
                                                            class="badge badge-info">{{ $jm->nama_kelas }}</span><br><small>{{ $jm->nama_mapel }}</small>
                                                    </td>
                                                    <td>{{ $jm->rencana_kegiatan ?? '-' }}</td>
                                                    <td>{{ $jm->catatan_pelaksanaan ?? '-' }}</td>
                                                    <td>{{ $jm->siswa_tidak_hadir ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="tabPerangkat">
                                <h5 class="mb-3 text-primary"><i class="fas fa-check-double mr-2"></i>Laporan Kelengkapan
                                    Perangkat Ajar & Asesmen</h5>
                                <div class="table-responsive">
                                    <table id="tblPerangkat" class="table table-bordered table-striped w-100 text-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>Nama Guru & NIP</th>
                                                <th>Mata Pelajaran</th>
                                                <th class="text-center">Modul/Materi (RPP)</th>
                                                <th class="text-center">Bank Soal (Valid/Draft)</th>
                                                <th class="text-center">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($kelengkapanAjar as $idx => $k)
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td><strong>{{ $k->nama_guru }}</strong><br><small class="text-muted">NIP:
                                                            {{ $k->nip ?? '-' }}</small></td>
                                                    <td>{{ $k->nama_mapel }}</td>
                                                    <td class="text-center"><span class="badge badge-secondary"
                                                            style="font-size:14px">{{ $k->rpp_count }} Dokumen</span></td>
                                                    <td class="text-center">
                                                        <span class="text-success font-weight-bold">{{ $k->soal_validated }}
                                                            Valid</span> /
                                                        <span class="text-secondary">{{ $k->soal_draft }} Draft</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($k->keterangan == 'Lengkap')
                                                            <span class="badge badge-success px-3 py-2"><i
                                                                    class="fas fa-check mr-1"></i>Lengkap</span>
                                                        @else
                                                            <span class="badge badge-warning px-3 py-2"><i
                                                                    class="fas fa-exclamation-triangle mr-1"></i>Perlu
                                                                Perbaikan</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="tabPresensi">
                                <h5 class="mb-1 text-primary">
                                    <i class="fas fa-users mr-2"></i>Rekapitulasi Presensi &amp; Nilai Akademik per Kelas
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Data dikelompokkan per kelas — total akumulasi dari seluruh mata pelajaran sepanjang
                                    tahun ajaran.
                                </p>
                                <div class="table-responsive">
                                    <table id="tblPresensi" class="table table-bordered table-striped w-100 text-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th rowspan="2" class="align-middle text-center" width="4%">No</th>
                                                <th rowspan="2" class="align-middle text-center">Kelas</th>
                                                <th rowspan="2" class="align-middle text-center">Jumlah Siswa</th>
                                                <th rowspan="2" class="align-middle text-center">Total Pertemuan</th>
                                                <th colspan="4" class="text-center bg-info text-white">Rekap Kehadiran
                                                    (Total)</th>
                                                <th rowspan="2" class="align-middle text-center">% Hadir</th>
                                                <th rowspan="2" class="align-middle text-center">Rata-rata Tugas</th>
                                                <th rowspan="2" class="align-middle text-center">Rata-rata Asesmen</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center text-success bg-light">H</th>
                                                <th class="text-center text-warning bg-light">S</th>
                                                <th class="text-center text-primary bg-light">I</th>
                                                <th class="text-center text-danger bg-light">A</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rekapAkademik as $idx => $ra)
                                                @php
                                                    $pct = $ra->pct_hadir ?? 0;
                                                    $pctColor = $pct >= 90 ? '#16a34a' : ($pct >= 75 ? '#d97706' : '#dc2626');
                                                    $tugasColor = $ra->rata_tugas >= 75 ? '#16a34a' : '#dc2626';
                                                    $asesmenColor = $ra->rata_asesmen >= 75 ? '#16a34a' : ($ra->rata_asesmen == 0 ? '#9ca3af' : '#dc2626');
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $idx + 1 }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-primary px-3 py-1"
                                                            style="font-size:0.9rem;">{{ $ra->nama_kelas }}</span>
                                                    </td>
                                                    <td class="text-center font-weight-bold">{{ $ra->jumlah_siswa }}</td>
                                                    <td class="text-center">{{ $ra->total_pertemuan }}</td>
                                                    <td class="text-center font-weight-bold text-success">{{ $ra->hadir }}</td>
                                                    <td class="text-center font-weight-bold text-warning">{{ $ra->sakit }}</td>
                                                    <td class="text-center font-weight-bold text-primary">{{ $ra->izin }}</td>
                                                    <td class="text-center font-weight-bold text-danger">{{ $ra->alpa }}</td>
                                                    <td class="text-center">
                                                        <span class="font-weight-bold"
                                                            style="color:{{ $pctColor }};">{{ $pct }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="font-weight-bold" style="color:{{ $tugasColor }};">
                                                            {{ $ra->rata_tugas > 0 ? $ra->rata_tugas : '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="font-weight-bold" style="color:{{ $asesmenColor }};">
                                                            {{ $ra->rata_asesmen > 0 ? $ra->rata_asesmen : '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                        Belum ada data kelas yang tersedia.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts_vendor')
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
@endpush

@push('scripts_custom')
    <script>
        $(document).ready(function () {
            
            $('#reportTabs .nav-link').on('shown.bs.tab', function (e) {
                $('#reportTabs .nav-link').css('background-color', 'transparent').css('color', '#495057');
                $(e.target).css('background-color', '#1a3c6e').css('color', 'white');
            });
            $('#reportTabs .nav-link.active').css('background-color', '#1a3c6e').css('color', 'white');

            const namaSekolah = '{{ \App\Models\AppSetting::where("setting_key", "sekolah_nama")->value("setting_value") ?? "GARUDA AKADEMI" }}';
            const taSemester = 'Tahun Ajaran {{ \App\Models\AppSetting::where("setting_key", "tahun_ajaran")->value("setting_value") ?? "2024/2025" }} | Semester {{ \App\Models\AppSetting::where("setting_key", "semester")->value("setting_value") ?? "Ganjil" }}';

            function getExportButtons(docTitle, pdfOrientation = 'landscape', roleKiri = 'Waka Kurikulum') {
                return [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: docTitle
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF Formal',
                        className: 'btn btn-danger btn-sm',
                        title: '', 
                        orientation: pdfOrientation,
                        pageSize: 'A4',
                        customize: function (doc) {
                            
                            doc.content.splice(0, 0, {
                                text: namaSekolah.toUpperCase(),
                                fontSize: 18,
                                bold: true,
                                alignment: 'center',
                                margin: [0, 0, 0, 5]
                            });
                            doc.content.splice(1, 0, {
                                text: taSemester,
                                fontSize: 12,
                                alignment: 'center',
                                margin: [0, 0, 0, 10]
                            });
                            doc.content.splice(2, 0, {
                                canvas: [{ type: 'line', x1: 0, y1: 0, x2: pdfOrientation === 'landscape' ? 760 : 515, y2: 0, lineWidth: 2 }]
                            });
                            doc.content.splice(3, 0, {
                                text: docTitle.toUpperCase(),
                                fontSize: 14,
                                bold: true,
                                alignment: 'center',
                                margin: [0, 15, 0, 15]
                            });

                            
                            doc.styles.tableHeader.fillColor = '#1a3c6e';
                            doc.styles.tableHeader.color = 'white';

                            
                            let dateStr = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
                            doc.content.push({
                                columns: [
                                    {
                                        text: roleKiri + '\n\n\n\n\n_________________________\nNIP. ....................',
                                        alignment: 'center',
                                        margin: [40, 40, 0, 0]
                                    },
                                    {
                                        text: 'Bogor, ' + dateStr + '\nKepala Sekolah\n\n\n\n\n_________________________\nNIP. ....................',
                                        alignment: 'center',
                                        margin: [0, 40, 40, 0]
                                    }
                                ]
                            });
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Cetak',
                        className: 'btn btn-info btn-sm',
                        title: docTitle,
                        customize: function (win) {
                            $(win.document.head).find('link[rel="stylesheet"], style').remove();
                            $(win.document.head).append('<style>@page{size:landscape;} body{font-family:sans-serif;padding:20px;background:#fff;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;color:#000;} th{background-color:#f8f9fa;color:#000;} h1{text-align:center;font-size:20px;margin-bottom:20px;color:#000;}</style>');
                        }
                    }
                ];
            }

            
            console.log("Initializing DataTables Kepsek (Final Attempt)...");

            
            var tblJurnal = $('#tblJurnal').DataTable({
                responsive: true,
                buttons: getExportButtons('REKAPITULASI JURNAL HARIAN GURU', 'landscape', 'Waka Kurikulum'),
                language: { search: "Filter Jurnal:" }
            });
            tblJurnal.buttons().container().appendTo('#tblJurnal_wrapper .col-md-6:eq(0)');

            
            var tblPerangkat = $('#tblPerangkat').DataTable({
                responsive: true,
                buttons: getExportButtons('LAPORAN KELENGKAPAN PERANGKAT AJAR DAN BANK SOAL', 'landscape', 'Waka Kurikulum'),
                language: { search: "Filter Perangkat:" }
            });
            tblPerangkat.buttons().container().appendTo('#tblPerangkat_wrapper .col-md-6:eq(0)');

            
            var tblPresensi = $('#tblPresensi').DataTable({
                responsive: true,
                buttons: getExportButtons('REKAPITULASI PRESENSI DAN NILAI AKADEMIK KELAS', 'portrait', 'Wali Kelas'),
                language: { search: "Filter Siswa/Kelas:" }
            });
            tblPresensi.buttons().container().appendTo('#tblPresensi_wrapper .col-md-6:eq(0)');

            console.log("DataTables Initialized. Check container append results.");
            
            
            var hash = window.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }

            
            @if(count($rekapAkademik) > 0)
            (function () {
                const labels    = @json($rekapAkademik->pluck('nama_kelas'));
                const rataTugas = @json($rekapAkademik->pluck('rata_tugas'));
                const rataAsmn  = @json($rekapAkademik->pluck('rata_asesmen'));
                const KKM       = 75;

                const tugasBg  = rataTugas.map(v => v >= KKM ? 'rgba(22,163,74,0.8)'   : 'rgba(220,38,38,0.8)');
                const asmBg    = rataAsmn.map(v  => v >= KKM ? 'rgba(37,99,235,0.8)'   : 'rgba(234,179,8,0.8)');

                
                const tabPane = document.getElementById('tabPresensi');
                if (tabPane) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'mt-4';
                    wrapper.innerHTML = `
                        <h6 class="text-primary font-weight-bold">
                            <i class="fas fa-chart-bar mr-1"></i>Grafik Rata-rata Nilai per Kelas
                            <small class="text-muted ml-2">(Garis merah = KKM ${KKM})</small>
                        </h6>
                        <div style="position:relative; height:280px;">
                            <canvas id="kkmBarChart"></canvas>
                        </div>`;
                    tabPane.appendChild(wrapper);

                    const ctx = document.getElementById('kkmBarChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                { label: 'Rata Tugas', data: rataTugas, backgroundColor: tugasBg, borderRadius: 4 },
                                { label: 'Rata Asesmen', data: rataAsmn, backgroundColor: asmBg, borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: {
                                    beginAtZero: true, max: 100,
                                    ticks: { stepSize: 10 },
                                    afterDataLimits: function(axis) {
                                        axis.max = 100;
                                    }
                                }
                            },
                            annotation: {
                                annotations: [{
                                    type: 'line',
                                    yMin: KKM, yMax: KKM,
                                    borderColor: '#dc2626',
                                    borderWidth: 2,
                                    borderDash: [6, 4],
                                    label: { enabled: true, content: 'KKM', position: 'end' }
                                }]
                            }
                        }
                    });
                }
            })();
            @endif
        });
    </script>
@endpush