@extends('layouts.operator')
@section('title', 'Kelola Waktu Belajar | Garuda Akademi')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Kelola Master Waktu Belajar</h1>
            <p class="text-muted mt-1">Definisikan slot waktu untuk KBM, Istirahat, Upacara, dll secara fleksibel.</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Pengaturan Global Waktu Belajar</h3>
                        </div>
                        <form action="{{ route('operator.settings.update') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="jam_masuk_sekolah">Jam Masuk Sekolah (Default)</label>
                                            <input type="time" class="form-control" id="jam_masuk_sekolah" name="settings[jam_masuk_sekolah]" value="{{ $settingsDB['jam_masuk_sekolah'] ?? '07:00' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="durasi_jp_menit">Durasi 1 JP (Default Menit)</label>
                                            <input type="number" class="form-control" id="durasi_jp_menit" name="settings[durasi_jp_menit]" value="{{ $settingsDB['durasi_jp_menit'] ?? '40' }}" required min="15" max="120">
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group w-100">
                                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Simpan Pengaturan Default</button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h5>Pengaturan Durasi Khusus (Custom Time)</h5>
                                <p class="text-muted small">Atur durasi spesifik untuk hari tertentu. Jika dikosongkan, durasi akan mengikuti default di atas.</p>
                                <div class="row">
                                    @php $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; @endphp
                                    @foreach($hariList as $hari)
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Durasi {{ $hari }} (Menit)</label>
                                            <input type="number" class="form-control" name="settings[durasi_jp_menit_{{ $hari }}]" value="{{ $settingsDB['durasi_jp_menit_'.$hari] ?? '' }}" placeholder="Default">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Durasi Khusus</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Tambah Slot Waktu</h3>
                        </div>
                        <form action="{{ route('operator.mapping.waktu.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Hari</label>
                                    <select name="hari" class="form-control" required>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                        <option value="Minggu">Minggu</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Jenis Kegiatan</label>
                                    <select name="jenis_kegiatan" class="form-control" required id="jenis_kegiatan">
                                        <option value="KBM">Kegiatan Belajar Mengajar (KBM)</option>
                                        <option value="ISTIRAHAT">Istirahat</option>
                                        <option value="UPACARA">Upacara</option>
                                        <option value="PEMBIASAAN">Pembiasaan</option>
                                        <option value="EKSKUL">Ekstrakurikuler</option>
                                        <option value="LAINNYA">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-group" id="group_urutan">
                                    <label>Jam Ke- (Hanya untuk KBM)</label>
                                    <input type="number" name="urutan_jam" class="form-control" placeholder="Contoh: 1, 2, 3">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Jam Mulai</label>
                                            <input type="time" name="jam_mulai" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Jam Selesai</label>
                                            <input type="time" name="jam_selesai" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Simpan Slot Waktu</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-outline card-success">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Daftar Waktu Belajar</h3>
                            <a href="{{ route('operator.mapping.jadwal') }}" class="btn btn-sm btn-outline-primary ml-auto"><i class="fas fa-arrow-left"></i> Kembali ke Matrix Jadwal</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                            <th>Kegiatan</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($masterJam->groupBy('hari') as $hari => $slots)
                                            <tr>
                                                <td colspan="4" class="bg-secondary text-white font-weight-bold"><i class="fas fa-calendar-day mr-2"></i> {{ $hari }}</td>
                                            </tr>
                                            @foreach($slots as $jam)
                                                <tr>
                                                    <td></td>
                                                    <td>
                                                        <span class="font-weight-bold text-primary">{{ substr($jam->jam_mulai,0,5) }} - {{ substr($jam->jam_selesai,0,5) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($jam->jenis_kegiatan == 'KBM')
                                                            <span class="badge badge-info px-2 py-1">KBM (Jam ke-{{ $jam->urutan_jam }})</span>
                                                        @else
                                                            <span class="badge badge-warning px-2 py-1">{{ $jam->jenis_kegiatan }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex" style="gap: 5px;">
                                                            <button type="button" class="btn btn-xs btn-warning btn-edit-waktu" 
                                                                data-id="{{ $jam->id }}"
                                                                data-hari="{{ $jam->hari }}"
                                                                data-kegiatan="{{ $jam->jenis_kegiatan }}"
                                                                data-urutan="{{ $jam->urutan_jam }}"
                                                                data-mulai="{{ substr($jam->jam_mulai,0,5) }}"
                                                                data-selesai="{{ substr($jam->jam_selesai,0,5) }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form action="{{ route('operator.mapping.waktu.destroy', $jam->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slot waktu ini? Jika jadwal sudah terisi di slot ini, data jadwal juga akan terhapus.')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data slot waktu yang didefinisikan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalEditWaktu" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditWaktu" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Slot Waktu</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="hari" id="edit_hari">
                    <div class="form-group">
                        <label>Jenis Kegiatan</label>
                        <select name="jenis_kegiatan" class="form-control" id="edit_kegiatan" required>
                            <option value="KBM">Kegiatan Belajar Mengajar (KBM)</option>
                            <option value="ISTIRAHAT">Istirahat</option>
                            <option value="UPACARA">Upacara</option>
                            <option value="PEMBIASAAN">Pembiasaan</option>
                            <option value="EKSKUL">Ekstrakurikuler</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group" id="edit_group_urutan">
                        <label>Jam Ke-</label>
                        <input type="number" name="urutan_jam" id="edit_urutan" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="edit_mulai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="edit_selesai" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="auto_shift" name="auto_shift" value="1" checked>
                            <label for="auto_shift" class="custom-control-label">Geser otomatis waktu di bawahnya</label>
                        </div>
                        <small class="text-muted d-block mt-1">Jika dicentang, perubahan durasi pada slot ini akan otomatis menggeser jam mulai dan selesai pada slot-slot setelahnya di hari yang sama.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#jenis_kegiatan').change(function() {
        if($(this).val() == 'KBM') {
            $('#group_urutan').slideDown();
        } else {
            $('#group_urutan').slideUp();
            $('input[name="urutan_jam"]').val('');
        }
    });

    $('#edit_kegiatan').change(function() {
        if($(this).val() == 'KBM') {
            $('#edit_group_urutan').slideDown();
        } else {
            $('#edit_group_urutan').slideUp();
            $('#edit_urutan').val('');
        }
    });

    $('.btn-edit-waktu').click(function() {
        let id = $(this).data('id');
        $('#formEditWaktu').attr('action', `/operator/mapping/waktu/${id}`);
        $('#edit_hari').val($(this).data('hari'));
        $('#edit_kegiatan').val($(this).data('kegiatan')).trigger('change');
        $('#edit_urutan').val($(this).data('urutan'));
        $('#edit_mulai').val($(this).data('mulai'));
        $('#edit_selesai').val($(this).data('selesai'));
        
        $('#modalEditWaktu').modal('show');
    });

    const settingsDB = @json($settingsDB);
    const defaultDurasi = parseInt(settingsDB['durasi_jp_menit']) || 40;

    function getDurasiByHari(hari) {
        if (!hari) return defaultDurasi;
        const key = 'durasi_jp_menit_' + hari;
        if (settingsDB[key] && !isNaN(parseInt(settingsDB[key]))) {
            return parseInt(settingsDB[key]);
        }
        return defaultDurasi;
    }

    function calculateEndTime(startTimeStr, durasi) {
        if (!startTimeStr) return '';
        let parts = startTimeStr.split(':');
        let hours = parseInt(parts[0]);
        let minutes = parseInt(parts[1]);

        minutes += durasi;
        hours += Math.floor(minutes / 60);
        minutes = minutes % 60;
        hours = hours % 24;

        let hStr = hours < 10 ? '0' + hours : hours;
        let mStr = minutes < 10 ? '0' + minutes : minutes;
        return hStr + ':' + mStr;
    }

    $('input[name="jam_mulai"]').on('change', function() {
        if ($('select[name="jenis_kegiatan"]').val() === 'KBM') {
            let hari = $('select[name="hari"]').val();
            let durasi = getDurasiByHari(hari);
            let startTime = $(this).val();
            if (startTime) {
                $('input[name="jam_selesai"]').val(calculateEndTime(startTime, durasi));
            }
        }
    });

    $('select[name="hari"], select[name="jenis_kegiatan"]').on('change', function() {
        $('input[name="jam_mulai"]').trigger('change');
    });
});
</script>
@endpush
