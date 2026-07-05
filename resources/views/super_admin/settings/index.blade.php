@extends('layouts.super_admin')
@section('title', 'Pengaturan Sistem | Garuda Akademi')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Pengaturan Sistem</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Global Configuration</h5>
                        </div>
                        <form method="POST" action="{{ route('superadmin.settings.update') }}">
                            @csrf
                            <div class="card-body">

                                <div class="form-group row">
                                    <label for="sekolah_nama" class="col-sm-3 col-form-label">Nama Institusi</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="sekolah_nama"
                                            name="settings[sekolah_nama]" value="{{ $settingsDB['sekolah_nama'] ?? '' }}"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="tahun_ajaran" class="col-sm-3 col-form-label">Tahun Ajaran</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="tahun_ajaran"
                                            name="settings[tahun_ajaran]" value="{{ $settingsDB['tahun_ajaran'] ?? '' }}"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="semester_aktif" class="col-sm-3 col-form-label">Semester Aktif</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="semester_aktif" name="settings[semester_aktif]">
                                            <option value="1" {{ ($settingsDB['semester_aktif'] ?? '') == '1' ? 'selected' : '' }}>Ganjil (1)</option>
                                            <option value="2" {{ ($settingsDB['semester_aktif'] ?? '') == '2' ? 'selected' : '' }}>Genap (2)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="jenjang_sekolah" class="col-sm-3 col-form-label">Jenjang Sekolah</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="jenjang_sekolah" name="settings[jenjang_sekolah]">
                                            <option value="SD" {{ ($settingsDB['jenjang_sekolah'] ?? '') == 'SD' ? 'selected' : '' }}>SD/MI Sederajat</option>
                                            <option value="SMP" {{ ($settingsDB['jenjang_sekolah'] ?? 'SMP') == 'SMP' ? 'selected' : '' }}>SMP/MTs Sederajat</option>
                                            <option value="SMA" {{ ($settingsDB['jenjang_sekolah'] ?? '') == 'SMA' ? 'selected' : '' }}>SMA/MA Sederajat</option>
                                            <option value="SMK" {{ ($settingsDB['jenjang_sekolah'] ?? '') == 'SMK' ? 'selected' : '' }}>SMK/MAK Sederajat</option>
                                        </select>
                                        <small class="form-text text-muted">Menentukan opsi tingkat kelas otomatis (I-VI, VII-IX, X-XII).</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="session_timeout" class="col-sm-3 col-form-label">Session Timeout
                                        (Menit)</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" id="session_timeout"
                                            name="settings[session_timeout]"
                                            value="{{ $settingsDB['session_timeout'] ?? '30' }}" required min="5">
                                        <small class="form-text text-muted">Batas waktu idle sebelum user otomatis
                                            logout.</small>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan
                                    Pengaturan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h5 class="m-0"><i class="fas fa-info-circle"></i> Informasi</h5>
                        </div>
                        <div class="card-body">
                            <p>Pengaturan di halaman ini akan berdampak secara global kepada seluruh pengguna sistem.</p>
                            <ul>
                                <li>Session timeout efektif saat aktivitas tidak terdeteksi dari pengguna.</li>
                                <li>Tahun ajaran & semester akan mengatur default data pada dashboard Operator/Guru.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection