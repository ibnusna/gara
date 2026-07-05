<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quality Control (QC) Soal | Garuda Akademi</title>

    
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

        
        .qc-page-header {
            background: linear-gradient(135deg, #1e293b 0%, #0b57d0 100%);
            padding: 0 32px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 4px 20px rgba(11,87,208,0.3);
            color: #fff;
        }
        .qc-page-header .qh-left { display:flex; align-items:center; gap:16px; }
        .qc-back-btn {
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
        .qc-back-btn:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
            text-decoration: none;
        }
        .qc-header-title { font-size:1.15rem; font-weight:800; }
        .qc-meta-badge {
            background: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
        }

        
        .qc-alert-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1.5px solid #34d399;
            border-radius: 12px;
            padding: 14px 20px;
            color: #065f46;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            font-size: 0.9rem;
        }
        .qc-alert-error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1.5px solid #f87171;
            border-radius: 12px;
            padding: 14px 20px;
            color: #991b1b;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        
        .qc-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 0;
            min-height: calc(100vh - 72px);
        }

        
        .qc-sidebar {
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 72px;
            height: calc(100vh - 72px);
            overflow-y: auto;
        }
        .qc-sidebar-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #fafafa;
        }
        .qcs-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .qcs-count { font-size: 0.9rem; font-weight: 700; color: #1e293b; }

        .qc-nav-grid {
            padding: 20px 24px;
            flex: 1;
        }
        .qc-soal-nav-grid {
            display: grid;
            grid-template-columns: repeat(5,1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        

        .qc-sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            background: #fafafa;
        }
        .btn-kirim-qc {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0b57d0, #2563eb);
            color: #fff;
            font-weight: 800;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 8px 20px -4px rgba(11,87,208,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-kirim-qc:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(11,87,208,0.5);
        }

        
        .qc-content {
            padding: 32px 40px;
            overflow-y: auto;
        }

        .qc-soal-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
            transition: box-shadow 0.2s;
        }
        .qsc-header {
            padding: 16px 28px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .qsc-number {
            font-weight: 800;
            color: #0b57d0;
            font-size: 1rem;
        }
        .qsc-tipe {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 99px;
        }
        .tipe-pg      { background:#eff6ff; color:#2563eb; }
        .tipe-pgk     { background:#f5f3ff; color:#7c3aed; }
        .tipe-bs      { background:#fffbeb; color:#d97706; }
        .tipe-isian   { background:#f0fdf4; color:#16a34a; }

        .qsc-body { padding: 28px; }

        
        .qc-q-text {
            background: white;
            padding: 28px 36px;
            border-radius: 16px;
            border: 2px solid transparent;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #1e293b;
            margin-bottom: 24px;
            position: relative;
            cursor: text;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .qc-q-text::before {
            content: '';
            position: absolute;
            left: 0; top: 28px; bottom: 28px;
            width: 4px;
            background: #0b57d0;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        .qc-q-text[contenteditable=true]:focus {
            border-color: #0b57d0;
            box-shadow: 0 0 0 4px rgba(11,87,208,0.1);
        }
        .qc-edit-hint {
            position: absolute;
            top: 10px;
            right: 14px;
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }
        .qc-q-text:hover .qc-edit-hint { opacity: 1; }

        
        .qc-options { display: grid; gap: 12px; }
        .qc-option-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            padding: 14px 20px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.15s;
        }
        .qc-option-card:hover { border-color:#0b57d0; transform:translateY(-1px); }
        .qc-option-card.is-kunci {
            background: #eff6ff;
            border-color: #0b57d0;
            box-shadow: 0 0 0 3px rgba(11,87,208,0.1);
        }
        .qc-opt-label {
            width: 42px; height: 42px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex; align-items:center; justify-content:center;
            font-weight: 800; font-size: 1rem; color: #64748b;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .qc-option-card.is-kunci .qc-opt-label {
            background: #0b57d0; color: #fff;
            box-shadow: 0 4px 10px rgba(11,87,208,0.3);
        }
        .qc-opt-text { font-size: 0.95rem; font-weight: 500; color: #334155; flex: 1; }
        .kunci-tick {
            font-size: 0.75rem; font-weight: 700;
            color: #0b57d0; display: none;
        }
        .qc-option-card.is-kunci .kunci-tick { display: block; }

        
        .qc-isian-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            color: #64748b;
            font-size: 0.9rem;
        }
        .qc-isian-answer {
            display: inline-block;
            margin-top: 8px;
            background: #ecfdf5;
            border: 1.5px solid #34d399;
            border-radius: 8px;
            padding: 4px 14px;
            font-weight: 700;
            color: #065f46;
            font-size: 0.95rem;
        }

        
        .qc-empty {
            text-align: center;
            padding: 80px 40px;
            color: #94a3b8;
        }
        .qc-empty i { font-size: 4rem; margin-bottom: 16px; display: block; }
        .qc-empty h5 { font-weight: 700; color: #64748b; margin-bottom: 8px; }

        .btn-add-asset {
            background: #f1f5f9; color: #0b57d0; border: 1px solid #cbd5e1;
            padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
        }
        .btn-add-asset:hover { background: #e2e8f0; border-color: #94a3b8; }
        
        
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5);
            display: none; align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-card {
            background: #fff; width: 90%; max-width: 500px; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-header { font-size: 1.2rem; font-weight: 800; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        .modal-close { cursor: pointer; color: #94a3b8; }
        .modal-close:hover { color: #1e293b; }
        .asset-preview-box {
            background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 16px; margin-top: 16px; min-height: 100px; display: flex; align-items: center; justify-content: center;
        }

        @media (max-width: 900px) {
            .qc-layout { grid-template-columns: 1fr; }
            .qc-sidebar { position: static; height: auto; }
        }
    </style>
</head>
<body>


<div class="qc-page-header">
    <div class="qh-left">
        <a href="{{ route('guru.ujian') }}" class="qc-back-btn">
            <i class="fas fa-arrow-left"></i> Ruang Asesmen
        </a>
        <div>
            <div class="qc-header-title"><i class="fas fa-search mr-2"></i>Quality Control (QC) Soal</div>
        </div>
    </div>
    <div style="display:flex; gap:12px; align-items:center;">
        <span class="qc-meta-badge"><i class="fas fa-book mr-1"></i> {{ $mapel }}</span>
        <span class="qc-meta-badge"><i class="fas fa-school mr-1"></i> {{ $kelas }}</span>
    </div>
</div>


<div class="qc-layout">

    
    <div class="qc-sidebar">
        <div class="qc-sidebar-header">
            <div class="qcs-title">Daftar Soal</div>
            <div class="qcs-count" id="qcSoalCount">0 soal</div>
        </div>

        <div class="qc-nav-grid">
            <div class="qc-soal-nav-grid" id="qcNavGrid"></div>
            <div style="font-size:0.77rem; color:#94a3b8;">
                <i class="fas fa-info-circle mr-1"></i>
                Klik teks soal untuk mengedit langsung. Klik opsi untuk toggle kunci jawaban.
            </div>
        </div>

        <div class="qc-sidebar-footer">
            <button class="btn-kirim-qc" id="btnKirim" onclick="konfirmasiKirim()">
                <i class="fas fa-paper-plane"></i> Kirim ke Operator
            </button>
        </div>
    </div>

    
    <div class="qc-content" id="qcContent">

        @if(session('error'))
            <div class="qc-alert-error">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        
        <div class="qc-empty" id="qcEmptyState">
            <i class="fas fa-inbox"></i>
            <h5>Belum Ada Soal</h5>
            <p style="font-size:0.9rem;">Soal akan muncul di sini setelah Anda selesai input dari <strong>Manual Input</strong>, atau buat soal baru dari menu Smart Input.</p>
            <a href="{{ route('guru.ujian.manual') }}" class="btn" style="background:#0b57d0;color:#fff;border-radius:10px;padding:10px 24px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;margin-top:8px;">
                <i class="fas fa-pencil-alt"></i> Ke Manual Input
            </a>
        </div>

        
        <div id="qcSoalList"></div>

    </div>
</div>


<form id="formKirimQC" method="POST" action="{{ route('guru.ujian.qc.kirim') }}" style="display:none;">
    @csrf
    <input type="hidden" name="mapel" id="fMapel" value="{{ $mapel }}">
    <input type="hidden" name="kelas" id="fKelas" value="{{ $kelas }}">
    <input type="hidden" name="soal_list" id="fSoalList" value="">
</form>


<div class="modal-overlay" id="modalAset">
    <div class="modal-card">
        <div class="modal-header">
            <span><i class="fas fa-photo-video mr-1"></i> Tambahkan Aset</span>
            <i class="fas fa-times modal-close" onclick="tutupModalAset()"></i>
        </div>
        <input type="hidden" id="modalAssetIdx">
        
        <div style="margin-bottom: 16px;">
            <label style="font-weight:600; font-size:0.9rem;">Tipe Aset</label>
            <select id="assetType" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;" onchange="gantiTipeAset()">
                <option value="local_image">Upload Gambar (Lokal)</option>
                <option value="external_image">Link Gambar (Eksternal)</option>
                <option value="youtube_link">Video YouTube</option>
                <option value="audio_mp3">File Audio (MP3)</option>
                <option value="google_drive">Google Drive (Link/ID)</option>
            </select>
        </div>

        <div id="assetInputWrapper">
            
        </div>

        <div class="asset-preview-box" id="assetPreview">
            <span style="color:#94a3b8; font-size:0.9rem;">Pratinjau akan muncul di sini</span>
        </div>

        <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
            <button class="btn" style="padding:10px 16px; border-radius:8px; background:#e2e8f0; color:#334155; font-weight:600; border:none;" type="button" onclick="tutupModalAset()">Batal</button>
            <button class="btn" style="padding:10px 16px; border-radius:8px; background:#0b57d0; color:#fff; font-weight:600; border:none;" type="button" onclick="simpanAset(event)">Simpan Aset</button>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/gara-picker.js') }}?v=1.0"></script>

<script>



let SOAL_DATA = [];

const phpDraft = @json($soalDraft);
const ssDraft = sessionStorage.getItem('soal_draft_manual');

if (ssDraft) {
    try {
        const parsed = JSON.parse(ssDraft);
        if (parsed && Array.isArray(parsed.soal_list) && parsed.soal_list.length > 0) {
            SOAL_DATA = parsed.soal_list;
            
            if (parsed.mapel) document.getElementById('fMapel').value = parsed.mapel;
            if (parsed.kelas) document.getElementById('fKelas').value = parsed.kelas;
        }
    } catch(e) { console.warn('sessionStorage parse error', e); }
}

if (SOAL_DATA.length === 0 && Array.isArray(phpDraft) && phpDraft.length > 0) {
    SOAL_DATA = phpDraft;
}


function renderAssetsHtml(assets, idx) {
    if (!assets || assets.length === 0) return '';
    let html = '<div style="margin-bottom: 16px; display:flex; flex-direction:column; gap:12px;">';
    assets.forEach((asset, aidx) => {
        let content = '';
        if (asset.asset_type === 'local_image' || asset.asset_type === 'external_image') {
            let src = asset.asset_type === 'local_image' ? (asset.url || '{{ asset('storage') }}/' + asset.asset_source) : asset.asset_source;
            if (asset.asset_type === 'local_image' && asset.asset_source && !asset.asset_source.startsWith('exam_assets/')) {
                src = asset.url || '{{ asset('storage/exam_assets') }}/' + asset.asset_source;
            }
            content = `<img src="${src}" style="max-width:100%; border-radius:12px; border:1px solid #e2e8f0; max-height:250px; object-fit:contain;">`;
        } else if (asset.asset_type === 'youtube_link') {
            let ytId = asset.asset_source.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&]{11})/);
            ytId = ytId ? ytId[1] : '';
            content = `<iframe width="100%" height="250" src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen style="border-radius:12px;"></iframe>`;
        } else if (asset.asset_type === 'audio_mp3') {
            let src = asset.url || (asset.asset_source.startsWith('exam_assets/') ? '{{ asset('storage') }}/' + asset.asset_source : '{{ asset('storage/exam_assets') }}/' + asset.asset_source);
            content = `<audio controls style="width:100%;"><source src="${src}" type="audio/mpeg"></audio>`;
        } else if (asset.asset_type === 'google_drive') {
            content = `<div style="padding:16px; background:#f1f5f9; border-radius:8px; font-weight:600;"><i class="fab fa-google-drive" style="color:#0ea5e9;"></i> Aset Google Drive (${asset.asset_source})</div>`;
        }
        
        html += `<div style="position:relative; display:inline-block; max-width:100%;">
            ${content}
            <button type="button" onclick="hapusAset(${idx}, ${aidx})" style="position:absolute; top:8px; right:8px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer;" title="Hapus Aset"><i class="fas fa-times"></i></button>
        </div>`;
    });
    html += '</div>';
    return html;
}

// ==================== RENDER ====================
function renderQC() {
    const list   = document.getElementById('qcSoalList');
    const empty  = document.getElementById('qcEmptyState');
    const navGrid= document.getElementById('qcNavGrid');
    const count  = document.getElementById('qcSoalCount');

    if (SOAL_DATA.length === 0) {
        empty.style.display = 'block';
        list.style.display  = 'none';
        navGrid.innerHTML   = '';
        count.textContent   = '0 soal';
        return;
    }

    empty.style.display = 'none';
    list.style.display  = 'block';
    count.textContent   = SOAL_DATA.length + ' soal';
    list.innerHTML      = '';
    navGrid.innerHTML   = '';

    SOAL_DATA.forEach((s, idx) => {
        // Nav pill
        const pill = document.createElement('div');
        pill.className = 'nav-item-box';
        pill.id = 'qcpill-'+idx;
        pill.textContent = idx+1;
        pill.title = 'Loncat ke soal '+(idx+1);
        pill.onclick = () => {
            const card = document.getElementById('qccard-'+idx);
            if (card) card.scrollIntoView({ behavior:'smooth', block:'center' });
        };
        navGrid.appendChild(pill);

        // Soal card
        const tipeMap = { PG:'PG', PG_KOMPLEKS:'Pilihan Ganda Kompleks', BENAR_SALAH:'Benar/Salah', ISIAN:'Isian' };
        const tipeClass = { PG:'tipe-pg', PG_KOMPLEKS:'tipe-pgk', BENAR_SALAH:'tipe-bs', ISIAN:'tipe-isian' };

        const card = document.createElement('div');
        card.className = 'qc-soal-card';
        card.id = 'qccard-'+idx;

        let bodyHtml = '';

        // Q text (contenteditable)
        bodyHtml += `
            <div class="qc-q-text" 
                contenteditable="true"
                data-idx="${idx}"
                id="qtxt-${idx}"
                oninput="updateSoalTeks(this)"
                title="Klik untuk mengedit teks soal">
                ${escHtml(s.soal)}
                <span class="qc-edit-hint"><i class="fas fa-pen" style="font-size:0.6rem;"></i> Edit</span>
            </div>`;

        if (s.tipe === 'ISIAN') {
            bodyHtml += `
                <div class="qc-isian-box">
                    <div style="font-size:0.82rem;font-weight:700;color:#64748b;margin-bottom:6px;">
                        <i class="fas fa-pencil mr-1"></i> Soal Isian
                    </div>
                    <div>Kunci Jawaban: <span class="qc-isian-answer" id="qcisian-${idx}" contenteditable="true" oninput="updateIsianTeks(this, ${idx})" style="outline:none; cursor:text; min-width:30px;">${escHtml(s.kunci)}</span></div>
                </div>`;
        } else if (s.tipe === 'BENAR_SALAH') {
            const letters = ['a','b','c','d','e'];
            let optsHtml = '<div class="bs-container" style="display: flex; flex-direction: column; gap: 16px;">';
            const kunciArr = (s.kunci||'').split(',').map(k=>k.trim().toUpperCase());
            letters.forEach((l, i) => {
                const val = s[l];
                if (!val && val !== '0') return;
                const isBenar = kunciArr[i] === 'BENAR';
                const isSalah = kunciArr[i] === 'SALAH';
                optsHtml += `
                <div class="bs-card-item" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                    <div class="bs-statement" style="margin-bottom: 12px; font-size: 0.95rem; line-height: 1.5; color: #1e293b;" id="qcopt-${idx}-${l}">
                        <strong style="color:#0b57d0; margin-right:5px;">${i + 1}.</strong>
                        <span class="qc-opt-text" contenteditable="true" oninput="updateOpsiTeks(this, ${idx}, '${l}')" onclick="event.stopPropagation()" style="outline:none;">${escHtml(val)}</span>
                    </div>
                    <div class="bs-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label onclick="toggleKunciBS(${idx}, ${i}, 'BENAR')" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid ${isBenar ? '#16a34a' : '#e2e8f0'}; background: ${isBenar ? '#dcfce7' : '#f8fafc'}; color: ${isBenar ? '#15803d' : '#64748b'}; font-weight: ${isBenar ? '700' : '500'}; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-check"></i> <span>BENAR</span>
                        </label>
                        <label onclick="toggleKunciBS(${idx}, ${i}, 'SALAH')" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 2px solid ${isSalah ? '#dc2626' : '#e2e8f0'}; background: ${isSalah ? '#fee2e2' : '#f8fafc'}; color: ${isSalah ? '#b91c1c' : '#64748b'}; font-weight: ${isSalah ? '700' : '500'}; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-times"></i> <span>SALAH</span>
                        </label>
                    </div>
                </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        } else if (s.tipe === 'PG_KOMPLEKS') {
            const letters = ['a','b','c','d','e'];
            const kunciArr = (s.kunci||'').split(',').map(k=>k.trim().toUpperCase());
            let optsHtml = '<div class="qc-options">';
            letters.forEach(l => {
                const val = s[l];
                if (!val && val !== '0') return;
                const letter = l.toUpperCase();
                const isKey = kunciArr.includes(letter) || kunciArr.includes(val);
                optsHtml += `
                    <div class="qc-option-card ${isKey?'is-kunci':''}"
                        id="qcopt-${idx}-${l}"
                        onclick="toggleKunci(${idx}, '${letter}')"
                        title="Klik untuk toggle kunci jawaban">
                        <div class="qc-opt-label" style="background:none; border:none; color:inherit; font-size:1.2rem;">
                            ${isKey ? '<i class="fas fa-check-square" style="color:#0b57d0;"></i>' : '<i class="far fa-square"></i>'}
                        </div>
                        <div class="qc-opt-text" 
                            contenteditable="true"
                            oninput="updateOpsiTeks(this, ${idx}, '${l}')"
                            onclick="event.stopPropagation()">${escHtml(val)}</div>
                        <span class="kunci-tick"><i class="fas fa-check-circle"></i> Kunci</span>
                    </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        } else {
            const letters = ['a','b','c','d','e'];
            const kunciArr = [(s.kunci||'').toUpperCase()];
            let optsHtml = '<div class="qc-options">';
            letters.forEach(l => {
                const val = s[l];
                if (!val && val !== '0') return;
                const letter = l.toUpperCase();
                const isKey = kunciArr.includes(letter) || kunciArr.includes(val);
                optsHtml += `
                    <div class="qc-option-card ${isKey?'is-kunci':''}"
                        id="qcopt-${idx}-${l}"
                        onclick="toggleKunci(${idx}, '${letter}')"
                        title="Klik untuk toggle kunci jawaban (PG hanya satu)">
                        <div class="qc-opt-label">${letter}</div>
                        <div class="qc-opt-text" 
                            contenteditable="true"
                            oninput="updateOpsiTeks(this, ${idx}, '${l}')"
                            onclick="event.stopPropagation()">${escHtml(val)}</div>
                        <span class="kunci-tick"><i class="fas fa-check-circle"></i> Kunci</span>
                    </div>`;
            });
            optsHtml += '</div>';
            bodyHtml += optsHtml;
        }

        card.innerHTML = `
            <div class="qsc-header">
                <div>
                    <span class="qsc-number"><i class="fas fa-circle-question mr-1"></i> Soal ${idx+1}</span>
                    <span class="qsc-tipe ${tipeClass[s.tipe]||'tipe-pg'}">${tipeMap[s.tipe]||s.tipe}</span>
                </div>
                <button class="btn-add-asset" type="button" onclick="bukaModalAset(${idx})"><i class="fas fa-plus"></i> Aset</button>
            </div>
            <div class="qsc-body">
                ${renderAssetsHtml(s.assets, idx)}
                ${bodyHtml}
            </div>
        `;

        list.appendChild(card);
    });
}

function escHtml(str) {
    return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ==================== INLINE EDIT HANDLERS ====================
function updateSoalTeks(el) {
    const idx = parseInt(el.dataset.idx);
    const clone = el.cloneNode(true);
    const hint = clone.querySelector('.qc-edit-hint');
    if (hint) hint.remove();
    SOAL_DATA[idx].soal = clone.innerText.trim();
}

function updateOpsiTeks(el, idx, letter) {
    // Check if element exists before accessing innerText
    if (el) {
        SOAL_DATA[idx][letter] = el.innerText.trim();
    }
}

function updateIsianTeks(el, idx) {
    if (el) {
        SOAL_DATA[idx].kunci = el.innerText.trim();
    }
}

function toggleKunciBS(idx, statementIdx, value) {
    const s = SOAL_DATA[idx];
    let keys = (s.kunci||'').split(',').map(k=>k.trim().toUpperCase());
    while(keys.length <= statementIdx) keys.push('');
    keys[statementIdx] = value;
    s.kunci = keys.join(',');
    renderQC();
}

function toggleKunci(idx, letter) {
    const s = SOAL_DATA[idx];
    if (s.tipe === 'PG') {
        s.kunci = letter;
    } else if (s.tipe === 'PG_KOMPLEKS') {
        let keys = (s.kunci||'').split(',').map(k=>k.trim().toUpperCase()).filter(Boolean);
        const pos = keys.indexOf(letter);
        if (pos > -1) keys.splice(pos, 1); else keys.push(letter);
        s.kunci = keys.join(',');
    }
    renderQC(); // re-render to reflect change
}

// ==================== ASSET LOGIC ====================
function bukaModalAset(idx) {
    document.getElementById('modalAssetIdx').value = idx;
    document.getElementById('modalAset').style.display = 'flex';
    document.getElementById('assetType').value = 'local_image';
    gantiTipeAset();
}
function tutupModalAset() {
    document.getElementById('modalAset').style.display = 'none';
}
function gantiTipeAset() {
    const t = document.getElementById('assetType').value;
    const w = document.getElementById('assetInputWrapper');
    const p = document.getElementById('assetPreview');
    p.innerHTML = '<span style="color:#94a3b8; font-size:0.9rem;">Pratinjau akan muncul di sini</span>';
    
    if (t === 'local_image') {
        w.innerHTML = `<input type="file" id="assetFile" accept="image/png, image/jpeg, image/webp" class="form-control" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:8px;" onchange="previewLocalFile(this, 'image')">`;
    } else if (t === 'audio_mp3') {
        w.innerHTML = `<input type="file" id="assetFile" accept="audio/mp3" class="form-control" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:8px;" onchange="previewLocalFile(this, 'audio')">`;
    } else if (t === 'google_drive') {
        w.innerHTML = `<button type="button" class="btn" style="width:100%; padding:10px; border-radius:8px; background:#0ea5e9; color:#fff; font-weight:700; border:none; display:flex; align-items:center; justify-content:center; gap:8px;" onclick="bukaGooglePicker()"><i class="fab fa-google-drive"></i> Pilih dari Google Drive</button>`;
    } else {
        w.innerHTML = `<input type="text" id="assetUrl" placeholder="Masukkan URL / Link / ID..." class="form-control" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" oninput="previewUrl(this.value, '${t}')">`;
    }
}
function previewLocalFile(input, type) {
    const p = document.getElementById('assetPreview');
    if (input.files && input.files[0]) {
        const url = URL.createObjectURL(input.files[0]);
        if (type === 'image') p.innerHTML = `<img src="${url}" style="max-height:150px; border-radius:8px;">`;
        if (type === 'audio') p.innerHTML = `<audio controls style="width:100%;"><source src="${url}" type="audio/mpeg"></audio>`;
    }
}
function previewUrl(url, type) {
    const p = document.getElementById('assetPreview');
    if (!url) { p.innerHTML = ''; return; }
    if (type === 'external_image') p.innerHTML = `<img src="${url}" style="max-height:150px; border-radius:8px;">`;
    if (type === 'youtube_link') {
        let ytId = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([a-zA-Z0-9_-]{11})/);
        ytId = ytId ? ytId[1] : '';
        if(ytId) p.innerHTML = `<iframe width="100%" height="150" src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen style="border-radius:8px;"></iframe>`;
        else p.innerHTML = '<i>URL YouTube tidak valid</i>';
    }
    if (type === 'google_drive') {
        p.innerHTML = `<div style="padding:10px; background:#e0f2fe; border-radius:8px; color:#0369a1;"><i class="fab fa-google-drive"></i> GDrive Link: ${url}</div>`;
    }
}
function bukaGooglePicker() {
    let picker = new GaraPicker({
        developerKey: '{{ config('services.google.developer_key') }}',
        appId: '{{ config('services.google.app_id') }}',
        tokenUrl: '{{ route('guru.google.picker_token') }}',
        mode: 'images',
        onSelect: function(data) {
            uploadGoogleDriveAsset(data.id, data.name);
        }
    });
    picker.open();
}
function uploadGoogleDriveAsset(fileId, fileName) {
    const idx = document.getElementById('modalAssetIdx').value;
    const type = 'google_drive';
    
    Swal.fire({
        title: 'Memproses file...',
        text: 'Sedang menyalin file ke server.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    const formData = new FormData();
    formData.append('asset_type', type);
    formData.append('asset_source', fileId);
    formData.append('original_name', fileName);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("guru.ujian.save_asset") }}', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.close();
            if(!SOAL_DATA[idx].assets) SOAL_DATA[idx].assets = [];
            // Result becomes a local_image for reliable rendering
            SOAL_DATA[idx].assets.push({ asset_type: 'local_image', asset_source: res.asset_source, url: res.url, original_name: res.original_name });
            tutupModalAset(); 
            renderQC();
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).catch(err => {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    });
}
function simpanAset(e) {
    const idx = document.getElementById('modalAssetIdx').value;
    const type = document.getElementById('assetType').value;
    
    if (type === 'local_image' || type === 'audio_mp3') {
        const fileInput = document.getElementById('assetFile');
        if (!fileInput.files[0]) return Swal.fire('Error', 'Pilih file terlebih dahulu!', 'error');
        
        const formData = new FormData();
        formData.append('asset_type', type);
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        
        const btn = e.target;
        btn.innerHTML = 'Mengupload...';
        btn.disabled = true;
        
        fetch('{{ route("guru.ujian.save_asset") }}', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.innerHTML = 'Simpan Aset'; btn.disabled = false;
            if (res.success) {
                if(!SOAL_DATA[idx].assets) SOAL_DATA[idx].assets = [];
                SOAL_DATA[idx].assets.push({ asset_type: type, asset_source: res.asset_source, url: res.url, original_name: res.original_name });
                tutupModalAset(); renderQC();
            } else Swal.fire('Gagal', res.message, 'error');
        }).catch(err => {
            btn.innerHTML = 'Simpan Aset'; btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        });
    } else if (type === 'google_drive') {
        Swal.fire('Info', 'Silakan klik tombol "Pilih dari Google Drive"', 'info');
    } else {
        const urlInput = document.getElementById('assetUrl');
        if (!urlInput.value) return Swal.fire('Error', 'URL tidak boleh kosong!', 'error');
        if(!SOAL_DATA[idx].assets) SOAL_DATA[idx].assets = [];
        SOAL_DATA[idx].assets.push({ asset_type: type, asset_source: urlInput.value });
        tutupModalAset(); renderQC();
    }
}
function hapusAset(idx, assetIdx) {
    if(SOAL_DATA[idx].assets) {
        SOAL_DATA[idx].assets.splice(assetIdx, 1);
        renderQC();
    }
}

// ==================== KIRIM ====================
function konfirmasiKirim() {
    if (SOAL_DATA.length === 0) {
        Swal.fire({ icon:'warning', title:'Tidak Ada Soal', text:'Tidak ada soal untuk dikirim.', confirmButtonColor:'#0b57d0' });
        return;
    }

    // Sync contenteditable content before submit
    SOAL_DATA.forEach((s, idx) => {
        const txtEl = document.getElementById('qtxt-'+idx);
        if (txtEl) {
            // Clone element to strip the .qc-edit-hint child before getting text
            const clone = txtEl.cloneNode(true);
            const hint = clone.querySelector('.qc-edit-hint');
            if (hint) hint.remove();
            s.soal = clone.innerText.trim();
        }
        const letters = ['a','b','c','d','e'];
        letters.forEach(l => {
            const optEl = document.getElementById('qcopt-'+idx+'-'+l);
            if (optEl) {
                const textDiv = optEl.querySelector('.qc-opt-text');
                if (textDiv) s[l] = textDiv.innerText.trim();
            }
        });
    });

    Swal.fire({
        title: 'Kirim ke Operator?',
        html: `<b>${SOAL_DATA.length} soal</b> akan dikirim ke operator untuk diproses dan dijadwalkan dalam ujian.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0b57d0',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Ya, Kirim!',
        cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('fSoalList').value = JSON.stringify(SOAL_DATA);
            // Clear sessionStorage after submission
            sessionStorage.removeItem('soal_draft_manual');
            document.getElementById('formKirimQC').submit();
        }
    });
}

// ==================== INIT ====================
renderQC();
</script>
</body>
</html>
