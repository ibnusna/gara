@extends('layouts.operator', ['page_title' => 'Hasil Ujian | Garuda Akademi', 'active_menu' => 'ujian', 'active_submenu' => 'hasil_ujian'])

@section('title', 'Hasil Ujian | Garuda Akademi')

@push('styles')
<style>
    .btn-download-group .btn { border-radius: 20px; font-size: 0.82rem; padding: 4px 14px; font-weight: 600; }
    .btn-download-group { display: flex; gap: 6px; flex-wrap: wrap; }
</style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-poll-h mr-2 text-success"></i>
                            Hasil Ujian
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('operator.asesmen.dashboard') }}">Asesmen</a></li>
                            <li class="breadcrumb-item active">Hasil Ujian</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                
                <div class="row mb-3 no-print">
                    <div class="col-lg-6 col-md-8">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Pilih Sesi Ujian</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="text-muted small font-weight-bold text-uppercase">Jadwal Ujian</label>
                                    <select id="selJadwal" class="form-control" onchange="loadHasil()">
                                        <option value="">-- Pilih Jadwal --</option>
                                    </select>
                                    <small class="form-text text-muted">Hanya menampilkan jadwal yang sudah ada
                                        token.</small>
                                </div>
                                <button onclick="loadHasil()" class="btn btn-success btn-sm mr-2">
                                    <i class="fas fa-search mr-1"></i> Tampilkan Hasil
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="hasilPanel" style="display:none;">

                    
                    <div class="d-none d-print-block text-center mb-4">
                        <h2 class="font-weight-bold text-uppercase" id="printTitle">REKAP NILAI UJIAN</h2>
                        <h4 id="printSubtitle">—</h4>
                        <hr>
                    </div>

                    
                    <div class="row mb-3 no-print">
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Peserta Submit</span>
                                    <span class="info-box-number" id="statSubmit">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rata-rata Nilai</span>
                                    <span class="info-box-number" id="statAvg">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nilai Tertinggi</span>
                                    <span class="info-box-number" id="statMax">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Di Bawah KKM (75)</span>
                                    <span class="info-box-number" id="statBelowKkm">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header no-print">
                                    <h3 class="card-title" id="hasilTitle">
                                        <i class="fas fa-table mr-2"></i>Rekap Nilai
                                    </h3>
                                    <div class="card-tools d-flex align-items-center" style="gap:6px;">
                                        <button onclick="kirimHasilKeGuru()" class="btn btn-sm btn-primary no-print"
                                            id="btnKirimGuruCard" style="display:none;">
                                            <i class="fas fa-paper-plane mr-1"></i> Kirim Hasil ke Guru
                                        </button>
                                        <button onclick="hapusSemuaHasil()" class="btn btn-sm btn-danger no-print"
                                            id="btnHapusSemuaCard" style="display:none;">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Jawaban
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover mb-0" id="hasilTable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th class="text-center">No</th>
                                                    <th>NIS</th>
                                                    <th>Nama Siswa</th>
                                                    <th class="text-center">Nilai</th>
                                                    <th class="text-center">Waktu Selesai</th>
                                                    <th class="text-center">Keterangan</th>
                                                    <th class="text-center no-print">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="hasilBody">
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">Pilih jadwal untuk
                                                        memuat data.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>
    </div>

    <style>
        @media print {

            .no-print,
            .main-sidebar,
            .main-header,
            .main-footer,
            .content-header,
            .breadcrumb {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #333 !important;
            }

            body {
                font-size: 12px;
            }
        }
        
        
        .dt-buttons {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 20px;
            padding: 4px 12px;
            margin-left: 8px;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #0b57d0;
            box-shadow: 0 0 0 0.2rem rgba(11, 87, 208, 0.15);
        }
    </style>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
@endsection

