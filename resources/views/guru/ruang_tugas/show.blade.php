@extends('layouts.guru')

@section('title', 'Monitoring Pengumpulan ' . $tugas->judul)

@php
    $active_menu = 'ruang_tugas';
@endphp

@push('css')
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .table-hover tbody tr:hover { background-color: rgba(11,87,208,0.03); }
        
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-belum { background: #fee2e2; color: #ef4444; }
        .status-kumpul { background: #dcfce7; color: #10b981; }
        
        .btn-view-work { background: #eff6ff; color: #0b57d0; border: 1px solid #bfdbfe; border-radius: 20px; font-size: 0.85rem; padding: 4px 12px; font-weight: 600; transition: 0.2s; white-space: nowrap; }
        .btn-view-work:hover { background: #0b57d0; color: #fff; text-decoration: none; }
    </style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h1 class="m-0 font-weight-bold" style="font-size: 1.5rem;">
                        <a href="{{ route('guru.ruang_tugas.index') }}" class="btn btn-sm btn-light rounded-circle shadow-sm mr-2" hx-boost="false"><i class="fas fa-arrow-left"></i></a>
                        Tugas: {{ $tugas->judul }}
                    </h1>
                </div>
                <div>
                    @if($tugas->is_finalized)
                        <button class="btn btn-secondary btn-sm rounded-pill font-weight-bold px-3 shadow-sm" disabled>
                            <i class="fas fa-lock mr-1"></i> Telah Difinalisasi
                        </button>
                    @else
                        <button class="btn btn-primary btn-sm rounded-pill font-weight-bold px-3 shadow-sm" id="btnRekap">
                            <i class="fas fa-clipboard-list mr-1"></i> Masukkan ke Rekap Tugas
                        </button>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 text-muted small mt-2 ml-5">
                <span class="mr-3"><i class="far fa-calendar-alt"></i> Ditugaskan pada: {{ \Carbon\Carbon::parse($tugas->created_at)->format('d M Y') }}</span>
                <span class="mr-3"><i class="fas fa-clock"></i> Batas Waktu: {{ $tugas->batas_waktu ? \Carbon\Carbon::parse($tugas->batas_waktu)->format('d M Y H:i') : 'Tidak Ada' }}</span>
            </div>
            <div class="alert alert-info py-2 small mb-0 mt-3 ml-4 border-0" style="border-radius: 10px; background-color: #e8f0fe; color: #1967d2;">
                <i class="fas fa-info-circle mr-1"></i> Ini adalah halaman monitoring untuk melihat file yang dikirimkan siswa. Anda dapat menginput nilai di sini. Setelah selesai, klik tombol <strong>"Masukkan ke Rekap Tugas"</strong> agar nilai masuk ke Rekap Tugas.
            </div>
        </div>
    </div>

    <section class="content mt-3">
        <div class="container-fluid">
            
            <div class="modal fade" id="viewerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                        <div class="modal-header bg-light border-bottom-0 py-2 px-3">
                            <h5 class="modal-title font-weight-bold m-0" id="viewerTitle" style="font-size: 1.1rem;">Preview Jawaban</h5>
                            <div>
                                <a href="#" id="viewerExternalLink" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill mr-2"><i class="fas fa-download mr-1"></i> Unduh / Buka di Tab Baru</a>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body p-0" id="viewerBody" style="height: 75vh; background: #e9ecef;">
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-0">
                    <div class="table-responsive px-3 py-4">
                        <table id="pengumpulanTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead class="bg-light" style="border-top: 1px solid #dee2e6;">
                                <tr>
                                    <th class="border-top-0 border-bottom-0 text-muted" style="border-radius: 8px 0 0 8px;" width="10%">NIS</th>
                                    <th class="border-top-0 border-bottom-0 text-muted" width="20%">Nama Siswa</th>
                                    <th class="border-top-0 border-bottom-0 text-muted text-center" width="15%">Status</th>
                                    <th class="border-top-0 border-bottom-0 text-muted" width="35%">File / Link & Catatan</th>
                                    <th class="border-top-0 border-bottom-0 text-muted text-center" style="border-radius: 0 8px 8px 0;" width="20%">Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $s)
                                    <tr>
                                        
                                        <td class="font-weight-500 text-secondary">{{ $s->nis }}</td>
                                        
                                        
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $s->nama }}</div>
                                            @if($s->submission_id)
                                                <div class="small text-muted mt-1"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($s->dikumpulkan_pada)->format('d M y - H:i') }}</div>
                                            @endif
                                        </td>
                                        
                                        
                                        <td class="text-center">
                                            @if(!$s->submission_id)
                                                <span class="status-badge status-belum"><i class="fas fa-times-circle mr-1"></i> Belum Mengumpulkan</span>
                                            @else
                                                <span class="status-badge status-kumpul"><i class="fas fa-check-circle mr-1"></i> Sudah Mengumpulkan</span>
                                            @endif
                                        </td>
                                        
                                        
                                        <td>
                                            @if($s->submission_id)
                                                @if($s->metode === 'manual')
                                                    
                                                    <span class="badge badge-info rounded-pill px-3 py-2"><i class="fas fa-eye mr-1"></i> Sudah Dibaca/Ditandai Selesai</span>
                                                    @if($s->catatan_siswa)
                                                        <div class="small text-muted mt-2 border-left border-info pl-2 ml-1 msg-catatan">"{{ $s->catatan_siswa }}"</div>
                                                    @endif
                                                @else
                                                    
                                                    @php
                                                        $isLink = ($s->metode === 'link');
                                                        $rawUrl = trim($s->link_pengumpulan);
                                                        if ($isLink && !preg_match("~^(?:f|ht)tps?://~i", $rawUrl)) {
                                                            $rawUrl = "https://" . $rawUrl;
                                                        }
                                                        $url = $isLink ? $rawUrl : asset('lms/uploads/tugas/' . $s->link_pengumpulan);
                                                    @endphp
                                                    
                                                    <a href="javascript:void(0)" onclick="openLampiran('{{ addslashes($s->nama) }}', '{{ $url }}', {{ $isLink ? 'true' : 'false' }})" class="btn-view-work">
                                                        <i class="fas {{ $isLink ? 'fa-link' : 'fa-file-alt' }} mr-1"></i> {{ $isLink ? 'Buka Link Jawaban' : 'Pratinjau / Unduh Dokumen' }}
                                                    </a>
                                                    
                                                    @if($s->catatan_siswa)
                                                        <div class="small text-muted mt-2 border-left border-primary pl-2 ml-1 msg-catatan font-italic">"{{ $s->catatan_siswa }}"</div>
                                                    @endif
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        
                                        
                                        <td class="text-center align-middle bg-light">
                                            <div class="input-group input-group-sm mb-2">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text font-weight-bold">Nilai</span>
                                                </div>
                                                <input type="number" class="form-control text-center input-nilai" 
                                                    id="nilai_{{ $s->uid }}"
                                                    value="{{ $s->nilai }}" 
                                                    placeholder="0-100" min="0" max="100"
                                                    {{ $tugas->is_finalized ? 'disabled' : '' }}>
                                            </div>
                                            <button class="btn btn-sm btn-success w-100 btn-save-nilai" data-siswa="{{ $s->uid }}" {{ $tugas->is_finalized ? 'disabled' : '' }}>
                                                <i class="fas fa-save mr-1"></i> Simpan
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        
        $('#pengumpulanTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
            },
            pageLength: 50,
            columnDefs: [
                { orderable: false, targets: [3] } 
            ]
        });
    });

    
    function openLampiran(nama_siswa, url, isLink) {
        let title = document.getElementById('viewerTitle');
        let extLink = document.getElementById('viewerExternalLink');
        let body = document.getElementById('viewerBody');

        title.innerHTML = 'Jawaban: ' + nama_siswa;
        extLink.href = url;

        
        let embedUrl = url;
        if(isLink) {
            if (url.includes('youtube') || url.includes('youtu.be')) {
                const id = url.match(/(?:v=|youtu\.be\/)([^&]+)/);
                if(id) embedUrl = `https://www.youtube.com/embed/${id[1]}`;
            } else if (url.includes('drive.google.com') || url.includes('docs.google.com')) {
                embedUrl = url.replace('/view', '/preview');
            }
        }
        
        
        let isImage = false;
        if(!isLink) {
            let ext = url.split('.').pop().toLowerCase();
            if(['jpg','jpeg','png','gif','webp'].includes(ext)) {
                isImage = true;
            }
        }

        if(isImage) {
            body.innerHTML = `<div class="d-flex justify-content-center align-items-center h-100 p-4"><img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.1);"></div>`;
        } else {
            body.innerHTML = `<iframe src="${embedUrl}" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>`;
        }

        $('#viewerModal').modal('show');
    }
    
    
    $('#viewerModal').on('hidden.bs.modal', function () {
        document.getElementById('viewerBody').innerHTML = '';
    });

    
    $('.btn-save-nilai').on('click', function() {
        let btn = $(this);
        let siswa_id = btn.data('siswa');
        let nilai = $('#nilai_' + siswa_id).val();

        if (nilai === '') {
            Swal.fire('Error', 'Nilai tidak boleh kosong', 'warning');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("guru.ruang_tugas.save_nilai", $tugas->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                siswa_id: siswa_id,
                nilai: nilai
            },
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Tersimpan').removeClass('btn-success').addClass('btn-primary');
                    setTimeout(() => {
                        btn.html('<i class="fas fa-save mr-1"></i> Simpan').removeClass('btn-primary').addClass('btn-success');
                    }, 2000);
                } else {
                    Swal.fire('Error', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal menghubungi server', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
            }
        });
    });

    
    $('#btnRekap').on('click', function() {
        Swal.fire({
            title: 'Masukkan ke Rekap Tugas?',
            text: "Nilai siswa yang sudah disimpan akan dimasukkan ke tabel Rekap Tugas.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0b57d0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Masukkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let btn = $('#btnRekap');
                let originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    url: '{{ route("guru.ruang_tugas.rekap_masukkan", $tugas->id) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.status === 'success') {
                            Swal.fire('Berhasil!', res.message, 'success');
                            btn.prop('disabled', false).html(originalText);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                            btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghubungi server', 'error');
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });
</script>
@endpush
