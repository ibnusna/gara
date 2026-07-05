@extends('layouts.guru')

@section('title', 'Agenda Mengajar - ' . session('nama_mapel'))

@push('styles')
    <style>
        .agenda-card {
            border-left: 4px solid #0b57d0;
            border-radius: 8px;
            transition: box-shadow .2s;
        }

        .agenda-card:hover {
            box-shadow: 0 4px 16px rgba(11, 87, 208, .15);
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
                        <h1 class="m-0">Agenda Mengajar</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Agenda</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                
                <div class="filter-card p-3 mb-3">
                    <form method="GET" class="form-inline">
                        <label class="font-weight-bold mr-2">Rentang Tanggal:</label>
                        <div class="form-group mr-2">
                            <label class="mr-1 small">Dari:</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1 small">Sampai:</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i
                                class="fas fa-filter mr-1"></i>Filter</button>
                    </form>
                </div>

                
                <div class="callout callout-info mb-3">
                    <h5><i class="fas fa-info-circle mr-2"></i>Agenda Otomatis</h5>
                    <p class="mb-0">Agenda dibuat secara otomatis setiap kali Anda menyimpan absensi baru. Anda bisa
                        mengedit catatan pelaksanaan di sini jika diperlukan.</p>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ session('nama_mapel') }} / Kelas {{ session('nama_kelas') }}
                            <small class="text-muted ml-2">{{ $agendaList->count() }} entri</small>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @if($agendaList->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-2" style="opacity:0.3"></i>
                                <p>Belum ada agenda di rentang tanggal ini.<br>Agenda akan muncul otomatis setelah Anda
                                    menyimpan absensi.</p>
                                <a href="{{ route('guru.absensi.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i>Input Absensi
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0" id="agendaTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th>Jam</th>
                                            <th>Rencana Kegiatan</th>
                                            <th>Catatan Pelaksanaan</th>
                                            <th class="text-center">Tidak Hadir</th>
                                            <th class="text-center" width="100">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($agendaList as $i => $ag)
                                            <tr class="agenda-card-row">
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($ag->tanggal)->format('d/m/Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($ag->jam)->format('H:i') }}</td>
                                                <td>{{ $ag->rencana_kegiatan }}</td>
                                                <td>{{ $ag->catatan_pelaksanaan ?? '—' }}</td>
                                                <td class="text-center">
                                                    @if($ag->siswa_tidak_hadir_count > 0)
                                                        <span class="badge badge-warning" title="{{ $ag->siswa_tidak_hadir_names }}">
                                                            {{ $ag->siswa_tidak_hadir_count }} siswa
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success">Semua Hadir</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning btn-sm btn-edit-agenda" data-id="{{ $ag->id }}"
                                                        data-rencana="{{ $ag->rencana_kegiatan }}"
                                                        data-catatan="{{ $ag->catatan_pelaksanaan }}" title="Edit agenda">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('guru.agenda.destroy', $ag->id) }}" method="POST"
                                                        class="d-inline form-delete-agenda">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus agenda">
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
        </section>
    </div>

    
    <div class="modal fade" id="editAgendaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAgendaForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Agenda</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Rencana Kegiatan / Pokok Bahasan</label>
                            <textarea class="form-control" name="rencana_kegiatan" id="edit_rencana" rows="3"
                                required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Catatan Pelaksanaan</label>
                            <textarea class="form-control" name="catatan_pelaksanaan" id="edit_catatan" rows="3"
                                placeholder="Contoh: Berjalan dengan lancar, diskusi aktif..."></textarea>
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
                        orientation: 'portrait',
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

            var table = $('#agendaTable').DataTable({
                pageLength: 20,
                order: [[1, 'desc']],
                buttons: getExportButtons('Agenda Mengajar Kelas {{ session('nama_kelas') }} - {{ session('nama_mapel') }}'),
                language: {
                    search: "Cari Agenda:"
                }
            });
            table.buttons().container().appendTo('#agendaTable_wrapper .col-md-6:eq(0)');

            $(document).on('click', '.btn-edit-agenda', function () {
                var id = $(this).data('id');
                $('#editAgendaForm').attr('action', '/guru/agenda/' + id);
                $('#edit_rencana').val($(this).data('rencana'));
                $('#edit_catatan').val($(this).data('catatan'));
                $('#editAgendaModal').modal('show');
            });

            $(document).on('submit', '.form-delete-agenda', function (e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: 'Hapus Agenda?', text: 'Entri agenda ini akan dihapus permanen.', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
                }).then(function (r) { if (r.isConfirmed) form.submit(); });
            });
        });
    </script>
@endpush