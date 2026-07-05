@extends('layouts.siswa')

@section('title', $materi['judul_materi'])

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/belajar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/student/css/yt_desktop.css') }}" media="(min-width: 992px)">
    <style>
        @if ($has_video)
        
        @media (max-width: 991.98px) {
            .app-header {
                display: none !important;
            }
        }
        
        .video-wrapper {
            margin-top: 0;
        }
        @else
        
        .app-header .btn-back,
        .app-header a[href*="logout"],
        .app-header .header-right,
        .app-header .fa-sign-out-alt,
        .app-header .page-title,
        .app-header .page-subtitle { 
            display: none !important; 
        }
        
        .app-header .brand-wrapper, 
        .app-header .brand-text,
        .app-header a[href*="dashboard"] {
            display: flex !important;
            pointer-events: auto; 
        }
        @endif

        
        .sheet-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-sheet-action {
            background: #f0f2f5;
            border: none;
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #555;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }
        .btn-sheet-action:hover { background: #e4e6eb; color: #333; }
        .btn-sheet-action.close { color: #dc3545; background: #ffebeb; }
    </style>
@endpush

@section('content')



<div class="desktop-layout-container {{ !$has_video ? 'no-video' : '' }}">

    
    @if ($has_video)
    <div class="desktop-video-column">
        <div class="video-wrapper">
            <div class="video-container">
                <iframe 
                    id="yt-player"
                    src="https://www.youtube.com/embed/{{ $youtube_id }}?rel=0&modestbranding=1&playsinline=1&controls=1&showinfo=0" 
                    allowfullscreen 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen">
                </iframe>

                
                <div class="video-cover" id="videoCover" 
                     style="background-image: url('https://img.youtube.com/vi/{{ $youtube_id }}/maxresdefault.jpg');">
                    <div class="play-button-circle">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    
    <div class="desktop-content-column">
        <div class="content-body glass-card-strong" style="min-height: 80vh; {{ !$has_video ? 'margin-top: 0; border-radius: 0;' : 'border-radius: 20px;' }} padding: 20px;">
            
            
            <span class="badge bg-light text-primary mb-2">
                BAB {{ $formatNomorBab($materi['bab']) }} - Bagian {{ $materi['bagian'] }}
            </span>
            <h1 class="materi-title-lg">{{ htmlspecialchars($materi['judul_materi']) }}</h1>



            <div class="materi-desc">
                <p>Silakan pelajari materi ini dan akses bahan ajar melalui menu di bawah ini.</p>
            </div>

            
            <div class="resource-grid">
                
                
                @if (!empty($materi['link_ppt']))
                    @php 
                        $link_ppt = $materi['link_ppt'];
                        $is_gdrive_ppt = $isGoogleDriveLink($link_ppt);
                    @endphp
                    <button onclick="openResource('Slide PPT', '{{ $link_ppt }}', {{ $is_gdrive_ppt ? 'true' : 'false' }})" 
                       class="btn-resource glass-card ripple" style="border-radius: 16px;">
                        <i class="fas fa-file-powerpoint text-warning"></i>
                        <span>Slide PPT</span>
                    </button>
                @endif

                
                @if (!empty($materi['link_modul']))
                    @php 
                        $link_modul = $materi['link_modul'];
                        $is_gdrive_modul = $isGoogleDriveLink($link_modul);
                    @endphp
                    <button onclick="openResource('E-Modul', '{{ $link_modul }}', {{ $is_gdrive_modul ? 'true' : 'false' }})" 
                       class="btn-resource glass-card ripple" style="border-radius: 16px;">
                        <i class="fas fa-file-pdf text-primary"></i>
                        <span>Baca Modul</span>
                    </button>
                @endif

                
                @if (!empty($materi['link_notebook']))
                    <a href="{{ $materi['link_notebook'] }}" target="_blank" class="btn-resource glass-card ripple" style="border-radius: 16px;">
                        <i class="fas fa-book-open text-info"></i>
                        <span>Notebook</span>
                    </a>
                @endif

                
                @if (!empty($materi['link_tugas']))
                    @php 
                        $link_tugas = $materi['link_tugas'];
                        $is_gdrive_tugas = $isGoogleDriveLink($link_tugas);
                    @endphp
                    <button onclick="openResource('Modul Utama', '{{ $link_tugas }}', {{ $is_gdrive_tugas ? 'true' : 'false' }})" 
                       class="btn-resource glass-card ripple" style="border-radius: 16px;">
                        <i class="fas fa-book text-success"></i>
                        <span>E-Book</span>
                    </button>
                @endif

            </div>

            
            <a href="{{ route('student.materi.bab', ['bab' => $materi['bab']]) }}" class="btn-back-bottom glass-card ripple" hx-boost="false" style="border-radius: 16px; display: inline-flex; justify-content: center; width: 100%;">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Topik
            </a>
            
        </div>
    </div>
</div>


<div id="backdrop" class="offcanvas-backdrop" onclick="closeSheet()"></div>
<div id="bottomSheet" class="bottom-sheet">
    <div class="sheet-header">
        <h5 id="sheetTitle" class="sheet-title">Document Viewer</h5>
        <div class="sheet-actions">
            <a id="sheetExternalLink" href="#" target="_blank" class="btn-sheet-action" title="Buka di Tab Baru">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <button class="btn-sheet-action close" onclick="closeSheet()" title="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="sheet-content" id="sheetBody">
        
    </div>
</div>

@endsection

@push('js')
<script>

function initVideoPlayer() {
    const cover = document.getElementById('videoCover');
    const iframe = document.getElementById('yt-player');
    
    if(cover && iframe) {
        cover.addEventListener('click', function() {
            this.style.display = 'none';
            let src = iframe.src;
            if (src.indexOf('?') > -1) {
                iframe.src = src + "&autoplay=1";
            } else {
                iframe.src = src + "?autoplay=1";
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initVideoPlayer);

function openResource(title, url, isGDrive) {
    const backdrop = document.getElementById('backdrop');
    const sheet = document.getElementById('bottomSheet');
    const sheetTitle = document.getElementById('sheetTitle');
    const sheetBody = document.getElementById('sheetBody');
    const sheetExternalLink = document.getElementById('sheetExternalLink');

    
    sheetTitle.innerText = title;
    sheetExternalLink.href = url; 

    
    let embedUrl = url;
    if (isGDrive) {
        embedUrl = "https://docs.google.com/viewer?url=" + encodeURIComponent(url) + "&embedded=true";
        if(url.includes('drive.google.com') && url.includes('/view')) {
            embedUrl = url.replace('/view', '/preview');
        }
    }

    
    sheetBody.innerHTML = `<iframe src="${embedUrl}" allow="autoplay"></iframe>`;

    
    backdrop.classList.add('show');
    sheet.classList.add('show');
    document.body.style.overflow = 'hidden'; 
}

function closeSheet() {
    const backdrop = document.getElementById('backdrop');
    const sheet = document.getElementById('bottomSheet');
    const sheetBody = document.getElementById('sheetBody');

    backdrop.classList.remove('show');
    sheet.classList.remove('show');
    document.body.style.overflow = ''; 

    
    setTimeout(() => {
        sheetBody.innerHTML = '';
    }, 300);
}
</script>
@endpush
