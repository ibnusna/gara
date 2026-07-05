@extends('layouts.super_admin')

@section('title', 'Dashboard Admin | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 text-dark">Super Admin Dashboard</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total_pengguna'] }}</h3>
                            <p>Total Pengguna</p>
                        </div>
                        <div class="icon"><i class="fas fa-users-cog"></i></div>
                        <a href="{{ route('superadmin.users.index') }}" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['total_peran'] }}</h3>
                            <p>Total Role</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-shield"></i></div>
                        <a href="{{ route('superadmin.roles.index') }}" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['total_audit_log'] }}</h3>
                            <p>Log Aktivitas</p>
                        </div>
                        <div class="icon"><i class="fas fa-history"></i></div>
                        <a href="{{ route('superadmin.audit.index') }}" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0"><i class="fas fa-server"></i> Status Sistem</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Semua layanan utama saat ini berjalan normal (Connected via PDO /
                                Eloquent).</p>
                            <ul>
                                <li><strong>Auth_Gara:</strong> <span class="badge badge-success">Online</span></li>
                                <li><strong>LMS_Pembelajaran:</strong> <span class="badge badge-success">Online</span></li>
                                <li><strong>Asesmen_Gara:</strong> <span class="badge badge-success">Online</span></li>
                            </ul>
                            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-outline-primary"><i
                                    class="fas fa-history"></i> Cek Log Aktivitas</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h5 class="m-0"><i class="fas fa-bolt"></i> Shortcut Tindakan Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2" style="gap: 10px;">
                                <a href="{{ route('superadmin.users.index') }}" class="btn btn-info"><i
                                        class="fas fa-user-plus"></i> Tambah Pengguna</a>
                                <a href="{{ route('superadmin.settings.index') }}" class="btn btn-success"><i
                                        class="fas fa-cogs"></i> Konfigurasi Sistem</a>
                                <a href="{{ route('superadmin.backup.index') }}" class="btn btn-secondary"><i
                                        class="fas fa-download"></i> Backup Data</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection