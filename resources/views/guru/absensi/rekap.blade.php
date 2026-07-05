@extends('layouts.guru')

@section('title', 'Rekapitulasi LMS - ' . session('nama_mapel'))

@push('styles')
    <style>
        .badge-H {
            background: #198754;
            color: #fff;
        }

        .badge-I {
            background: #0dcaf0;
            color: #000;
        }

        .badge-S {
            background: #ffc107;
            color: #000;
        }

        .badge-A {
            background: #dc3545;
            color: #fff;
        }

        .badge-status {
            font-size: 0.78rem;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 700;
        }

        .filter-card {
            background: linear-gradient(135deg, rgba(11, 87, 208, 0.06), rgba(255, 255, 255, 0.95));
            border: 1px solid rgba(11, 87, 208, 0.15);
            border-radius: 12px;
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
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0">Pusat Rekapitulasi Guru <small class="text-muted"
                                style="font-size:0.6em;">{{ session('nama_mapel') }} / Kelas
                                {{ session('nama_kelas') }}</small></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Pusat Rekapitulasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: "{!! session('success') !!}",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        });
                    </script>
                @endif

                
                <div class="filter-card p-3 mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <label class="mr-1 font-weight-bold">Bulan:</label>
                                    <select name="bulan" class="form-control form-control-sm">
                                        <option value="semua" {{ $bulan == 'semua' ? 'selected' : '' }}>Semua Bulan</option>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <label class="mr-1 font-weight-bold">Tahun:</label>
                                    <select name="tahun" class="form-control form-control-sm">
                                        @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i
                                        class="fas fa-filter mr-1"></i>Filter</button>
                            </form>
                        </div>
                        <div class="col-md-4 text-md-right mt-2 mt-md-0">
                            <a href="{{ route('guru.absensi.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i>Input Absensi Baru
                            </a>
                        </div>
                    </div>
                </div>

                
                <div class="card card-primary card-outline">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="absensiTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-pivot" role="tab">
                                    <i class="fas fa-calendar-check mr-1 text-success"></i>Rekap Absensi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-pertemuan" role="tab">
                                    <i class="fas fa-list mr-1 text-primary"></i>Manajemen Pertemuan
                                    <span class="badge badge-primary ml-1">{{ $absensiList->count() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            
                            <div class="tab-pane fade show active" id="tab-pivot" role="tabpanel">
                                @if($absensiList->isEmpty())
                                    <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Belum ada absensi di
                                        bulan ini. Silakan input terlebih dahulu.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm text-nowrap w-100"
                                            id="pivotTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Nama Siswa</th>
                                                    <th class="text-center">NIS</th>
                                                    @foreach($pertemuanCols->sort() as $pt)
                                                        <th class="text-center">P-{{ $pt }}</th>
                                                    @endforeach
                                                    <th class="text-center text-success" title="Hadir">H</th>
                                                    <th class="text-center text-warning" title="Sakit">S</th>
                                                    <th class="text-center text-info" title="Izin">I</th>
                                                    <th class="text-center text-danger" title="Alpha">A</th>
                                                    <th class="text-center bg-light">% Hadir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($siswaList as $i => $siswa)
                                                    @php
                                                        $siswaStatuses = $pivotMap[$siswa->id] ?? [];
                                                        $countH = collect($siswaStatuses)->filter(fn($s) => $s === 'H')->count();
                                                        $countS = collect($siswaStatuses)->filter(fn($s) => $s === 'S')->count();
                                                        $countI = collect($siswaStatuses)->filter(fn($s) => $s === 'I')->count();
                                                        $countA = collect($siswaStatuses)->filter(fn($s) => $s === 'A')->count();
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $i + 1 }}</td>
                                                        <td>{{ $siswa->nama }}</td>
                                                        <td class="text-center text-muted small">{{ $siswa->nis }}</td>
                                                        @foreach($pertemuanCols->sort() as $pt)
                                                            @php $st = $siswaStatuses[$pt] ?? null; @endphp
                                                            <td class="text-center">
                                                                <span class="badge badge-status {{ $st ? 'badge-'.$st : 'bg-light text-muted' }} update-status" 
                                                                    style="cursor: pointer;"
                                                                    data-siswa="{{ $siswa->id }}" 
                                                                    data-pertemuan="{{ $pt }}" 
                                                                    data-current="{{ $st ?? '' }}">
                                                                    {{ $st ?? '-' }}
                                                                </span>
                                                            </td>
                                                        @endforeach
                                                        @php
                                                            $totalP = count($pertemuanCols);
                                                            $persen = $totalP > 0 ? round(($countH / $totalP) * 100) : 0;
                                                        @endphp
                                                        <td class="text-center font-weight-bold text-success">{{ $countH }}</td>
                                                        <td class="text-center font-weight-bold text-warning">{{ $countS }}</td>
                                                        <td class="text-center font-weight-bold text-info">{{ $countI }}</td>
                                                        <td class="text-center font-weight-bold text-danger">{{ $countA }}</td>
                                                        <td class="text-center font-weight-bold bg-light">{{ $persen }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted d-block mt-2"><i class="fas fa-info-circle mr-1"></i>P-N = Pertemuan ke-N
                                        &nbsp;|&nbsp;
                                        <span class="badge badge-status badge-H">H</span> Hadir &nbsp;
                                        <span class="badge badge-status badge-S">S</span> Sakit &nbsp;
                                        <span class="badge badge-status badge-I">I</span> Izin &nbsp;
                                        <span class="badge badge-status badge-A">A</span> Alpha
                                    </small>
                                @endif
                            </div>

                            
                            <div class="tab-pane fade" id="tab-pertemuan" role="tabpanel">
                                @if($absensiList->isEmpty())
                                    <div class="alert alert-info">Belum ada data pertemuan bulan ini.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover w-100" id="pertemuanTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">Pertemuan</th>
                                                    <th>Tanggal</th>
                                                    <th>Pokok Bahasan</th>
                                                    <th class="text-center" width="160px">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($absensiList as $ab)
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">ke-{{ $ab->pertemuan_ke }}</span>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($ab->tanggal)->format('d/m/Y') }}</td>
                                                        <td>{{ $ab->pokok_bahasan }}</td>
                                                        <td class="text-center">
                                                            <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $ab->id }}"
                                                                data-tanggal="{{ $ab->tanggal }}"
                                                                data-pertemuan="{{ $ab->pertemuan_ke }}"
                                                                data-pokok="{{ $ab->pokok_bahasan }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form action="{{ route('guru.absensi.destroy', $ab->id) }}"
                                                                method="POST" class="d-inline form-delete">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit mr-2 text-dark"></i>Edit Data Pertemuan</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="edit_tanggal" required>
                        </div>
                        <div class="form-group">
                            <label>Pertemuan Ke-</label>
                            <input type="number" class="form-control" name="pertemuan_ke" id="edit_pertemuan_ke" required>
                        </div>
                        <div class="form-group">
                            <label>Pokok Bahasan</label>
                            <input type="text" class="form-control" name="pokok_bahasan" id="edit_pokok_bahasan" required>
                        </div>
                        
                        <div class="mt-4">
                            <label class="d-block border-bottom pb-2 mb-2"><i class="fas fa-users mr-1"></i> Kehadiran Siswa</label>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-sm text-sm">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th class="text-center" width="200px">Status Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editStudentsList">
                                        <tr><td colspan="2" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data kehadiran...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            
            function getExportButtons(titleText, orientationVal = 'portrait') {
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
                        orientation: orientationVal,
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

            
            var dtPivot = $('#pivotTable').DataTable({
                paging: false,
                info: false,
                scrollX: true,
                searching: true,
                buttons: getExportButtons('Rekap Absensi Kelas {{ session('nama_kelas') }} - {{ session('nama_mapel') }} (Bulan: {{ $bulan }}/{{ $tahun }})', 'landscape'),
                language: {
                    search: "Cari Siswa:"
                }
            });
            dtPivot.buttons().container().appendTo('#pivotTable_wrapper .col-md-6:eq(0)');



            $('#pertemuanTable').DataTable({ 
                pageLength: 25,
                language: {
                    search: "Cari Pertemuan:"
                }
            });

            
            $(document).on('click', '.update-status', function() {
                let btn = $(this);
                let siswa_id = btn.data('siswa');
                let pertemuan = btn.data('pertemuan');
                let current = btn.data('current');

                Swal.fire({
                    title: 'Kehadiran P-' + pertemuan,
                    text: 'Ubah status absensi siswa:',
                    input: 'select',
                    inputOptions: {
                        'H': 'Hadir',
                        'I': 'Izin',
                        'S': 'Sakit',
                        'A': 'Alpha'
                    },
                    inputValue: current || 'H',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: (status) => {
                        return $.ajax({
                            url: '{{ route("guru.absensi.quick_update") }}',
                            type: 'PATCH',
                            data: {
                                _token: '{{ csrf_token() }}',
                                siswa_id: siswa_id,
                                pertemuan_ke: pertemuan,
                                status: status,
                                bulan: '{{ $bulan }}',
                                tahun: '{{ $tahun }}'
                            }
                        }).then(response => {
                            if (response.status !== 'success') {
                                throw new Error(response.message);
                            }
                            return status;
                        }).catch(error => {
                            Swal.showValidationMessage('Gagal menyimpan: ' + error.message);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Status diperbarui',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            });

            
            $(document).on('click', '.btn-edit', function () {
                var id = $(this).data('id');
                $('#editForm').attr('action', '/guru/absensi/' + id);
                $('#edit_tanggal').val($(this).data('tanggal'));
                $('#edit_pertemuan_ke').val($(this).data('pertemuan'));
                $('#edit_pokok_bahasan').val($(this).data('pokok'));
                
                $('#editStudentsList').html('<tr><td colspan="2" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data kehadiran...</td></tr>');
                
                $.get('/guru/absensi/' + id + '/detail', function(res) {
                    let html = '';
                    let students = res.students;
                    let studentMap = {};
                    students.forEach(s => studentMap[s.siswa_id] = s.status);
                    
                    @foreach($siswaList as $s)
                        var sid = {{ $s->id }};
                        var currentStatus = studentMap[sid] || 'H';
                        html += `<tr>
                            <td class="align-middle font-weight-500">{{ addslashes($s->nama) }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-success btn-sm ${currentStatus === 'H' ? 'active' : ''}">
                                        <input type="radio" name="status[${sid}]" value="H" ${currentStatus === 'H' ? 'checked' : ''} required> H
                                    </label>
                                    <label class="btn btn-outline-warning btn-sm ${currentStatus === 'S' ? 'active' : ''}">
                                        <input type="radio" name="status[${sid}]" value="S" ${currentStatus === 'S' ? 'checked' : ''} required> S
                                    </label>
                                    <label class="btn btn-outline-info btn-sm ${currentStatus === 'I' ? 'active' : ''}">
                                        <input type="radio" name="status[${sid}]" value="I" ${currentStatus === 'I' ? 'checked' : ''} required> I
                                    </label>
                                    <label class="btn btn-outline-danger btn-sm ${currentStatus === 'A' ? 'active' : ''}">
                                        <input type="radio" name="status[${sid}]" value="A" ${currentStatus === 'A' ? 'checked' : ''} required> A
                                    </label>
                                </div>
                            </td>
                        </tr>`;
                    @endforeach
                    $('#editStudentsList').html(html);
                }).fail(function() {
                    $('#editStudentsList').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data.</td></tr>');
                });
                
                $('#editModal').modal('show');
            });

            
            $(document).on('submit', '.form-delete', function (e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: 'Hapus Pertemuan?',
                    text: 'Data kehadiran seluruh siswa pada pertemuan ini akan hilang!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(function (r) { if (r.isConfirmed) form.submit(); });
            });
        });
    </script>
@endpush