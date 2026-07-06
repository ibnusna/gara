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
                        <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
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

                                <hr>
                                <h6 class="font-weight-bold mb-3">Manajemen Tampilan Logo</h6>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group row">
                                            <label for="logo_mode" class="col-sm-4 col-form-label">Mode Logo</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="logo_mode" name="settings[logo_mode]">
                                                    <option value="default" {{ ($settingsDB['logo_mode'] ?? 'default') == 'default' ? 'selected' : '' }}>Default GARA</option>
                                                    <option value="custom" {{ ($settingsDB['logo_mode'] ?? '') == 'custom' ? 'selected' : '' }}>Logo Kustom Sekolah</option>
                                                    <option value="preset_tutwuri" {{ ($settingsDB['logo_mode'] ?? '') == 'preset_tutwuri' ? 'selected' : '' }}>Preset Tut Wuri</option>
                                                </select>
                                                <small class="form-text text-muted">Akan mengubah logo di form login, ruang ujian, dll.</small>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="custom_logo_file" class="col-sm-4 col-form-label">Upload Logo Kustom</label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control-file" id="custom_logo_file" name="custom_logo_file" accept=".png,.jpg,.jpeg,.svg">
                                                <small class="form-text text-muted">Hanya diperlukan jika Anda memilih "Logo Kustom Sekolah". Maksimal 2MB.</small>
                                                @if(isset($settingsDB['custom_logo_filename']))
                                                    <div class="mt-2">
                                                        <small class="text-info">Tersimpan saat ini: <strong>{{ $settingsDB['custom_logo_filename'] }}</strong></small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center" style="background: rgba(0,0,0,0.02); border-radius: 8px; padding: 15px; border: 1px dashed rgba(0,0,0,0.15);">
                                        <span class="text-muted small font-weight-bold mb-2">PREVIEW LOGO</span>
                                        <img id="logo_preview" src="" alt="Preview Logo" style="max-width: 100%; max-height: 120px; object-fit: contain;">
                                    </div>
                                </div>
                                
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const modeSelect = document.getElementById('logo_mode');
                                    const fileInput = document.getElementById('custom_logo_file');
                                    const previewImg = document.getElementById('logo_preview');
                                    
                                    const assetDefault = "{{ asset('logo/FARA_BLACK.svg') }}";
                                    const assetTutwuri = "{{ asset('logo/logo_tutwuri.svg') }}";
                                    const assetCustom = "{{ asset('logo/' . ($settingsDB['custom_logo_filename'] ?? 'logo_sekolah.png')) }}";
                                    
                                    function updatePreview() {
                                        if (modeSelect.value === 'default') {
                                            previewImg.src = assetDefault;
                                        } else if (modeSelect.value === 'preset_tutwuri') {
                                            previewImg.src = assetTutwuri;
                                        } else if (modeSelect.value === 'custom') {
                                            if (fileInput.files && fileInput.files[0]) {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                    previewImg.src = e.target.result;
                                                }
                                                reader.readAsDataURL(fileInput.files[0]);
                                            } else {
                                                previewImg.src = assetCustom;
                                            }
                                        }
                                    }
                                    
                                    modeSelect.addEventListener('change', updatePreview);
                                    fileInput.addEventListener('change', updatePreview);
                                    
                                    // Set initial preview on load
                                    updatePreview();
                                });
                                </script>

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