@extends('layouts.super_admin')
@section('title', 'Manajemen Admin & Operator | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Admin & Operator Management</h1>
            <div>
                <button class="btn btn-info mr-2" data-toggle="modal" data-target="#importUserModal"><i class="fas fa-file-import"></i>
                    Import CSV</button>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal"><i class="fas fa-user-plus"></i>
                    Tambah Pengguna</button>
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
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td>
                                        <strong>{{ $u->nama_lengkap ?: $u->username }}</strong><br>
                                        <small class="text-muted">{{ $u->username }}</small>
                                    </td>
                                    <td><span class="badge badge-info text-uppercase">{{ $u->role->role_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if ($u->status_aktif)
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Suspended</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($u->id != 1 || session('user_id') == 1)
                                            <button class="btn btn-sm btn-info reset-btn" data-id="{{ $u->id }}"
                                                data-username="{{ $u->username }}" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>

                                            @if ($u->id != 1)
                                                <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $u->id }}"
                                                    title="Hapus Pengguna">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted"><i class="fas fa-shield-alt"></i> Protected</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('superadmin.users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Admin/Operator</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required autocomplete="off"
                                placeholder="Contoh: Ahmad Fauzi">
                        </div>
                        <div class="form-group">
                            <label>ID Login (Username)</label>
                            <input type="text" name="username" class="form-control" required autocomplete="off"
                                placeholder="Contoh: admin01">
                        </div>
                        <div class="form-group">
                            <label>Peran (Role)</label>
                            <select name="role_id" class="form-control" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">
                                        {{ strtoupper($r->role_name === 'super_admin' ? 'ADMIN' : $r->role_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="alert alert-info py-2" style="font-size: 0.9em;">
                            <i class="fas fa-info-circle"></i> Password akan di-generate otomatis:<br>
                            - Admin: <b>admingara776</b><br>
                            - Operator: <b>garaop457</b>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="resetPasswordForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="reset_username" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6"
                                placeholder="Masukkan password baru">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="importUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <form method="POST" action="{{ route('superadmin.users.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-file-import mr-1"></i> Import Admin & Operator</h5>
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
                                    <h6 class="card-title text-success mb-0" style="font-size: 0.9em;"><i class="fas fa-eye mr-1"></i> Preview Data Terdeteksi</h6>
                                    <span id="detectedRoleBadge" class="badge badge-pill badge-info">Admin/Op</span>
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
                                <i class="fas fa-exclamation-circle mr-1"></i> <span id="importErrorMessage">Format file tidak cocok!</span>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 small">
                            <strong><i class="fas fa-exclamation-triangle"></i> Format CSV Wajib:</strong><br>
                            Kolom pertama harus berisi header: <code>username</code>, <code>nama_lengkap</code>, dan <code>role</code> (dengan pemisah koma <code>,</code> atau titik-koma <code>;</code>).<br><br>
                            <strong>Contoh isi file CSV:</strong>
                            <pre class="bg-dark text-white p-2 rounded mb-0" style="font-size: 0.85em;">username,nama_lengkap,role
sa_doni,Doni Wijaya,super_admin
op_eko,Eko Prasetyo,operator</pre>
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="fas fa-info-circle"></i> Password default setelah di-import:<br>
                            - Super Admin: <b>admingara776</b><br>
                            - Operator: <b>garaop457</b>
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
    
    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            var table = $('#usersTable').DataTable({
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "responsive": true
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

                    const usernameIndex = findHeaderIndex(['username', 'user'], headers);
                    const namaIndex = findHeaderIndex(['nama', 'name', 'nama_lengkap'], headers);
                    const roleIndex = findHeaderIndex(['role', 'peran'], headers);

                    if (usernameIndex === -1 || namaIndex === -1 || roleIndex === -1) {
                        showError('Format CSV/TXT tidak valid. Wajib ada kolom: <strong>username</strong>, <strong>nama_lengkap</strong>, dan <strong>role</strong>.');
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
                            let isHighlighted = (idx === usernameIndex || idx === namaIndex || idx === roleIndex);
                            let style = isHighlighted ? 'font-weight: bold; color: #0b57d0;' : '';
                            trHtml += `<td style="${style}">${val.replace(/['"]/g, '')}</td>`;
                        });
                        trHtml += '</tr>';
                        $('#previewTableBody').append(trHtml);
                    }

                    
                    $('#detectedRoleBadge').text('Admin/Op').addClass('badge-info');
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

            $('#usersTable tbody').on('click', '.reset-btn', function () {
                var id = $(this).data('id');
                var username = $(this).data('username');
                $('#reset_username').val(username);
                $('#resetPasswordForm').attr('action', '/super-admin/users/' + id + '/password');
                $('#resetPasswordModal').modal('show');
            });

            $('#usersTable tbody').on('click', '.delete-btn', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Pengguna?',
                    text: "Akun ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#deleteForm').attr('action', '/super-admin/users/' + id).submit();
                    }
                });
            });
        });
    </script>
@endpush