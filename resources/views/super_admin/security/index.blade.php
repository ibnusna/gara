@extends('layouts.super_admin')
@section('title', 'System Security | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">System Security</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                
                <div class="col-md-5">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-shield-alt"></i> Pengaturan Keamanan Global</h5>
                        </div>
                        <form method="POST" action="{{ route('superadmin.settings.update') }}">
                            @csrf
                            
                        </form>
                        <form method="POST" action="{{ route('superadmin.emergency.toggle') }}">
                            @csrf
                            <div class="card-body">
                                <input type="hidden" name="action" value="toggle_maintenance">
                                <div class="form-group">
                                    <label>Mode Maintenance</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="maintenanceSwitch"
                                            name="maintenance_status" value="1" {{ $maintenance_mode == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="maintenanceSwitch">Aktifkan
                                            Maintenance</label>
                                    </div>
                                    <small class="form-text text-muted">Saat aktif, hanya Super Admin yang bisa
                                        login.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning text-bold"><i class="fas fa-save"></i> Ubah
                                    Aturan Maintenance</button>
                            </div>
                        </form>
                    </div>
                </div>

                
                <div class="col-md-7">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-ban"></i> Daftar IP Diblokir</h5>
                            <button class="btn btn-sm btn-danger float-right" data-toggle="modal"
                                data-target="#addIpModal"><i class="fas fa-plus"></i> Tambah IP Blokir</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ipTable" class="table table-bordered table-striped text-sm">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Alasan Blokir</th>
                                        <th>Tanggal Diblokir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ip_blocks as $ip)
                                        <tr>
                                            <td><code>{{ $ip->ip_address }}</code></td>
                                            <td>{{ $ip->reason }}</td>
                                            <td>{{ $ip->created_at }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('superadmin.security.unblock', $ip->id) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Hapus IP ini dari daftar blokir?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-success btn-sm" title="Hapus Blokir"><i
                                                            class="fas fa-unlock"></i> Unblock</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                            @if(is_array($ip_blocks) ? empty($ip_blocks) : $ip_blocks->isEmpty())
                                <p class="text-center mt-3 text-muted">Belum ada IP yang diblokir.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    
    <div class="modal fade" id="addIpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <form method="POST" action="{{ route('superadmin.security.block') }}">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-ban"></i> Tambah IP Blokir</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>IP Address</label>
                            <input type="text" name="ip_address" class="form-control" required
                                placeholder="Contoh: 192.168.1.100">
                        </div>
                        <div class="form-group">
                            <label>Alasan / Keterangan</label>
                            <textarea name="reason" class="form-control" rows="3"
                                placeholder="Contoh: Spam login attempts"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Blokir IP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ipTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });

            
            @if ($errors->has('ip_address'))
                Swal.fire({
                    icon: 'error',
                    title: 'Format IP Tidak Valid',
                    text: '{{ $errors->first("ip_address") }}',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Tutup',
                }).then(() => {
                    
                    $('#addIpModal').modal('show');
                });
            @endif

            
            @if (session('success_message'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session("success_message") }}', timer: 3000, showConfirmButton: false });
            @endif
            @if (session('error_message'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ session("error_message") }}' });
            @endif
        });
    </script>
@endpush