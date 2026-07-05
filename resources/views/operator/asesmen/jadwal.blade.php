@extends('layouts.operator', ['page_title' => 'Jadwal Ujian | Garuda Akademi', 'active_menu' => 'ujian', 'active_submenu' => 'jadwal_ujian'])

@section('title', 'Jadwal Ujian | Garuda Akademi')

@section('content')
<div class="content-wrapper">

    
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                        Jadwal Ujian
                        <small class="badge badge-primary ml-2" style="font-size:0.58em;vertical-align:middle;" id="globalJenisBadge">Memuat...</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('operator.asesmen.dashboard') }}">Asesmen</a></li>
                        <li class="breadcrumb-item active">Jadwal Ujian</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            


            <div class="card card-outline card-info mb-3" id="configCard">
                <div class="card-header" style="cursor:pointer;" onclick="toggleConfigPanel()">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-sliders-h mr-2 text-info"></i>
                        Pengaturan Ujian
                        <small class="text-muted font-weight-normal ml-2" style="font-size:0.8rem;">— konfigurasi default untuk jadwal baru</small>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info" id="configSavedBadge" style="display:none;">
                            <i class="fas fa-check mr-1"></i>Tersimpan
                        </span>
                        <button type="button" class="btn btn-tool">
                            <i class="fas fa-chevron-down" id="configChevron"></i>
                        </button>
                    </div>
                </div>
                <div id="configBody" class="collapse show">
                    <div class="card-body">
                        <div class="row">

                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="cfg-label">Tampilkan Jawaban?</div>
                                <div class="toggle-group" id="tgJawaban">
                                    <button class="tg-btn tg-active" data-val="TIDAK" onclick="setToggle('tgJawaban','TIDAK')">TIDAK</button>
                                    <button class="tg-btn" data-val="YA" onclick="setToggle('tgJawaban','YA')">YA</button>
                                </div>
                            </div>

                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="cfg-label">Izin Ulang?</div>
                                <div class="toggle-group" id="tgUlang">
                                    <button class="tg-btn tg-active" data-val="TIDAK" onclick="setToggle('tgUlang','TIDAK')">TIDAK</button>
                                    <button class="tg-btn" data-val="YA" onclick="setToggle('tgUlang','YA')">YA</button>
                                </div>
                            </div>

                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="cfg-label">Tampilkan Nilai?</div>
                                <div class="toggle-group" id="tgNilai">
                                    <button class="tg-btn tg-active" data-val="YA" onclick="setToggle('tgNilai','YA')">YA — Tampilkan ke Siswa</button>
                                    <button class="tg-btn" data-val="TIDAK" onclick="setToggle('tgNilai','TIDAK')">TIDAK — Sembunyikan</button>
                                </div>
                                <small class="text-muted">Jika TIDAK, siswa hanya melihat pesan selesai.</small>
                            </div>

                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="cfg-label">Mode Submit</div>
                                <div class="toggle-group" id="tgSubmit">
                                    <button class="tg-btn tg-active" data-val="MANDIRI" onclick="setToggle('tgSubmit','MANDIRI')">MANDIRI — Siswa Klik Selesai</button>
                                    <button class="tg-btn" data-val="SERENTAK" onclick="setToggle('tgSubmit','SERENTAK')">SERENTAK — Auto Waktu Habis</button>
                                </div>
                                <small class="text-muted">Serentak: tombol Selesai disembunyikan.</small>
                            </div>

                        </div>
                        <div class="text-right">
                            <button class="btn btn-info btn-sm font-weight-bold" onclick="saveConfig()">
                                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            


            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-2 text-primary"></i>
                        Buat Jadwal Baru
                    </h3>
                    <div class="card-tools">
                        <small class="text-muted">Pilih hari → kelas → mapel → atur waktu → simpan</small>
                    </div>
                </div>
                <div class="card-body">

                    
                    <div class="batch-step">
                        <div class="batch-step-num">1</div>
                        <div class="batch-step-body">
                            <div class="batch-step-title">Pilih Tanggal Ujian</div>
                            <div class="row align-items-end">
                                <div class="col-lg-3 col-md-5">
                                    <input type="date" id="bTanggal" class="form-control form-control-lg font-weight-bold"
                                           oninput="onTanggalChange()">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <div class="hari-display" id="hariDisplay">—</div>
                                </div>
                                <div class="col-lg-6 col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Mapel yang sudah dijadwalkan di hari ini akan ditandai.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="batch-step">
                        <div class="batch-step-num">2</div>
                        <div class="batch-step-body">
                            <div class="batch-step-title">
                                Pilih Kelas
                                <button class="btn btn-xs btn-outline-secondary ml-2" onclick="toggleAllKelas()">
                                    <i class="fas fa-check-square mr-1"></i>Pilih Semua
                                </button>
                            </div>
                            <div class="checkbox-grid" id="kelasGrid">
                                <div class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="batch-step">
                        <div class="batch-step-num">3</div>
                        <div class="batch-step-body">
                            <div class="batch-step-title">
                                Pilih Mata Pelajaran
                                <button class="btn btn-xs btn-outline-secondary ml-2" onclick="toggleAllMapel()">
                                    <i class="fas fa-check-square mr-1"></i>Pilih Semua Tersedia
                                </button>
                                <span class="badge badge-light ml-2">
                                    <span class="dot-green"></span> Soal siap
                                    &nbsp;<span class="dot-yellow"></span> Belum validated
                                    &nbsp;<span class="dot-red"></span> Belum ada soal
                                </span>
                            </div>
                            <div class="checkbox-grid" id="mapelGrid">
                                <div class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="batch-step" id="step4Wrap" style="display:none;">
                        <div class="batch-step-num">4</div>
                        <div class="batch-step-body">
                            <div class="batch-step-title">Atur Waktu per Mata Pelajaran</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="batchTimeTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:34%;">Mata Pelajaran</th>
                                            <th style="width:22%;">Jam Mulai</th>
                                            <th style="width:18%;">Durasi (mnt)</th>
                                            <th style="width:18%;">Jam Selesai</th>
                                            <th style="width:8%;" class="text-center">Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="batchTimeBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    
                    <div class="text-right mt-3" id="btnSimpanWrap" style="display:none;">
                        <button class="btn btn-primary btn-lg font-weight-bold px-5" onclick="simpanBatch()">
                            <i class="fas fa-save mr-2"></i> Simpan Semua Jadwal
                            <span class="badge badge-light ml-2" id="batchCountBadge">0</span>
                        </button>
                    </div>

                </div>
            </div>

            


            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list mr-2"></i> Daftar Jadwal Ujian
                    </h3>
                    <div class="card-tools">
                        <button onclick="resetAllTokens()" class="btn btn-sm btn-outline-warning mr-1" title="Reset semua token jadwal hari ini">
                            <i class="fas fa-redo-alt mr-1"></i> Reset Token Hari Ini
                        </button>
                        <button onclick="loadJadwal()" class="btn btn-sm btn-outline-secondary" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:520px;overflow-y:auto;">
                        <table id="jadwalTable" class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-dark" style="position:sticky;top:0;z-index:2;">
                                <tr>
                                    <th>Hari / Tanggal</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Waktu</th>
                                    <th class="text-center">Soal</th>
                                    <th class="text-center">Token</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="jadwalTableBody">
                                <tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Generate Token</strong> hanya aktif jika soal sudah divalidasi.
                    <strong>Portal</strong> aktif saat ujian berlangsung.
                </div>
            </div>

        </div>
    </section>
