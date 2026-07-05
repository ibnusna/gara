@extends('layouts.guru')

@section('title', 'Ruang Kompetensi | GARA')

@php
    $active_menu = 'ruang_kompetensi';
    $use_datatables = true;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tugas_admin_style.css') }}?v=1.1">
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="m-0 text-dark" style="font-weight: 800; letter-spacing: -0.5px;">Ruang Kompetensi</h1>
                        <p class="text-muted m-0 small">Modul Evaluasi Berbasis In-App Browser</p>
                    </div>
                    <button class="btn btn-primary shadow-sm rounded-pill px-4 py-2 font-weight-bold" data-toggle="modal"
                        data-target="#modalTambah">
                        <i class="fas fa-plus mr-2"></i> Tambah Evaluasi Baru
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-2">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-modern">
                    <ul class="nav nav-tabs nav-tabs-modern" id="evaluasi-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-aktif-link" data-toggle="pill" href="#tab-aktif" role="tab">Evaluasi Aktif</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-arsip-link" data-toggle="pill" href="#tab-arsip" role="tab">Arsip</a>
                        </li>
                    </ul>

                    <div class="card-body p-0">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="tab-aktif">
                                @if($evaluasiAktif->isEmpty())
                                    <div class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80"
                                            class="mb-3 opacity-50" style="filter: grayscale(100%);">
                                        <h6 class="text-muted font-weight-bold">Belum ada evaluasi aktif.</h6>
                                        <p class="text-muted small">Klik tombol "Tambah Evaluasi Baru" untuk membuat sesi ujian baru.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-modern datatable-init">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40%;">Judul Evaluasi</th>
                                                    <th style="width: 25%;">Link Eksternal</th>
                                                    <th style="width: 15%;">Waktu (Menit)</th>
                                                    <th style="width: 20%;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($evaluasiAktif as $eval)
                                                    <tr>
                                                        <td>
                                                            <span class="task-title">{{ $eval->judul }}</span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ $eval->link_evaluasi }}" target="_blank" class="badge badge-light text-primary border px-2 py-1" style="white-space:normal; word-break: break-all;">
                                                                <i class="fas fa-external-link-alt mr-1"></i> Buka Link
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="badge-modern safe">
                                                                <i class="far fa-clock"></i>
                                                                <span>{{ $eval->waktu_menit }} Menit</span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-action-group">
                                                                <button class="btn-action edit btn-edit" data-eval="{{ base64_encode(json_encode($eval)) }}" title="Edit">
                                                                    <i class="fas fa-pen"></i>
                                                                </button>
                                                                <form action="{{ route('guru.ruang_kompetensi.toggle', $eval->id) }}" method="POST" onsubmit="return confirm('Arsipkan evaluasi ini?')" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="current_status" value="aktif">
                                                                    <button type="submit" class="btn-action archive" title="Arsipkan">
                                                                        <i class="fas fa-box-archive"></i>
                                                                    </button>
                                                                </form>
                                                                <button class="btn-action delete btn-hapus" data-id="{{ $eval->id }}" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            
                            <div class="tab-pane fade" id="tab-arsip">
                                @if($evaluasiArsip->isEmpty())
                                    <div class="p-4 text-center text-muted small">Belum ada evaluasi yang diarsipkan.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-modern datatable-init">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50%;">Judul Evaluasi</th>
                                                    <th style="width: 30%;">Waktu Pembuatan</th>
                                                    <th style="width: 20%;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($evaluasiArsip as $eval)
                                                    <tr style="background: #fafafa;">
                                                        <td>
                                                            <span class="task-title text-muted">{{ $eval->judul }}</span>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($eval->created_at)->format('d M Y') }}</td>
                                                        <td class="text-center">
                                                            <div class="btn-action-group">
                                                                <form action="{{ route('guru.ruang_kompetensi.toggle', $eval->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="current_status" value="arsip">
                                                                    <button type="submit" class="btn-action restore" title="Publikasikan Kembali">
                                                                        <i class="fas fa-upload"></i>
                                                                    </button>
                                                                </form>
                                                                <button class="btn-action edit btn-edit" data-eval="{{ base64_encode(json_encode($eval)) }}">
                                                                    <i class="fas fa-pen"></i>
                                                                </button>
                                                                <button class="btn-action delete btn-hapus" data-id="{{ $eval->id }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
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

    
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white pb-0 border-bottom-0">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">Tambah Evaluasi Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="formEvaluasi" action="{{ route('guru.ruang_kompetensi.store') }}" method="POST">
                    @csrf
                    <div id="methodContainer"></div>
                    <div class="modal-body">
                        <div class="form-group mb-4">
                            <label class="small text-uppercase text-muted font-weight-bold">Judul Evaluasi</label>
                            <input type="text" name="judul" id="inputJudul" class="form-control form-control-lg" placeholder="Contoh: Ulangan Harian BAB 1" required>
                        </div>
                        <div class="form-group mb-4">
                            <label class="small text-uppercase text-muted font-weight-bold">Link Soal (External URL)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-link"></i></span>
                                </div>
                                <input type="url" name="link_evaluasi" id="inputLink" class="form-control border-left-0" placeholder="https://..." required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" onclick="openGFormPicker('inputLink')" title="Pilih Google Form dari Drive">
                                        <i class="fab fa-google-drive mr-1"></i> Pilih Form
                                    </button>
                                </div>
                            </div>
                            <small class="text-danger mt-1 d-block">*Pastikan platform mendukung In-App Browser (Iframe).</small>
                        </div>
                        <div class="form-group mb-4">
                            <label class="small text-uppercase text-muted font-weight-bold">Alokasi Waktu (Menit)</label>
                            <input type="number" name="waktu_menit" id="inputWaktu" class="form-control" placeholder="Contoh: 60" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            if ($.fn.DataTable) {
                $('.datatable-init').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "language": { "search": "", "searchPlaceholder": "Cari evaluasi..." },
                    "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                });
            }

            $('[data-target="#modalTambah"]').click(function () {
                $('#formEvaluasi').attr('action', '{{ route('guru.ruang_kompetensi.store') }}');
                $('#methodContainer').html('');
                $('#inputJudul').val(''); 
                $('#inputLink').val(''); 
                $('#inputWaktu').val('');
                $('#modalTitle').text('Tambah Evaluasi Baru');
            });

            $(document).on('click', '.btn-edit', function () {
                const evalItem = JSON.parse(atob($(this).data('eval')));
                $('#formEvaluasi').attr('action', '{{ route('guru.ruang_kompetensi.update', 0) }}'.replace('/0', '/' + evalItem.id));
                $('#methodContainer').html('<input type="hidden" name="_method" value="PUT">');
                $('#inputJudul').val(evalItem.judul);
                $('#inputLink').val(evalItem.link_evaluasi);
                $('#inputWaktu').val(evalItem.waktu_menit);
                $('#modalTitle').text('Edit Evaluasi');
                $('#modalTambah').modal('show');
            });

            $(document).on('click', '.btn-hapus', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Evaluasi?',
                    text: 'Data evaluasi akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) {
                        const f = document.createElement('form');
                        f.method = 'POST';
                        f.action = '{{ route('guru.ruang_kompetensi.destroy', 0) }}'.replace('/0', '/' + id);
                        f.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
                        document.body.appendChild(f);
                        f.submit();
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('assets/js/gara-picker.js') }}?v={{ time() }}"></script>
    <script>
        function openGFormPicker(targetInputId) {
            let picker = new GaraPicker({
                developerKey: '{{ config('services.google.developer_key') }}',
                appId: '{{ config('services.google.app_id') }}',
                tokenUrl: '{{ route('guru.google.picker_token') }}',
                mode: 'gforms_only',
                onSelect: function(data) {
                    let formId = data.id;
                    
                    
                    Swal.fire({
                        title: 'Menghubungkan Google Form...',
                        text: 'Sedang mengambil public view link...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    
                    $.ajax({
                        url: '{{ route('guru.google.resolve_form') }}',
                        method: 'GET',
                        data: { form_id: formId },
                        success: function(response) {
                            let resolvedUrl = response.resolved_url;
                            document.getElementById(targetInputId).value = resolvedUrl;
                            
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Google Form berhasil dihubungkan',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        },
                        error: function(xhr) {
                            
                            let fallbackUrl = `https://docs.google.com/forms/d/${formId}/viewform?usp=dialog`;
                            document.getElementById(targetInputId).value = fallbackUrl;
                            
                            Swal.fire({
                                icon: 'warning',
                                title: 'Peringatan',
                                text: 'Gagal mendapatkan tautan publik langsung, tautan default dipasang sebagai fallback.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
            picker.open();
        }
    </script>
@endpush
