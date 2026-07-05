@extends('layouts.guru')

@section('title', 'Ruang Tugas ' . session('nama_mapel'))

@php
    $active_menu = 'ruang_tugas';
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Bagikan Tugas & Materi</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary rounded-pill shadow-sm px-4" data-toggle="modal" data-target="#modalTambahTugas">
                            <i class="fas fa-plus mr-1"></i> Buat Tugas Baru
                        </button>
                    </div>
                </div>
                <div class="alert alert-info py-2 small mb-0 mt-2">
                    <i class="fas fa-info-circle mr-1"></i> Ruang ini digunakan untuk membagikan detail tugas, instruksi, dan mengumpulkan file/link dari siswa.
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <ul class="nav nav-tabs mb-4 px-2" id="tugasTab" role="tablist" style="border-bottom-width: 2px;">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="aktif-tab" data-toggle="tab" href="#aktif" role="tab" aria-controls="aktif" aria-selected="true" style="color: #495057;">Tugas Aktif ({{ count($tugas_aktif) }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="draft-tab" data-toggle="tab" href="#draft" role="tab" aria-controls="draft" aria-selected="false" style="color: #6c757d;">Arsip / Draft ({{ count($tugas_draft) }})</a>
                    </li>
                </ul>

                <div class="tab-content" id="tugasTabContent">
                    
                    <div class="tab-pane fade show active" id="aktif" role="tabpanel" aria-labelledby="aktif-tab">
                        <div class="row">
                            @forelse($tugas_aktif as $t)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card shadow-sm h-100" style="border-radius:12px; transition: 0.3s;"
                                        onmouseover="this.style.boxShadow='0 10px 20px rgba(0,0,0,0.1)'"
                                        onmouseout="this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)'">
                                        
                                        <div class="card-body pb-0">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title font-weight-bold mb-0 text-dark" style="line-height:1.4;">
                                                    {{ $t->judul }}
                                                </h5>
                                                
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light p-1" type="button" data-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v px-1"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item editbtn" href="#" data-id="{{ $t->id }}" data-judul="{{ $t->judul }}" data-deskripsi="{{ $t->deskripsi }}" data-deadline="{{ $t->batas_waktu ? date('Y-m-d\TH:i', strtotime($t->batas_waktu)) : '' }}" data-link="{{ $t->link_lampiran }}" data-upload="{{ $t->allow_upload }}" data-close="{{ $t->is_auto_close }}">
                                                            <i class="fas fa-edit text-primary mr-2"></i> Edit Tugas
                                                        </a>
                                                        <form action="{{ route('guru.ruang_tugas.toggle') }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $t->id }}">
                                                            <input type="hidden" name="current_status" value="{{ $t->status }}">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-archive text-warning mr-2"></i> Arsipkan (Draft)
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('guru.ruang_tugas.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus permanen tugas ini beserta seluruh file siswanya?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash mr-2"></i> Hapus Permanen
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <p class="small text-muted mb-2">
                                                <i class="far fa-clock mr-1"></i> 
                                                Batas Waktu: 
                                                @if($t->batas_waktu)
                                                    <b class="{{ strtotime($t->batas_waktu) < time() ? 'text-danger' : 'text-success' }}">
                                                        {{ date('d M Y, H:i', strtotime($t->batas_waktu)) }}
                                                    </b>
                                                @else
                                                    <i>Tidak ada batas waktu</i>
                                                @endif
                                            </p>
                                            
                                            <p class="card-text small text-secondary" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ Str::limit(strip_tags($t->deskripsi), 80) }}
                                            </p>
                                        </div>
                                        
                                        <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                            <div class="bg-light rounded p-2 mb-3 d-flex justify-content-between text-center align-items-center">
                                                <div class="w-50" style="border-right: 1px solid #dee2e6;">
                                                    <div class="font-weight-bold text-primary" style="font-size:1.1rem;">
                                                        {{ $t->total_dikumpulkan }}
                                                    </div>
                                                    <div class="small text-muted">Dikumpulkan</div>
                                                </div>
                                                <div class="w-50">
                                                    @if($t->allow_upload)
                                                        <div class="text-success"><i class="fas fa-upload"></i></div>
                                                        <div class="small text-success">Upload Aktif</div>
                                                    @else
                                                        <div class="text-secondary"><i class="fas fa-ban"></i></div>
                                                        <div class="small text-secondary">Hanya Info</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <a href="{{ route('guru.ruang_tugas.show', $t->id) }}"
                                                class="btn btn-primary btn-block btn-sm rounded-pill font-weight-bold">
                                                <i class="fas fa-tasks mr-1"></i> Monitoring & File Siswa
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/folder-is-empty-4064360-3363921.png" width="150" class="mb-3 opacity-50">
                                    <h5 class="text-muted font-weight-bold">Belum ada Tugas Aktif</h5>
                                    <p class="text-muted small">Buat tugas baru untuk mulai mengumpulkan pekerjaan siswa.</p>
                                    <button class="btn btn-primary mt-2 rounded-pill shadow-sm" data-toggle="modal" data-target="#modalTambahTugas">
                                        <i class="fas fa-plus mr-1"></i> Buat Tugas Baru
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    
                    <div class="tab-pane fade" id="draft" role="tabpanel" aria-labelledby="draft-tab">
                        <div class="row">
                            @forelse($tugas_draft as $t)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card shadow-sm h-100 bg-light" style="border-radius:12px;">
                                        
                                        <div class="card-body pb-0">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title font-weight-bold mb-0 text-secondary" style="line-height:1.4;">
                                                    {{ $t->judul }}
                                                </h5>
                                                
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary p-1" type="button" data-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v px-1"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item editbtn" href="#" data-id="{{ $t->id }}" data-judul="{{ $t->judul }}" data-deskripsi="{{ $t->deskripsi }}" data-deadline="{{ $t->batas_waktu ? date('Y-m-d\TH:i', strtotime($t->batas_waktu)) : '' }}" data-link="{{ $t->link_lampiran }}" data-upload="{{ $t->allow_upload }}" data-close="{{ $t->is_auto_close }}">
                                                            <i class="fas fa-edit text-primary mr-2"></i> Edit Tugas
                                                        </a>
                                                        <form action="{{ route('guru.ruang_tugas.toggle') }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $t->id }}">
                                                            <input type="hidden" name="current_status" value="{{ $t->status }}">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-eye text-success mr-2"></i> Aktifkan
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('guru.ruang_tugas.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus permanen tugas ini beserta seluruh file siswanya?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash mr-2"></i> Hapus Permanen
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <p class="card-text small text-secondary mt-3">
                                                {{ Str::limit(strip_tags($t->deskripsi), 80) }}
                                            </p>
                                        </div>
                                        
                                        <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 text-right">
                                            <form action="{{ route('guru.ruang_tugas.toggle') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $t->id }}">
                                                <input type="hidden" name="current_status" value="{{ $t->status }}">
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill font-weight-bold px-3">
                                                    <i class="fas fa-bullhorn mr-1"></i> Terbitkan Ulang
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <h5 class="text-muted">Tidak ada tugas dalam arsip.</h5>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    
    <div class="modal fade" id="modalTambahTugas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="modalTitle"><i class="fas fa-plus-circle mr-2"></i> Buat Tugas Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('guru.ruang_tugas.store') }}" method="POST" id="formTugas">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="formMethod">
                    
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Judul Tugas / Materi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="t_judul" class="form-control" required placeholder="Contoh: Tugas Praktikum Biologi 1">
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Instruksi / Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" id="t_deskripsi" class="form-control" rows="4" required placeholder="Jelaskan apa yang harus dikerjakan oleh siswa..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Link Lampiran (Opsional)</label>
                                    <div class="input-group">
                                        <input type="url" name="link_lampiran" id="t_link" class="form-control" placeholder="https://youtube.com/... atau link Drive">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" onclick="openTugasPicker()" title="Pilih dari Drive"><i class="fab fa-google-drive"></i></button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Jika ada bahan bacaan/video referensi.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Batas Waktu Pengumpulan (Opsional)</label>
                                    <input type="datetime-local" name="batas_waktu" id="t_deadline" class="form-control">
                                    <small class="text-muted">Kosongkan jika tidak ada deadline.</small>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mt-3" style="border: 1px solid #e9ecef;">
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-cogs mr-1"></i> Pengaturan Tambahan</h6>
                            
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" name="allow_upload" id="t_upload" value="1" checked>
                                <label class="custom-control-label font-weight-normal" for="t_upload" style="cursor:pointer;">
                                    <b>Izinkan Pengumpulan File/Link</b>
                                    <br><small class="text-muted">Matikan jika ini hanya materi bacaan/pengumuman.</small>
                                </label>
                            </div>
                            
                            <hr class="my-2">
                            
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_auto_close" id="t_close" value="1">
                                <label class="custom-control-label font-weight-normal" for="t_close" style="cursor:pointer;">
                                    <b>Kunci Otomatis Setelah Deadline</b>
                                    <br><small class="text-muted">Siswa tidak bisa mengumpulkan jika melewati batas waktu.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnSubmit"><i class="fas fa-paper-plane mr-1"></i> Publikasikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        $('#modalTambahTugas').on('hidden.bs.modal', function () {
            $('#formTugas')[0].reset();
            $('#modalTitle').html('<i class="fas fa-plus-circle mr-2"></i> Buat Tugas Baru');
            $('#btnSubmit').html('<i class="fas fa-paper-plane mr-1"></i> Publikasikan');
            $('#formTugas').attr('action', '{{ route('guru.ruang_tugas.store') }}');
            $('#formMethod').val('POST');
            $('#t_upload').prop('checked', true);
            $('#t_close').prop('checked', false);
        });

        
        $('.editbtn').click(function(e) {
            e.preventDefault();
            
            var id = $(this).data('id');
            var judul = $(this).data('judul');
            var deskripsi = $(this).data('deskripsi');
            var link = $(this).data('link');
            var deadline = $(this).data('deadline');
            var upload = $(this).data('upload');
            var close = $(this).data('close');

            $('#modalTitle').html('<i class="fas fa-edit mr-2"></i> Edit Tugas');
            $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
            
            
            $('#t_judul').val(judul);
            $('#t_deskripsi').val(deskripsi);
            $('#t_link').val(link);
            $('#t_deadline').val(deadline);
            
            $('#t_upload').prop('checked', upload == 1);
            $('#t_close').prop('checked', close == 1);

            
            $('#formTugas').attr('action', '/guru/ruang-tugas/' + id);
            $('#formMethod').val('PUT');

            $('#modalTambahTugas').modal('show');
        });
    });
</script>
<script src="{{ asset('assets/js/gara-picker.js') }}?v={{ time() }}"></script>
<script>
    function openTugasPicker() {
        let picker = new GaraPicker({
            developerKey: '{{ config('services.google.developer_key') }}',
            appId: '{{ config('services.google.app_id') }}',
            tokenUrl: '{{ route('guru.google.picker_token') }}',
            mode: 'docs_only', 
            onSelect: function(data) {
                document.getElementById('t_link').value = data.url;
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Lampiran berhasil disalin',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
        picker.open();
    }
</script>
@endpush