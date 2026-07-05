@extends('layouts.operator', ['page_title' => 'Manajemen User - Operator', 'active_menu' => 'users'])

@section('title', 'Manajemen User - Operator')

@section('content')
    
    <div class="content-wrapper">
        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Manajemen User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manajemen User</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        
        <section class="content">
            <div class="container-fluid">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="icon fas fa-ban"></i> Terjadi Kesalahan!</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card card-primary card-outline card-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="userTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="guru-tab" data-toggle="pill" href="#guru-pane" role="tab"
                                    aria-controls="guru-pane" aria-selected="true">Data Guru</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="siswa-tab" data-toggle="pill" href="#siswa-pane" role="tab"
                                    aria-controls="siswa-pane" aria-selected="false">Data Siswa</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="userTabsContent">

                            
                            <div class="tab-pane fade show active" id="guru-pane" role="tabpanel"
                                aria-labelledby="guru-tab">
                                <button class="btn btn-primary mb-3 mr-2" data-toggle="modal" data-target="#modalAddGuru">
                                    <i class="fas fa-plus"></i> Tambah Guru
                                </button>
                                <button class="btn btn-info mb-3" data-toggle="modal" data-target="#modalImportCSV">
                                    <i class="fas fa-file-import"></i> Import Guru/Siswa (CSV)
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped datatable">
                                        <thead>
                                            <tr>
                                                <th>NIP (Username)</th>
                                                <th>Nama Lengkap</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($gurus as $g)
                                                <tr>
                                                    <td>{{ $g->username }}</td>
                                                    <td>{{ $g->nama_lengkap }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning btn-edit-guru"
                                                            data-id="{{ $g->id }}" data-user-id="{{ $g->user_id }}"
                                                            data-nip="{{ $g->username }}" data-nama="{{ $g->nama_lengkap }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-info btn-reset-pass"
                                                            data-id="{{ $g->user_id }}" data-nama="{{ $g->nama_lengkap }}">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-delete"
                                                            data-id="{{ $g->user_id }}" data-role="guru">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="siswa-pane" role="tabpanel" aria-labelledby="siswa-tab">
                                <button class="btn btn-primary mb-3 mr-2" data-toggle="modal" data-target="#modalAddSiswa">
                                    <i class="fas fa-plus"></i> Tambah Siswa
                                </button>
                                <button class="btn btn-info mb-3" data-toggle="modal" data-target="#modalImportCSV">
                                    <i class="fas fa-file-import"></i> Import Guru/Siswa (CSV)
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped datatable">
                                        <thead>
                                            <tr>
                                                <th>NIS (Username)</th>
                                                <th>Nama Siswa</th>
                                                <th>Kelas</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($siswas as $s)
                                                <tr>
                                                    <td>{{ $s->username }}</td>
                                                    <td>{{ $s->nama }}</td>
                                                    <td>{{ $s->nama_kelas ?? '-' }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning btn-edit-siswa"
                                                            data-id="{{ $s->id }}" data-user-id="{{ $s->user_id }}"
                                                            data-nis="{{ $s->username }}" data-nama="{{ $s->nama }}"
                                                            data-kelas="{{ $s->kelas_id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-info btn-reset-pass"
                                                            data-id="{{ $s->user_id }}" data-nama="{{ $s->nama }}">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-delete"
                                                            data-id="{{ $s->user_id }}" data-role="siswa">
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
                    </div>
                    
                </div>

            </div>
        </section>
    </div>

    
    <div class="modal fade" id="modalAddGuru">
        <div class="modal-dialog">
            <form action="{{ route('operator.users.guru.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Guru Baru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIP (Username Login)</label>
                        <div class="input-group">
                            <input type="text" name="username" id="inputUsernameGuru" class="form-control"
                                value="{{ $autoGuruUsername }}" readonly required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="customUsernameGuru">
                                        <label class="custom-control-label" for="customUsernameGuru"
                                            style="font-weight:normal; font-size: 0.85rem; cursor:pointer;">Kustom</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="alert alert-info small">
                        Password default: <b>guru123</b>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalAddSiswa">
        <div class="modal-dialog">
            <form action="{{ route('operator.users.siswa.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Siswa Baru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIS (Username Login)</label>
                        <div class="input-group">
                            <input type="text" name="username" id="inputUsernameSiswa" class="form-control"
                                value="{{ $autoSiswaUsername }}" readonly required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="customUsernameSiswa">
                                        <label class="custom-control-label" for="customUsernameSiswa"
                                            style="font-weight:normal; font-size: 0.85rem; cursor:pointer;">Kustom</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control" required>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelas_list as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info small">
                        Password default: <b>siswa123</b>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalEditGuru">
        <div class="modal-dialog">
            <form action="{{ route('operator.users.guru.update') }}" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="editGuruId">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data Guru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIP (Username Login)</label>
                        <input type="text" name="username" id="editGuruNip" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="editGuruNama" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalEditSiswa">
        <div class="modal-dialog">
            <form action="{{ route('operator.users.siswa.update') }}" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="editSiswaId">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data Siswa</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIS (Username Login)</label>
                        <input type="text" name="username" id="editSiswaNis" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama" id="editSiswaNama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" id="editSiswaKelas" class="form-control" required>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelas_list as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalImportCSV" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <form method="POST" action="{{ route('operator.users.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-file-import mr-1"></i> Import Guru / Siswa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pilih File CSV/TXT</label>
                            <input type="file" name="csv_file" class="form-control-file" accept=".csv,.txt" required>
                        </div>

                        
                        <div id="importPreviewContainer" class="mt-3" style="display: none;">
                            <div class="card card-outline card-success">
                                <div class="card-header py-1 d-flex justify-content-between align-items-center">
                                    <h6 class="card-title text-success mb-0" style="font-size: 0.9em;"><i
                                            class="fas fa-eye mr-1"></i> Preview DatA</h6>
                                    <span id="detectedRoleBadge" class="badge badge-pill badge-primary">Guru</span>
                                </div>
                                <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-striped table-hover mb-0" style="font-size: 0.85em;">
                                        <thead class="bg-light">
                                            <tr id="previewTableHead">
                                                
                                            </tr>
                                        </thead>
                                        <tbody id="previewTableBody">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer py-1 text-muted text-right" style="font-size: 0.8em;">
                                    Total: <strong id="detectedRowCount">0</strong> baris data terdeteksi
                                </div>
                            </div>
                        </div>

                        
                        <div id="importErrorContainer" class="mt-3" style="display: none;">
                            <div class="alert alert-danger p-2 mb-0" style="font-size: 0.85em;">
                                <i class="fas fa-exclamation-circle mr-1"></i> <span id="importErrorMessage">Format file
                                    tidak cocok!</span>
                            </div>
                        </div>

                        <div class="card card-outline card-info mt-3">
                            <div class="card-header py-1">
                                <h6 class="card-title text-info mb-0" style="font-size: 0.9em;"><i
                                        class="fas fa-magic mr-1"></i> Sistem Auto-Detection Cerdas</h6>
                            </div>
                            <div class="card-body py-2 small">
                                Cukup unggah satu file CSV, sistem akan otomatis mengenali tipe datanya:
                                <ul class="pl-3 mb-0">
                                    <li>Jika header berisi kolom <code>kelas</code>: Terdeteksi sebagai <strong>Data
                                            Siswa</strong> (sistem akan otomatis mencocokkan kelas yang terdaftar).</li>
                                    <li>Jika tidak ada kolom kelas: Terdeteksi sebagai <strong>Data Guru</strong>.</li>
                                </ul>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="accordion" id="csvFormatAccordion">
                            <div class="card card-secondary card-outline mb-1">
                                <div class="card-header py-1" id="headingGuru">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left text-dark py-1"
                                            style="font-size: 0.85em;" type="button" data-toggle="collapse"
                                            data-target="#collapseGuru">
                                            <i class="fas fa-chevron-down mr-1"></i> Format CSV Guru
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseGuru" class="collapse show" data-parent="#csvFormatAccordion">
                                    <div class="card-body py-2 small">
                                        Wajib memiliki kolom: <code>username</code> (atau <code>nip</code>) dan
                                        <code>nama_lengkap</code>.<br>
                                        <pre class="bg-dark text-white p-2 rounded mb-0 mt-1" style="font-size: 0.85em;">username,nama_lengkap
                                1989010203,Rudi Hermawan
                                1987040506,Siti Aminah</pre>
                                        <span class="text-muted" style="font-size: 0.85em;">* Password default:
                                            <strong>guru123</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-secondary card-outline mb-0">
                                <div class="card-header py-1" id="headingSiswa">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left text-dark py-1"
                                            style="font-size: 0.85em;" type="button" data-toggle="collapse"
                                            data-target="#collapseSiswa">
                                            <i class="fas fa-chevron-right mr-1"></i> Format CSV Siswa
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseSiswa" class="collapse" data-parent="#csvFormatAccordion">
                                    <div class="card-body py-2 small">
                                        Wajib memiliki kolom: <code>username</code> (atau <code>nis</code>),
                                        <code>nama</code> (atau <code>nama_lengkap</code>), dan <code>kelas</code>.<br>
                                        <pre class="bg-dark text-white p-2 rounded mb-0 mt-1" style="font-size: 0.85em;">username,nama,kelas
                                260081,Ahmad Dani,7-A
                                260082,Budi Cahyono,7-B</pre>
                                        <span class="text-muted" style="font-size: 0.85em;">* Password default:
                                            <strong>siswa123</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Proses Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            
            $('.datatable').DataTable({
                "responsive": true, "autoWidth": false,
            });

            
            $('input[name="csv_file"]').on('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (evt) {
                    const text = evt.target.result;

                    
                    const lines = text.split(/\r\n|\r|\n/).map(l => l.trim()).filter(l => l.length > 0);
                    if (lines.length === 0) {
                        showError("File kosong atau tidak valid.");
                        return;
                    }

                    
                    const firstLine = lines[0];
                    const countComma = (firstLine.match(/,/g) || []).length;
                    const countSemicolon = (firstLine.match(/;/g) || []).length;
                    const countTab = (firstLine.match(/\t/g) || []).length;

                    let delimiter = ',';
                    if (countSemicolon > countComma && countSemicolon > countTab) {
                        delimiter = ';';
                    } else if (countTab > countComma && countTab > countSemicolon) {
                        delimiter = '\t';
                    }

                    
                    function splitCSVLine(line, delim) {
                        let result = [];
                        let current = '';
                        let inQuotes = false;
                        for (let i = 0; i < line.length; i++) {
                            let char = line[i];
                            if (char === '"' || char === "'") {
                                inQuotes = !inQuotes;
                            } else if (char === delim && !inQuotes) {
                                result.push(current.trim());
                                current = '';
                            } else {
                                current += char;
                            }
                        }
                        result.push(current.trim());

                        if (result.length === 1 && result[0].includes(delim)) {
                            return splitCSVLine(result[0], delim);
                        }
                        return result;
                    }

                    
                    const rawHeaders = splitCSVLine(firstLine, delimiter);
                    const headers = rawHeaders.map(h => h.replace(/['"]/g, '').toLowerCase().trim());

                    
                    function findHeaderIndex(patterns, headersList) {
                        for (let i = 0; i < headersList.length; i++) {
                            let cleanHeader = headersList[i].replace(/[^a-z0-9]/g, '');
                            for (let p of patterns) {
                                let cleanPattern = p.replace(/[^a-z0-9]/g, '');
                                if (cleanHeader.includes(cleanPattern)) {
                                    return i;
                                }
                            }
                        }
                        return -1;
                    }

                    const kelasIndex = findHeaderIndex(['kelas'], headers);
                    const isSiswa = (kelasIndex !== -1);

                    let usernameIndex = -1;
                    let namaIndex = -1;
                    let validationError = '';

                    if (isSiswa) {
                        usernameIndex = findHeaderIndex(['username', 'nis', 'nip'], headers);
                        namaIndex = findHeaderIndex(['nama', 'name', 'nama_lengkap'], headers);
                        if (usernameIndex === -1 || namaIndex === -1) {
                            validationError = 'Format Siswa tidak valid. Wajib ada kolom: <strong>username/nis</strong>, <strong>nama</strong>, dan <strong>kelas</strong>.';
                        }
                    } else {
                        usernameIndex = findHeaderIndex(['username', 'nip', 'nis'], headers);
                        namaIndex = findHeaderIndex(['nama', 'name', 'nama_lengkap'], headers);
                        if (usernameIndex === -1 || namaIndex === -1) {
                            validationError = 'Format Guru tidak valid. Wajib ada kolom: <strong>username/nip</strong> dan <strong>nama_lengkap/nama</strong>.';
                        }
                    }

                    if (validationError) {
                        showError(validationError);
                        return;
                    }

                    
                    $('#importErrorContainer').hide();

                    
                    $('#previewTableHead').empty();
                    $('#previewTableHead').append('<th>#</th>');
                    headers.forEach(h => {
                        $('#previewTableHead').append(`<th>${h.toUpperCase()}</th>`);
                    });

                    $('#previewTableBody').empty();
                    const maxPreviewRows = Math.min(5, lines.length - 1);

                    for (let i = 1; i <= maxPreviewRows; i++) {
                        const rowData = splitCSVLine(lines[i], delimiter);
                        if (rowData.length < headers.length) continue;

                        let trHtml = `<tr><td>${i}</td>`;
                        rowData.forEach((val, idx) => {
                            let isHighlighted = (idx === usernameIndex || idx === namaIndex || idx === kelasIndex);
                            let style = isHighlighted ? 'font-weight: bold; color: #0b57d0;' : '';
                            trHtml += `<td style="${style}">${val.replace(/['"]/g, '')}</td>`;
                        });
                        trHtml += '</tr>';
                        $('#previewTableBody').append(trHtml);
                    }

                    
                    $('#detectedRoleBadge').text(isSiswa ? 'Siswa' : 'Guru')
                        .removeClass('badge-primary badge-success')
                        .addClass(isSiswa ? 'badge-success' : 'badge-primary');

                    $('#detectedRowCount').text(lines.length - 1);
                    $('#importPreviewContainer').show();
                    $('button[type="submit"]').prop('disabled', false);
                };

                reader.readAsText(file);

                function showError(msg) {
                    $('#importPreviewContainer').hide();
                    $('#importErrorMessage').html(msg);
                    $('#importErrorContainer').show();
                    $('button[type="submit"]').prop('disabled', true);
                }
            });

            
            
            $(document).on('click', '.btn-delete', function () {
                const id = $(this).data('id');
                const role = $(this).data('role');

                Swal.fire({
                    title: 'Hapus User?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ url("operator/users") }}/' + id;
                        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                })
            });

            
            $(document).on('click', '.btn-reset-pass', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');

                Swal.fire({
                    title: 'Reset Password?',
                    text: `Reset password untuk ${nama}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Reset'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `{{ url('operator/users/reset-password') }}/${id}`;
                    }
                })
            });

            

            
            $(document).on('click', '.btn-edit-guru', function () {
                const id = $(this).data('user-id');
                const nip = $(this).data('nip');
                const nama = $(this).data('nama');

                $('#editGuruId').val(id);
                $('#editGuruNip').val(nip);
                $('#editGuruNama').val(nama);

                $('#modalEditGuru').modal('show');
            });

            
            $(document).on('click', '.btn-edit-siswa', function () {
                const id = $(this).data('user-id');
                const nis = $(this).data('nis');
                const nama = $(this).data('nama');
                const kelas = $(this).data('kelas');

                $('#editSiswaId').val(id);
                $('#editSiswaNis').val(nis);
                $('#editSiswaNama').val(nama);
                $('#editSiswaKelas').val(kelas);

                $('#modalEditSiswa').modal('show');
            });

            
            $('#customUsernameGuru').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#inputUsernameGuru').prop('readonly', false).focus();
                } else {
                    $('#inputUsernameGuru').prop('readonly', true).val('{{ $autoGuruUsername }}');
                }
            });

            
            $('#customUsernameSiswa').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#inputUsernameSiswa').prop('readonly', false).focus();
                } else {
                    $('#inputUsernameSiswa').prop('readonly', true).val('{{ $autoSiswaUsername }}');
                }
            });

        });
    </script>
@endpush