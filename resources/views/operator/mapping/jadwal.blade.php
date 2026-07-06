@extends('layouts.operator')
@section('title', 'Jadwal Pelajaran | Garuda Akademi')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<style>
    .dt-buttons { margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 5px; }
    .dataTables_wrapper .dataTables_filter { display: none; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Jadwal Pelajaran Kelas</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            
            <div class="card card-outline card-primary mb-4">
                <div class="card-body">
                    <form action="{{ route('operator.mapping.jadwal') }}" method="GET" class="row align-items-end">
                        <div class="col-md-6">
                            <label>Pilih Kelas</label>
                            <select name="class_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelases as $k)
                                    <option value="{{ $k->id }}" {{ $selected_class_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('operator.mapping.waktu') }}" class="btn btn-warning">
                                <i class="fas fa-clock"></i> Kelola Waktu Belajar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($selected_class_id)
                @if($groupedMaster->isEmpty())
                    <div class="alert alert-warning">
                        Waktu Belajar belum diatur. Silakan klik tombol <b>Kelola Waktu Belajar</b> untuk menambahkan slot waktu.
                    </div>
                @else
                    <div class="row mb-3 no-print">
                        <div class="col-12" id="exportContainer"></div>
                    </div>
                    
                    @php
                        $mergedJadwal = [];
                        $maxSlots = 0;
                        
                        foreach($hariList as $hari) {
                            $mergedJadwal[$hari] = [];
                            if(isset($groupedMaster[$hari])) {
                                $currentBlock = null;
                                foreach($groupedMaster[$hari] as $jam) {
                                    $jadwal = $jadwalData[$jam->id] ?? null;
                                    $mapel = '';
                                    $guru = '';
                                    $taId = '';
                                    
                                    if ($jam->jenis_kegiatan == 'KBM' && $jadwal) {
                                        $curr = collect($classCurriculum)->firstWhere('id', $jadwal->teaching_assignment_id);
                                        if ($curr) {
                                            $mapel = $curr->nama_mapel;
                                            $guru = $curr->nama_lengkap;
                                            $taId = $jadwal->teaching_assignment_id;
                                        }
                                    } else if ($jam->jenis_kegiatan != 'KBM') {
                                        $mapel = $jam->jenis_kegiatan;
                                    }

                                    $contentKey = ($jam->jenis_kegiatan == 'KBM') ? "KBM_{$taId}" : "NON_KBM_{$jam->jenis_kegiatan}";

                                    if ($currentBlock === null) {
                                        $currentBlock = [
                                            'content_key' => $contentKey,
                                            'jenis_kegiatan' => $jam->jenis_kegiatan,
                                            'mapel' => $mapel,
                                            'guru' => $guru,
                                            'jam_mulai' => $jam->jam_mulai,
                                            'jam_selesai' => $jam->jam_selesai,
                                        ];
                                    } else {
                                        if ($currentBlock['content_key'] === $contentKey && $contentKey !== 'KBM_') {
                                            $currentBlock['jam_selesai'] = $jam->jam_selesai;
                                        } else {
                                            $mergedJadwal[$hari][] = $currentBlock;
                                            $currentBlock = [
                                                'content_key' => $contentKey,
                                                'jenis_kegiatan' => $jam->jenis_kegiatan,
                                                'mapel' => $mapel,
                                                'guru' => $guru,
                                                'jam_mulai' => $jam->jam_mulai,
                                                'jam_selesai' => $jam->jam_selesai,
                                            ];
                                        }
                                    }
                                }
                                if ($currentBlock !== null) {
                                    $mergedJadwal[$hari][] = $currentBlock;
                                }
                                
                                $count = count($mergedJadwal[$hari]);
                                if($count > $maxSlots) $maxSlots = $count;
                            }
                        }
                    @endphp
                    <table id="hiddenJadwalTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>Sesi</th>
                                @foreach($hariList as $hari)
                                    <th>{{ $hari }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < $maxSlots; $i++)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    @foreach($hariList as $hari)
                                        @if(isset($mergedJadwal[$hari][$i]))
                                            @php
                                                $block = $mergedJadwal[$hari][$i];
                                                $waktu = substr($block['jam_mulai'], 0, 5) . ' - ' . substr($block['jam_selesai'], 0, 5);
                                                $mapel = $block['mapel'];
                                                $guru = $block['guru'];
                                            @endphp
                                            <td>
                                                @if($block['jenis_kegiatan'] == 'KBM' && !empty($mapel))
                                                    {{ $waktu }}<br>{{ $mapel }}<br>{{ $guru }}
                                                @elseif($block['jenis_kegiatan'] != 'KBM' && !empty($mapel))
                                                    {{ $waktu }}<br>{{ $mapel }}
                                                @else
                                                    {{ $waktu }}
                                                @endif
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>

                    <div class="row">
                        @foreach($hariList as $hari)
                            @if(isset($groupedMaster[$hari]))
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm" style="border-top: 3px solid #0b57d0;">
                                        <div class="card-header bg-light">
                                            <h3 class="card-title font-weight-bold text-primary">
                                                <i class="fas fa-calendar-day mr-2"></i>{{ $hari }}
                                            </h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead class="bg-secondary text-white">
                                                    <tr>
                                                        <th width="30%">Waktu</th>
                                                        <th width="70%">Mata Pelajaran</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($groupedMaster[$hari] as $jam)
                                                        <tr>
                                                            <td class="align-middle">
                                                                <small class="font-weight-bold">{{ substr($jam->jam_mulai,0,5) }} - {{ substr($jam->jam_selesai,0,5) }}</small>
                                                                <br>
                                                                @if($jam->jenis_kegiatan == 'KBM')
                                                                    <span class="badge badge-info">Jam ke-{{ $jam->urutan_jam }}</span>
                                                                @else
                                                                    <span class="badge badge-warning">{{ $jam->jenis_kegiatan }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle">
                                                                @if($jam->jenis_kegiatan == 'KBM')
                                                                    @php
                                                                        $jadwal = $jadwalData[$jam->id] ?? null;
                                                                    @endphp
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <select class="form-control form-control-sm select-jadwal" 
                                                                            data-jam-id="{{ $jam->id }}" 
                                                                            data-class-id="{{ $selected_class_id }}"
                                                                            style="width: 85%;">
                                                                            <option value="">- Kosong -</option>
                                                                            @foreach($classCurriculum as $curr)
                                                                                <option value="{{ $curr->id }}"
                                                                                    {{ $jadwal && $jadwal->teaching_assignment_id == $curr->id ? 'selected' : '' }}>
                                                                                    {{ $curr->nama_mapel }} ({{ $curr->nama_lengkap }})
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @if($jadwal)
                                                                            <button class="btn btn-sm btn-danger btn-delete-jadwal ml-1" data-jam-id="{{ $jam->id }}">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <div class="text-muted font-italic text-center mt-2">
                                                                        <i class="fas fa-coffee"></i> {{ $jam->jenis_kegiatan }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <div class="text-center mt-5 text-muted">
                    <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                    <h5>Silakan pilih kelas terlebih dahulu untuk melihat dan mengatur jadwal.</h5>
                </div>
            @endif

        </div>
    </section>
</div>
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
$(document).ready(function() {
    @if($selected_class_id && !$groupedMaster->isEmpty())
    let className = '{{ collect($kelases)->firstWhere("id", $selected_class_id)->nama_kelas ?? "" }}';
    let dtTitle = 'Jadwal Pelajaran Kelas ' + className;
    let exportFormatter = {
        body: function ( data, row, column, node ) {
            if (node) {
                let html = node.innerHTML;
                let text = html.replace(/<br\s*\/?>/ig, "\n");
                let temp = document.createElement("div");
                temp.innerHTML = text;
                return temp.textContent || temp.innerText || "";
            }
            return data;
        }
    };

    let dt = $('#hiddenJadwalTable').DataTable({
        "responsive": false, "paging": false, "info": false, "ordering": false,
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-success btn-sm px-3 mr-2 rounded-pill shadow-sm',
                title: dtTitle,
                exportOptions: {
                    format: exportFormatter
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf mr-1"></i> Export PDF',
                className: 'btn btn-danger btn-sm px-3 mr-2 rounded-pill shadow-sm',
                orientation: 'landscape',
                pageSize: 'A4',
                title: dtTitle,
                exportOptions: {
                    format: exportFormatter
                },
                customize: function(doc) {
                    if (doc.content && doc.content[1] && doc.content[1].table) {
                        let colCount = doc.content[1].table.body[0].length;
                        let widths = Array(colCount).fill('*');
                        widths[0] = '5%'; 
                        doc.content[1].table.widths = widths;
                        
                        doc.content[1].layout = {
                            hLineWidth: function(i, node) { return 0.5; },
                            vLineWidth: function(i, node) { return 0.5; },
                            hLineColor: function(i, node) { return '#ccc'; },
                            vLineColor: function(i, node) { return '#ccc'; },
                            paddingLeft: function(i, node) { return 8; },
                            paddingRight: function(i, node) { return 8; },
                            paddingTop: function(i, node) { return 8; },
                            paddingBottom: function(i, node) { return 8; }
                        };
                        
                        let headerRow = doc.content[1].table.body[0];
                        for (let i = 0; i < headerRow.length; i++) {
                            headerRow[i].fillColor = '#f8f9fa';
                            headerRow[i].color = '#000';
                            headerRow[i].alignment = 'center';
                            headerRow[i].bold = true;
                        }
                        
                        for (let r = 1; r < doc.content[1].table.body.length; r++) {
                            let row = doc.content[1].table.body[r];
                            for (let c = 0; c < row.length; c++) {
                                row[c].fillColor = null; 
                                row[c].alignment = 'center';
                                row[c].fontSize = 8;
                            }
                        }
                    }
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print Jadwal',
                className: 'btn btn-info btn-sm px-3 rounded-pill shadow-sm',
                title: dtTitle,
                customize: function (win) {
                    $(win.document.head).find('link[rel="stylesheet"], style').remove();
                    $(win.document.head).append('<style>' +
                        '@page { size: landscape; margin: 1cm; }' +
                        'body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; background: #fff; padding: 10px; }' +
                        'h1 { text-align: center; font-size: 16pt; margin-bottom: 20px; font-weight: bold; }' +
                        'table { width: 100%; border-collapse: collapse; margin-top: 10px; }' +
                        'th, td { border: 1px solid #ccc; padding: 8px; text-align: center; font-size: 9pt; vertical-align: middle; line-height: 1.4; }' +
                        'th { background-color: #f8f9fa; color: #000; font-weight: bold; }' +
                        'tr { background-color: transparent !important; }' +
                        '</style>');
                }
            }
        ]
    });
    dt.buttons().container().appendTo('#exportContainer');
    @endif

    $('.select-jadwal').change(function() {
        let jam_id = $(this).data('jam-id');
        let class_id = $(this).data('class-id');
        let teaching_assignment_id = $(this).val();

        if(!teaching_assignment_id) return;

        $.ajax({
            url: "{{ route('operator.mapping.jadwal.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                jam_pelajaran_id: jam_id,
                class_id: class_id,
                teaching_assignment_id: teaching_assignment_id
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Jadwal tersimpan',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Bentrok!', res.message, 'error');
                    $(this).val('');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        });
    });

    $('.btn-delete-jadwal').click(function() {
        let jam_id = $(this).data('jam-id');
        let class_id = "{{ $selected_class_id }}";

        Swal.fire({
            title: 'Hapus Jadwal?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/operator/mapping/jadwal/${jam_id}`,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}",
                        class_id: class_id
                    },
                    success: function(res) {
                        if(res.success) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
