@extends('layouts.super_admin')
@section('title', 'Role & Permission | Garuda Akademi')

@push('styles')
    <style>
        .custom-switch .custom-control-label::before {
            left: -2.25rem;
            width: 1.75rem;
            pointer-events: all;
            border-radius: .5rem;
        }

        .custom-switch .custom-control-label::after {
            top: calc(.25rem + 2px);
            left: calc(-2.25rem + 2px);
            width: calc(1rem - 4px);
            height: calc(1rem - 4px);
            background-color: #adb5bd;
            border-radius: .5rem;
            transition: background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-transform .15s ease-in-out;
        }
    </style>
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Role & Permission Management</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addRoleModal"><i class="fas fa-plus"></i>
                Tambah Role</button>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rolesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Role</th>
                                <th>Hak Akses (Permissions)</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $r)
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td><span class="badge badge-info text-uppercase">{{ $r->role_name }}</span></td>
                                    <td>
                                        @if ($r->permissions->isEmpty())
                                            <span class="text-muted italic">Tidak ada permission (Default/System Only)</span>
                                        @else
                                            @foreach ($r->permissions as $p)
                                                <span class="badge badge-success mr-1 mb-1">{{ $p->permission_name }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $r->id }}"
                                            data-name="{{ $r->role_name }}"
                                            data-perms="{{ json_encode($r->permissions->pluck('id')) }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if ($r->id != 1)
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $r->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

    
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('superadmin.roles.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Role Baru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Role</label>
                            <input type="text" name="role_name" class="form-control" required placeholder="Contoh: proktor">
                        </div>
                        <div class="form-group">
                            <label>Pilih Hak Akses (Permissions)</label>
                            <div class="row">
                                @foreach ($all_permissions as $p)
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="add_perm_{{ $p->id }}"
                                                name="permissions[]" value="{{ $p->id }}">
                                            <label class="custom-control-label font-weight-normal" for="add_perm_{{ $p->id }}"
                                                title="{{ $p->description }}">
                                                {{ $p->permission_name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editRoleForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Role</label>
                            <input type="text" name="role_name" id="edit_role_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pilih Hak Akses (Permissions)</label>
                            <div class="row">
                                @foreach ($all_permissions as $p)
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input edit-perm-checkbox"
                                                id="edit_perm_{{ $p->id }}" name="permissions[]" value="{{ $p->id }}">
                                            <label class="custom-control-label font-weight-normal" for="edit_perm_{{ $p->id }}"
                                                title="{{ $p->description }}">
                                                {{ $p->permission_name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Update Role</button>
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
            var table = $('#rolesTable').DataTable({
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "responsive": true
            });

            $('#rolesTable tbody').on('click', '.edit-btn', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var perms = $(this).data('perms'); 

                $('#edit_role_name').val(name);
                $('#editRoleForm').attr('action', '/super-admin/roles/' + id);

                $('.edit-perm-checkbox').prop('checked', false);
                if (perms && perms.length > 0) {
                    perms.forEach(function (perm_id) {
                        $('#edit_perm_' + perm_id).prop('checked', true);
                    });
                }
                $('#editRoleModal').modal('show');
            });

            $('#rolesTable tbody').on('click', '.delete-btn', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Role?',
                    text: "Role yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#deleteForm').attr('action', '/super-admin/roles/' + id).submit();
                    }
                });
            });
        });
    </script>
@endpush