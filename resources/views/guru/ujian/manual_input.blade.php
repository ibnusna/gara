@extends('layouts.guru')

@section('title', 'Manual Input Soal | Garuda Akademi')

@php
    $active_menu = 'ujian';
@endphp

@push('styles')
<style>



:root {
    --primary: #0b57d0;
    --primary-light: #eff6ff;
    --primary-glow: rgba(11,87,208,0.15);
    --success: #10b981;
    --warn: #f59e0b;
    --border: #e2e8f0;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --radius-lg: 20px;
    --radius-md: 14px;
}


.wizard-steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 36px;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    position: relative;
}
.wizard-step::after {
    content: '';
    flex: 1;
    height: 2px;
    background: var(--border);
    transition: background 0.4s;
}
.wizard-step:last-child::after { display: none; }
.wizard-step.done::after { background: var(--primary); }
.step-bubble {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.3s;
    z-index: 1;
}
.wizard-step.active .step-bubble {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 4px 12px var(--primary-glow);
}
.wizard-step.done .step-bubble {
    background: var(--success);
    border-color: var(--success);
    color: #fff;
}
.step-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}
.wizard-step.active .step-label { color: var(--primary); font-weight: 700; }
.wizard-step.done  .step-label  { color: var(--success); }


.wi-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 36px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}


.config-section { margin-bottom: 32px; }
.config-section h6 {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-bottom: 16px;
}
.jumlah-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
}
.jumlah-box {
    background: var(--primary-light);
    border: 1.5px solid #bfdbfe;
    border-radius: var(--radius-md);
    padding: 16px;
    transition: all 0.2s;
}
.jumlah-box label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 8px;
}
.jumlah-box input {
    width: 100%;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 8px 10px;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-main);
    outline: none;
    background: #fff;
    transition: all 0.2s;
}
.jumlah-box input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-glow);
}


.opsi-toggle {
    display: flex;
    gap: 12px;
}
.opsi-toggle label {
    flex: 1;
    padding: 12px;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    text-align: center;
    cursor: pointer;
    font-weight: 700;
    color: var(--text-muted);
    transition: all 0.2s;
}
.opsi-toggle input[type=radio] { display: none; }
.opsi-toggle input[type=radio]:checked + label {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 4px 12px var(--primary-glow);
}


