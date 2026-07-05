@extends('layouts.super_admin')
@section('title', 'Audit Logs | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">System Audit Log</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-history"></i> Log Aktivitas Terakhir (Max 1000)</h5>
                    <div class="card-tools">
                        <form action="{{ route('superadmin.audit.clear') }}" method="POST" id="formClearAudit" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmClear()">
                                <i class="fas fa-trash"></i> Hapus Semua Log
                            </button>
                        </form>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="auditTable" class="table table-bordered table-striped table-hover text-sm">
                            <thead>
                                <tr>
                                    <th width="15%">Waktu</th>
                                    <th>Pelaku</th>
                                    <th>Modul</th>
                                    <th>Aktivitas</th>
                                    <th>URL / Deskripsi Tambahan</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ date('Y-m-d H:i:s', strtotime($log->created_at)) }}</td>
                                        <td>
                                            <strong>{{ $log->actor_name }}</strong><br>
                                            <small class="text-muted">{{ '@' . ($log->actor_username ?? '-') }} ·
                                                <span class="badge badge-secondary">{{ $log->user_type ?? '-' }}</span></small>
                                        </td>
                                        <td><span class="badge badge-secondary">{{ $log->module }}</span></td>
                                        <td>{{ $log->action }}</td>
                                        <td>
                                            @if($log->url)
                                                <a href="{{ $log->url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->url }}">
                                                    {{ $log->url }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (empty($logs) || count($logs) == 0)
                        <div class="alert alert-info mt-3"><i class="fas fa-info-circle"></i> Belum ada rekaman audit log.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#auditTable').DataTable({
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "responsive": true,
                "order": [[0, "desc"]] 
            });
        });

        function confirmClear() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Seluruh log aktivitas akan dihapus permanen dari database. Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formClearAudit').submit();
                }
            })
        }
    </script>
@endpush