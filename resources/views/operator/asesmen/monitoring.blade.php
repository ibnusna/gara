@extends('layouts.operator', ['page_title' => 'Monitor Soal Guru | Garuda Akademi', 'active_menu' => 'ujian', 'active_submenu' => 'asesmen_dashboard'])

@section('title', 'Monitor Soal Guru | Garuda Akademi')

@section('content')
<div class="content-wrapper">

    
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-chart-bar mr-2 text-warning"></i>
                        Monitor Soal Guru
                        <small class="badge badge-warning ml-2" style="font-size:0.6em;vertical-align:middle;">Live</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('operator.asesmen.dashboard') }}">Ruang Asesmen</a></li>
                        <li class="breadcrumb-item active">Monitor Soal</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    
    <section class="content">
        <div class="container-fluid">

            
            <div class="row" id="summaryCards">
                <div class="col-lg col-md-4 col-6">
                    <div class="mon-stat-card mon-card-blue">
                        <div class="mon-stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div class="mon-stat-body">
                            <div class="mon-stat-label">Total Paket Soal</div>
                            <div class="mon-stat-value" id="sumTotalGuru">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-6">
                    <div class="mon-stat-card mon-card-green">
                        <div class="mon-stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="mon-stat-body">
                            <div class="mon-stat-label">Sudah Lengkap</div>
                            <div class="mon-stat-value" id="sumLengkap">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-6">
                    <div class="mon-stat-card mon-card-yellow">
                        <div class="mon-stat-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="mon-stat-body">
                            <div class="mon-stat-label">Perlu Validasi</div>
                            <div class="mon-stat-value" id="sumPerluValidasi">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-6">
                    <div class="mon-stat-card mon-card-red">
                        <div class="mon-stat-icon"><i class="fas fa-inbox"></i></div>
                        <div class="mon-stat-body">
                            <div class="mon-stat-label">Belum Input</div>
                            <div class="mon-stat-value" id="sumBelumInput">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-6">
                    <div class="mon-stat-card mon-card-teal">
                        <div class="mon-stat-icon"><i class="fas fa-upload"></i></div>
                        <div class="mon-stat-body">
                            <div class="mon-stat-label">Sudah Submit</div>
                            <div class="mon-stat-value" id="sumSudahSubmit">—</div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card card-outline card-warning mb-3">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1">Filter Kelas</label>
                            <select id="filterKelas" class="form-control form-control-sm" onchange="applyFilters()">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1">Filter Status</label>
                            <select id="filterStatus" class="form-control form-control-sm" onchange="applyFilters()">
                                <option value="">Semua Status</option>
                                <option value="LENGKAP">✅ Sudah Lengkap</option>
                                <option value="PERLU_VALIDASI">⚠️ Perlu Validasi</option>
                                <option value="BELUM_INPUT">❌ Belum Input</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1">Cari Guru / Mapel</label>
                            <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Ketik nama guru atau mapel..." oninput="applyFilters()">
                        </div>
                        <div class="col-lg-3 col-md-12 d-flex align-items-end gap-2" style="gap:8px;">
                            <button class="btn btn-warning btn-sm" onclick="loadMonitoring()" title="Refresh data">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                            <button id="btnAutoRefresh" class="btn btn-outline-secondary btn-sm" onclick="toggleAutoRefresh()" title="Auto-refresh setiap 30 detik">
                                <i class="fas fa-clock mr-1"></i> Auto-Refresh: <strong id="arLabel">OFF</strong>
                            </button>
                            <a href="{{ route('operator.asesmen.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Data terakhir dimuat: <span id="lastRefreshTime">—</span>
                    &nbsp;|&nbsp; Menampilkan <strong id="rowCountDisplay">0</strong> dari <strong id="rowCountTotal">0</strong> entri
                </small>
                <div id="refreshSpinner" style="display:none;">
                    <i class="fas fa-circle-notch fa-spin text-warning mr-1"></i>
                    <small class="text-muted">Memuat...</small>
                </div>
            </div>

            
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-table mr-2"></i> Status Pengumpulan & Validasi Soal
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning" id="badgeLiveCount">0 Entri</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="monitorTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width:26px;" class="text-center">#</th>
                                    <th>Guru</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th class="text-center" style="width:70px;">Siswa</th>
                                    <th class="text-center" style="width:80px;">Total Soal</th>
                                    <th class="text-center" style="width:100px;">Progress</th>
                                    <th class="text-center" style="width:90px;">Validated</th>
                                    <th class="text-center" style="width:90px;">Draft/Submit</th>
                                    <th class="text-center" style="width:110px;">Status</th>
                                    <th class="text-center" style="width:55px;">Update</th>
                                    <th class="text-center" style="width:145px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="monitorBody">
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Memuat data monitoring...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted small text-center" id="tableFooter">
                    Klik <strong>QC</strong> untuk preview soal, <strong>Validasi</strong> untuk approve paket soal.
                </div>
            </div>

        </div>
    </section>
