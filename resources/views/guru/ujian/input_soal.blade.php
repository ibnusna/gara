@extends('layouts.guru')

@section('title', 'Ruang Asesmen | Garuda Akademi')

@php
    $active_menu = 'ujian';
@endphp

@push('styles')
<style>
    
    .method-hub {
        padding: 40px 0;
    }
    .method-hub-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 28px;
    }
    .method-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
    }
    .method-card {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 36px 28px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .method-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(11,87,208,0.04) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .method-card:hover::before { opacity: 1; }
    .method-card:hover {
        border-color: #0b57d0;
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -10px rgba(11,87,208,0.15);
        text-decoration: none;
        color: inherit;
    }
    .method-card.active-card {
        border-color: #0b57d0;
        box-shadow: 0 0 0 4px rgba(11,87,208,0.12), 0 20px 40px -10px rgba(11,87,208,0.2);
    }
    .method-card .mc-icon {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 20px;
        transition: transform 0.3s;
    }
    .method-card:hover .mc-icon { transform: scale(1.1) rotate(-3deg); }
    .mc-icon-smart  { background: linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; }
    .mc-icon-manual { background: linear-gradient(135deg,#0b57d0,#2563eb); color:#fff; }
    .mc-icon-qc     { background: linear-gradient(135deg,#10b981,#059669); color:#fff; }
    .method-card .mc-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .method-card .mc-desc {
        font-size: 0.87rem;
        color: #64748b;
        line-height: 1.6;
    }
    .method-card .mc-badge {
        display: inline-block;
        margin-top: 16px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .badge-existing { background:#f1f5f9; color:#64748b; }
    .badge-new      { background:#eff6ff; color:#2563eb; }
    .badge-new-green{ background:#ecfdf5; color:#059669; }

    
    #smart-panel { display: none; }
    #smart-panel.show { display: block; }

    
    .gate-alert {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 16px 20px;
        color: #991b1b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="m-0"><i class="fas fa-edit mr-2" style="color:#0b57d0;"></i> Ruang Asesmen</h1>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-pill" style="background:#eff6ff; color:#0b57d0; font-size:0.8rem; padding:6px 14px;">
                        <i class="fas fa-book mr-1"></i> {{ session('nama_mapel') }}
                    </span>
                    <span class="badge badge-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.8rem; padding:6px 14px; margin-left:8px;">
                        <i class="fas fa-school mr-1"></i> {{ session('nama_kelas') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            
            @if ($statusSummary && $statusSummary['validated'] == 0)
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #16a34a !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="mr-3" style="width: 48px; height: 48px; border-radius: 12px; background: #16a34a; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="font-weight-bold text-success mb-2">Informasi Penginputan Soal</h5>
                                <p class="mb-2 text-dark" style="font-size: 0.95rem; line-height: 1.5;">
                                    Anda sudah menginput total <strong>{{ $statusSummary['total'] }} soal</strong> untuk Mata Pelajaran <strong>{{ $mapel }}</strong> di Kelas <strong>{{ $kelas }}</strong>.
                                </p>
                                <div class="d-flex flex-wrap" style="gap: 12px; display: flex; flex-direction: row;">
                                    @if($statusSummary['draft'] > 0)
                                        <span class="badge badge-warning px-3 py-2 text-white mr-2 mb-2" style="font-size: 0.8rem; border-radius: 8px;">
                                            <i class="fas fa-clock mr-1"></i> {{ $statusSummary['draft'] }} Soal berstatus DRAFT (Menunggu Validasi Operator)
                                        </span>
                                    @endif
                                    @if($statusSummary['validated'] > 0)
                                        <span class="badge badge-success px-3 py-2 text-white mb-2" style="font-size: 0.8rem; border-radius: 8px; background-color: #16a34a;">
                                            <i class="fas fa-check-double mr-1"></i> {{ $statusSummary['validated'] }} Soal berstatus VALIDATED (Telah Diterima Operator)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            
            @if(isset($statusSummary) && $statusSummary['validated'] > 0)
                @if(isset($jadwal_hasil) && $jadwal_hasil->hasil_dirilis_ke_guru === 'YA')
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; border: 1px solid #bae6fd;">
                        <div class="card-header border-bottom-0 pt-4 pb-3" style="background: linear-gradient(135deg, #0b57d0, #2563eb); border-radius: 16px 16px 0 0;">
                            <h5 class="font-weight-bold mb-0 text-white"><i class="fas fa-chart-bar mr-2"></i> Hasil Asesmen Siswa</h5>
                            <p class="mb-0 text-white mt-1" style="opacity: 0.9; font-size: 0.9rem;">
                                Nilai ujian telah dirilis oleh Operator pada tanggal {{ date('d-m-Y', strtotime($jadwal_hasil->tanggal_ujian)) }}
                            </p>
                        </div>
                        <div class="card-body p-0">
                            @if(empty($hasil_siswa))
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="font-weight-bold text-dark">Belum Ada Jawaban Siswa</h5>
                                    <p class="text-muted">Belum ada siswa yang mensubmit jawaban untuk jadwal ini.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th style="width: 5%" class="text-center">No</th>
                                                <th style="width: 20%">NIS</th>
                                                <th style="width: 40%">Nama Siswa</th>
                                                <th style="width: 15%" class="text-center">Nilai</th>
                                                <th style="width: 20%" class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hasil_siswa as $index => $row)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $row->nis }}</td>
                                                <td class="font-weight-bold text-dark">{{ $row->nama }}</td>
                                                <td class="text-center font-weight-bold {{ $row->skor >= 75 ? 'text-success' : 'text-danger' }}" style="font-size: 1.1rem;">
                                                    {{ number_format($row->skor, 1) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($row->skor >= 75)
                                                        <span class="badge badge-success px-3 py-2" style="border-radius: 20px;"><i class="fas fa-check mr-1"></i> Tuntas</span>
                                                    @else
                                                        <span class="badge badge-danger px-3 py-2" style="border-radius: 20px;"><i class="fas fa-times mr-1"></i> Tidak Tuntas</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-info shadow-sm mb-4" style="border-radius: 12px; background-color: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fa-2x mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1">Menunggu Hasil Ujian</h6>
                                <p class="mb-0" style="font-size: 0.95rem;">Soal Anda telah divalidasi. Hasil ujian siswa akan muncul di sini setelah Operator merilisnya.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            
            @if(isset($questions) && $questions->count() > 0)
                @if($questions->first()->status_soal != 'VALIDATED')
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold mb-0"><i class="fas fa-list mr-2" style="color:#0b57d0;"></i> Daftar Soal yang Telah Diinput</h5>
                        @if($questions->first()->status_soal == 'DRAFT' && $gateOpen)
                            <div>
                                <a href="{{ route('guru.ujian.qc') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                                    <i class="fas fa-edit"></i> Edit di QC Preview
                                </a>
                                <form action="{{ route('guru.ujian.destroy_paket') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SELURUH soal draft pada paket ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                                        <i class="fas fa-trash-alt"></i> Hapus Paket
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead style="background-color: #f8fafc;">
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th style="width: 15%">Tipe</th>
                                        <th style="width: 65%">Pertanyaan</th>
                                        <th style="width: 15%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($questions as $index => $q)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($q->tipe_soal == 'PG')
                                                <span class="badge badge-primary">Pilihan Ganda</span>
                                            @elseif($q->tipe_soal == 'PG_KOMPLEKS')
                                                <span class="badge badge-info">PG Kompleks</span>
                                            @elseif($q->tipe_soal == 'BENAR_SALAH')
                                                <span class="badge badge-warning">Benar / Salah</span>
                                            @else
                                                <span class="badge badge-dark">Isian</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ Str::limit(strip_tags($q->konten_soal), 80) }}
                                        </td>
                                        <td>
                                            @if($q->status_soal == 'VALIDATED')
                                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Validated</span>
                                            @else
                                                <span class="badge badge-warning text-white"><i class="fas fa-clock mr-1"></i> Draft</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            @else

            
            @if (!$gateOpen)
                <div class="card shadow-sm border-0" style="border-radius:20px; background:#fff; overflow:hidden;">
                    <div class="card-body text-center py-5">
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #fef2f2, #fee2e2); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; border: 2px dashed #fca5a5;">
                            <i class="fas fa-lock fa-3x" style="color: #ef4444;"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-2">Gerbang Penginputan Ditutup</h4>
                        <p class="text-muted mx-auto" style="max-width: 500px; line-height:1.6; font-size:1rem;">
                            Saat ini Operator sedang menutup akses penginputan soal. Anda belum diizinkan untuk menginput atau mengubah soal baru. Silakan hubungi operator sekolah jika Anda memerlukan akses penginputan.
                        </p>
                        <span class="badge badge-pill badge-danger px-3 py-2" style="font-size:0.85rem; font-weight:700;">
                            <i class="fas fa-ban mr-1"></i> Input Ditangguhkan
                        </span>
                    </div>
                </div>
            @else
                
                <div class="method-hub" id="method-hub">
                    <div class="method-hub-title">Pilih Metode Input Soal</div>
                    <div class="method-cards">

                        
                        <div class="method-card" onclick="showSmartInput()" id="card-smart">
                            <div class="mc-icon mc-icon-smart">
                                <i class="fas fa-magic"></i>
                            </div>
                            <div class="mc-title">Smart Input</div>
                            <div class="mc-desc">
                                Paste soal dari Word/Notepad dalam format teks. Sistem akan mem-parse otomatis.
                            </div>
                        </div>

                        
                        <a href="{{ route('guru.ujian.manual') }}" class="method-card" id="card-manual">
                            <div class="mc-icon mc-icon-manual">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                            <div class="mc-title">Manual Input</div>
                            <div class="mc-desc">
                                Input soal satu per satu dengan panduan form interaktif. Cocok untuk guru yang tidak familiar dengan format teks.
                            </div>
                        </a>

                        
                        <a href="{{ route('guru.ujian.qc') }}" class="method-card" id="card-qc">
                            <div class="mc-icon mc-icon-qc">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="mc-title">Quality Control (QC)</div>
                            <div class="mc-desc">
                                Pratinjau soal persis seperti yang dilihat siswa. Edit inline & kirim ke operator dalam satu langkah.
                            </div>
                        </a>

                    </div>
                </div>
            @endif

            
            <div id="smart-panel">
                <div class="d-flex align-items-center mb-3">
                    <button class="btn btn-sm btn-light border mr-3" onclick="hideSmartInput()">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </button>
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-magic mr-2" style="color:#6366f1;"></i> Smart Input — Mode Teks</h5>
                </div>

                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="card card-primary card-outline h-100">
                            <div class="card-header">
                                <h3 class="card-title">Paste Soal Disini</h3>
                                <div class="card-tools">
                                    @php
                                        $hasGoogle = \App\Integrations\Google\GoogleOAuthService::class;
                                        $isGoogleConnected = false;
                                        if(class_exists($hasGoogle)) {
                                            $gService = new \App\Integrations\Google\GoogleOAuthService();
                                            $isGoogleConnected = !is_null($gService->getConnectedStatus(auth()->id()));
                                        }
                                    @endphp
                                    <button class="btn btn-sm btn-outline-primary" onclick="openGdocsPicker()" @if(!$gateOpen || !$isGoogleConnected) disabled @if(!$isGoogleConnected) title="Hubungkan Google di Profil" @endif @endif><i class="fab fa-google-drive"></i> Tarik GDocs</button>
                                    <button class="btn btn-sm btn-info" onclick="loadContoh()" {{ !$gateOpen ? 'disabled' : '' }}><i class="fas fa-magic"></i> Contoh</button>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label>Mata Pelajaran</label>
                                        <input type="text" class="form-control form-control-sm" id="mapelInput"
                                            value="{{ session('nama_mapel') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed; font-weight: 600; color: #475569;">
                                    </div>
                                    <div class="col-6">
                                        <label>Kelas</label>
                                        <input type="text" class="form-control form-control-sm" id="kelasInput"
                                            value="{{ session('nama_kelas') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed; font-weight: 600; color: #475569;">
                                    </div>
                                </div>

                                <label>Format Input (Copy dari Word/Notepad)</label>
                                <textarea class="form-control flex-fill p-3" id="rawSoal"
                                    style="min-height: 400px; font-family: monospace; font-size: 14px; background: #f8f9fa;"
                                    {{ !$gateOpen ? 'disabled' : '' }}>
1. Siapakah penemu bola lampu?
A. Thomas Edison
B. Nikola Tesla
C. Einstein
D. Newton
E. Habibie
Kunci Jawaban: A

2. Pilih 2 buah yang berwarna merah!
A. Apel
B. Jeruk
C. Stroberi
D. Pisang
E. Melon
Kunci Jawaban: A, C

3. [TIPE:BENAR-SALAH]
Matahari terbit di timur : BENAR
Air mendidih 100 derajat : BENAR
Bumi itu datar : SALAH
Kunci Jawaban: BENAR,BENAR,SALAH

4. Berapakah hasil 1 + 1?
Kunci Jawaban: 2
                                </textarea>

                                <button class="btn btn-warning btn-block font-weight-bold mt-3" onclick="parseText()"
                                    {{ !$gateOpen ? 'disabled' : '' }}>
                                    <i class="fas fa-sync-alt mr-2"></i> PARSE & PREVIEW
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-6">
                        <div class="card card-success card-outline h-100">
                            <div class="card-header">
                                <h3 class="card-title">Hasil Preview (<span id="countSoal">0</span> Soal)</h3>
                            </div>
                            <div class="card-body p-0 table-responsive" style="height: 500px;">
                                <table class="table table-striped table-hover table-sm text-sm m-0">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th style="width: 10px">#</th>
                                            <th>Tipe</th>
                                            <th>Konten & Kunci</th>
                                            <th style="width: 40px">Valid</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">Silakan klik tombol Parse</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex gap-2" style="gap:10px;">
                                    <button class="btn btn-success flex-fill" id="btnSimpan" disabled onclick="simpanPaket()">
                                        <i class="fas fa-save mr-1"></i> Simpan Langsung
                                    </button>
                                    <button class="btn btn-primary flex-fill" id="btnQCSmart" disabled onclick="lanjutKeQCSmart()"
                                        style="background:linear-gradient(135deg,#0b57d0,#2563eb);border:none;font-weight:700;">
                                        <i class="fas fa-search mr-1"></i> QC Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @endif

        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    let PARSED_DATA = [];

    function showSmartInput() {
        document.getElementById('method-hub').style.display = 'none';
        document.getElementById('smart-panel').classList.add('show');
        document.getElementById('card-smart').classList.add('active-card');
    }

    function hideSmartInput() {
        document.getElementById('method-hub').style.display = '';
        document.getElementById('smart-panel').classList.remove('show');
        document.getElementById('card-smart').classList.remove('active-card');
    }

    function alertGate() {
        Swal.fire({
            icon: 'warning',
            title: 'Gerbang Ditutup',
            text: 'Operator sedang menutup gerbang penginputan soal. Silakan coba lagi nanti.',
            confirmButtonColor: '#0b57d0'
        });
    }

    function parseText() {
        const text = document.getElementById('rawSoal').value;
        const result = parseSoalFromText(text);
        PARSED_DATA = result;
        renderPreview(result);
    }

    function renderPreview(data) {
        const tbody = document.getElementById('previewBody');
        document.getElementById('countSoal').innerText = data.length;
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-5">Format tidak dikenali. Cek contoh.</td></tr>';
            document.getElementById('btnSimpan').disabled = true;
            return;
        }

        data.forEach(item => {
            let badge = 'badge-secondary';
            if (item.tipe === 'PG') badge = 'badge-primary';
            if (item.tipe === 'PG_KOMPLEKS') badge = 'badge-info';
            if (item.tipe === 'BENAR_SALAH') badge = 'badge-warning';
            if (item.tipe === 'ISIAN') badge = 'badge-dark';

            let contentHtml = `<b>${item.soal.substring(0, 50)}...</b><br>`;
            if (item.tipe === 'BENAR_SALAH') {
                contentHtml += `<small>A: ${item.a} (${item.kunci.split(',')[0] || '?'})</small>...`;
            } else {
                contentHtml += `<small>Kunci: <b class="text-success">${item.kunci}</b></small>`;
            }

            tbody.innerHTML += `
            <tr>
                <td>${item.no}</td>
                <td><span class="badge ${badge}">${item.tipe}</span></td>
                <td>${contentHtml}</td>
                <td><i class="fas fa-check-circle text-success"></i></td>
            </tr>
        `;
        });

        document.getElementById('btnSimpan').disabled = false;
        document.getElementById('btnQCSmart').disabled = false;
    }

    function lanjutKeQCSmart() {
        if (PARSED_DATA.length === 0) {
            Swal.fire({ icon:'warning', title:'Belum Ada Data', text:'Parse soal terlebih dahulu!', confirmButtonColor:'#0b57d0' });
            return;
        }
        const mapel = document.getElementById('mapelInput').value;
        const kelas = document.getElementById('kelasInput').value;

        
        const soalList = PARSED_DATA.map(item => ({
            no: item.no, tipe: item.tipe, soal: item.soal,
            a: item.a, b: item.b, c: item.c, d: item.d, e: item.e,
            kunci: item.kunci
        }));

        sessionStorage.setItem('soal_draft_manual', JSON.stringify({
            mapel: mapel,
            kelas: kelas,
            soal_list: soalList
        }));
        window.location.href = '{{ route('guru.ujian.qc') }}?from=smart';
    }

    function loadContoh() {
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: 'Contoh format sudah ada di kolom input.',
            confirmButtonColor: '#0b57d0'
        });
    }

    async function simpanPaket() {
        if (PARSED_DATA.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Silakan klik Parse & Preview terlebih dahulu sebelum menyimpan.', confirmButtonColor: '#0b57d0' });
            return;
        }

        const mapel = document.getElementById('mapelInput').value;
        const kelas = document.getElementById('kelasInput').value;

        if (!mapel || !kelas) {
            Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', text: 'Mapel dan Kelas harus diisi!', confirmButtonColor: '#0b57d0' });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Simpan',
            text: `Simpan ${PARSED_DATA.length} soal untuk Mapel ${mapel}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0b57d0',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan!',
            cancelButtonText: '<i class="fas fa-times"></i> Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btnSimpan');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                btn.disabled = true;

                const payload = {
                    mapel: mapel,
                    kelas: kelas,
                    soal_list: JSON.stringify(PARSED_DATA),
                    _token: '{{ csrf_token() }}'
                };

                try {
                    const formData = new FormData();
                    for (let k in payload) formData.append(k, payload[k]);

                    const req = await fetch('{{ route('guru.ujian.save_paket') }}', { method: 'POST', body: formData });
                    const res = await req.json();

                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Sukses!', text: 'Semua soal berhasil disimpan.', confirmButtonColor: '#0b57d0' })
                            .then(() => { window.location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#0b57d0' });
                        btn.innerHTML = '<i class="fas fa-save mr-2"></i> SIMPAN SEMUA KE BANK SOAL';
                        btn.disabled = false;
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire({ icon: 'error', title: 'Error Network', text: 'Terjadi kesalahan komunikasi dengan server.', confirmButtonColor: '#0b57d0' });
                    btn.innerHTML = '<i class="fas fa-save mr-2"></i> SIMPAN SEMUA KE BANK SOAL';
                    btn.disabled = false;
                }
            }
        });
    }

    function parseSoalFromText(text) {
        if (!text || typeof text !== 'string') return [];
        
        
        text = text.replace(/\r\n|\r/g, "\n");
        
        text = text.replace(/(?<!\n)[ \t]+(?=[A-E][\.\)]\s)/gi, "\n");
        text = text.replace(/(?<!\n)[ \t]+(?=Kunci\s+Jawaban[\s:]+)/gi, "\n");
        text = text.replace(/(?<!\n)[ \t]+(?=Kunci[\s:]+(?!\s*Jawaban))/gi, "\n");
        text = text.replace(/(?<!\n)[ \t]+(?=(?<!Kunci\s)Jawaban[\s:]+)/gi, "\n");
        text = text.replace(/\n{3,}/g, "\n\n");

        const questionBlocks = text.trim().split(/^\s*\d+[\.\)]\s/m).filter(Boolean);
        return questionBlocks.map((block, index) => {
            let type = "PG";
            let soalText = "";
            let kunci = "";
            const options = { A: "", B: "", C: "", D: "", E: "" };

            if (block.includes("[TIPE:BENAR-SALAH]")) {
                type = "BENAR_SALAH";
                block = block.replace("[TIPE:BENAR-SALAH]", "").trim();
            }

            const lines = block.trim().split("\n");
            soalText = lines.shift().trim();

            if (type === "BENAR_SALAH" && (soalText.toUpperCase().includes(": BENAR") || soalText.toUpperCase().includes(": SALAH") || soalText.toUpperCase().includes(":BENAR") || soalText.toUpperCase().includes(":SALAH"))) {
                lines.unshift(soalText);
                soalText = "Tentukan Benar/Salah dari pernyataan berikut!";
            }

            const bsKeys = [];
            let bsIndex = 0;
            const mapChar = ["A", "B", "C", "D", "E"];

            lines.forEach((line) => {
                const t = line.trim();
                if (!t) return;
                if (type === "BENAR_SALAH" && t.toLowerCase().startsWith("kunci jawaban:")) return;
                if (type === "BENAR_SALAH") {
                    const parts = t.split(":");
                    if (parts.length >= 2) {
                        const statement = parts[0].trim();
                        const answer = parts[1].trim().toUpperCase();
                        if (bsIndex < 5) { options[mapChar[bsIndex]] = statement; bsKeys.push(answer); bsIndex++; }
                    }
                } else {
                    const keyMatch = t.match(/^(?:Kunci\s+Jawaban|Kunci|Jawaban)[\s:]+(.*)/i);
                    if (keyMatch) {
                        kunci = keyMatch[1].trim();
                    } else {
                        const optRegex = /(?<=^|\s)([A-E])[\.\)]\s*(.*?)(?=\s+(?:[A-E][\.\)]|$)|$)/gi;
                        let match;
                        while ((match = optRegex.exec(t)) !== null) {
                            options[match[1].toUpperCase()] = match[2].trim();
                        }
                    }
                }
            });

            if (type === "BENAR_SALAH") {
                kunci = bsKeys.join(",");
            } else {
                const hasOptions = Object.values(options).some(val => val !== "");
                if (kunci && !hasOptions) { type = "ISIAN"; }
                else if (kunci.includes(",")) { type = "PG_KOMPLEKS"; kunci = kunci.split(",").map(k => k.trim().toUpperCase()).join(","); }
                else { kunci = kunci.toUpperCase(); }
            }

            return { no: index + 1, tipe: type, soal: soalText, a: options.A, b: options.B, c: options.C, d: options.D, e: options.E, kunci: kunci };
        });
    }
</script>
<script src="{{ asset('assets/js/gara-picker.js') }}?v=1.0"></script>
<script>
    function openGdocsPicker() {
        let picker = new GaraPicker({
            developerKey: '{{ config('services.google.developer_key') }}',
            appId: '{{ config('services.google.app_id') }}',
            tokenUrl: '{{ route('guru.google.picker_token') }}',
            mode: 'gdocs_only',
            onSelect: function(data) {
                Swal.fire({
                    title: 'Mengekstrak Soal...',
                    text: 'Sedang membaca dokumen Google Docs.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData();
                formData.append('doc_id', data.id);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('guru.ujian.extract_gdocs') }}', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            PARSED_DATA = res.data;
                            document.getElementById('rawSoal').value = res.raw_text;
                            renderPreview(PARSED_DATA);
                            Swal.close();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Ekstraksi GDocs Berhasil',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal memproses dokumen', 'error');
                    });
            }
        });
        picker.open();
    }
</script>
@endpush
