@extends('layouts.super_admin')
@section('title', 'Database & Backup | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Manajemen Database & Ekspor</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title">Buat Backup Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('superadmin.backup.store') }}">
                                @csrf
                                <input type="hidden" name="action" value="backup_db">
                                <div class="form-group">
                                    <label>Pilih Database</label>
                                    <select name="db_name" class="form-control" required>
                                        <option value="auth_gara">Admin & Accounts (auth_gara)</option>
                                        <option value="lms_pembelajaran">LMS Pembelajaran (lms_pembelajaran)</option>

                                        <option value="asesmen_gara">Assessment/Ujian (asesmen_gara)</option>
                                        <option value="all">🗄️ Semua Database (All-in-One)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-download"></i> Buat
                                    Backup SQL</button>
                            </form>
                            <hr>
                            <small class="text-muted">Proses backup mungkin memakan waktu beberapa detik tergantung pada
                                ukuran database.</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h5 class="card-title">Daftar File Backup</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="backupTable" class="table table-bordered table-striped text-sm">
                                <thead>
                                    <tr>
                                        <th>Nama File</th>
                                        <th>Ukuran</th>
                                        <th>Tanggal Dibuat</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($backups as $b)
                                        <tr>
                                            <td><code>{{ $b['filename'] }}</code></td>
                                            <td>{{ round($b['size'] / 1024, 2) }} KB</td>
                                            <td>{{ date('Y-m-d H:i:s', $b['time']) }}</td>
                                            <td>
                                                <a href="{{ route('superadmin.backup.download', ['file' => $b['filename']]) }}"
                                                    class="btn btn-success btn-sm" title="Download"><i
                                                        class="fas fa-file-download"></i></a>
                                                <form method="POST" action="{{ route('superadmin.backup.destroy') }}"
                                                    class="d-inline" onsubmit="return confirm('Hapus file backup ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="filename" value="{{ $b['filename'] }}">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                            @if (empty($backups))
                                <p class="text-center mt-3 text-muted">Belum ada file backup tersedia.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#backupTable').DataTable({
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "responsive": true,
                "order": [[2, "desc"]]
            });
        });
    </script>
@endpush