@push('scripts')
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script>
        const API = '{{ route("operator.api.asesmen") }}';
        const PRE_SELECT = {{ $id_jadwal ?? 0 }};
        const DAYS_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        async function post(action, data = {}) {
            const fd = new FormData();
            fd.append('action', action);
            fd.append('_token', '{{ csrf_token() }}');
            for (const k in data) fd.append(k, data[k]);
            const r = await fetch(API, {
                method: 'POST', body: fd, headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            return r.json();
        }

        
        async function init() {
            const res = await post('get_jadwal_list');
            const sel = document.getElementById('selJadwal');
            sel.innerHTML = '<option value="">-- Pilih Jadwal --</option>';

            if (!res.success || !res.data.length) return;

            
            const usable = res.data.filter(r => r.token);
            usable.forEach(r => {
                const tgl = new Date(r.tanggal_ujian + 'T00:00:00');
                const label = `[${DAYS_ID[tgl.getDay()]} ${r.tanggal_ujian}] ${r.mapel || '—'} — Kelas ${r.kelas || '?'}  (${r.jam_mulai}–${r.jam_selesai})`;
                const opt = new Option(label, r.id_jadwal);
                sel.appendChild(opt);
            });

            
            if (PRE_SELECT) {
                sel.value = PRE_SELECT;
                if (sel.value) loadHasil();
            }
        }

        let dataTable = null;

        function getExportButtons(titleText) {
            return [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-success btn-sm px-3 mr-2 rounded-pill shadow-sm',
                    title: titleText,
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5 ],
                        format: {
                            body: function ( data, row, column, node ) {
                                let cleanData = data.replace(/<[^>]*>?/gm, ''); 
                                
                                if (column === 1) {
                                    return "'" + cleanData;
                                }
                                return cleanData;
                            }
                        }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-danger btn-sm px-3 mr-2 rounded-pill shadow-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: titleText,
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5 ]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Cetak',
                    className: 'btn btn-info btn-sm px-3 rounded-pill shadow-sm',
                    title: titleText,
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5 ]
                    },
                    customize: function(win) {
                        $(win.document.head).find('link[rel="stylesheet"], style').remove();
                        $(win.document.head).append('<style>@page{size:landscape;} body{font-family:sans-serif;padding:20px;background:#fff;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;color:#000;} th{background-color:#f8f9fa;color:#000;} h1{text-align:center;font-size:20px;margin-bottom:20px;color:#000;}</style>');
                    }
                }
            ];
        }

        
        async function loadHasil() {
            const idJadwal = document.getElementById('selJadwal').value;
            if (!idJadwal) {
                document.getElementById('hasilPanel').style.display = 'none';
                return;
            }

            
            window.history.replaceState(null, null, "?id_jadwal=" + idJadwal);

            
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

            const tbody = document.getElementById('hasilBody');
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>`;
            document.getElementById('hasilPanel').style.display = 'block';

            const res = await post('get_hasil_by_jadwal', { id_jadwal: idJadwal });
            if (!res.success) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${res.message}</td></tr>`;
                return;
            }

            const jadwal = res.data.jadwal;
            const hasil = res.data.hasil;

            
            const tgl = new Date(jadwal.tanggal_ujian + 'T00:00:00');
            const tglStr = `${DAYS_ID[tgl.getDay()]}, ${tgl.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
            document.getElementById('printTitle').textContent = `REKAP NILAI — ${jadwal.mapel} — KELAS ${jadwal.kelas}`;
            document.getElementById('printSubtitle').textContent = `${tglStr} | ${jadwal.jam_mulai} – ${jadwal.jam_selesai} | ${jadwal.jenis_asesmen}`;
            document.getElementById('hasilTitle').innerHTML = `<i class="fas fa-table mr-2"></i>Rekap Nilai: <strong>${jadwal.mapel}</strong> — Kelas <strong>${jadwal.kelas}</strong>`;

            if (!hasil.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox mr-1"></i> Belum ada siswa yang submit.</td></tr>`;
                resetStats();
                document.getElementById('btnHapusSemuaCard').style.display = 'none';
                document.getElementById('btnKirimGuruCard').style.display = 'none';
                return;
            }

            
            let total = 0, max = 0, belowKkm = 0;
            hasil.forEach(h => {
                const s = parseFloat(h.skor_akhir);
                total += s;
                if (s > max) max = s;
                if (s < 75) belowKkm++;
            });
            document.getElementById('statSubmit').textContent = hasil.length;
            document.getElementById('statAvg').textContent = (total / hasil.length).toFixed(1);
            document.getElementById('statMax').textContent = max.toFixed(1);
            document.getElementById('statBelowKkm').textContent = belowKkm;

            
            tbody.innerHTML = hasil.map((h, i) => {
                const skor = parseFloat(h.skor_akhir).toFixed(1);
                const ket = parseFloat(skor) >= 75
                    ? '<span class="badge badge-success">Tuntas</span>'
                    : '<span class="badge badge-danger">Tidak Tuntas</span>';
                const clsSkor = parseFloat(skor) >= 75 ? 'text-success' : 'text-danger';

                const fmt = ts => ts ? new Date(ts).toLocaleString('id-ID') : '—';

                return `<tr>
                <td class="text-center">${i + 1}</td>
                <td>${h.nis || '—'}</td>
                <td>${h.nama || '—'}</td>
                <td class="text-center font-weight-bold ${clsSkor}" style="font-size:1.1rem;">${skor}</td>
                <td class="text-center"><small>${fmt(h.waktu_selesai)}</small></td>
                <td class="text-center">${ket}</td>
                <td class="text-center no-print">
                    <button onclick="hapusSiswa(${h.id_hasil}, '${(h.nama || '').replace(/'/g, "\'")}')" class="btn btn-xs btn-outline-danger" title="Hapus jawaban siswa ini">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
            }).join('');

            
            document.getElementById('btnHapusSemuaCard').style.display = 'inline-block';
            
            const btnKirim = document.getElementById('btnKirimGuruCard');
            btnKirim.style.display = 'inline-block';
            if (jadwal.hasil_dirilis_ke_guru === 'YA') {
                btnKirim.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Sudah Dirilis ke Guru';
                btnKirim.className = 'btn btn-sm btn-success no-print';
                btnKirim.disabled = true;
            } else {
                btnKirim.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Kirim Hasil ke Guru';
                btnKirim.className = 'btn btn-sm btn-primary no-print';
                btnKirim.disabled = false;
            }

            
            
            const safeLabel = jadwal ? `Rekap Nilai ${jadwal.mapel} Kelas ${jadwal.kelas} (${jadwal.tanggal_ujian})` : 'Rekap Nilai';

            dataTable = $('#hasilTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "info": true, 
                "lengthChange": true, 
                "autoWidth": false, 
                "searching": true,
                "buttons": getExportButtons(safeLabel),
                "language": {
                    "search": "Cari Siswa:"
                }
            });
            dataTable.buttons().container().appendTo('#hasilTable_wrapper .col-md-6:eq(0)');
        }

        
        async function hapusSiswa(idHasil, namaSiswa) {
            const conf = await Swal.fire({
                title: 'Hapus Jawaban Siswa?',
                html: `Jawaban <strong>${namaSiswa}</strong> akan dihapus permanen.<br><small class="text-muted">Siswa dapat mengikuti ujian ulang jika opsi pengulangan diaktifkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal'
            });
            if (!conf.isConfirmed) return;

            const res = await post('delete_hasil_siswa', { id_hasil: idHasil });
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Jawaban dihapus!', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                loadHasil();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        
        async function hapusSemuaHasil() {
            const idJadwal = document.getElementById('selJadwal').value;
            if (!idJadwal) { Swal.fire({ icon: 'warning', title: 'Pilih Jadwal dulu!' }); return; }

            const selText = document.getElementById('selJadwal').options[document.getElementById('selJadwal').selectedIndex].text;
            const conf = await Swal.fire({
                title: 'Hapus SEMUA Jawaban?',
                html: `Semua jawaban siswa untuk sesi:<br><strong>${selText}</strong><br>akan dihapus permanen!<br><small class="text-danger">⚠ Tindakan ini tidak bisa dibatalkan.</small>`,
                icon: 'error',
                input: 'text',
                inputPlaceholder: 'Ketik HAPUS untuk konfirmasi',
                showCancelButton: true,
                confirmButtonText: 'Hapus Semua',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal',
                preConfirm: (input) => {
                    if (input !== 'HAPUS') {
                        Swal.showValidationMessage('Ketik HAPUS (huruf kapital) untuk konfirmasi');
                        return false;
                    }
                    return true;
                }
            });
            if (!conf.isConfirmed) return;

            const res = await post('delete_semua_hasil', { id_jadwal: idJadwal });
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                loadHasil();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        
        async function kirimHasilKeGuru() {
            const idJadwal = document.getElementById('selJadwal').value;
            if (!idJadwal) { Swal.fire({ icon: 'warning', title: 'Pilih Jadwal dulu!' }); return; }

            const selText = document.getElementById('selJadwal').options[document.getElementById('selJadwal').selectedIndex].text;
            const conf = await Swal.fire({
                title: 'Kirim Hasil ke Guru Mapel?',
                html: `Nilai dari sesi ujian:<br><strong>${selText}</strong><br>akan dapat diakses oleh guru yang bersangkutan.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                confirmButtonColor: '#0b57d0',
                cancelButtonText: 'Batal'
            });
            if (!conf.isConfirmed) return;

            const res = await post('publish_hasil_ke_guru', { id_jadwal: idJadwal });
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                loadHasil(); 
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        function resetStats() {
            ['statSubmit', 'statAvg', 'statMax', 'statBelowKkm'].forEach(id => document.getElementById(id).textContent = '0');
        }

        window.onload = init;
    </script>
@endpush