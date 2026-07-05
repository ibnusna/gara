@extends('layouts.guru')

@section('title', 'Ruang Materi (RPP) | GARA')

@php
    $active_menu = 'materi';
    $use_datatables = true;
@endphp

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manajemen RPP & Materi</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus-circle"></i> Tambah Materi Baru
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Materi Pembelajaran - {{ session('nama_mapel') }} Kelas
                            {{ session('nama_kelas') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <table id="tabelMateri" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Materi</th>
                                    <th>Sem</th>
                                    <th>Bab</th>
                                    <th>Bagian</th>
                                    <th>Judul Materi</th>
                                    <th>Link</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materiList as $m)
                                    <tr>
                                        <td><span class="badge badge-secondary">{{ $m->id_materi }}</span></td>
                                        <td>{{ $m->semester }}</td>
                                        <td>{{ $m->bab }}</td>
                                        <td>{{ $m->bagian }}</td>
                                        <td class="font-weight-bold">{{ $m->judul_materi }}</td>
                                        <td>
                                            
                                            @if ($m->link_ppt) <i class="fas fa-file-powerpoint text-danger mr-1"
                                            title="PPT"></i> @endif
                                            @if ($m->link_youtube) <i class="fab fa-youtube text-danger mr-1"
                                            title="YouTube"></i> @endif
                                            @if ($m->link_modul) <i class="fas fa-file-pdf text-danger mr-1" title="Modul"></i>
                                            @endif
                                            @if ($m->link_tugas) <i class="fas fa-tasks text-success mr-1" title="Tugas"></i>
                                            @endif
                                            @if ($m->link_notebook) <i class="fas fa-book text-warning mr-1"
                                            title="Notebook"></i> @endif

                                            
                                            @if ($m->link_ppt || $m->link_youtube || $m->link_modul || $m->link_tugas || $m->link_notebook)
                                                <button
                                                    class="btn btn-sm btn-link-preview btn-view-links p-0 ml-1"
                                                    title="Lihat Semua Link"
                                                    data-judul="{{ $m->judul_materi }}"
                                                    data-ppt="{{ $m->link_ppt }}"
                                                    data-youtube="{{ $m->link_youtube }}"
                                                    data-modul="{{ $m->link_modul }}"
                                                    data-tugas="{{ $m->link_tugas }}"
                                                    data-notebook="{{ $m->link_notebook }}"
                                                    style="color: hsl(220,90%,50%); background:none; border:none; cursor:pointer;"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info btn-edit" data-id="{{ $m->id }}"
                                                data-semester="{{ $m->semester }}" data-bab="{{ $m->bab }}"
                                                data-bagian="{{ $m->bagian }}" data-judul="{{ $m->judul_materi }}"
                                                data-ppt="{{ $m->link_ppt }}" data-youtube="{{ $m->link_youtube }}"
                                                data-modul="{{ $m->link_modul }}" data-tugas="{{ $m->link_tugas }}"
                                                data-notebook="{{ $m->link_notebook }}"><i class="fas fa-edit"></i></button>

                                            <button class="btn btn-sm btn-danger btn-hapus" data-id="{{ $m->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    
    <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Materi Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('guru.materi.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester" class="form-control" required>
                                        <option value="1">1 (Ganjil)</option>
                                        <option value="2">2 (Genap)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bab Ke-</label>
                                    <input type="number" name="bab" class="form-control" required min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bagian (Pertemuan)</label>
                                    <input type="number" name="bagian" class="form-control" required min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Judul Materi</label>
                                    <input type="text" name="judul_materi" class="form-control" required
                                        placeholder="Contoh: Sistem Pencernaan Manusia">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>Tautan Sumber Belajar (Opsional)</h6>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><i class="fas fa-file-powerpoint text-danger"></i> Link
                                PPT</label>
                            <div class="col-sm-8"><input type="url" name="link_ppt" id="add_link_ppt" class="form-control"
                                    placeholder="https://..."></div>
                            <div class="col-sm-1 pl-0">
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('add_link_ppt', 'docs_only')" title="Pilih dari Drive"><i class="fab fa-google-drive"></i></button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><i class="fab fa-youtube text-danger"></i> Link
                                YouTube</label>
                            <div class="col-sm-9"><input type="url" name="link_youtube" id="add_link_youtube" class="form-control"
                                    placeholder="https://..."></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><i class="fas fa-file-pdf text-danger"></i> Link Modul
                                PDF</label>
                            <div class="col-sm-8"><input type="url" name="link_modul" id="add_link_modul" class="form-control"
                                    placeholder="https://..."></div>
                            <div class="col-sm-1 pl-0">
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('add_link_modul', 'docs_only')" title="Pilih dari Drive"><i class="fab fa-google-drive"></i></button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><i class="fas fa-tasks text-success"></i> Link
                                Tugas</label>
                            <div class="col-sm-8"><input type="url" name="link_tugas" id="add_link_tugas" class="form-control"
                                    placeholder="https://..."></div>
                            <div class="col-sm-1 pl-0">
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('add_link_tugas', 'docs_only')" title="Pilih dari Drive"><i class="fab fa-google-drive"></i></button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><i class="fas fa-book text-warning"></i> Link
                                Notebook</label>
                            <div class="col-sm-8"><input type="url" name="link_notebook" id="add_link_notebook" class="form-control"
                                    placeholder="https://..."></div>
                            <div class="col-sm-1 pl-0">
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('add_link_notebook', 'docs_only')" title="Pilih dari Drive"><i class="fab fa-google-drive"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Materi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester" id="edit_semester" class="form-control" required>
                                        <option value="1">1 (Ganjil)</option>
                                        <option value="2">2 (Genap)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bab Ke-</label>
                                    <input type="number" name="bab" id="edit_bab" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bagian</label>
                                    <input type="number" name="bagian" id="edit_bagian" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Judul Materi</label>
                                    <input type="text" name="judul_materi" id="edit_judul" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>Edit Tautan Sumber Belajar</h6>
                        <div class="form-group row"><label class="col-sm-3"><i
                                    class="fas fa-file-powerpoint text-danger"></i> Link PPT</label>
                            <div class="col-sm-8"><input type="url" name="link_ppt" id="edit_ppt" class="form-control"></div>
                            <div class="col-sm-1 pl-0"><button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('edit_ppt', 'docs_only')"><i class="fab fa-google-drive"></i></button></div>
                        </div>
                        <div class="form-group row"><label class="col-sm-3"><i class="fab fa-youtube text-danger"></i>
                                YouTube</label>
                            <div class="col-sm-9"><input type="url" name="link_youtube" id="edit_youtube"
                                    class="form-control"></div>
                        </div>
                        <div class="form-group row"><label class="col-sm-3"><i class="fas fa-file-pdf text-danger"></i>
                                Modul</label>
                            <div class="col-sm-8"><input type="url" name="link_modul" id="edit_modul" class="form-control"></div>
                            <div class="col-sm-1 pl-0"><button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('edit_modul', 'docs_only')"><i class="fab fa-google-drive"></i></button></div>
                        </div>
                        <div class="form-group row"><label class="col-sm-3"><i class="fas fa-tasks text-success"></i>
                                Tugas</label>
                            <div class="col-sm-8"><input type="url" name="link_tugas" id="edit_tugas" class="form-control"></div>
                            <div class="col-sm-1 pl-0"><button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('edit_tugas', 'docs_only')"><i class="fab fa-google-drive"></i></button></div>
                        </div>
                        <div class="form-group row"><label class="col-sm-3"><i class="fas fa-book text-warning"></i>
                                Notebook</label>
                            <div class="col-sm-8"><input type="url" name="link_notebook" id="edit_notebook"
                                    class="form-control"></div>
                            <div class="col-sm-1 pl-0"><button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openPicker('edit_notebook', 'docs_only')"><i class="fab fa-google-drive"></i></button></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalLinkPreview" tabindex="-1" role="dialog" aria-labelledby="modalLinkPreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(135deg, hsl(220,90%,50%), hsl(220,90%,35%)); color: white; border: none; padding: 20px 24px;">
                    <div>
                        <h5 class="modal-title mb-0" id="modalLinkPreviewLabel" style="font-weight: 700; font-size: 1rem;">
                            <i class="fas fa-link mr-2"></i> Tautan Sumber Belajar
                        </h5>
                        <small id="previewMateriJudul" style="opacity: 0.85; font-size: 0.8rem;"></small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1; font-size: 1.4rem;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="previewLinkBody" style="padding: 20px 24px;">
                    
                </div>
                <div class="modal-footer" style="border: none; padding: 12px 24px; background: #f8fafc;">
                    <small class="text-muted"><i class="fas fa-shield-alt mr-1"></i> Semua link dibuka dengan noopener noreferrer</small>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#tabelMateri').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" }
                });

                $('.btn-edit').click(function () {
                    const id = $(this).data('id');
                    const sem = $(this).data('semester');
                    const bab = $(this).data('bab');
                    const bag = $(this).data('bagian');
                    const judul = $(this).data('judul');

                    $('#edit_semester').val(sem);
                    $('#edit_bab').val(bab);
                    $('#edit_bagian').val(bag);
                    $('#edit_judul').val(judul);
                    $('#edit_ppt').val($(this).data('ppt'));
                    $('#edit_youtube').val($(this).data('youtube'));
                    $('#edit_modul').val($(this).data('modul'));
                    $('#edit_tugas').val($(this).data('tugas'));
                    $('#edit_notebook').val($(this).data('notebook'));

                    $('#editForm').attr('action', '{{ route('guru.materi.update', 0) }}'.replace('/0', '/' + id));

                    $('#modalEdit').modal('show');
                });

                $('.btn-hapus').click(function () {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Hapus Materi?',
                        text: "Data yang dihapus tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route('guru.materi.destroy', 0) }}'.replace('/0', '/' + id);

                            const tokenInput = document.createElement('input');
                            tokenInput.type = 'hidden';
                            tokenInput.name = '_token';
                            tokenInput.value = '{{ csrf_token() }}';

                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'DELETE';

                            form.appendChild(tokenInput);
                            form.appendChild(methodInput);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });

                
                
                $(document).on('click', '.btn-view-links', function () {
                    const judul   = $(this).data('judul')   || '';
                    const ppt     = $(this).data('ppt')     || '';
                    const youtube = $(this).data('youtube') || '';
                    const modul   = $(this).data('modul')   || '';
                    const tugas   = $(this).data('tugas')   || '';
                    const notebook= $(this).data('notebook')|| '';

                    $('#previewMateriJudul').text(judul);

                    
                    const linkDefs = [
                        { label: 'Presentasi (PPT)',  icon: 'fas fa-file-powerpoint', color: '#dc2626', url: ppt },
                        { label: 'Video YouTube',      icon: 'fab fa-youtube',          color: '#dc2626', url: youtube },
                        { label: 'Modul PDF',          icon: 'fas fa-file-pdf',          color: '#dc2626', url: modul },
                        { label: 'Tugas',              icon: 'fas fa-tasks',             color: '#16a34a', url: tugas },
                        { label: 'Notebook / Catatan', icon: 'fas fa-book',              color: '#d97706', url: notebook },
                    ].filter(item => item.url && item.url.trim() !== '');

                    if (linkDefs.length === 0) {
                        $('#previewLinkBody').html('<p class="text-muted text-center py-3">Tidak ada link tersedia.</p>');
                    } else {
                        const cardsHtml = linkDefs.map(item => {
                            
                            const safeUrl = item.url.replace(/"/g, '&quot;');
                            const displayUrl = item.url.length > 55 ? item.url.substring(0, 52) + '...' : item.url;
                            return `
                                <div style="
                                    display: flex;
                                    align-items: center;
                                    gap: 14px;
                                    padding: 14px 16px;
                                    margin-bottom: 10px;
                                    border-radius: 12px;
                                    border: 1.5px solid #e2e8f0;
                                    background: #f8fafc;
                                    transition: all 0.2s;
                                " onmouseover="this.style.borderColor='hsl(220,90%,65%)'; this.style.background='#f0f6ff'"
                                   onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'">
                                    <div style="
                                        width: 40px; height: 40px;
                                        border-radius: 10px;
                                        background: ${item.color}15;
                                        display: flex; align-items: center; justify-content: center;
                                        flex-shrink: 0;
                                    ">
                                        <i class="${item.icon}" style="color: ${item.color}; font-size: 1.1rem;"></i>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #1e293b; margin-bottom: 2px;">${item.label}</div>
                                        <div style="font-size: 0.75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayUrl}</div>
                                    </div>
                                    <a href="${safeUrl}" target="_blank" rel="noopener noreferrer"
                                       style="
                                           padding: 7px 14px;
                                           background: hsl(220,90%,50%);
                                           color: white;
                                           border-radius: 8px;
                                           font-size: 0.8rem;
                                           font-weight: 600;
                                           text-decoration: none;
                                           white-space: nowrap;
                                           transition: background 0.2s;
                                           flex-shrink: 0;
                                       "
                                       onmouseover="this.style.background='hsl(220,90%,40%)'"
                                       onmouseout="this.style.background='hsl(220,90%,50%)'"
                                    >
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka
                                    </a>
                                </div>
                            `;
                        }).join('');
                        $('#previewLinkBody').html(cardsHtml);
                    }

                    $('#modalLinkPreview').modal('show');
                });
            });
        </script>
        <script src="{{ asset('assets/js/gara-picker.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('assets/js/youtube-helper.js') }}?v=1.0"></script>
        <script>
            function openPicker(targetInputId, mode) {
                let picker = new GaraPicker({
                    developerKey: '{{ config('services.google.developer_key') }}',
                    appId: '{{ config('services.google.app_id') }}',
                    tokenUrl: '{{ route('guru.google.picker_token') }}',
                    mode: mode,
                    onSelect: function(data) {
                        let finalUrl = data.url;
                        if (mode === 'youtube' || data.serviceId === 'youtube') {
                            finalUrl = transformYoutubeUrlToEmbed(data.url) || data.url;
                        }
                        
                        document.getElementById(targetInputId).value = finalUrl;
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Link berhasil disalin',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
                picker.open();
            }
        </script>
    @endpush
@endsection