@extends('layouts.guru')

@section('title', 'Input Asesmen & Tugas - ' . $namaMapel)

@push('styles')
    
    <style>
        
        #drag-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            
            z-index: 9999;
            
            display: none;
            
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            backdrop-filter: blur(4px);
            
            transition: opacity 0.2s ease-in-out;
        }

        #drag-overlay.active {
            display: flex;
            animation: fadeIn 0.3s forwards;
        }

        #drag-overlay i {
            color: #28a745;
            
            margin-bottom: 20px;
            filter: drop-shadow(0 0 15px rgba(40, 167, 69, 0.6));
            animation: pulseIcon 1.5s infinite;
        }

        #drag-overlay h2 {
            font-weight: 700;
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 10px;
        }

        #drag-overlay p {
            font-size: 1.2rem;
            color: #e0e0e0;
            font-weight: 300;
        }

        
        #drag-overlay * {
            pointer-events: none;
        }

        @keyframes pulseIcon {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
@endpush

@section('content')
    
    <div id="drag-overlay">
        <i class="fas fa-file-csv fa-5x"></i>
        <h2>Lepaskan File CSV</h2>
        <p>Sistem akan otomatis membaca & mengisi nilai siswa</p>
    </div>
    

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Input Asesmen & Tugas</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Input Nilai</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Swal.fire('Berhasil!', '{{ session('success') }}', 'success');
                        });
                    </script>
                @endif
                
                @if($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan!',
                                html: {!! json_encode(implode('<br>', $errors->all())) !!}
                            });
                        });
                    </script>
                @endif

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Formulir Penilaian - {{ $namaMapel }} / Kelas {{ $namaKelas }}</h3>

                        
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm"
                                onclick="document.getElementById('csvFileInput').click()">
                                <i class="fas fa-file-csv"></i> Import CSV
                            </button>
                            <input type="file" id="csvFileInput" accept=".csv" style="display: none;"
                                onchange="handleFileSelect(this)">
                        </div>
                    </div>

                    <form action="{{ route('guru.tugas.store') }}" method="POST" id="formTugas">
                        @csrf
                        <div class="card-body">

                            
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tugas Ke-</label>
                                        <input type="number" class="form-control" name="tugas_ke" value="{{ $nextTugasKe }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mode Pengerjaan</label>
                                        <select class="form-control" name="tipe_tugas">
                                            <option value="Individual">Individual</option>
                                            <option value="Kelompok">Kelompok</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="p-3 mb-3 rounded" style="background-color: #f4f6f9; border: 1px solid #ced4da;">
                                <label class="text-primary mb-2"><i class="fas fa-robot mr-1"></i> Generator Judul &
                                    Setup</label>
                                <div class="row">
                                    
                                    <div class="col-md-4">
                                        <div class="form-group mb-1">
                                            <small class="text-muted">Jenis Aktivitas</small>
                                            <select class="form-control" id="select_metode" required>
                                                <option value="" selected disabled>-- Pilih Jenis Asesmen --</option>

                                                <optgroup label="1. FORMATIF (Proses Belajar)">
                                                    <option value="Asesmen Diagnostik" data-kategori="Formatif"
                                                        data-teknik="Tes Tulis">Asesmen Awal / Diagnostik</option>
                                                    <option value="Latihan Soal" data-kategori="Formatif"
                                                        data-teknik="Tes Tulis">Latihan Soal / PR (Harian)</option>
                                                    <option value="Kuis" data-kategori="Formatif" data-teknik="Tes Tulis">
                                                        Kuis Pemahaman</option>
                                                    <option value="Observasi Diskusi" data-kategori="Formatif"
                                                        data-teknik="Performa">Observasi Keaktifan/Sikap</option>
                                                </optgroup>

                                                <optgroup label="2. SUMATIF (Lingkup Materi)">
                                                    <option value="Ulangan Harian (PH)" data-kategori="Sumatif"
                                                        data-teknik="Tes Tulis">Tes Tertulis / PH</option>
                                                    <option value="Produk" data-kategori="Sumatif" data-teknik="Produk">
                                                        Produk Sederhana (Karya)</option>
                                                    <option value="Praktik" data-kategori="Sumatif" data-teknik="Performa">
                                                        Unjuk Kerja / Praktik</option>
                                                    <option value="Portofolio" data-kategori="Sumatif"
                                                        data-teknik="Portofolio">Portofolio Terbaik</option>
                                                </optgroup>

                                                <optgroup label="3. PROJEK P5 (Kokurikuler)">
                                                    <option value="Asesmen Proyek P5" data-kategori="P5"
                                                        data-teknik="Proyek">Penilaian Proyek P5</option>
                                                    <option value="Jurnal P5" data-kategori="P5" data-teknik="Portofolio">
                                                        Catatan Proses P5</option>
                                                </optgroup>

                                                <optgroup label="4. TINDAK LANJUT">
                                                    <option value="Remedial" data-kategori="Remedial"
                                                        data-teknik="Tes Tulis">Remedial (Perbaikan)</option>
                                                    <option value="Pengayaan" data-kategori="Formatif"
                                                        data-teknik="Lainnya">Pengayaan</option>
                                                </optgroup>

                                                <option value="Lainnya" data-kategori="Formatif" data-teknik="Lainnya"
                                                    style="font-weight:bold; color:red;">Lainnya / Manual</option>
                                            </select>
                                        </div>
                                    </div>

                                    
                                    <div class="col-md-4">
                                        <div class="form-group mb-1">
                                            <small class="text-muted">Topik / Materi</small>
                                            <select class="form-control" id="select_materi">
                                                <option value="" selected disabled>-- Menunggu Metode... --</option>
                                            </select>
                                            <input type="text" class="form-control mt-2 d-none" id="manual_materi"
                                                placeholder="Ketik topik materi manual...">
                                        </div>
                                    </div>

                                    
                                    <div class="col-md-4">
                                        <div class="form-group mb-1">
                                            <small class="text-muted">Input Nilai Masal</small>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <input type="checkbox" id="cek_serentak"
                                                            title="Centang untuk aktifkan">
                                                    </div>
                                                </div>
                                                <input type="number" class="form-control" id="nilai_serentak"
                                                    placeholder="Isi Semua (0-100)" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <small class="text-muted">Judul Tugas (Otomatis)</small>
                                            <input type="text" class="form-control bg-white font-weight-bold"
                                                name="pokok_bahasan" id="pokok_bahasan"
                                                placeholder="Judul tugas akan muncul di sini..." readonly required>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="rpp_id" id="input_rpp_id">
                                <input type="hidden" name="kategori_asesmen" id="input_kategori">
                                <input type="hidden" name="teknik_penilaian" id="input_teknik">
                            </div>

                            <hr>

                            
                            <div class="row mb-2">
                                <div class="col-md-6 d-flex align-items-center">
                                    <h5 class="m-0"><i class="fas fa-users"></i> Daftar Siswa</h5>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm float-right" id="cari_siswa"
                                        placeholder="Cari nama siswa..." style="width: 200px;">
                                </div>
                            </div>

                            <table class="table table-bordered table-striped table-hover" id="tabel_nilai">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 10px">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th style="width: 150px" class="text-center">Nilai (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($siswaList as $siswa)
                                        <tr>
                                            <td>{{ $loop->iteration }}.</td>
                                            <td>{{ $siswa->nis }}</td>
                                            <td>{{ $siswa->nama }}</td>
                                            <td class="p-1">
                                                <input type="number"
                                                    class="form-control nilai-siswa text-center font-weight-bold"
                                                    style="height: 40px; font-size: 16px;" name="nilai[{{ $siswa->id }}]"
                                                    data-nis="{{ $siswa->nis }}" min="0" max="100" placeholder="-">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan='4' class='text-center text-danger'>Tidak ada siswa di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                        <div class="card-footer sticky-bottom-action">
                            <small class="text-muted mr-auto align-self-center d-none d-md-block">Pastikan judul tugas sudah
                                sesuai sebelum menyimpan.</small>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save"></i> Simpan Data
                                Nilai</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <div style="height: 100px;"></div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            
            $.ajax({
                url: '{{ route('guru.tugas.rpp') }}',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var dropdown = $('#select_materi');
                    dropdown.empty();

                    if (response.status === 'success' && Array.isArray(response.data)) {
                        if (response.data.length > 0) {
                            dropdown.append('<option value="" selected disabled>-- Pilih Topik Materi --</option>');
                            $.each(response.data, function (index, item) {
                                var label = "Bab " + item.bab + ": " + item.judul_materi;
                                dropdown.append('<option value="' + item.judul_materi + '" data-id="' + item.id + '">' + label + '</option>');
                            });
                        } else {
                            dropdown.append('<option value="" disabled>Data RPP Kosong di Database</option>');
                        }
                    }
                    dropdown.append('<option value="Lainnya" data-id="" style="font-weight:bold;">Lainnya / Manual</option>');
                },
                error: function () {
                    $('#select_materi').html('<option value="Lainnya">Gagal Connect RPP (Mode Manual)</option>');
                }
            });

            
            function updateJudul() {
                var metodeOpt = $('#select_metode option:selected');
                var materiOpt = $('#select_materi option:selected');

                var metodeVal = metodeOpt.val();
                var materiVal = materiOpt.val();

                $('#input_kategori').val(metodeOpt.data('kategori'));
                $('#input_teknik').val(metodeOpt.data('teknik'));
                $('#input_rpp_id').val(materiOpt.data('id'));

                if (metodeVal === 'Lainnya') {
                    $('#pokok_bahasan').prop('readonly', false).val('').attr('placeholder', 'Ketik judul tugas secara manual...');
                    $('#manual_materi').addClass('d-none');
                    return;
                }

                if (materiVal === 'Lainnya') {
                    $('#manual_materi').removeClass('d-none').focus();
                    materiVal = $('#manual_materi').val();
                } else {
                    $('#manual_materi').addClass('d-none');
                }

                $('#pokok_bahasan').prop('readonly', true);

                if (metodeVal) {
                    var judulFinal = metodeVal;
                    var sambung = " pada materi ";

                    if (materiVal) {
                        if (metodeVal.includes("Proyek") || metodeVal.includes("P5")) {
                            sambung = " dengan tema ";
                        } else if (metodeVal.includes("Produk")) {
                            sambung = " membuat karya tentang ";
                        } else if (metodeVal.includes("Remedial")) {
                            sambung = " untuk perbaikan nilai ";
                        } else if (metodeVal.includes("Jurnal")) {
                            sambung = " perkembangan ";
                        }

                        judulFinal = metodeVal + sambung + materiVal;
                    }

                    $('#pokok_bahasan').val(judulFinal);
                }
            }

            $('#select_metode, #select_materi').change(updateJudul);
            $('#manual_materi').on('input', updateJudul);

            
            $('#cek_serentak').on('change', function () {
                var isActive = $(this).is(':checked');
                $('#nilai_serentak').prop('disabled', !isActive);
                if (isActive) {
                    $('#nilai_serentak').focus();
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'info',
                        title: 'Mode Input Serentak Aktif', showConfirmButton: false, timer: 1500
                    });
                } else {
                    $('#nilai_serentak').val('');
                }
            });

            $('#nilai_serentak').on('input keyup', function () {
                var nilai = $(this).val();
                $('.nilai-siswa').val(nilai).trigger('input');
            });

            
            $(document).on('input', '.nilai-siswa', function () {
                var val = parseInt($(this).val());
                $(this).css({ 'background-color': '#fff', 'color': '#495057' });

                if (!isNaN(val)) {
                    if (val >= 75) {
                        $(this).css({ 'background-color': '#e8f5e9', 'color': '#155724', 'border-color': '#c3e6cb' });
                    } else {
                        $(this).css({ 'background-color': '#f8d7da', 'color': '#721c24', 'border-color': '#f5c6cb' });
                    }
                }
            });

            
            $('#cari_siswa').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#tabel_nilai tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        
        (function () {
            const overlay = document.getElementById('drag-overlay');
            let dragCounter = 0;

            window.addEventListener('dragover', function (e) { e.preventDefault(); }, false);
            window.addEventListener('drop', function (e) { e.preventDefault(); }, false);

            window.addEventListener('dragenter', function (e) {
                e.preventDefault();
                dragCounter++;
                overlay.classList.add('active');
            });

            window.addEventListener('dragleave', function (e) {
                e.preventDefault();
                dragCounter--;
                if (dragCounter === 0) { overlay.classList.remove('active'); }
            });

            window.addEventListener('drop', function (e) {
                e.preventDefault();
                dragCounter = 0;
                overlay.classList.remove('active');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.name.toLowerCase().endsWith('.csv') || file.type === 'text/csv' || file.type === 'application/vnd.ms-excel') {
                        handleFileSelect({ files: [file], value: '' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Format File Salah', text: 'Harap lepaskan file dengan format .csv' });
                    }
                }
            });
        })();

        
        function handleFileSelect(input) {
            if (!input.files[0]) return;

            Swal.fire({
                title: 'Sedang Memproses CSV...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            var reader = new FileReader();
            reader.onload = function (e) {
                var rows = e.target.result.split("\n");
                var count = 0;
                rows.forEach(function (row, i) {
                    if (i === 0) return;

                    var cols = row.split(",");
                    if (cols.length >= 2) {
                        var nis = cols[1].replace(/["\r]/g, "").trim();
                        var val = cols[4] ? cols[4].replace(/["\r]/g, "").trim() : "";

                        var inputSiswa = $('input[data-nis="' + nis + '"]');
                        if (inputSiswa.length && val !== "") {
                            inputSiswa.val(val).trigger('input');
                            count++;
                        }
                    }
                });

                Swal.fire('Selesai!', count + ' data nilai berhasil diimpor.', 'success');

                if (typeof input.value !== 'undefined') { input.value = ''; }
            };
            reader.readAsText(input.files[0]);
        }
    </script>
@endpush