<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QC Soal — {{ $mapel }} / {{ $kelas }} | Operator</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="{{ asset('exam/assets/root.css') }}">
    <link rel="stylesheet" href="{{ asset('exam/assets/ujian.css') }}">

    
    <style>
        
        body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }

        
        .oqc-header {
            background: linear-gradient(135deg, #1e293b 0%, #7c3aed 100%);
            padding: 0 32px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 4px 20px rgba(124,58,237,0.3);
            color: #fff;
        }
        .oqc-header-left { display:flex; align-items:center; gap:16px; }
        .oqc-back-btn {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .oqc-back-btn:hover { background: rgba(255,255,255,0.2); color:#fff; text-decoration:none; }
        .oqc-title { font-size:1.1rem; font-weight:800; }
        .oqc-meta-badge {
            background: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .oqc-operator-badge {
            background: rgba(245,158,11,0.25);
            border: 1px solid rgba(245,158,11,0.4);
            color: #fbbf24;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        
        .oqc-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
            min-height: calc(100vh - 72px);
        }

        
        .oqc-sidebar {
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 72px;
            height: calc(100vh - 72px);
            overflow-y: auto;
        }
        .oqcs-header {
            padding: 20px 24px;
            background: #fafafa;
            border-bottom: 1px solid #e2e8f0;
        }
        .oqcs-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .oqcs-count { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
        .oqcs-guru { font-size: 0.8rem; color: #64748b; margin-top: 2px; }

        .oqc-nav-grid-wrap { padding: 20px 24px; flex: 1; }
        .oqc-nav-grid {
            display: grid;
            grid-template-columns: repeat(5,1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .oqc-nav-pill {
            aspect-ratio: 1;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.18s;
            color: #64748b;
        }
        .oqc-nav-pill:hover { border-color: #7c3aed; color: #7c3aed; }
        .oqc-nav-pill.validated { background: #10b981; border-color: #10b981; color: #fff; }
        .oqc-nav-pill.draft     { background: #f59e0b; border-color: #f59e0b; color: #fff; }

        .oqc-legend {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 16px;
        }
        .oqc-legend span { display:inline-flex; align-items:center; gap:4px; margin-right:10px; }
        .oqc-dot { width:9px; height:9px; border-radius:3px; display:inline-block; }

        
        .oqcs-footer {
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            background: #fafafa;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-validate-all {
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            font-weight: 800;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 6px 16px -4px rgba(124,58,237,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-validate-all:hover { transform:translateY(-2px); box-shadow:0 10px 20px -4px rgba(124,58,237,0.5); }
        .btn-back-dash {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-back-dash:hover { border-color:#7c3aed; color:#7c3aed; text-decoration:none; }

        
        .oqc-content { padding: 32px 40px; overflow-y: auto; }

        
        .oqc-loading, .oqc-empty {
            text-align: center;
            padding: 80px 40px;
            color: #94a3b8;
        }
        .oqc-loading i, .oqc-empty i { font-size: 3.5rem; margin-bottom: 16px; display: block; }
        .oqc-empty h5 { font-weight: 700; color: #64748b; }

        
        .oqc-soal-card {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
            transition: box-shadow 0.2s;
        }
        .oqc-soal-card.is-validated { border-color: #10b981; }
        .oqc-soal-card.is-draft     { border-color: #f59e0b; }

        .oqcsc-header {
            padding: 14px 24px;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .oqcsc-number { font-weight: 800; color: #7c3aed; font-size: 0.95rem; }
        .oqcsc-tipe {
            font-size: 0.72rem; font-weight: 700;
            padding: 3px 10px; border-radius: 99px;
        }
        .tipe-pg      { background:#eff6ff; color:#2563eb; }
        .tipe-pgk     { background:#f5f3ff; color:#7c3aed; }
        .tipe-bs      { background:#fffbeb; color:#d97706; }
        .tipe-isian   { background:#f0fdf4; color:#16a34a; }

        .oqcsc-status-badge {
            font-size: 0.72rem; font-weight: 700;
            padding: 3px 10px; border-radius: 99px;
        }
        .status-validated { background:#ecfdf5; color:#059669; }
        .status-draft     { background:#fffbeb; color:#b45309; }

        .oqcsc-body { padding: 24px 28px; }

        
        .oqc-q-text {
            background: white;
            padding: 24px 32px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            font-size: 1.05rem;
            line-height: 1.8;
            color: #1e293b;
            margin-bottom: 20px;
            position: relative;
        }
        .oqc-q-text::before {
            content: '';
            position: absolute;
            left: 0; top: 24px; bottom: 24px;
            width: 4px;
            background: #7c3aed;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        
        .oqc-options { display: grid; gap: 10px; }
        .oqc-option-item {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            padding: 12px 18px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
        }
        .oqc-option-item.is-kunci {
            background: #eff6ff;
            border-color: #2563eb;
        }
        .oqc-opt-label {
            width: 38px; height: 38px;
            border-radius: 9px;
            background: #f1f5f9;
            display: flex; align-items:center; justify-content:center;
            font-weight: 800; font-size: 0.9rem; color: #64748b; flex-shrink:0;
        }
        .oqc-option-item.is-kunci .oqc-opt-label
            { background: #2563eb; color: #fff; box-shadow: 0 3px 8px rgba(37,99,235,0.3); }
        .oqc-opt-text { font-size: 0.9rem; font-weight: 500; color: #334155; flex: 1; }
        .oqc-kunci-tag {
            font-size: 0.7rem; font-weight: 700; color: #2563eb;
            background: #dbeafe; padding: 2px 8px; border-radius: 99px;
        }

        .oqc-isian-box {
            background: #f8fafc; border: 1.5px solid #e2e8f0;
            border-radius: 12px; padding: 14px 18px;
            font-size: 0.9rem; color: #64748b;
        }
        .oqc-isian-answer {
            display: inline-block; margin-top: 6px;
            background: #ecfdf5; border: 1.5px solid #34d399;
            border-radius: 8px; padding: 3px 12px;
            font-weight: 700; color: #065f46; font-size: 0.9rem;
        }

        @media (max-width: 900px) {
            .oqc-layout { grid-template-columns: 1fr; }
            .oqc-sidebar { position: static; height: auto; }
        }
    </style>
</head>
<body>


<div class="oqc-header">
    <div class="oqc-header-left">
        <a href="javascript:window.close()" class="oqc-back-btn">
            <i class="fas fa-times"></i> Tutup
        </a>
        <div>
            <div class="oqc-title"><i class="fas fa-search mr-2"></i>QC Soal — Operator View</div>
        </div>
    </div>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <span class="oqc-meta-badge"><i class="fas fa-user-graduate mr-1"></i> {{ $guruNama }}</span>
        <span class="oqc-meta-badge"><i class="fas fa-book mr-1"></i> {{ $mapel }}</span>
        <span class="oqc-meta-badge"><i class="fas fa-school mr-1"></i> {{ $kelas }}</span>
        <span class="oqc-operator-badge"><i class="fas fa-user-shield mr-1"></i> OPERATOR</span>
    </div>
</div>


<div class="oqc-layout">

    
    <div class="oqc-sidebar">
        <div class="oqcs-header">
            <div class="oqcs-title">Daftar Soal</div>
            <div class="oqcs-count" id="oqcCount">Memuat...</div>
            <div class="oqcs-guru"><i class="fas fa-user mr-1"></i> {{ $guruNama }}</div>
        </div>

        <div class="oqc-nav-grid-wrap">
            <div class="oqc-nav-grid" id="oqcNavGrid"></div>
            <div class="oqc-legend">
                <span><span class="oqc-dot" style="background:#10b981;"></span> Tervalidasi</span>
                <span><span class="oqc-dot" style="background:#f59e0b;"></span> Draft</span>
            </div>
        </div>

        <div class="oqcs-footer">
            <button class="btn-validate-all" id="btnValidateAll" onclick="konfirmasiValidasiSemua()">
                <i class="fas fa-check-double"></i> Validasi Semua Soal
            </button>
            <a href="{{ route('operator.asesmen.dashboard') }}" class="btn-back-dash">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    
    <div class="oqc-content" id="oqcContent">

        
        <div class="oqc-loading" id="oqcLoading">
            <i class="fas fa-spinner fa-spin" style="color:#7c3aed;"></i>
            <p style="color:#64748b; font-weight:600;">Memuat soal dari database...</p>
        </div>

        
        <div class="oqc-empty" id="oqcEmpty" style="display:none;">
            <i class="fas fa-inbox"></i>
            <h5>Tidak Ada Soal</h5>
            <p style="font-size:0.9rem;">Belum ada soal dari guru ini untuk mapel/kelas tersebut.</p>
        </div>

        
        <div id="oqcSoalList"></div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
const API = '{{ route("operator.api.asesmen") }}';
const CSRF = '{{ csrf_token() }}';
const GURU_ID = {{ (int) $guruId }};
const MAPEL   = {!! json_encode($mapel) !!};
const KELAS   = {!! json_encode($kelas) !!};

let SOAL_DATA = [];

async function apiPost(action, data = {}) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('_token', CSRF);
    for (const k in data) fd.append(k, data[k]);
    const r = await fetch(API, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF } });
    return r.json();
}


async function loadSoal() {
    const res = await apiPost('get_soal_by_paket', { guru_id: GURU_ID, mapel: MAPEL, kelas: KELAS });

    document.getElementById('oqcLoading').style.display = 'none';

    if (!res.success || !res.data || res.data.length === 0) {
        document.getElementById('oqcEmpty').style.display = 'block';
        document.getElementById('oqcCount').textContent = '0 soal';
        return;
    }

    SOAL_DATA = res.data;
    document.getElementById('oqcCount').textContent = SOAL_DATA.length + ' soal';
    renderSoal();
}

function renderSoal() {
    const list    = document.getElementById('oqcSoalList');
    const navGrid = document.getElementById('oqcNavGrid');
    list.innerHTML = '';
    navGrid.innerHTML = '';

    const tipeMap   = { PG:'PG', PG_KOMPLEKS:'Pilihan Ganda Kompleks', BENAR_SALAH:'Benar/Salah', ISIAN:'Isian' };
    const tipeClass = { PG:'tipe-pg', PG_KOMPLEKS:'tipe-pgk', BENAR_SALAH:'tipe-bs', ISIAN:'tipe-isian' };
    const LETTERS   = ['a','b','c','d','e'];

    SOAL_DATA.forEach((s, idx) => {
        const isValidated = (s.status_soal === 'VALIDATED');
        const statusClass = isValidated ? 'is-validated' : 'is-draft';

        
        const pill = document.createElement('div');
        pill.className = 'oqc-nav-pill ' + (isValidated ? 'validated' : 'draft');
        pill.textContent = idx + 1;
        pill.title = `Soal ${idx+1} — ${s.status_soal}`;
        pill.onclick = () => {
            const card = document.getElementById('oqccard-'+idx);
            if (card) card.scrollIntoView({ behavior:'smooth', block:'center' });
        };
        navGrid.appendChild(pill);

        
        const card = document.createElement('div');
        card.className = `oqc-soal-card ${statusClass}`;
        card.id = 'oqccard-'+idx;

        
        function renderAssetsHtml(assets) {
            if (!assets || assets.length === 0) return '';
            let html = '<div style="margin-bottom: 20px; display:flex; flex-direction:column; gap:12px;">';
            assets.forEach((asset) => {
                let content = '';
                if (asset.asset_type === 'local_image' || asset.asset_type === 'external_image') {
                    let src = asset.asset_source;
                    if (asset.asset_type === 'local_image') {
                        src = src.startsWith('exam_assets/') ? '{{ asset('storage') }}/' + src : '{{ asset('storage/exam_assets') }}/' + src;
                    }
                    content = `<img src="${src}" style="max-width:100%; border-radius:12px; border:1px solid #e2e8f0; max-height:250px; object-fit:contain;">`;
                } else if (asset.asset_type === 'youtube_link') {
                    let ytId = asset.asset_source.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([a-zA-Z0-9_-]{11})/);
                    ytId = ytId ? ytId[1] : '';
                    content = `<iframe width="100%" height="250" src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen style="border-radius:12px;"></iframe>`;
                } else if (asset.asset_type === 'audio_mp3') {
                    let src = asset.asset_source.startsWith('exam_assets/') ? '{{ asset('storage') }}/' + asset.asset_source : '{{ asset('storage/exam_assets') }}/' + asset.asset_source;
                    content = `<audio controls style="width:100%;"><source src="${src}" type="audio/mpeg"></audio>`;
                } else if (asset.asset_type === 'google_drive') {
                    content = `<div style="padding:16px; background:#f1f5f9; border-radius:8px; font-weight:600;"><i class="fab fa-google-drive" style="color:#0ea5e9;"></i> Aset Google Drive (${asset.asset_source})</div>`;
                }
                
                html += `<div style="position:relative; display:inline-block; max-width:100%;">
                    ${content}
                </div>`;
            });
            html += '</div>';
            return html;
        }

        let bodyHtml = renderAssetsHtml(s.assets);
        bodyHtml += `<div class="oqc-q-text">${escHtml(s.konten_soal)}</div>`;
        
        if (s.tipe_soal === 'ISIAN') {
            bodyHtml += `<div class="oqc-isian-box">
                <div style="font-size:0.78rem;font-weight:700;color:#64748b;margin-bottom:4px;"><i class="fas fa-pencil mr-1"></i> Soal Isian</div>
                Kunci Jawaban: <span class="oqc-isian-answer">${escHtml(s.kunci_jawaban)}</span>
            </div>`;
        } else if (s.tipe_soal === 'BENAR_SALAH') {
            let optsHtml = '<div class="bs-container" style="display: flex; flex-direction: column; gap: 16px;">';
            const kunciArr = (s.kunci_jawaban||'').split(',').map(k=>k.trim().toUpperCase());
            LETTERS.forEach((l, i) => {
                const val = s['opsi_' + l];
                if (!val) return;
                const isBenar = kunciArr[i] === 'BENAR';
                const isSalah = kunciArr[i] === 'SALAH';
                
                optsHtml += `
                <div class="bs-card-item" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                    <div class="bs-statement" style="margin-bottom: 12px; font-size: 0.95rem; line-height: 1.5; color: #1e293b;">
                        <strong style="color:#7c3aed; margin-right:5px;">${i + 1}.</strong> ${escHtml(val)}
                    </div>
                    <div class="bs-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid ${isBenar ? '#10b981' : '#e2e8f0'}; background: ${isBenar ? '#ecfdf5' : '#f8fafc'}; color: ${isBenar ? '#059669' : '#64748b'}; font-weight: ${isBenar ? '800' : '600'}; border-radius: 8px;">
                            <i class="fas fa-check"></i> <span>BENAR</span>
                        </label>
                        <label style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid ${isSalah ? '#ef4444' : '#e2e8f0'}; background: ${isSalah ? '#fef2f2' : '#f8fafc'}; color: ${isSalah ? '#b91c1c' : '#64748b'}; font-weight: ${isSalah ? '800' : '600'}; border-radius: 8px;">
                            <i class="fas fa-times"></i> <span>SALAH</span>
                        </label>
                    </div>
                </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        } else if (s.tipe_soal === 'PG_KOMPLEKS') {
            const kunciArr = (s.kunci_jawaban||'').split(',').map(k=>k.trim().toUpperCase());
            let optsHtml = '<div class="oqc-options">';
            LETTERS.forEach(l => {
                const val = s['opsi_' + l];
                if (!val) return;
                const letter = l.toUpperCase();
                const isKey = kunciArr.includes(letter) || kunciArr.includes(val?.trim()?.toUpperCase());
                optsHtml += `<div class="oqc-option-item ${isKey?'is-kunci':''}">
                    <div class="oqc-opt-label" style="background:none; border:none; color:inherit; font-size:1.2rem;">
                        ${isKey ? '<i class="fas fa-check-square"></i>' : '<i class="far fa-square"></i>'}
                    </div>
                    <div class="oqc-opt-text">${escHtml(val)}</div>
                    ${isKey ? '<span class="oqc-kunci-tag"><i class="fas fa-check mr-1"></i>Kunci</span>' : ''}
                </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        } else {
            const kunciArr = [(s.kunci_jawaban||'').trim().toUpperCase()];
            let optsHtml = '<div class="oqc-options">';
            LETTERS.forEach(l => {
                const val = s['opsi_' + l];
                if (!val) return;
                const letter = l.toUpperCase();
                const isKey = kunciArr.includes(letter) || kunciArr.includes(val?.trim()?.toUpperCase());
                optsHtml += `<div class="oqc-option-item ${isKey?'is-kunci':''}">
                    <div class="oqc-opt-label">${letter}</div>
                    <div class="oqc-opt-text">${escHtml(val)}</div>
                    ${isKey ? '<span class="oqc-kunci-tag"><i class="fas fa-check mr-1"></i>Kunci</span>' : ''}
                </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        }

        card.innerHTML = `
            <div class="oqcsc-header">
                <span class="oqcsc-number"><i class="fas fa-question-circle mr-1"></i> Soal ${idx+1}</span>
                <div style="display:flex;gap:6px;align-items:center;">
                    <span class="oqcsc-tipe ${tipeClass[s.tipe_soal]||'tipe-pg'}">${tipeMap[s.tipe_soal]||s.tipe_soal}</span>
                    <span class="oqcsc-status-badge ${isValidated?'status-validated':'status-draft'}">
                        ${isValidated ? '<i class="fas fa-check mr-1"></i>VALIDATED' : '<i class="fas fa-clock mr-1"></i>DRAFT'}
                    </span>
                </div>
            </div>
            <div class="oqcsc-body">${bodyHtml}</div>
        `;
        list.appendChild(card);
    });
}

function escHtml(str) {
    return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ==================== VALIDASI ====================
async function konfirmasiValidasiSemua() {
    if (SOAL_DATA.length === 0) return;

    const alreadyValidated = SOAL_DATA.filter(s => s.status_soal === 'VALIDATED').length;
    const toValidate = SOAL_DATA.length - alreadyValidated;

    if (toValidate === 0) {
        Swal.fire({ icon:'info', title:'Sudah Tervalidasi', text:'Semua soal dalam paket ini sudah berstatus VALIDATED.', confirmButtonColor:'#7c3aed' });
        return;
    }

    const conf = await Swal.fire({
        title: 'Validasi Semua Soal?',
        html: `<b>${toValidate} soal DRAFT</b> akan divalidasi dari total <b>${SOAL_DATA.length} soal</b>.<br>
               <small class="text-muted">Soal tervalidasi akan tersedia untuk dijadwalkan dalam ujian siswa.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Validasi!',
        cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
    });

    if (!conf.isConfirmed) return;

    const btn = document.getElementById('btnValidateAll');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memvalidasi...';
    btn.disabled = true;

    const res = await apiPost('validate_paket_soal', { guru_id: GURU_ID, mapel: MAPEL, kelas: KELAS });

    if (res.success) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Divalidasi!',
            html: res.message + '<br><small>Soal siap dijadwalkan oleh operator.</small>',
            confirmButtonColor: '#7c3aed'
        }).then(() => {
            loadSoal(); // Refresh tampilan
        });
    } else {
        Swal.fire({ icon:'error', title:'Gagal', text: res.message, confirmButtonColor:'#7c3aed' });
    }

    btn.innerHTML = '<i class="fas fa-check-double mr-1"></i> Validasi Semua Soal';
    btn.disabled = false;
}

// ==================== INIT ====================
loadSoal();
</script>
</body>
</html>
