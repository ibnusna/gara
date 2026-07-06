@extends('layouts.super_admin')

@section('title', 'Ganti Semester')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pengaturan Semester</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan Semester</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(!$is_backup_valid)
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-ban"></i> Backup Wajib Sebelum Ganti Semester!</h5>
            Sistem mendeteksi bahwa Anda belum melakukan backup database (Opsi: All Databases) dalam 10 menit terakhir. Proses Ganti Semester/Naik Kelas <strong>TIDAK DAPAT DILAKUKAN</strong> sebelum Anda melakukan backup.
            <br><br>
            <a href="{{ route('superadmin.backup.index') }}" class="btn btn-warning btn-sm" style="color:black;"><i class="fas fa-database"></i> Pergi ke Halaman Backup</a>
        </div>
        @endif

        <div class="row">
            <!-- Info Status Saat Ini -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <i class="fas fa-school fa-4x text-primary"></i>
                        </div>
                        <h3 class="profile-username text-center">Status Aktif</h3>
                        <p class="text-muted text-center">Sistem Akademik</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Tahun Ajaran</b>
                                <a class="float-right badge badge-primary" style="font-size: 1rem;">
                                    {{ $tahun_ajaran }}
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b>Semester</b>
                                <a class="float-right badge badge-info" style="font-size: 1rem;">
                                    {{ $semester_label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Panel Eksekusi -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <h3 class="card-title p-1"><i class="fas fa-cogs mr-1"></i> Tindakan Pembaruan Data</h3>
                    </div>
                    <div class="card-body">
                        <div class="callout callout-{{ $mode_aksi == 'naik_kelas' ? 'danger' : 'warning' }}">
                            <h5><i class="fas fa-info-circle"></i> Perhatian!</h5>
                            <p>Anda akan melakukan proses: <strong>{{ $tombol_text }}</strong>.</p>
                            <ul>
                                @if ($mode_aksi == 'ganti_semester')
                                <li>Tahun Ajaran TETAP: <strong>{{ $tahun_ajaran }}</strong>.</li>
                                <li>Semester berubah menjadi: <strong>Genap</strong>.</li>
                                <li>Data yang <strong>DIHAPUS/RESET</strong>: Absensi, Jurnal, Tugas (Diarsipkan), Diskusi (Diarsipkan), UTS, UAS.</li>
                                <li>Data yang <strong>AMAN</strong>: Data Siswa, Kelas, Materi/RPP.</li>
                                @else
                                <li>Tahun Ajaran BERUBAH menjadi: <strong>{{ $next_tahun_ajaran }}</strong>.</li>
                                <li>Semester kembali menjadi: <strong>Ganjil</strong>.</li>
                                <li><strong>SISWA KELAS IX AKAN DIHAPUS PERMANEN (Lulus).</strong></li>
                                <li>Siswa Kelas VIII naik ke IX. Siswa Kelas VII naik ke VIII.</li>
                                <li>Kelas VII Baru akan kosong (siap input/import).</li>
                                <li>Semua data nilai, absensi, tugas, diskusi akan dibersihkan/diarsipkan.</li>
                                @endif
                            </ul>
                        </div>

                        <div class="text-right mt-4">
                            <form action="{{ route('superadmin.semester.upgrade') }}" method="POST" id="formUpgrade">
                                @csrf
                                <input type="hidden" name="mode" value="{{ $mode_aksi }}">
                                <input type="hidden" name="next_tahun" value="{{ $next_tahun_ajaran }}">
                                <input type="hidden" name="next_semester" value="{{ $next_semester_code }}">
                                
                                <button type="button" class="btn {{ $tombol_class }} btn-lg" onclick="konfirmasiUpgrade()" {{ !$is_backup_valid ? 'disabled' : '' }}>
                                    <i class="{{ $icon }} mr-2"></i> {{ $tombol_text }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function konfirmasiUpgrade() {
    let mode = "{{ $mode_aksi }}";
    let pesan = "";
    
    if (mode === 'naik_kelas') {
        pesan = "PERINGATAN KERAS: Siswa Kelas 9 akan DIHAPUS. Siswa lain akan naik tingkat. Data transaksi akademik akan di-reset. Tindakan ini TIDAK BISA DIBATALKAN.";
    } else {
        pesan = "Anda yakin ingin lanjut ke Semester Genap? Data transaksi akademik semester ini akan di-reset.";
    }

    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Proses Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memproses...',
                html: 'Mohon tunggu, ini akan memakan waktu. Jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('formUpgrade').submit();
        }
    });
}
</script>
@endpush