</div>


<style>
    
    .toggle-group {
        display: flex;
        border: 1.5px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 6px;
    }
    .tg-btn {
        flex: 1;
        padding: 8px 10px;
        font-size: 0.78rem;
        font-weight: 700;
        border: none;
        background: #f8f9fa;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.18s;
        line-height: 1.3;
        text-align: center;
    }
    .tg-btn + .tg-btn { border-left: 1px solid #dee2e6; }
    .tg-btn:hover { background: #e9ecef; color: #343a40; }
    .tg-btn.tg-active {
        background: #0b57d0;
        color: #fff;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
    }
    .cfg-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    
    .batch-step {
        display: flex;
        gap: 16px;
        margin-bottom: 28px;
        padding-bottom: 24px;
        border-bottom: 1px dashed #dee2e6;
    }
    .batch-step:last-of-type { border-bottom: none; margin-bottom: 0; }
    .batch-step-num {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a73e8, #0b57d0);
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(11,87,208,0.3);
        margin-top: 2px;
    }
    .batch-step-body { flex: 1; }
    .batch-step-title {
        font-weight: 700;
        font-size: 1rem;
        color: #1e293b;
        margin-bottom: 12px;
    }

    
    .checkbox-grid { display: flex; flex-wrap: wrap; gap: 10px; }
    .cb-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border: 1.5px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.87rem;
        color: #374151;
        background: #fff;
        transition: all 0.15s;
        user-select: none;
        position: relative;
    }
    .cb-item:hover { border-color: #1a73e8; background: #eff6ff; }
    .cb-item input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: #1a73e8; }
    .cb-item.cb-checked { border-color: #1a73e8; background: #eff6ff; color: #1a73e8; }
    .cb-item.cb-disabled { opacity: 0.45; cursor: not-allowed; text-decoration: line-through; }
    .cb-item.cb-disabled:hover { border-color: #dee2e6; background: #fff; }

    
    .dot-green  { display:inline-block; width:9px; height:9px; border-radius:50%; background:#10b981; }
    .dot-yellow { display:inline-block; width:9px; height:9px; border-radius:50%; background:#f59e0b; }
    .dot-red    { display:inline-block; width:9px; height:9px; border-radius:50%; background:#ef4444; }
    .soal-dot   { width:9px; height:9px; border-radius:50%; display:inline-block; margin-left:4px; flex-shrink:0; }

    
    .hari-display {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1a73e8;
        padding: 10px 0;
    }

    
    #batchTimeBody td { vertical-align: middle; }
    .time-input { width: 100%; padding: 5px 8px; border: 1.5px solid #dee2e6; border-radius: 6px; font-size: 0.87rem; }
    .time-input:focus { border-color: #1a73e8; outline: none; }
    .selesai-preview { font-weight: 700; color: #1a73e8; font-size: 0.87rem; }

    
    .token-display {
        font-family: 'Courier New', monospace;
        font-size: 1.05rem;
        font-weight: bold;
        letter-spacing: 0.2rem;
        color: #0b57d0;
    }
    .status-berlangsung { animation: blink-badge 2s infinite; }
    @keyframes blink-badge { 0%,100%{opacity:1;} 50%{opacity:0.6;} }

    
    .soal-ok   { background:#d1fae5; color:#065f46; }
    .soal-warn { background:#fef3c7; color:#92400e; }
    .soal-none { background:#fee2e2; color:#991b1b; }
    .soal-badge { font-size:0.68rem; font-weight:700; padding:2px 7px; border-radius:99px; white-space:nowrap; }

    /* DataTables styles */
    .dt-buttons { margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 5px; }
    .dataTables_wrapper .dataTables_filter { float: right; text-align: right; }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da; border-radius: 20px; padding: 4px 12px; margin-left: 8px; outline: none;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
const API      = '{{ route("operator.api.asesmen") }}';
const CSRF     = '{{ csrf_token() }}';
const DAYS_ID  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const MONTHS   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];


let GLOBAL_JENIS    = 'ASTS';
let ALL_KELAS       = [];   
let ALL_MAPEL       = [];   
let SOAL_MAP        = {};   
let JADWAL_LIST     = [];   
let SELECTED_MAPEL  = new Set();
let SELECTED_KELAS  = new Set();




const CFG_KEY = 'ujian_config_v1';

function loadConfig() {
    const def = { jawaban:'TIDAK', ulang:'TIDAK', nilai:'YA', submit:'MANDIRI' };
    try {
        return Object.assign(def, JSON.parse(localStorage.getItem(CFG_KEY) || '{}'));
    } catch(e) { return def; }
}
function saveConfig() {
    const cfg = {
        jawaban: getToggle('tgJawaban'),
        ulang:   getToggle('tgUlang'),
        nilai:   getToggle('tgNilai'),
        submit:  getToggle('tgSubmit'),
    };
    localStorage.setItem(CFG_KEY, JSON.stringify(cfg));
    const badge = document.getElementById('configSavedBadge');
    badge.style.display = 'inline-block';
    setTimeout(() => badge.style.display = 'none', 2500);
}

function applyConfigUI(cfg) {
    setToggle('tgJawaban', cfg.jawaban || 'TIDAK');
    setToggle('tgUlang',   cfg.ulang   || 'TIDAK');
    setToggle('tgNilai',   cfg.nilai   || 'YA');
    setToggle('tgSubmit',  cfg.submit  || 'MANDIRI');
}

function getToggle(groupId) {
    const active = document.querySelector(`#${groupId} .tg-btn.tg-active`);
    return active ? active.dataset.val : '';
}
function setToggle(groupId, val) {
    document.querySelectorAll(`#${groupId} .tg-btn`).forEach(btn => {
        btn.classList.toggle('tg-active', btn.dataset.val === val);
    });
}




function toggleConfigPanel() {
    const body = document.getElementById('configBody');
    const icon = document.getElementById('configChevron');
    const isOpen = body.classList.contains('show');
    body.classList.toggle('show', !isOpen);
    icon.className = isOpen ? 'fas fa-chevron-right' : 'fas fa-chevron-down';
}




async function post(action, data = {}) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('_token', CSRF);
    for (const k in data) fd.append(k, data[k]);
    const r = await fetch(API, { method:'POST', body:fd, headers:{'X-CSRF-TOKEN': CSRF} });
    return r.json();
}




async function init() {
    
    applyConfigUI(loadConfig());

    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('bTanggal').value = today;
    onTanggalChange();

    
    const [configRes, listsRes] = await Promise.all([
        post('get_gate_status'),
        post('get_kelas_mapel_list')
    ]);

    if (configRes.success && configRes.data) {
        GLOBAL_JENIS = configRes.data.jenis_asesmen || 'ASTS';
        const label = GLOBAL_JENIS === 'ASTS' ? 'ASTS — Tengah Semester' : 'ASAS — Akhir Semester';
        document.getElementById('globalJenisBadge').textContent = label;
    }

    if (listsRes.success && listsRes.data) {
        ALL_KELAS = listsRes.data.kelas || [];
        ALL_MAPEL = listsRes.data.mapel || [];
        renderKelasGrid();
        await loadSoalAvailability();
        renderMapelGrid();
    }

    await loadJadwal();
}




function onTanggalChange() {
    const val = document.getElementById('bTanggal').value;
    if (!val) { document.getElementById('hariDisplay').textContent = '—'; return; }
    const d = new Date(val + 'T00:00:00');
    document.getElementById('hariDisplay').textContent = DAYS_ID[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear();
    reRenderBatchTable();
}




async function loadSoalAvailability() {
    
    const res = await post('get_guru_soal_status');
    SOAL_MAP = {};
    if (res.success && res.data) {
        res.data.forEach(r => {
            if (!r.mapel) return;
            const key = r.mapel + '|' + (r.kelas || '');
            const ex = SOAL_MAP[key] || { total:0, validated:0 };
            ex.total     += parseInt(r.total_soal)     || 0;
            ex.validated += parseInt(r.soal_validated) || 0;
            SOAL_MAP[key] = ex;
        });
    }
}

function getSoalStatus(mapelNama) {
    
    let total = 0, validated = 0;
    Object.keys(SOAL_MAP).forEach(k => {
        if (k.startsWith(mapelNama + '|')) {
            total     += SOAL_MAP[k].total;
            validated += SOAL_MAP[k].validated;
        }
    });
    if (total === 0) return 'none';
    if (validated >= total) return 'ok';
    return 'warn';
}

function getSoalStatusForPair(mapelNama, kelasNama) {
    const key = mapelNama + '|' + kelasNama;
    const d = SOAL_MAP[key];
    if (!d || d.total === 0) return 'none';
    if (d.validated >= d.total) return 'ok';
    return 'warn';
}




function renderKelasGrid() {
    const grid = document.getElementById('kelasGrid');
    grid.innerHTML = '';
    ALL_KELAS.forEach(k => {
        const label = document.createElement('label');
        label.className = 'cb-item';
        label.id = 'kelas-label-' + k.id;
        label.innerHTML = `<input type="checkbox" id="ckKelas${k.id}" value="${escHtml(k.nama_kelas)}"
                            onchange="onKelasChange()"> Kelas ${escHtml(k.nama_kelas)}`;
        grid.appendChild(label);
    });
}

function onKelasChange() {
    SELECTED_KELAS.clear();
    document.querySelectorAll('#kelasGrid input[type=checkbox]:checked').forEach(cb => {
        SELECTED_KELAS.add(cb.value);
    });
    document.querySelectorAll('#kelasGrid input[type=checkbox]').forEach(cb => {
        const label = cb.closest('label');
        label.classList.toggle('cb-checked', cb.checked);
    });
    reRenderBatchTable();
}

function toggleAllKelas() {
    const all = document.querySelectorAll('#kelasGrid input[type=checkbox]');
    const allChecked = [...all].every(c => c.checked);
    all.forEach(cb => { cb.checked = !allChecked; });
    onKelasChange();
}




function renderMapelGrid() {
    const grid = document.getElementById('mapelGrid');
    grid.innerHTML = '';
    const tanggal = document.getElementById('bTanggal').value;

    
    const scheduledMapel = new Set();
    if (tanggal) {
        JADWAL_LIST.forEach(j => {
            if ((j.tanggal_ujian || '').substring(0,10) === tanggal) {
                scheduledMapel.add(j.mapel);
            }
        });
    }

    ALL_MAPEL.forEach(m => {
        const status   = getSoalStatus(m.nama_mapel);
        const dotColor = status === 'ok' ? '#10b981' : status === 'warn' ? '#f59e0b' : '#ef4444';
        const dotTitle = status === 'ok' ? 'Soal lengkap & validated' : status === 'warn' ? 'Ada soal tapi belum semua validated' : 'Belum ada soal';

        const isScheduled = scheduledMapel.has(m.nama_mapel);

        const label = document.createElement('label');
        label.className = 'cb-item' + (isScheduled ? ' cb-disabled' : '');
        label.id = 'mapel-label-' + m.id;
        label.title = isScheduled ? 'Sudah ada jadwal di tanggal ini' : dotTitle;
        label.innerHTML = `
            <input type="checkbox" id="ckMapel${m.id}" value="${escHtml(m.nama_mapel)}"
                   ${isScheduled ? 'disabled' : ''} onchange="onMapelChange()">
            ${escHtml(m.nama_mapel)}
            <span class="soal-dot" style="background:${dotColor};" title="${dotTitle}"></span>
            ${isScheduled ? '<small style="font-size:0.68rem;opacity:0.6;margin-left:2px;">(sudah dijadwalkan)</small>' : ''}
        `;
        grid.appendChild(label);
    });
}

function onMapelChange() {
    SELECTED_MAPEL.clear();
    document.querySelectorAll('#mapelGrid input[type=checkbox]:checked').forEach(cb => {
        SELECTED_MAPEL.add(cb.value);
    });
    document.querySelectorAll('#mapelGrid input[type=checkbox]').forEach(cb => {
        const label = cb.closest('label');
        if (!label.classList.contains('cb-disabled')) {
            label.classList.toggle('cb-checked', cb.checked);
        }
    });
    reRenderBatchTable();
}

function toggleAllMapel() {
    const all = document.querySelectorAll('#mapelGrid input[type=checkbox]:not([disabled])');
    const allChecked = [...all].every(c => c.checked);
    all.forEach(cb => { cb.checked = !allChecked; });
    onMapelChange();
}




function reRenderBatchTable() {
    const wrap  = document.getElementById('step4Wrap');
    const btnW  = document.getElementById('btnSimpanWrap');
    const tbody = document.getElementById('batchTimeBody');
    const badge = document.getElementById('batchCountBadge');

    const mapels = [...SELECTED_MAPEL];
    const kelas  = [...SELECTED_KELAS];

    if (!mapels.length || !kelas.length) {
        wrap.style.display = 'none';
        btnW.style.display = 'none';
        return;
    }

    
    const rows = mapels.map(mapelNama => {
        
        const statusArr = kelas.map(k => getSoalStatusForPair(mapelNama, k));
        const hasOk     = statusArr.some(s => s === 'ok');
        const hasSome   = statusArr.some(s => s !== 'none');
        const soalClass = hasOk ? 'text-success' : hasSome ? 'text-warning' : 'text-danger';
        const soalIcon  = hasOk ? 'fa-check-circle' : hasSome ? 'fa-exclamation-circle' : 'fa-times-circle';
        const soalTip   = hasOk ? 'Soal validated' : hasSome ? 'Ada soal, belum semua validated' : 'Belum ada soal di kelas ini';

        const kelasChips = kelas.map(k => `<span class="badge badge-secondary">${escHtml(k)}</span>`).join(' ');

        const id = 'bt_' + mapelNama.replace(/\s+/g, '_');
        return `<tr>
            <td>
                <strong>${escHtml(mapelNama)}</strong>
                <i class="fas ${soalIcon} ${soalClass} ml-1" title="${soalTip}"></i>
            </td>
            <td>
                <input type="time" class="time-input" id="${id}_jam" value="08:00"
                       oninput="updateSelesai('${id}')">
            </td>
            <td>
                <input type="number" class="time-input" id="${id}_dur" value="90" min="1" max="360"
                       oninput="updateSelesai('${id}')">
            </td>
            <td>
                <span class="selesai-preview" id="${id}_selesai">—</span>
            </td>
            <td class="text-center">${kelasChips}</td>
        </tr>`;
    });

    tbody.innerHTML = rows.join('');
    wrap.style.display = 'flex';
    btnW.style.display = 'block';
    badge.textContent  = mapels.length * kelas.length;

    
    mapels.forEach(mapelNama => {
        const id = 'bt_' + mapelNama.replace(/\s+/g, '_');
        updateSelesai(id);
    });
}

function updateSelesai(id) {
    const jamEl = document.getElementById(id + '_jam');
    const durEl = document.getElementById(id + '_dur');
    const sel   = document.getElementById(id + '_selesai');
    if (!jamEl || !durEl || !sel) return;
    const jam = jamEl.value;
    const dur = parseInt(durEl.value) || 0;
    if (!jam || dur <= 0) { sel.textContent = '—'; return; }
    const [h, m] = jam.split(':').map(Number);
    const total = h * 60 + m + dur;
    const hh = String(Math.floor(total / 60) % 24).padStart(2,'0');
    const mm = String(total % 60).padStart(2,'0');
    sel.textContent = hh + ':' + mm;
}




async function simpanBatch() {
    const tanggal = document.getElementById('bTanggal').value;
    const mapels  = [...SELECTED_MAPEL];
    const kelas   = [...SELECTED_KELAS];
    const cfg     = loadConfig();

    if (!tanggal) { Swal.fire({ icon:'warning', title:'Pilih tanggal ujian terlebih dahulu.' }); return; }
    if (!mapels.length) { Swal.fire({ icon:'warning', title:'Pilih minimal satu mata pelajaran.' }); return; }
    if (!kelas.length)  { Swal.fire({ icon:'warning', title:'Pilih minimal satu kelas.' }); return; }

    
    const tasks = [];
    for (const mapelNama of mapels) {
        const id = 'bt_' + mapelNama.replace(/\s+/g, '_');
        const jamEl = document.getElementById(id + '_jam');
        const durEl = document.getElementById(id + '_dur');
        if (!jamEl || !durEl) continue;
        const jam = jamEl.value;
        const dur = parseInt(durEl.value) || 0;
        if (!jam || dur <= 0) {
            Swal.fire({ icon:'warning', title:`Isi jam mulai dan durasi untuk: ${mapelNama}` });
            return;
        }
        for (const kelasNama of kelas) {
            tasks.push({ mapel: mapelNama, kelas: kelasNama, tanggal, jam_mulai: jam, durasi: dur });
        }
    }

    
    const conf = await Swal.fire({
        title: 'Simpan ' + tasks.length + ' Jadwal?',
        html: `<div class="text-left small">
            <b>Tanggal:</b> ${tanggal}<br>
            <b>Kelas:</b> ${kelas.join(', ')}<br>
            <b>Mapel:</b> ${mapels.join(', ')}<br>
            <b>Total:</b> ${tasks.length} jadwal
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a73e8',
        confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Semua',
        cancelButtonText: 'Batal'
    });
    if (!conf.isConfirmed) return;

    
    let success = 0, failed = [];
    for (const t of tasks) {
        const res = await post('save_jadwal', {
            mapel:             t.mapel,
            kelas:             t.kelas,
            tanggal:           t.tanggal,
            jam_mulai:         t.jam_mulai,
            durasi:            t.durasi,
            jenis_asesmen:     GLOBAL_JENIS,
            tampilkan_jawaban: cfg.jawaban,
            pengulangan:       cfg.ulang,
            tampilkan_nilai:   cfg.nilai,
            mode_submit:       cfg.submit,
        });
        if (res.success) { success++; }
        else { failed.push(t.mapel + ' / Kelas ' + t.kelas + ': ' + res.message); }
    }

    if (failed.length === 0) {
        Swal.fire({
            icon: 'success',
            title: `${success} Jadwal Berhasil Disimpan!`,
            toast: true, position: 'top-end', timer: 3000, showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: `${success} berhasil, ${failed.length} gagal`,
            html: '<small>' + failed.join('<br>') + '</small>'
        });
    }

    
    await Promise.all([loadJadwal(), loadSoalAvailability()]);
    renderMapelGrid();
    
    mapels.forEach(m => {
        const cb = document.querySelector(`#mapelGrid input[value="${CSS.escape(m)}"]`);
        if (cb && !cb.disabled) cb.checked = false;
    });
    onMapelChange();
}




let dtJadwal = null;

async function loadJadwal() {
    if (dtJadwal) {
        dtJadwal.destroy();
        dtJadwal = null;
    }
    
    const tbody = document.getElementById('jadwalTableBody');
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i></td></tr>`;

    let res;
    try { res = await post('get_jadwal_list'); }
    catch(e) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
        return;
    }

    if (!res.success || !res.data || !res.data.length) {
        JADWAL_LIST = [];
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox mr-1"></i> Belum ada jadwal.</td></tr>`;
        return;
    }

    JADWAL_LIST = res.data;
    const now = new Date();

    tbody.innerHTML = JADWAL_LIST.map(r => {
        
        let jamSelesai = r.jam_selesai;
        if (!jamSelesai || jamSelesai === '00:00:00') {
            const [h, m] = r.jam_mulai.split(':').map(Number);
            const tot = h * 60 + m + parseInt(r.durasi || 0);
            jamSelesai = String(Math.floor(tot/60)%24).padStart(2,'0') + ':' + String(tot%60).padStart(2,'0');
        } else {
            jamSelesai = jamSelesai.substring(0,5);
        }

        const tgl     = (r.tanggal_ujian || '').substring(0,10);
        const mulaiDt = new Date(tgl + 'T' + r.jam_mulai);
        const [hs,ms] = jamSelesai.split(':').map(Number);
        const selDt   = new Date(tgl + 'T' + String(hs).padStart(2,'0') + ':' + String(ms).padStart(2,'0') + ':00');

        let status;
        if (!r.token)             status = 'belum_token';
        else if (now < mulaiDt)   status = 'akan_datang';
        else if (now <= selDt)    status = 'berlangsung';
        else                      status = 'selesai';

        const statusMap = {
            belum_token: { cls:'secondary', txt:'Belum Token' },
            akan_datang: { cls:'info',      txt:'Akan Datang' },
            berlangsung: { cls:'success',   txt:'Berlangsung' },
            selesai:     { cls:'dark',      txt:'Selesai' }
        };
        const s = statusMap[status];
        const statusCell = `<span class="badge badge-${s.cls}${status==='berlangsung'?' status-berlangsung':''}">${s.txt}</span>`;

        
        const soalKey = (r.mapel||'') + '|' + (r.kelas||'');
        const sd = SOAL_MAP[soalKey] || { total:0, validated:0 };
        let soalBadge;
        if (sd.total === 0) {
            soalBadge = `<span class="soal-badge soal-none">❌ Belum Ada</span>`;
        } else if (sd.validated >= sd.total) {
            soalBadge = `<span class="soal-badge soal-ok">✅ Siap (${sd.validated})</span>`;
        } else {
            soalBadge = `<span class="soal-badge soal-warn">⚠ ${sd.validated}/${sd.total}</span>`;
        }

        const tokenCell = r.token
            ? `<span class="token-display">${r.token}</span>`
            : `<span class="text-muted small">—</span>`;

        
        const soalOk = sd.total > 0 && sd.validated >= sd.total;
        let tokenBtn;
        if (r.token) {
            tokenBtn = `<button onclick="regenToken(${r.id_jadwal})" class="btn btn-xs btn-outline-secondary" title="Regenerate token"><i class="fas fa-redo"></i></button>`;
        } else if (soalOk) {
            tokenBtn = `<button onclick="generateToken(${r.id_jadwal})" class="btn btn-xs btn-primary"><i class="fas fa-key mr-1"></i>Token</button>`;
        } else {
            const tip = sd.total === 0 ? 'Soal belum ada' : 'Soal belum semua divalidasi';
            tokenBtn = `<button class="btn btn-xs btn-outline-secondary" disabled title="${tip}"><i class="fas fa-lock mr-1"></i>Token</button>`;
        }

        const portalUrl = '{{ route("operator.asesmen.portal") }}?id_jadwal=' + r.id_jadwal;
        const portalBtn = r.token
            ? `<a href="${portalUrl}" class="btn btn-xs btn-${status==='berlangsung'?'success':'outline-primary'} ml-1" title="Buka Portal Ujian"><i class="fas fa-door-open mr-1"></i>Portal</a>`
            : `<button class="btn btn-xs btn-outline-secondary ml-1" disabled><i class="fas fa-lock mr-1"></i>Portal</button>`;

        const tglEdit = (r.tanggal_ujian || '').substring(0, 10);
        const jamMulaiEdit = (r.jam_mulai || '').substring(0, 5);
        const durasiEdit = r.durasi || 90;
        const editBtn = `<button onclick="editConfig(${r.id_jadwal}, '${r.pengulangan || 'YA'}', '${r.tampilkan_jawaban || 'TIDAK'}', '${r.tampilkan_nilai || 'YA'}', '${r.mode_submit || 'MANDIRI'}', '${tglEdit}', '${jamMulaiEdit}', ${durasiEdit})" class="btn btn-xs btn-outline-info ml-1" title="Edit Pengaturan Ujian Ini"><i class="fas fa-cog"></i></button>`;

        const delBtn = `<button onclick="hapusJadwal(${r.id_jadwal})" class="btn btn-xs btn-outline-danger ml-1" title="Hapus"><i class="fas fa-trash"></i></button>`;

        const tglObj = new Date(tgl + 'T00:00:00');
        const tglStr = `<strong>${DAYS_ID[tglObj.getDay()]}</strong><br><small>${tglObj.getDate()} ${MONTHS[tglObj.getMonth()]} ${tglObj.getFullYear()}</small>`;

        return `<tr>
            <td>${tglStr}</td>
            <td><strong>${escHtml(r.mapel||'—')}</strong></td>
            <td><span class="badge badge-secondary">${escHtml(r.kelas||'—')}</span></td>
            <td><small class="text-muted">${r.jam_mulai.substring(0,5)} – ${jamSelesai}<br><span class="badge badge-light">${r.durasi} mnt</span></small></td>
            <td class="text-center">${soalBadge}</td>
            <td class="text-center">${tokenCell}</td>
            <td class="text-center">${statusCell}</td>
            <td class="text-center text-nowrap">${tokenBtn}${portalBtn}${editBtn}${delBtn}</td>
        </tr>`;
    }).join('');

    renderMapelGrid();

    dtJadwal = $('#jadwalTable').DataTable({
        "responsive": true, "paging": true, "info": true, "lengthChange": true,
        "autoWidth": false, "searching": true, "ordering": false,
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                className: 'btn btn-success btn-sm px-3 mr-2 rounded-pill shadow-sm',
                title: 'Jadwal Ujian Asesmen',
                exportOptions: { columns: [ 0, 1, 2, 3, 6 ] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                className: 'btn btn-danger btn-sm px-3 mr-2 rounded-pill shadow-sm',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Jadwal Ujian Asesmen',
                exportOptions: { columns: [ 0, 1, 2, 3, 6 ] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Cetak',
                className: 'btn btn-info btn-sm px-3 rounded-pill shadow-sm',
                title: 'Jadwal Ujian Asesmen',
                exportOptions: { columns: [ 0, 1, 2, 3, 6 ] }
            }
        ],
        "language": { "search": "Cari:" }
    });
    dtJadwal.buttons().container().insertBefore('#jadwalTable_wrapper .row:eq(0)');
}




async function generateToken(id) {
    const res = await post('generate_token_for_jadwal', { id_jadwal: id });
    if (res.success) {
        Swal.fire({ icon:'success', title:'Token: ' + res.data.token, toast:true, position:'top-end', timer:3000, showConfirmButton:false });
        loadJadwal();
    } else {
        Swal.fire({ icon:'error', title:'Gagal', text:res.message });
    }
}

async function regenToken(id) {
    const conf = await Swal.fire({
        title: 'Reset Token?', text:'Token lama tidak berlaku. Siswa harus pakai token baru.',
        icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Reset', cancelButtonText:'Batal'
    });
    if (!conf.isConfirmed) return;
    await generateToken(id);
}

async function resetAllTokens() {
    const conf = await Swal.fire({
        title:'Reset SEMUA Token Hari Ini?', text:'Semua token jadwal hari ini akan dibuat ulang.',
        icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Reset Semua', cancelButtonText:'Batal'
    });
    if (!conf.isConfirmed) return;
    const res = await post('reset_all_tokens');
    if (res.success) {
        Swal.fire({ icon:'success', title:res.message, toast:true, position:'top-end', timer:2500, showConfirmButton:false });
        loadJadwal();
    } else {
        Swal.fire({ icon:'error', title:'Gagal', text:res.message });
    }
}




async function hapusJadwal(id) {
    const safeConf = await Swal.fire({
        title: 'Hapus Jadwal?',
        html: `<div class="text-left"><p>Pilih mode penghapusan:</p>
               <ul>
                 <li><strong>Hapus Jadwal Saja</strong> — hanya bisa jika belum ada jawaban siswa</li>
                 <li><strong>Hapus Paksa</strong> — hapus jadwal DAN semua jawaban siswa (permanen)</li>
               </ul></div>`,
        icon: 'warning', showCancelButton:true, showDenyButton:true,
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Hapus Jadwal Saja',
        denyButtonText:    '<i class="fas fa-bolt mr-1"></i> Hapus Paksa (+ Jawaban)',
        denyButtonColor:   '#dc3545', cancelButtonText:'Batal'
    });
    if (!safeConf.isConfirmed && !safeConf.isDenied) return;

    if (safeConf.isConfirmed) {
        const res = await post('delete_jadwal', { id_jadwal: id });
        if (res.success) {
            Swal.fire({ icon:'success', title:'Jadwal dihapus', toast:true, position:'top-end', timer:1500, showConfirmButton:false });
            loadJadwal();
        } else {
            Swal.fire({ icon:'error', title:'Gagal Hapus', text:res.message + ' Gunakan Hapus Paksa jika ada jawaban siswa.' });
        }
        return;
    }

    const forceConf = await Swal.fire({
        title: '⚠ Hapus Paksa?',
        html: `Jadwal dan SEMUA jawaban siswa dihapus permanen!<br><small class="text-danger">Tidak bisa dibatalkan.</small>`,
        icon:'error', input:'text', inputPlaceholder:'Ketik HAPUS untuk konfirmasi',
        showCancelButton:true, confirmButtonText:'Hapus Paksa', confirmButtonColor:'#dc3545', cancelButtonText:'Batal',
        preConfirm: (v) => { if (v !== 'HAPUS') { Swal.showValidationMessage('Ketik HAPUS (huruf kapital)'); return false; } return true; }
    });
    if (!forceConf.isConfirmed) return;

    const res = await post('delete_jadwal_force', { id_jadwal: id });
    if (res.success) {
        Swal.fire({ icon:'success', title:res.message, toast:true, position:'top-end', timer:3000, showConfirmButton:false });
        loadJadwal();
    } else {
        Swal.fire({ icon:'error', title:'Gagal', text:res.message });
    }
}




async function editConfig(id, pengulangan, tampilkan_jawaban, tampilkan_nilai, mode_submit, tgl, jam_mulai, durasi) {
    const html = `
        <div class="text-left small" style="margin-top: 10px;">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="text-muted mb-1 font-weight-bold">Tanggal Ujian</label>
                    <input type="date" id="swal-tgl" class="form-control" value="${tgl}">
                </div>
                <div class="col-6 mb-3">
                    <label class="text-muted mb-1 font-weight-bold">Jam Mulai</label>
                    <input type="time" id="swal-jam" class="form-control" value="${jam_mulai}">
                </div>
                <div class="col-6 mb-3">
                    <label class="text-muted mb-1 font-weight-bold">Durasi (Mnt)</label>
                    <input type="number" id="swal-durasi" class="form-control" value="${durasi}" min="1">
                </div>
            </div>
            <hr class="mt-0 mb-3">
            <div class="form-group mb-3">
                <label class="text-muted mb-1 font-weight-bold">Tampilkan Jawaban?</label>
                <select id="swal-jawaban" class="form-control">
                    <option value="TIDAK" ${tampilkan_jawaban==='TIDAK'?'selected':''}>TIDAK</option>
                    <option value="YA" ${tampilkan_jawaban==='YA'?'selected':''}>YA</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="text-muted mb-1 font-weight-bold">Izin Ulang?</label>
                <select id="swal-ulang" class="form-control">
                    <option value="TIDAK" ${pengulangan==='TIDAK'?'selected':''}>TIDAK</option>
                    <option value="YA" ${pengulangan==='YA'?'selected':''}>YA</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="text-muted mb-1 font-weight-bold">Tampilkan Nilai?</label>
                <select id="swal-nilai" class="form-control">
                    <option value="YA" ${tampilkan_nilai==='YA'?'selected':''}>YA — Tampilkan ke Siswa</option>
                    <option value="TIDAK" ${tampilkan_nilai==='TIDAK'?'selected':''}>TIDAK — Sembunyikan</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="text-muted mb-1 font-weight-bold">Mode Submit</label>
                <select id="swal-submit" class="form-control">
                    <option value="MANDIRI" ${mode_submit==='MANDIRI'?'selected':''}>MANDIRI — Siswa Klik Selesai</option>
                    <option value="SERENTAK" ${mode_submit==='SERENTAK'?'selected':''}>SERENTAK — Auto Waktu Habis</option>
                </select>
            </div>
        </div>
    `;

    const { value: formValues } = await Swal.fire({
        title: 'Edit Pengaturan Ujian',
        html: html,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Perubahan',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            return {
                tampilkan_jawaban: document.getElementById('swal-jawaban').value,
                pengulangan: document.getElementById('swal-ulang').value,
                tampilkan_nilai: document.getElementById('swal-nilai').value,
                mode_submit: document.getElementById('swal-submit').value,
                tanggal_ujian: document.getElementById('swal-tgl').value,
                jam_mulai: document.getElementById('swal-jam').value,
                durasi: document.getElementById('swal-durasi').value
            }
        }
    });

    if (formValues) {
        const res = await post('update_jadwal_config', {
            id_jadwal: id,
            pengulangan: formValues.pengulangan,
            tampilkan_jawaban: formValues.tampilkan_jawaban,
            tampilkan_nilai: formValues.tampilkan_nilai,
            mode_submit: formValues.mode_submit,
            tanggal_ujian: formValues.tanggal_ujian,
            jam_mulai: formValues.jam_mulai,
            durasi: formValues.durasi
        });

        if (res.success) {
            Swal.fire({ icon:'success', title:'Tersimpan', text:res.message, toast:true, position:'top-end', timer:3000, showConfirmButton:false });
            loadJadwal();
        } else {
            Swal.fire({ icon:'error', title:'Gagal', text:res.message });
        }
    }
}




function escHtml(str) {
    return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

window.onload = init;
</script>
@endpush