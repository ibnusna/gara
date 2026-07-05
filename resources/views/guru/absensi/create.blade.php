@extends('layouts.guru')

@section('title', 'Input Absensi - ' . session('nama_mapel'))

@push('styles')
    <style>
        .status-btn-group .btn {
            min-width: 42px;
            font-weight: 600;
        }

        .student-row:hover {
            background: rgba(11, 87, 208, 0.04);
        }

        .smart-journal-box {
            background: linear-gradient(135deg, rgba(11, 87, 208, 0.06), rgba(255, 255, 255, 0.8));
            border: 1px solid rgba(11, 87, 208, 0.2);
            border-radius: 12px;
        }

        .pertemuan-badge {
            background: linear-gradient(135deg, #0b57d0, #1a73e8);
            color: #fff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0">Input Absensi <span class="pertemuan-badge">Pertemuan ke-{{ $nextPertemuan }}</span>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('guru.absensi.rekap') }}">Absensi</a></li>
                            <li class="breadcrumb-item active">Input</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                <form action="{{ route('guru.absensi.store') }}" method="POST" id="absensiForm">
                    @csrf
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Jurnal Mengajar —
                                {{ session('nama_mapel') }} / Kelas {{ session('nama_kelas') }}</h3>
                        </div>
                        <div class="card-body">

                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tanggal"><i class="fas fa-calendar mr-1 text-primary"></i>
                                            Tanggal</label>
                                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                            id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                                            required>
                                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pertemuan_ke"><i class="fas fa-hashtag mr-1 text-info"></i> Pertemuan
                                            Ke-</label>
                                        <input type="number" class="form-control" id="pertemuan_ke" name="pertemuan_ke"
                                            value="{{ old('pertemuan_ke', $nextPertemuan) }}" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="metode"><i class="fas fa-chalkboard mr-1 text-success"></i>
                                            Metode</label>
                                        <select class="form-control" id="metode" name="metode">
                                            <option value="Tatap Muka" selected>Tatap Muka</option>
                                            <option value="Daring">Daring (Online)</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="smart-journal-box p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-magic text-primary mr-2"></i>
                                        <strong class="text-primary">Generator Jurnal Otomatis</strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted">Aktivitas (KKO)</label>
                                            <select class="form-control form-control-sm" id="select_aktivitas">
                                                <option value="" disabled selected>-- Pilih Aktivitas --</option>
                                                <optgroup label="Pre-structural">
                                                    <option value="Melakukan Review">Melakukan Review</option>
                                                    <option value="Melakukan Remedial">Melakukan Remedial</option>
                                                    <option value="Apersepsi">Apersepsi / Pengenalan</option>
                                                </optgroup>
                                                <optgroup label="Uni-structural">
                                                    <option value="Menjelaskan konsep">Menjelaskan konsep</option>
                                                    <option value="Mencatat materi">Mencatat materi</option>
                                                    <option value="Menyimak video">Menyimak video</option>
                                                </optgroup>
                                                <optgroup label="Multi-structural">
                                                    <option value="Mengerjakan Latihan Soal">Mengerjakan L Soal</option>
                                                    <option value="Melakukan Praktik">Melakukan Praktik</option>
                                                </optgroup>
                                                <optgroup label="Relational">
                                                    <option value="Melakukan Diskusi Kelompok">Diskusi Kelompok</option>
                                                    <option value="Menganalisis Kasus">Menganalisis Kasus</option>
                                                    <option value="Presentasi Kelompok">Presentasi Kelompok</option>
                                                </optgroup>
                                                <optgroup label="Evaluasi">
                                                    <option value="Melaksanakan Ulangan Harian">Ulangan Harian</option>
                                                    <option value="Melaksanakan Quiz">Melaksanakan Quiz</option>
                                                    <option value="Persiapan Ujian">Persiapan Ujian</option>
                                                </optgroup>
                                                <option value="Lainnya">Lainnya (Ketik Manual)</option>
                                            </select>
                                            <input type="text" class="form-control form-control-sm mt-1 d-none" id="manual_aktivitas"
                                                placeholder="Ketik aktivitas...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted">Materi (Sesuai RPP)</label>
                                            <select class="form-control form-control-sm" id="select_materi">
                                                <option value="" disabled selected>-- Pilih Materi --</option>
                                                @forelse($rppMateri as $m)
                                                    <option value="{{ $m->judul_materi }}">B{{ $m->bab }}: {{ $m->judul_materi }}</option>
                                                @empty
                                                    <option value="" disabled>RPP belum diinput</option>
                                                @endforelse
                                                <option value="Lainnya">Lainnya (Ketik Manual)</option>
                                            </select>
                                            <input type="text" class="form-control form-control-sm mt-1 d-none" id="manual_materi"
                                                placeholder="Ketik materi...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold text-muted">Review Akhir <i class="fas fa-pen ml-1 text-warning" style="font-size:0.8em"></i></label>
                                            <textarea class="form-control form-control-sm @error('pokok_bahasan') is-invalid @enderror"
                                                id="pokok_bahasan" name="pokok_bahasan" rows="2"
                                                placeholder="Pilih aktivitas untuk mengenerate otomatis..."
                                                required>{{ old('pokok_bahasan') }}</textarea>
                                            @error('pokok_bahasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            
                            <h5 class="mb-3"><i class="fas fa-users mr-2 text-primary"></i>Daftar Kehadiran Siswa
                                <small class="ml-2 text-muted">({{ $siswaList->count() }} siswa)</small>
                            </h5>

                            @if($siswaList->isEmpty())
                                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Belum ada siswa
                                    di kelas ini.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th>Nama Siswa</th>
                                                <th class="text-center" width="80px">NIS</th>
                                                <th class="text-center" style="width:320px">
                                                    Status
                                                    <div class="btn-group btn-group-sm ml-2">
                                                        <button type="button"
                                                            class="btn btn-outline-success btn-sm py-0 set-all" data-val="H">All
                                                            H</button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($siswaList as $i => $siswa)
                                                <tr class="student-row">
                                                    <td class="text-center">{{ $i + 1 }}</td>
                                                    <td class="font-weight-medium">{{ $siswa->nama }}</td>
                                                    <td class="text-center text-muted small">{{ $siswa->nis }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm w-100 status-btn-group" role="group">
                                                            @foreach(['H' => ['success', 'Hadir'], 'I' => ['info', 'Izin'], 'S' => ['warning', 'Sakit'], 'A' => ['danger', 'Alpha']] as $val => [$color, $label])
                                                                <input type="radio" class="btn-check" name="status[{{ $siswa->id }}]"
                                                                    id="s_{{ $val }}_{{ $siswa->id }}" value="{{ $val }}" {{ $val === 'H' ? 'checked' : '' }}>
                                                                <label class="btn btn-outline-{{ $color }} px-3"
                                                                    for="s_{{ $val }}_{{ $siswa->id }}" title="{{ $label }}">
                                                                    {{ $val }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="{{ route('guru.absensi.rekap') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save mr-2"></i>Simpan Absensi & Buat Agenda
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            function generateSentence() {
                var aktivitas = $('#select_aktivitas').val() === 'Lainnya' ? $('#manual_aktivitas').val() : $('#select_aktivitas').val();
                var materi = $('#select_materi').val() === 'Lainnya' ? $('#manual_materi').val() : $('#select_materi').val();
                if (!aktivitas && !materi) return;
                var kataSambung = ' mengenai materi ';
                if (aktivitas && (aktivitas.toLowerCase().includes('ulangan') || aktivitas.toLowerCase().includes('quiz') || aktivitas.toLowerCase().includes('ujian'))) {
                    kataSambung = ' pada bab ';
                }
                var sentence = aktivitas && materi ? aktivitas + kataSambung + materi :
                    aktivitas ? aktivitas + '...' : 'Mempelajari materi ' + materi;
                $('#pokok_bahasan').val(sentence);
            }

            $('#select_aktivitas').change(function () {
                $('#manual_aktivitas').toggleClass('d-none', $(this).val() !== 'Lainnya');
                generateSentence();
            });
            $('#select_materi').change(function () {
                $('#manual_materi').toggleClass('d-none', $(this).val() !== 'Lainnya');
                generateSentence();
            });
            $('#manual_aktivitas, #manual_materi').on('input', generateSentence);

            
            $('.set-all').click(function () {
                var val = $(this).data('val');
                $('input[type=radio][value="' + val + '"]').prop('checked', true);
            });

            
            $('#absensiForm').on('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Simpan Absensi?',
                    html: 'Pertemuan ke-<strong>{{ $nextPertemuan }}</strong> akan disimpan.<br>Agenda harian akan dibuat otomatis.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#0b57d0',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
                        document.getElementById('absensiForm').submit();
                    }
                });
            });
        });
    </script>
@endpush