</div>


<style>
    
    .mon-stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-radius: 12px;
        margin-bottom: 16px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .mon-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.12); }

    .mon-stat-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .mon-stat-label  { font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; }
    .mon-stat-value  { font-size: 2rem; font-weight: 800; line-height: 1.1; }

    .mon-card-blue   { background: linear-gradient(135deg, #ebf4ff, #dbeafe); color: #1d4ed8; }
    .mon-card-blue   .mon-stat-icon { background: #2563eb; color: #fff; }
    .mon-card-green  { background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #065f46; }
    .mon-card-green  .mon-stat-icon { background: #10b981; color: #fff; }
    .mon-card-yellow { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #92400e; }
    .mon-card-yellow .mon-stat-icon { background: #f59e0b; color: #fff; }
    .mon-card-red    { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #991b1b; }
    .mon-card-red    .mon-stat-icon { background: #ef4444; color: #fff; }
    .mon-card-teal   { background: linear-gradient(135deg, #f0fdfa, #ccfbf1); color: #134e4a; }
    .mon-card-teal   .mon-stat-icon { background: #14b8a6; color: #fff; }

    
    .badge-lengkap   { background: #d1fae5; color: #065f46; font-weight: 700; }
    .badge-validasi  { background: #fef3c7; color: #92400e; font-weight: 700; }
    .badge-belum     { background: #fee2e2; color: #991b1b; font-weight: 700; }

    
    .mon-progress-wrap { background: #e5e7eb; border-radius: 99px; height: 8px; min-width: 60px; }
    .mon-progress-bar  { height: 8px; border-radius: 99px; transition: width 0.5s; }
    .mon-progress-pct  { font-size: 0.7rem; font-weight: 700; text-align: right; margin-top: 2px; }

    
    #monitorTable th { white-space: nowrap; font-size: 0.78rem; padding: 10px 8px; }
    #monitorTable td { font-size: 0.85rem; vertical-align: middle; padding: 8px 8px; }

    
    .btn-xs { padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }

    
    tr.row-lengkap   td:first-child { border-left: 3px solid #10b981; }
    tr.row-validasi  td:first-child { border-left: 3px solid #f59e0b; }
    tr.row-belum     td:first-child { border-left: 3px solid #ef4444; }

    
    .guru-avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6d28d9, #2563eb);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: 8px;
        flex-shrink: 0;
        vertical-align: middle;
    }

    
    .mon-empty { text-align: center; padding: 60px 20px; color: #6b7280; }
    .mon-empty i { font-size: 3rem; margin-bottom: 12px; display: block; opacity: 0.4; }

    
    @keyframes ar-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
        50%       { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
    }
    .ar-active { animation: ar-pulse 2s infinite; border-color: #f59e0b !important; }
</style>
@endsection

@push('scripts')
<script>
const API   = '{{ route("operator.api.asesmen") }}';
const CSRF  = '{{ csrf_token() }}';
const QC_URL = '{{ route("operator.asesmen.qc_soal") }}';

let ALL_DATA       = [];
let arInterval     = null;
let ARActive       = false;
const AR_SECONDS   = 30;




async function post(action, data = {}) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('_token', CSRF);
    for (const k in data) fd.append(k, data[k]);
    const r = await fetch(API, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF } });
    return r.json();
}




async function loadMonitoring() {
    document.getElementById('refreshSpinner').style.display = 'flex';
    document.getElementById('monitorBody').innerHTML =
        `<tr><td colspan="12" class="text-center py-4 text-muted">
            <i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat data...
        </td></tr>`;

    const res = await post('get_monitoring_soal');
    document.getElementById('refreshSpinner').style.display = 'none';

    if (!res.success) {
        document.getElementById('monitorBody').innerHTML =
            `<tr><td colspan="12" class="text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat: ${res.message}
            </td></tr>`;
        return;
    }

    ALL_DATA = res.data.rows || [];
    updateSummaryCards(res.data.summary || {});
    populateKelasFilter(ALL_DATA);
    applyFilters();

    
    const now = new Date();
    document.getElementById('lastRefreshTime').textContent =
        now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}




function updateSummaryCards(s) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('sumTotalGuru',     s.total_guru      ?? 0);
    set('sumLengkap',       s.lengkap         ?? 0);
    set('sumPerluValidasi', s.perlu_validasi  ?? 0);
    set('sumBelumInput',    s.belum_input     ?? 0);
    set('sumSudahSubmit',   s.sudah_submit    ?? 0);
}




function populateKelasFilter(data) {
    const sel = document.getElementById('filterKelas');
    const current = sel.value;
    const kelasList = [...new Set(data.map(r => r.kelas).filter(Boolean))].sort();
    sel.innerHTML = '<option value="">Semua Kelas</option>';
    kelasList.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k;
        opt.textContent = k;
        sel.appendChild(opt);
    });
    if (current) sel.value = current;
}




function applyFilters() {
    const kelas  = document.getElementById('filterKelas').value.trim().toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('filterSearch').value.trim().toLowerCase();

    const filtered = ALL_DATA.filter(r => {
        if (kelas  && (r.kelas  || '').toLowerCase() !== kelas)  return false;
        if (status && r.status_kategori !== status)               return false;
        if (search && !(
            (r.guru_nama || '').toLowerCase().includes(search) ||
            (r.mapel     || '').toLowerCase().includes(search)
        )) return false;
        return true;
    });

    document.getElementById('rowCountDisplay').textContent = filtered.length;
    document.getElementById('rowCountTotal').textContent   = ALL_DATA.length;
    document.getElementById('badgeLiveCount').textContent  = filtered.length + ' Entri';

    renderTable(filtered);
}




function renderTable(data) {
    const tbody = document.getElementById('monitorBody');

    if (!data.length) {
        tbody.innerHTML = `
            <tr><td colspan="12">
                <div class="mon-empty">
                    <i class="fas fa-search"></i>
                    <p class="font-weight-bold">Tidak ada data yang sesuai filter.</p>
                    <small>Coba ubah filter kelas, status, atau kata kunci.</small>
                </div>
            </td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((r, idx) => {
        const total     = parseInt(r.total_soal)      || 0;
        const validated = parseInt(r.soal_validated)  || 0;
        const draft     = parseInt(r.soal_draft)      || 0;
        const submitted = parseInt(r.soal_submitted)  || 0;
        const perluVal  = draft + submitted;
        const pct       = parseInt(r.pct_validated)   || 0;
        const siswa     = parseInt(r.jumlah_siswa)    || 0;
        const guruId    = parseInt(r.guru_user_id)    || 0;
        const mapel     = r.mapel  || '—';
        const kelas     = r.kelas  || '—';
        const guruNama  = r.guru_nama || 'Unknown';
        const kategori  = r.status_kategori || 'BELUM_INPUT';

        
        const statusMap = {
            'LENGKAP':       `<span class="badge badge-lengkap px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Lengkap</span>`,
            'PERLU_VALIDASI':`<span class="badge badge-validasi px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i>Perlu Validasi</span>`,
            'BELUM_INPUT':   `<span class="badge badge-belum px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Belum Input</span>`,
        };
        const statusBadge = statusMap[kategori] || statusMap['BELUM_INPUT'];

        
        const rowClass = { 'LENGKAP': 'row-lengkap', 'PERLU_VALIDASI': 'row-validasi', 'BELUM_INPUT': 'row-belum' }[kategori] || '';

        
        const barColor = kategori === 'LENGKAP' ? '#10b981' : kategori === 'PERLU_VALIDASI' ? '#f59e0b' : '#e5e7eb';

        
        const initials = guruNama.split(' ').slice(0,2).map(w => w[0]?.toUpperCase() || '').join('');

        
        const lastUpd = r.last_updated ? new Date(r.last_updated).toLocaleDateString('id-ID', {day:'2-digit',month:'short'}) : '—';

        
        const qcBtn = total > 0
            ? `<a href="${QC_URL}?guru_id=${guruId}&mapel=${encodeURIComponent(mapel)}&kelas=${encodeURIComponent(kelas)}&guru_nama=${encodeURIComponent(guruNama)}"
                  target="_blank" class="btn btn-xs btn-outline-info" title="QC Preview Soal">
                   <i class="fas fa-search"></i> QC
               </a>`
            : `<span class="btn btn-xs btn-outline-secondary disabled" title="Belum ada soal"><i class="fas fa-search"></i></span>`;

        const valBtn = (kategori === 'PERLU_VALIDASI')
            ? `<button onclick="doValidasi('${escJs(guruNama)}','${escJs(mapel)}','${escJs(kelas)}',${guruId})"
                       class="btn btn-xs btn-outline-success" title="Validasi semua soal paket ini">
                   <i class="fas fa-check"></i> Validasi
               </button>`
            : kategori === 'LENGKAP'
                ? `<span class="text-success small"><i class="fas fa-check-circle"></i></span>`
                : `<span class="text-muted small">—</span>`;

        const hapusBtn = total > 0
            ? `<button onclick="doHapus('${escJs(mapel)}','${escJs(kelas)}',${guruId},'${escJs(guruNama)}')"
                       class="btn btn-xs btn-outline-danger" title="Hapus paket soal">
                   <i class="fas fa-trash"></i>
               </button>`
            : `<span class="text-muted">—</span>`;

        return `<tr class="${rowClass}" data-kategori="${kategori}" data-kelas="${kelas}" data-guru="${guruNama.toLowerCase()}" data-mapel="${mapel.toLowerCase()}">
            <td class="text-center text-muted font-weight-bold">${idx + 1}</td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="guru-avatar">${initials || '?'}</span>
                    <span class="font-weight-bold">${escHtml(guruNama)}</span>
                </div>
            </td>
            <td><strong>${escHtml(mapel)}</strong></td>
            <td><span class="badge badge-secondary px-2">${escHtml(kelas)}</span></td>
            <td class="text-center">${siswa > 0 ? `<span class="badge badge-info">${siswa}</span>` : '<span class="text-muted">—</span>'}</td>
            <td class="text-center font-weight-bold">${total || '<span class="text-muted">0</span>'}</td>
            <td class="text-center">
                ${total > 0 ? `
                <div class="mon-progress-wrap"><div class="mon-progress-bar" style="width:${pct}%;background:${barColor};"></div></div>
                <div class="mon-progress-pct" style="color:${barColor};">${pct}%</div>
                ` : '<span class="text-muted small">—</span>'}
            </td>
            <td class="text-center"><span class="text-success font-weight-bold">${validated || '—'}</span></td>
            <td class="text-center"><span class="${perluVal > 0 ? 'text-warning font-weight-bold' : 'text-muted'}">${perluVal > 0 ? perluVal : '—'}</span></td>
            <td class="text-center">${statusBadge}</td>
            <td class="text-center"><small class="text-muted">${lastUpd}</small></td>
            <td class="text-center text-nowrap" style="gap:4px;">
                ${qcBtn}
                ${valBtn}
                ${hapusBtn}
            </td>
        </tr>`;
    }).join('');
}

// ─────────────────────────────────────────
// AKSI: VALIDASI
// ─────────────────────────────────────────
async function doValidasi(guruNama, mapel, kelas, guruId) {
    const conf = await Swal.fire({
        title: 'Validasi Paket Soal?',
        html: `Guru <strong>${escHtml(guruNama)}</strong><br>
               Mapel <strong>${escHtml(mapel)}</strong> — Kelas <strong>${escHtml(kelas)}</strong><br>
               <small class="text-muted">Semua soal DRAFT/SUBMITTED akan diubah ke VALIDATED.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Validasi',
        cancelButtonText: 'Batal'
    });
    if (!conf.isConfirmed) return;

    const res = await post('validate_paket_soal', { guru_id: guruId, mapel, kelas });
    if (res.success) {
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: res.message,
            toast: true, position: 'top-end', timer: 2500, showConfirmButton: false
        });
        loadMonitoring();
    } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
    }
}

// ─────────────────────────────────────────
// AKSI: HAPUS PAKET SOAL
// ─────────────────────────────────────────
async function doHapus(mapel, kelas, guruId, guruNama) {
    const conf = await Swal.fire({
        title: 'Hapus Paket Soal?',
        html: `Semua soal <strong>${escHtml(mapel)}</strong> — Kelas <strong>${escHtml(kelas)}</strong><br>
               oleh guru <strong>${escHtml(guruNama)}</strong> akan dihapus permanen!<br>
               <small class="text-danger">⚠ Tindakan ini tidak bisa dibatalkan.</small>`,
        icon: 'error',
        input: 'text',
        inputPlaceholder: 'Ketik HAPUS untuk konfirmasi',
        showCancelButton: true,
        confirmButtonText: 'Hapus Paket Soal',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Batal',
        preConfirm: (input) => {
            if (input !== 'HAPUS') {
                Swal.showValidationMessage('Ketik HAPUS (huruf kapital) untuk konfirmasi');
                return false;
            }
            return true;
        }
    });
    if (!conf.isConfirmed) return;

    const res = await post('delete_paket_soal', { mapel, kelas, id_guru: guruId });
    if (res.success) {
        Swal.fire({
            icon: 'success', title: res.message,
            toast: true, position: 'top-end', timer: 2500, showConfirmButton: false
        });
        loadMonitoring();
    } else {
        Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: res.message });
    }
}

// ─────────────────────────────────────────
// AUTO REFRESH
// ─────────────────────────────────────────
function toggleAutoRefresh() {
    ARActive = !ARActive;
    const btn = document.getElementById('btnAutoRefresh');
    document.getElementById('arLabel').textContent = ARActive ? 'ON' : 'OFF';
    if (ARActive) {
        btn.classList.add('ar-active');
        btn.classList.replace('btn-outline-secondary', 'btn-outline-warning');
        arInterval = setInterval(loadMonitoring, AR_SECONDS * 1000);
    } else {
        btn.classList.remove('ar-active');
        btn.classList.replace('btn-outline-warning', 'btn-outline-secondary');
        clearInterval(arInterval);
    }
}

// ─────────────────────────────────────────
// UTILS
// ─────────────────────────────────────────
function escHtml(str) {
    return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(str) {
    return (str || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

// ─────────────────────────────────────────
// INIT
// ─────────────────────────────────────────
window.onload = loadMonitoring;
</script>
@endpush
