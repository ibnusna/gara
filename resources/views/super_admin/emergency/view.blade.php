@extends('layouts.super_admin')
@section('title', 'Emergency Control | Garuda Akademi')

@push('styles')
    <style>
        .custom-switch.custom-switch-lg .custom-control-label {
            padding-left: 3rem;
            padding-bottom: 2rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::before {
            height: 2rem;
            width: calc(3rem + 0.75rem);
            border-radius: 4rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::after {
            width: calc(2rem - 4px);
            height: calc(2rem - 4px);
            border-radius: calc(3rem - (2rem / 2));
        }

        .custom-switch.custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(2rem - 0.25rem));
        }
    </style>
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 text-danger"><i class="fas fa-exclamation-triangle"></i> Emergency Control</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                
                <div class="col-md-6">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h5 class="card-title">Force Logout Semua Pengguna</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tindakan ini akan mengakhiri semua sesi yang aktif di sistem, termasuk
                                ujian yang sedang berjalan. <strong>Gunakan hanya dalam keadaan darurat keamanan.</strong>
                            </p>
                            <form method="POST" action="{{ route('superadmin.emergency.toggle') }}"
                                onsubmit="return confirm('PERINGATAN KRITIS!\n\nAnda yakin ingin memaksa keluar semua pengguna dari sistem?\nTindakan ini dapat mengganggu ujian yang sedang berlangsung!');">
                                @csrf
                                <input type="hidden" name="action" value="force_logout_all">
                                <button type="submit" class="btn btn-danger btn-lg w-100 mt-2"><i
                                        class="fas fa-sign-out-alt"></i> FORCE LOGOUT ALL</button>
                            </form>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-6">
                    <div class="card card-outline {{ $is_maintenance ? 'card-warning' : 'card-success' }}">
                        <div class="card-header">
                            <h5 class="card-title">Sistem Maintenance Mode</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Mengaktifkan mode ini akan mencegah semua pengguna (kecuali Admin) untuk
                                login atau mengakses LMS. Berguna saat Anda sedang melakukan update sistem besar.</p>
                            <form method="POST" action="{{ route('superadmin.emergency.toggle') }}">
                                @csrf
                                <input type="hidden" name="action" value="toggle_maintenance">
                                <input type="hidden" name="maintenance_status" value="{{ $is_maintenance ? '0' : '1' }}">
                                <div class="d-flex justify-content-center align-items-center flex-column mt-4">
                                    <h4 class="mb-3">Status Saat Ini: <span
                                            class="badge {{ $is_maintenance ? 'badge-warning' : 'badge-success' }}">{{ $is_maintenance ? 'MAINTENANCE ON' : 'SYSTEM ONLINE' }}</span>
                                    </h4>
                                    <button type="submit"
                                        class="btn {{ $is_maintenance ? 'btn-success' : 'btn-warning' }} btn-lg">
                                        {!! $is_maintenance ? '<i class="fas fa-power-off"></i> MATIKAN MAINTENANCE' : '<i class="fas fa-tools"></i> AKTIFKAN MAINTENANCE' !!}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="alert alert-warning mt-4">
                <i class="fas fa-info-circle"></i> <strong>Catatan Audit:</strong> Semua aktivitas yang dilakukan pada
                halaman ini akan direkam secara permanen ke dalam Audit Log untuk alasan transparansi dan keamanan.
            </div>

        </div>
    </section>
@endsection