.breakdown-counter {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 12px 16px;
}
.bc-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.bc-label { flex:1; font-weight:700; font-size:0.9rem; color:var(--text-main); }
.bc-sub   { font-size:0.75rem; color:var(--text-muted); font-weight:400; }
.bc-ctrl  { display:flex; align-items:center; gap:8px; }
.bc-btn   { width:32px; height:32px; border-radius:50%; border:1.5px solid var(--border); background:#fff; cursor:pointer; font-size:1.1rem; font-weight:700; line-height:1; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all 0.2s; }
.bc-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
.bc-val   { min-width:28px; text-align:center; font-weight:800; font-size:1rem; color:var(--text-main); }
.total-badge {
    background: var(--primary-light);
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: var(--radius-md);
    padding: 14px 20px;
    font-weight: 800;
    font-size: 1rem;
    text-align: center;
}
.total-badge.over { background:#fef2f2; border-color:#f87171; color:#dc2626; }
.total-badge.match { background:#ecfdf5; border-color:#34d399; color:#059669; }


.btn-primary-full {
    width: 100%;
    padding: 16px;
    border-radius: 14px;
    background: linear-gradient(135deg,#0b57d0,#2563eb);
    color: #fff;
    font-size: 1.05rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 8px 20px -4px rgba(11,87,208,0.3);
    letter-spacing: 0.3px;
}
.btn-primary-full:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -4px rgba(11,87,208,0.4); }
.btn-primary-full:disabled { opacity:0.5; cursor:not-allowed; transform:none; box-shadow:none; }


#step2 { display:none; }
.soal-wrapper {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 24px;
}
@media(max-width:900px) { .soal-wrapper { grid-template-columns: 1fr; } }


.soal-nav-panel {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
    position: sticky;
    top: 80px;
    height: fit-content;
}
.snp-title {
    font-size:0.75rem; font-weight:700; text-transform:uppercase;
    letter-spacing:1.2px; color:var(--text-muted); margin-bottom:16px;
}
.soal-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 20px;
}
.soal-pill {
    aspect-ratio: 1;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.18s;
    color: var(--text-muted);
}
.soal-pill:hover { border-color:var(--primary); color:var(--primary); }
.soal-pill.active { background:var(--primary); border-color:var(--primary); color:#fff; box-shadow:0 4px 10px var(--primary-glow); transform:scale(1.05); }
.soal-pill.filled { background:#10b981; border-color:#10b981; color:#fff; }


.soal-editor {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.se-header {
    background: linear-gradient(135deg, #0b57d0, #2563eb);
    padding: 20px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
}
.se-header .q-num {
    font-size: 1.1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}
.tipe-badge-pill {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
}
.se-body { padding: 28px; }
.field-group { margin-bottom: 24px; }
.field-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.field-input {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 0.95rem;
    color: var(--text-main);
    outline: none;
    resize: vertical;
    transition: all 0.2s;
    font-family: inherit;
}
.field-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }

.options-grid { display: grid; gap: 10px; }
.option-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.opt-letter {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: var(--text-muted);
    flex-shrink: 0;
    font-size: 0.95rem;
}
.option-row input[type=text] {
    flex: 1;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.95rem;
    outline: none;
    transition: border 0.2s;
}
.option-row input[type=text]:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
.kunci-select {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main);
    outline: none;
    transition: all 0.2s;
    background: #fff;
}
.kunci-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }

.se-footer {
    padding: 20px 28px;
    border-top: 1px solid var(--border);
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.btn-nav-soal {
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 700;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.btn-nav-soal:hover { border-color:var(--primary); color:var(--primary); }
.btn-save-soal {
    padding: 10px 28px;
    border-radius: 10px;
    font-weight: 800;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
    box-shadow: 0 6px 14px rgba(16,185,129,0.25);
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-save-soal:hover { transform:translateY(-2px); box-shadow:0 10px 20px rgba(16,185,129,0.35); }

.progress-bar-custom {
    height: 6px;
    background: var(--border);
    border-radius: 99px;
    margin-bottom: 16px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0b57d0, #10b981);
    border-radius: 99px;
    transition: width 0.4s;
}

.btn-lanjut-qc {
    width: 100%;
    padding: 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 8px 20px rgba(16,185,129,0.3);
    display: none;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 16px;
}
.btn-lanjut-qc:hover { transform:translateY(-2px); box-shadow:0 12px 24px rgba(16,185,129,0.4); }
.btn-lanjut-qc.visible { display: flex; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('guru.ujian') }}" class="btn btn-sm btn-light border mr-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="m-0"><i class="fas fa-pencil-alt mr-2" style="color:#0b57d0;"></i> Manual Input Soal</h1>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if(!$gateOpen)
                <div class="alert alert-danger shadow-sm d-flex align-items-center gap-2" style="border-radius:12px;">
                    <i class="fas fa-lock fa-lg mr-2"></i>
                    <div><strong>Gerbang Input Ditutup.</strong> Operator sedang menutup penginputan soal.</div>
                </div>
            @endif

            
            <div class="wizard-steps" id="wizardSteps">
                <div class="wizard-step active" id="ws1">
                    <div class="step-bubble">1</div>
                    <div class="step-label">Konfigurasi Soal</div>
                </div>
                <div class="wizard-step" id="ws2">
                    <div class="step-bubble">2</div>
                    <div class="step-label">Input Soal</div>
                </div>
            </div>

            
            <div id="step1">
                <div class="wi-card">
                    <h5 class="font-weight-bold mb-4" style="color:var(--text-main);">
                        <i class="fas fa-sliders-h mr-2" style="color:#0b57d0;"></i> Konfigurasi Paket Soal
                    </h5>

                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="field-label">Mata Pelajaran</label>
                            <input type="text" class="field-input" id="cfgMapel" value="{{ session('nama_mapel') }}" readonly
                                style="background:#f8fafc; font-weight:700;">
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Kelas</label>
                            <input type="text" class="field-input" id="cfgKelas" value="{{ session('nama_kelas') }}" readonly
                                style="background:#f8fafc; font-weight:700;">
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Total Soal</label>
                            <input type="number" class="field-input" id="cfgTotal" min="1" max="100" value="10"
                                oninput="updateTotalDisplay()" style="font-weight:800; font-size:1.2rem;">
                        </div>
                    </div>

                    
                    <div class="config-section">
                        <h6>Jumlah Opsi Pilihan Ganda</h6>
                        <div class="opsi-toggle">
                            <input type="radio" name="cfgOpsi" id="opsi4" value="4">
                            <label for="opsi4"><i class="fas fa-list-ol mr-1"></i> 4 Opsi (A–D)</label>
                            <input type="radio" name="cfgOpsi" id="opsi5" value="5" checked>
                            <label for="opsi5"><i class="fas fa-list-ol mr-1"></i> 5 Opsi (A–E)</label>
                        </div>
                    </div>

                    
                    <div class="config-section">
                        <h6>Breakdown Tipe Soal</h6>
                        <div class="jumlah-grid mb-3">
                            <div class="jumlah-box">
                                <label><i class="fas fa-dot-circle mr-1"></i> Pilihan Ganda (PG)</label>
                                <input type="number" id="jmlPG" value="10" min="0" oninput="syncBreakdown()">
                            </div>
                            <div class="jumlah-box" style="background:#faf5ff; border-color:#c4b5fd;">
                                <label style="color:#7c3aed;"><i class="fas fa-check-double mr-1"></i> Pilihan Ganda Kompleks</label>
                                <input type="number" id="jmlPGK" value="0" min="0" oninput="syncBreakdown()">
                            </div>
                            <div class="jumlah-box" style="background:#fffbeb; border-color:#fcd34d;">
                                <label style="color:#b45309;"><i class="fas fa-toggle-on mr-1"></i> Benar/Salah</label>
                                <input type="number" id="jmlBS" value="0" min="0" oninput="syncBreakdown()">
                            </div>
                            <div class="jumlah-box" style="background:#f0fdf4; border-color:#86efac;">
                                <label style="color:#16a34a;"><i class="fas fa-pencil mr-1"></i> Isian</label>
                                <input type="number" id="jmlIS" value="0" min="0" oninput="syncBreakdown()">
                            </div>
                        </div>

                        <div class="total-badge" id="statusBadge">
                            <i class="fas fa-sigma mr-2"></i>
                            Total terdistribusi: <span id="distCount">10</span> / <span id="distTotal">10</span>
                        </div>
                    </div>

                    <button class="btn-primary-full mt-3" id="btnMulai" onclick="mulaiInput()" {{ !$gateOpen ? 'disabled' : '' }}>
                        <i class="fas fa-play-circle mr-2"></i> MULAI INPUT SOAL
                    </button>
                </div>
            </div>

            
            <div id="step2">
                <div class="soal-wrapper">
                    
                    <div class="soal-nav-panel">
                        <div class="snp-title">Navigasi Soal</div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" id="progressFill" style="width:0%"></div>
                        </div>
                        <div id="soalGrid" class="soal-grid"></div>

                        <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:12px;">
                            <span style="display:inline-block;width:10px;height:10px;background:#10b981;border-radius:3px;margin-right:4px;"></span> Sudah diisi
                            <span style="display:inline-block;width:10px;height:10px;background:#0b57d0;border-radius:3px;margin:0 4px 0 12px;"></span> Aktif
                        </div>

                        
                        <button class="btn-lanjut-qc" id="btnLanjutQC" onclick="lanjutKeQC()">
                            <i class="fas fa-search"></i> Lanjut ke QC Preview
                        </button>
                    </div>

                    
                    <div class="soal-editor" id="soalEditorCard">
                        <div class="se-header">
                            <div class="q-num">
                                <i class="fas fa-question-circle"></i>
                                Soal <span id="qNumDisplay">1</span>
                                <span style="font-size:0.85rem; font-weight:400; opacity:0.8;">dari <span id="qTotal">-</span></span>
                            </div>
                            <span class="tipe-badge-pill" id="tipeBadge">PG</span>
                        </div>

                        <div class="se-body">
                            
                            <div class="field-group">
                                <label class="field-label">Teks Soal / Pertanyaan</label>
                                <textarea class="field-input" id="inputSoalTeks" rows="4"
                                    placeholder="Ketik soal di sini..."></textarea>
                            </div>

                            
                            <div class="field-group" id="opsiSection">
                                <label class="field-label">Opsi Jawaban</label>
                                <div class="options-grid" id="opsiGrid"></div>
                            </div>

                            
                            <div class="field-group">
                                <label class="field-label">Kunci Jawaban</label>
                                <select class="kunci-select" id="inputKunci" multiple style="display:none;"></select>
                                <input type="text" class="field-input" id="inputKunciTeks"
                                    placeholder="Jawaban isian..." style="display:none;">
                            </div>
                        </div>

                        <div class="se-footer">
                            <button class="btn-nav-soal" id="btnPrev" onclick="navSoal(-1)">
                                <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                            </button>

                            <button class="btn-save-soal" onclick="saveSoal()">
                                <i class="fas fa-save"></i> Simpan Soal
                            </button>

                            <button class="btn-nav-soal" id="btnNext" onclick="navSoal(1)">
                                Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <form id="formToQC" action="{{ route('guru.ujian.qc') }}" method="GET" style="display:none;">
                @csrf
            </form>

        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>

let CFG = {
    total: 10,
    opsi: 5,
    jml: { PG: 10, PG_KOMPLEKS: 0, BENAR_SALAH: 0, ISIAN: 0 }
};
let SOAL_LIST = []; 
let CURRENT_IDX = 0;
let TYPE_SEQ = [];  


function updateTotalDisplay() {
    const total = parseInt(document.getElementById('cfgTotal').value) || 0;
    document.getElementById('distTotal').textContent = total;
    syncBreakdown();
}

function syncBreakdown() {
    const pg  = parseInt(document.getElementById('jmlPG').value)  || 0;
    const pgk = parseInt(document.getElementById('jmlPGK').value) || 0;
    const bs  = parseInt(document.getElementById('jmlBS').value)  || 0;
    const is_ = parseInt(document.getElementById('jmlIS').value)  || 0;
    const dist = pg + pgk + bs + is_;
    const total = parseInt(document.getElementById('cfgTotal').value) || 0;

    document.getElementById('distCount').textContent = dist;
    document.getElementById('distTotal').textContent = total;

    const badge = document.getElementById('statusBadge');
    badge.className = 'total-badge';
    if (dist > total) badge.classList.add('over');
    else if (dist === total && total > 0) badge.classList.add('match');

    document.getElementById('btnMulai').disabled = !(dist === total && total > 0) || {{ !$gateOpen ? 'true' : 'false' }};
}

function mulaiInput() {
    
    CFG.total = parseInt(document.getElementById('cfgTotal').value);
    CFG.opsi  = parseInt(document.querySelector('input[name=cfgOpsi]:checked').value);
    CFG.jml   = {
        PG: parseInt(document.getElementById('jmlPG').value)  || 0,
        PG_KOMPLEKS: parseInt(document.getElementById('jmlPGK').value) || 0,
        BENAR_SALAH: parseInt(document.getElementById('jmlBS').value)  || 0,
        ISIAN: parseInt(document.getElementById('jmlIS').value)  || 0,
    };

    
    TYPE_SEQ = [];
    for (let i=0; i<CFG.jml.PG; i++)         TYPE_SEQ.push('PG');
    for (let i=0; i<CFG.jml.PG_KOMPLEKS; i++) TYPE_SEQ.push('PG_KOMPLEKS');
    for (let i=0; i<CFG.jml.BENAR_SALAH; i++) TYPE_SEQ.push('BENAR_SALAH');
    for (let i=0; i<CFG.jml.ISIAN; i++)        TYPE_SEQ.push('ISIAN');

    
    SOAL_LIST = TYPE_SEQ.map((t, i) => ({
        no: i+1, tipe: t,
        soal: '', a: '', b: '', c: '', d: '', e: '', kunci: ''
    }));

    document.getElementById('qTotal').textContent = CFG.total;

    
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';

    
    document.getElementById('ws1').classList.remove('active');
    document.getElementById('ws1').classList.add('done');
    document.getElementById('ws1').querySelector('.step-bubble').innerHTML = '<i class="fas fa-check"></i>';
    document.getElementById('ws2').classList.add('active');

    buildSoalGrid();
    loadSoal(0);
}


function buildSoalGrid() {
    const grid = document.getElementById('soalGrid');
    grid.innerHTML = '';
    SOAL_LIST.forEach((s, i) => {
        const pill = document.createElement('div');
        pill.className = 'soal-pill' + (i===0?' active':'');
        pill.id = 'pill-'+i;
        pill.textContent = i+1;
        pill.onclick = () => { saveSoal(false); loadSoal(i); };
        grid.appendChild(pill);
    });
}

const LABELS = { PG:'PG', PG_KOMPLEKS:'Pilihan Ganda Kompleks', BENAR_SALAH:'Benar/Salah', ISIAN:'Isian' };
const LETTERS = ['A','B','C','D','E'];

function loadSoal(idx) {
    CURRENT_IDX = idx;
    const s = SOAL_LIST[idx];

    
    document.getElementById('qNumDisplay').textContent = idx+1;
    document.getElementById('tipeBadge').textContent = LABELS[s.tipe] || s.tipe;

    
    document.getElementById('inputSoalTeks').value = s.soal;

    
    document.querySelectorAll('.soal-pill').forEach((p,i) => {
        p.classList.remove('active');
        if (SOAL_LIST[i].soal) p.classList.add('filled'); else p.classList.remove('filled');
    });
    const activePill = document.getElementById('pill-'+idx);
    if (activePill) { activePill.classList.add('active'); activePill.classList.remove('filled'); }

    
    renderOpsi(s);

    
    document.getElementById('btnPrev').disabled = idx === 0;
    document.getElementById('btnNext').disabled = idx === SOAL_LIST.length - 1;

    
    const filled = SOAL_LIST.filter(q => q.soal.trim() !== '').length;
    document.getElementById('progressFill').style.width = (filled/CFG.total*100)+'%';

    
    const allFilled = SOAL_LIST.every(q => q.soal.trim() !== '');
    const btn = document.getElementById('btnLanjutQC');
    if (allFilled) btn.classList.add('visible'); else btn.classList.remove('visible');
}

function renderOpsi(s) {
    const opsiSection = document.getElementById('opsiSection');
    const opsiGrid    = document.getElementById('opsiGrid');
    const kunciMul    = document.getElementById('inputKunci');
    const kunciTeks   = document.getElementById('inputKunciTeks');

    opsiGrid.innerHTML = '';
    kunciMul.innerHTML = '';
    kunciMul.style.display = 'none';
    kunciTeks.style.display = 'none';

    const numOpsi = ['PG','PG_KOMPLEKS','BENAR_SALAH'].includes(s.tipe) ? (s.tipe==='BENAR_SALAH' ? 2 : CFG.opsi) : 0;
    const isOpsiType = numOpsi > 0;

    opsiSection.style.display = isOpsiType ? 'block' : 'none';

    if (s.tipe === 'ISIAN') {
        
        kunciTeks.style.display = 'block';
        kunciTeks.value = s.kunci;
        return;
    }

    if (s.tipe === 'BENAR_SALAH') {
        
        
        const maxPrn = CFG.opsi; 
        for (let i=0; i<maxPrn; i++) {
            const letter = LETTERS[i];
            const val = s[letter.toLowerCase()] || '';
            const row = document.createElement('div');
            row.className = 'option-row';
            row.innerHTML = `
                <div class="opt-letter">${letter}</div>
                <input type="text" placeholder="Pernyataan ${letter}..." value="${escHtml(val)}"
                    data-opt="${letter.toLowerCase()}"
                    oninput="updateOpsiBS(this)"
                    style="flex:1; border:1.5px solid var(--border); border-radius:10px; padding:10px 14px; font-size:0.95rem; outline:none;">
                <select data-key="${letter}" onchange="updateKunciBS()"
                    style="padding:8px 10px; border:1.5px solid var(--border); border-radius:8px; font-weight:700; outline:none; background:#fff; color:var(--text-main);">
                    <option value="">—</option>
                    <option value="BENAR" ${s.kunci.split(',')[i]==='BENAR'?'selected':''}>BENAR</option>
                    <option value="SALAH" ${s.kunci.split(',')[i]==='SALAH'?'selected':''}>SALAH</option>
                </select>
            `;
            opsiGrid.appendChild(row);
        }
        return;
    }

    
    const count = numOpsi;
    for (let i=0; i<count; i++) {
        const letter = LETTERS[i];
        const val = s[letter.toLowerCase()] || '';
        const row = document.createElement('div');
        row.className = 'option-row';
        row.innerHTML = `
            <div class="opt-letter">${letter}</div>
            <input type="text" placeholder="Opsi ${letter}..." value="${escHtml(val)}"
                data-opt="${letter.toLowerCase()}"
                oninput="updateOpsi(this)"
                style="flex:1; border:1.5px solid var(--border); border-radius:10px; padding:10px 14px; font-size:0.95rem; outline:none;">
        `;
        opsiGrid.appendChild(row);
    }

    
    if (s.tipe === 'PG_KOMPLEKS') {
        kunciMul.multiple = true;
        kunciMul.style.display = 'block';
        kunciMul.style.height = '90px';
        kunciMul.style.borderRadius = '10px';
        kunciMul.style.border = '1.5px solid var(--border)';
        kunciMul.style.padding = '8px';
        kunciMul.style.outline = 'none';
        const selectedKeys = s.kunci ? s.kunci.split(',').map(k=>k.trim().toUpperCase()) : [];
        for (let i=0; i<count; i++) {
            const opt = document.createElement('option');
            opt.value = LETTERS[i];
            opt.textContent = LETTERS[i];
            if (selectedKeys.includes(LETTERS[i])) opt.selected = true;
            kunciMul.appendChild(opt);
        }
        kunciMul.onchange = () => {
            const selected = Array.from(kunciMul.selectedOptions).map(o=>o.value);
            SOAL_LIST[CURRENT_IDX].kunci = selected.join(',');
        };
    } else {
        
        kunciMul.multiple = false;
        kunciMul.style.display = 'block';
        kunciMul.style.height = 'auto';
        kunciMul.style.borderRadius = '10px';
        kunciMul.style.border = '1.5px solid var(--border)';
        kunciMul.style.padding = '10px 14px';
        kunciMul.style.outline = 'none';
        kunciMul.style.fontWeight = '700';
        kunciMul.style.background = '#fff';
        const noOpt = document.createElement('option');
        noOpt.value=''; noOpt.textContent='— Pilih Kunci —';
        kunciMul.appendChild(noOpt);
        for (let i=0; i<count; i++) {
            const opt = document.createElement('option');
            opt.value = LETTERS[i];
            opt.textContent = `${LETTERS[i]}`;
            if (s.kunci.toUpperCase() === LETTERS[i]) opt.selected = true;
            kunciMul.appendChild(opt);
        }
        kunciMul.onchange = () => { SOAL_LIST[CURRENT_IDX].kunci = kunciMul.value; };
    }
}

function escHtml(str) {
    return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function updateOpsi(input) {
    SOAL_LIST[CURRENT_IDX][input.dataset.opt] = input.value;
}
function updateOpsiBS(input) {
    SOAL_LIST[CURRENT_IDX][input.dataset.opt] = input.value;
}
function updateKunciBS() {
    const selects = document.querySelectorAll('[data-key]');
    const keys = Array.from(selects).map(s => s.value);
    SOAL_LIST[CURRENT_IDX].kunci = keys.join(',');
}

function saveSoal(showAlert = true) {
    const s = SOAL_LIST[CURRENT_IDX];
    s.soal = document.getElementById('inputSoalTeks').value.trim();

    
    if (s.tipe === 'ISIAN') {
        s.kunci = document.getElementById('inputKunciTeks').value.trim();
    }

    
    const pill = document.getElementById('pill-'+CURRENT_IDX);
    if (pill) {
        if (s.soal) { pill.classList.add('filled'); }
        else { pill.classList.remove('filled'); }
    }

    
    const filled = SOAL_LIST.filter(q => q.soal.trim() !== '').length;
    document.getElementById('progressFill').style.width = (filled/CFG.total*100)+'%';

    
    const allFilled = SOAL_LIST.every(q => q.soal.trim() !== '');
    const btn = document.getElementById('btnLanjutQC');
    if (allFilled) btn.classList.add('visible'); else btn.classList.remove('visible');

    if (showAlert && s.soal) {
        
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:30px;right:30px;background:#10b981;color:#fff;padding:12px 24px;border-radius:12px;font-weight:700;z-index:9999;box-shadow:0 8px 20px rgba(16,185,129,0.3);transition:opacity .3s;';
        toast.innerHTML = '<i class="fas fa-check mr-2"></i> Soal '+(CURRENT_IDX+1)+' tersimpan!';
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity='0'; setTimeout(()=>toast.remove(),300); }, 1500);
    }
}

function navSoal(dir) {
    saveSoal(false);
    const next = CURRENT_IDX + dir;
    if (next >= 0 && next < SOAL_LIST.length) loadSoal(next);
}

function lanjutKeQC() {
    saveSoal(false);

    const allFilled = SOAL_LIST.every(q => q.soal.trim() !== '');
    if (!allFilled) {
        Swal.fire({ icon:'warning', title:'Belum Lengkap', text:'Semua soal harus diisi sebelum lanjut ke QC.', confirmButtonColor:'#0b57d0' });
        return;
    }

    
    sessionStorage.setItem('soal_draft_manual', JSON.stringify({
        mapel: document.getElementById('cfgMapel').value,
        kelas: document.getElementById('cfgKelas').value,
        soal_list: SOAL_LIST
    }));

    window.location.href = '{{ route('guru.ujian.qc') }}?from=manual';
}


updateTotalDisplay();
</script>
@endpush
