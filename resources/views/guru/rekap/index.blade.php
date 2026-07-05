@extends('layouts.guru')

@section('title', 'Rekap & Manajemen Tugas')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Rekap & Manajemen Tugas</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Rekap Tugas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire('Berhasil!', '{{ session('success') }}', 'success');
                    });
                </script>
            @endif
            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire('Error!', '{{ session('error') }}', 'error');
                    });
                </script>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header p-0 pt-1 border-bottom-0">
                     <ul class="nav nav-tabs" id="rekap-tugas-tab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="rekap-nilai-tab" data-toggle="tab" data-target="#rekap-nilai" type="button" role="tab"><i class="fas fa-table mr-1"></i> Rekapitulasi</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="manajemen-tugas-tab" data-toggle="tab" data-target="#manajemen-tugas" type="button" role="tab"><i class="fas fa-tasks mr-1"></i> Daftar Tugas</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="manajemen-siswa-tab" data-toggle="tab" data-target="#manajemen-siswa" type="button" role="tab"><i class="fas fa-users-cog mr-1"></i> Koreksi Manual</button>
                        </li>
                        
                        <li class="nav-item">
                            <button class="nav-link text-danger font-weight-bold" id="katrol-nilai-tab" data-toggle="tab" data-target="#katrol-nilai" type="button" role="tab"><i class="fas fa-magic mr-1"></i> Nilai Pasca-Perbaikan</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="rekap-tugas-tabContent">
                        
                        
                        <div class="tab-pane fade show active" id="rekap-nilai" role="tabpanel">
                            <div class="table-responsive">
                                <table id="rekapTugasTable" class="table table-bordered table-striped table-hover text-sm">
                                    @if(count($siswaList) > 0)
                                        <thead class='bg-light'>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama Siswa</th>
                                                @foreach ($tugasHeaders as $th)
                                                    <th class='text-center'>Tugas {{ $th }}</th>
                                                @endforeach
                                                <th class='bg-primary text-white text-center'>Rata-Rata</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($siswaList as $siswa)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $siswa->nis }}</td>
                                                    <td>{{ $siswa->nama }}</td>
                                                    
                                                    @php $nilaiSiswa = []; @endphp
                                                    @foreach ($tugasHeaders as $th)
                                                        @php 
                                                            $nilai = isset($nilaiMap[$siswa->id][$th]) ? (float)$nilaiMap[$siswa->id][$th] : 0.0; 
                                                            $colorClass = ($nilai < 75) ? 'text-danger font-weight-bold' : '';
                                                            $nilaiSiswa[] = $nilai;
                                                        @endphp
                                                        <td class='text-center {{ $colorClass }}'>{{ round($nilai) }}</td>
                                                    @endforeach

                                                    @php
                                                        $jumlahTugas = count($tugasHeaders);
                                                        $rataRata = $jumlahTugas > 0 ? round(array_sum($nilaiSiswa) / $jumlahTugas) : 0;
                                                        $bgBadge = ($rataRata < 75) ? 'badge-danger' : 'badge-success';
                                                    @endphp
                                                    <td class='text-center'>
                                                        <span class='badge {{ $bgBadge }}' style='font-size:1em'>{{ $rataRata }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @else
                                        <thead><tr><th>Info</th></tr></thead><tbody><tr><td>Tidak ada siswa di kelas ini.</td></tr></tbody>
                                    @endif
                                </table>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="manajemen-tugas" role="tabpanel">
                             <table class="table table-bordered table-hover">
                                <thead class="bg-light"><tr><th style="width: 150px;">Tanggal</th><th>Tugas Ke-</th><th>Pokok Bahasan</th><th style="width: 150px;">Aksi</th></tr></thead>
                                <tbody>
                                    @forelse ($tugasList as $tugas)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($tugas->tanggal)->format('d M Y') }}</td>
                                            <td>{{ $tugas->tugas_ke }}</td>
                                            <td>{{ $tugas->pokok_bahasan }}</td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm btn-edit-tugas" data-id="{{ $tugas->id }}" data-tanggal="{{ \Carbon\Carbon::parse($tugas->tanggal)->format('Y-m-d') }}" data-tugas-ke="{{ $tugas->tugas_ke }}" data-pokok-bahasan="{{ $tugas->pokok_bahasan }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('guru.rekap.tugas.destroy', $tugas->id) }}" method="POST" style="display:inline-block" class="form-delete-tugas">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">Tidak ada data tugas yang pernah dibuat.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        
                        <div class="tab-pane fade" id="manajemen-siswa" role="tabpanel">
                            <div class="callout callout-info mb-3">
                                <h5><i class="fas fa-info-circle"></i> Mode Koreksi Manual</h5>
                                <p>Gunakan tab ini untuk melihat siswa yang nilainya masih di bawah standar secara umum (berdasarkan rata-rata).</p>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Batas KKM</span>
                                        </div>
                                        <input type="number" class="form-control" id="filterNilai" placeholder="Input KKM (misal: 75)" min="0" max="100" value="75">
                                        <button class="btn btn-primary" id="applyFilterBtn">Terapkan</button>
                                        <button class="btn btn-secondary" id="resetFilterBtn">Reset</button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="manajemenSiswaTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Nilai Rata-rata</th>
                                            <th class="text-center" style="width: 120px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="siswaTableBody">
                                        @foreach ($siswaList as $siswa)
                                            @php
                                                
                                                $nilaiSiswaArr = [];
                                                if (isset($nilaiMap[$siswa->id])) {
                                                    $nilaiSiswaArr = array_map(function($v) { return (float)$v; }, $nilaiMap[$siswa->id]);
                                                }
                                                $rataRataSiswa = count($nilaiSiswaArr) > 0 ? round(array_sum($nilaiSiswaArr) / count($nilaiSiswaArr), 2) : 0;
                                            @endphp
                                            <tr class="siswa-row" data-rata-rata="{{ $rataRataSiswa }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $siswa->nis }}</td>
                                                <td>{{ $siswa->nama }}</td>
                                                <td>
                                                    <span class="badge badge-lg" style="background-color: #d1ecf1; color: #0c5460; padding: 8px 12px; font-size: 14px; font-weight: 500;">
                                                        {{ $rataRataSiswa }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary btn-koreksi" data-siswa="{{ $siswa->id }}" data-nama="{{ $siswa->nama }}">
                                                        <i class="fas fa-edit"></i> Koreksi
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="katrol-nilai" role="tabpanel">
                            <div class="alert alert-warning border-left-danger">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                    <div>
                                        <strong>PERHATIAN GURU:</strong><br>
                                        Fitur ini akan mengubah nilai tugas siswa secara permanen di database. 
                                        Pastikan Anda melakukan <strong>Preview</strong> terlebih dahulu sebelum melakukan eksekusi.<br>
                                        <span class="text-danger font-weight-bold"><i class="fas fa-info-circle"></i> Catatan:</span> Perbaikan nilai hanya dilakukan setelah siswa melaksanakan remedial baik secara langsung di kelas maupun secara online.
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                <label for="katrol_threshold">Batas Bawah (Nilai < X)</label>
                                                <div class="input-group">
                                                    <input type="number" id="katrol_threshold" class="form-control" placeholder="Contoh: 70" min="0" max="100">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Cari nilai di bawah angka ini.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                <label for="katrol_target">Target Nilai Baru</label>
                                                <div class="input-group">
                                                    <input type="number" id="katrol_target" class="form-control" placeholder="Contoh: 75" min="0" max="100">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fas fa-arrow-up"></i></span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Ubah nilai menjadi angka ini.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label for="katrol_tugas_id">Lingkup Tugas (Opsional)</label>
                                                <select id="katrol_tugas_id" class="form-control">
                                                    <option value="">Semua Tugas di Kelas Ini</option>
                                                    @foreach ($tugasList as $tugas)
                                                        <option value="{{ $tugas->id }}">
                                                            Tugas {{ $tugas->tugas_ke }} - {{ \Illuminate\Support\Str::limit($tugas->pokok_bahasan, 40) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Pilih satu atau terapkan ke semua.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100 font-weight-bold" id="btnPreviewKatrol">
                                                <i class="fas fa-search"></i> Cek Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div id="katrolResultArea" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0 text-primary"><i class="fas fa-list-alt"></i> Daftar Siswa Terdampak</h5>
                                    <button class="btn btn-success font-weight-bold px-4 shadow" id="btnEksekusiKatrol">
                                        <i class="fas fa-magic mr-2"></i> Eksekusi Perbaikan Nilai
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablePreviewKatrol">
                                        <thead class="bg-secondary">
                                            <tr>
                                                <th style="width: 50px">No</th>
                                                <th>Nama Siswa</th>
                                                <th style="width: 100px">Tugas Ke</th>
                                                <th>Topik Bahasan</th>
                                                <th style="width: 120px" class="text-center bg-danger">Nilai Lama</th>
                                                <th style="width: 50px" class="text-center"><i class="fas fa-arrow-right"></i></th>
                                                <th style="width: 120px" class="text-center bg-success">Nilai Baru</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyPreviewKatrol" class="bg-white">
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            
                            <div id="katrolEmptyState" class="text-center py-5 text-muted" style="display:none;">
                                <i class="fas fa-check-circle fa-4x mb-3 text-success"></i>
                                <h5>Tidak ada data yang ditemukan.</h5>
                                <p>Semua siswa sudah mencapai batas nilai tersebut!</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<div class="modal fade" id="modalEditTugas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('guru.rekap.tugas.update') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Tugas & Nilai</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tugas_id" id="edit_tugas_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tugas Ke-</label>
                                <input type="number" name="tugas_ke" id="edit_tugas_ke" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pokok Bahasan</label>
                        <input type="text" name="pokok_bahasan" id="edit_pokok_bahasan" class="form-control" required>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-users"></i> Update Nilai Siswa</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center" style="width: 150px">Nilai</th>
                                </tr>
                            </thead>
                            <tbody id="editTugasSiswaTable">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalKoreksiSiswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-user-edit"></i> Koreksi Nilai: <span id="koreksiNamaSiswa"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formKoreksiSiswa">
                    <input type="hidden" id="koreksiSiswaId">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tugas Ke</th>
                                    <th>Topik / Pokok Bahasan</th>
                                    <th style="width: 150px">Nilai</th>
                                </tr>
                            </thead>
                            <tbody id="koreksiTableBody">
                                <tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSaveKoreksi"><i class="fas fa-save"></i> Simpan Nilai</button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('styles')
    <style>
        
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
@endpush

@push('scripts')
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    
    if ($.fn.DataTable) {
        
        function getExportButtons(titleText) {
            return [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-success btn-sm px-3 mr-2 rounded-pill shadow-sm',
                    title: titleText
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-danger btn-sm px-3 mr-2 rounded-pill shadow-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: titleText
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Cetak',
                    className: 'btn btn-info btn-sm px-3 rounded-pill shadow-sm',
                    title: titleText,
                    customize: function(win) {
                        $(win.document.head).find('link[rel="stylesheet"], style').remove();
                        $(win.document.head).append('<style>@page{size:landscape;} body{font-family:sans-serif;padding:20px;background:#fff;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;color:#000;} th{background-color:#f8f9fa;color:#000;} h1{text-align:center;font-size:20px;margin-bottom:20px;color:#000;}</style>');
                    }
                }
            ];
        }

        var table = $('#rekapTugasTable').DataTable({
            "responsive": true, 
            "paging": false, 
            "info": false, 
            "lengthChange": false, 
            "autoWidth": false, 
            "searching": true,
            "buttons": getExportButtons('Rekap Nilai Tugas Kelas {{ session('nama_kelas') }} - {{ session('nama_mapel') }}'),
            "language": {
                "search": "Cari Siswa:"
            }
        });
        table.buttons().container().appendTo('#rekapTugasTable_wrapper .col-md-6:eq(0)');
        
        $('#manajemenSiswaTable').DataTable({
            "responsive": true, "paging": true, "info": true, "lengthChange": true, "autoWidth": false, "searching": false
        });
    }

    
    const hash = window.location.hash;
    if (hash) {
        $(`[data-target="${hash}"]`).tab('show');
    }

    
    $('#applyFilterBtn').on('click', filterSiswaByNilai);
    $('#resetFilterBtn').on('click', function() { $('#filterNilai').val('75'); resetSiswaFilter(); });
    $('#filterNilai').on('keypress', function(e) { if (e.which == 13) filterSiswaByNilai(); });

    function filterSiswaByNilai() {
        const threshold = parseFloat($('#filterNilai').val());
        if (isNaN(threshold)) return Swal.fire('Error', 'Input nilai tidak valid', 'error');

        const rows = $('#siswaTableBody tr.siswa-row').get();
        const bawahKKM = [], atasKKM = [];

        rows.forEach(row => {
            const rata = parseFloat($(row).data('rata-rata'));
            (rata < threshold) ? bawahKKM.push(row) : atasKKM.push(row);
        });

        $('#siswaTableBody').empty();
        
        if (bawahKKM.length > 0) {
            $('#siswaTableBody').append('<tr class="bg-danger text-white font-weight-bold"><td colspan="4">PERLU PERBAIKAN (Nilai < ' + threshold + ')</td></tr>');
            bawahKKM.forEach(row => $('#siswaTableBody').append(row));
        }
        if (atasKKM.length > 0) {
            $('#siswaTableBody').append('<tr class="bg-success text-white font-weight-bold"><td colspan="4">SUDAH AMAN (Nilai ≥ ' + threshold + ')</td></tr>');
            atasKKM.forEach(row => $('#siswaTableBody').append(row));
        }
        if(bawahKKM.length === 0 && atasKKM.length === 0) $('#siswaTableBody').html('<tr><td colspan="4" class="text-center">Data kosong.</td></tr>');
    }

    function resetSiswaFilter() { window.location.href = window.location.pathname + "#manajemen-siswa"; window.location.reload(); }

    
    $('#btnPreviewKatrol').on('click', function() {
        const threshold = $('#katrol_threshold').val();
        const target = $('#katrol_target').val();
        const tugasId = $('#katrol_tugas_id').val();

        if (!threshold || !target) {
            return Swal.fire('Data Belum Lengkap', 'Mohon isi Batas Nilai dan Target Nilai.', 'warning');
        }
        
        if (parseFloat(target) < parseFloat(threshold)) {
            return Swal.fire('Logika Salah', 'Target nilai tidak boleh lebih kecil dari batas nilai (nanti nilainya turun dong!).', 'error');
        }

        Swal.fire({ title: 'Mencari Data...', didOpen: () => Swal.showLoading() });

        $.ajax({
            url: '{{ route('guru.rekap.katrol.preview') }}',
            type: 'GET',
            data: { threshold: threshold, tugas_id: tugasId },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if (response.status === 'success') {
                    const tbody = $('#bodyPreviewKatrol');
                    tbody.empty();

                    if (response.count > 0) {
                        $('#katrolResultArea').slideDown();
                        $('#katrolEmptyState').hide();
                        
                        let no = 1;
                        response.data.forEach(item => {
                            tbody.append(`
                                <tr>
                                    <td>${no++}</td>
                                    <td>${item.nama_siswa} <br><small class="text-muted">${item.nis}</small></td>
                                    <td>Ke-${item.tugas_ke}</td>
                                    <td>${item.pokok_bahasan}</td>
                                    <td class="text-center text-danger font-weight-bold">${Math.round(item.nilai_lama)}</td>
                                    <td class="text-center"><i class="fas fa-arrow-right text-muted"></i></td>
                                    <td class="text-center text-success font-weight-bold">${target}</td>
                                </tr>
                            `);
                        });

                        $('html, body').animate({ scrollTop: $("#katrolResultArea").offset().top - 100 }, 500);
                    } else {
                        $('#katrolResultArea').hide();
                        $('#katrolEmptyState').fadeIn();
                    }
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            }
        });
    });

    
    $('#btnEksekusiKatrol').on('click', function() {
        const threshold = $('#katrol_threshold').val();
        const target = $('#katrol_target').val();
        const tugasId = $('#katrol_tugas_id').val();
        const jumlahSiswa = $('#bodyPreviewKatrol tr').length;

        Swal.fire({
            title: 'Konfirmasi Katrol',
            html: `Anda akan mengubah nilai <b>${jumlahSiswa} siswa</b> menjadi <b>${target}</b>.<br>Tindakan ini tidak dapat dibatalkan. Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Katrol Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', didOpen: () => Swal.showLoading() });
                
                $.ajax({
                    url: '{{ route("guru.rekap.katrol.save") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', threshold: threshold, target_nilai: target, tugas_id: tugasId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                window.location.href = window.location.pathname + "?status=success#katrol-nilai";
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan.', 'error');
                    }
                });
            }
        });
    });

    
    $('.btn-edit-tugas').on('click', function() {
        let id = $(this).data('id');
        let tk = $(this).data('tugas-ke');
        
        $('#edit_tugas_id').val(id);
        $('#edit_tanggal').val($(this).data('tanggal'));
        $('#edit_tugas_ke').val(tk);
        $('#edit_pokok_bahasan').val($(this).data('pokok-bahasan'));

        
        let siswaList = @json($siswaList);
        let nilaiMap = @json($nilaiMap);
        let html = '';
        
        siswaList.forEach((siswa, index) => {
            let nilai = (nilaiMap[siswa.id] && nilaiMap[siswa.id][tk] !== undefined) ? Math.round(nilaiMap[siswa.id][tk]) : '';
            html += `<tr>
                <td class="text-center align-middle">${index + 1}</td>
                <td class="align-middle">${siswa.nama}</td>
                <td>
                    <input type="number" name="nilai[${siswa.id}]" value="${nilai}" class="form-control form-control-sm text-center" min="0" max="100" placeholder="Kosong">
                </td>
            </tr>`;
        });
        $('#editTugasSiswaTable').html(html);

        $('#modalEditTugas').modal('show');
    });

    $('.btn-delete').on('click', function() {
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Hapus Tugas?',
            text: "Data nilai siswa di tugas ini akan ikut terhapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    
    $('.btn-koreksi').on('click', function() {
        let id = $(this).data('siswa');
        let nama = $(this).data('nama');
        $('#koreksiSiswaId').val(id);
        $('#koreksiNamaSiswa').text(nama);
        $('#koreksiTableBody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
        $('#modalKoreksiSiswa').modal('show');

        $.get('/guru/tugas/koreksi-siswa/' + id, function(res) {
            let html = '';
            if(res.status === 'success') {
                res.data.forEach(item => {
                    html += `<tr>
                        <td class="align-middle text-center">Tugas ${item.tugas_ke}</td>
                        <td class="align-middle">${item.pokok_bahasan}</td>
                        <td>
                            <input type="number" name="nilai[${item.tugas_id}]" class="form-control text-center" value="${item.nilai}" min="0" max="100">
                        </td>
                    </tr>`;
                });
                $('#koreksiTableBody').html(html || '<tr><td colspan="3" class="text-center">Belum ada tugas.</td></tr>');
            }
        });
    });

    $('#btnSaveKoreksi').on('click', function() {
        let id = $('#koreksiSiswaId').val();
        let data = $('#formKoreksiSiswa').serialize() + '&_token={{ csrf_token() }}';
        
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.post('/guru/tugas/koreksi-siswa/' + id, data, function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Nilai');
            if(res.status === 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: res.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Nilai');
            Swal.fire('Error', 'Gagal menyimpan data.', 'error');
        });
    });
});
</script>
@endpush