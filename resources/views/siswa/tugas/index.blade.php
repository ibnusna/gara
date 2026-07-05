@extends('layouts.siswa')

@section('title', 'Ruang Tugas')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/student/css/dashboard_v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/student/tugas_style.css') }}?v={{ time() }}">
    <style>
        
        .border-danger-soft {
            border: 1px solid #feb2b2;
        }

        .bg-danger-soft {
            background-color: #fff5f5;
        }

        .btn-susulan {
            background-color: #ed8936;
            
            color: white;
            border: none;
            transition: all 0.2s;
        }

        .btn-susulan:hover {
            background-color: #dd6b20;
            color: white;
            transform: translateY(-2px);
        }

        .countdown-timer {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #e53e3e;
            font-size: 0.9rem;
        }

        
        .app-toast-container {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #323232;
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transition: all 0.4s;
        }

        .app-toast-container.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .app-toast-success .app-toast-icon {
            color: #2ecc71;
        }

        .app-toast-error .app-toast-icon {
            color: #e74c3c;
        }
    </style>
@endpush

@push('js')
    
    <script src="{{ asset('assets/student/js/search_filter.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', initTugasSearch);
    </script>
@endpush

@section('content')

    <div class="page-sub-header">
        <div class="header-left">
            <a href="{{ route('student.dashboard') }}" class="btn-back" hx-boost="false"><i
                    class="fas fa-arrow-left"></i></a>
            <div>
                <h1 class="page-title">Ruang Tugas</h1>
                <span class="page-subtitle">{{ session('nama_mapel', 'Mata Pelajaran') }}</span>
            </div>
        </div>
    </div>

    
    
    <div id="tugas-search-container"></div>

    <div class="tabs-container glass-card mb-4" style="border-radius: 16px; padding: 8px;">
        <div class="tab-item active ripple" style="border-radius: 12px;" onclick="switchTab('aktif', this)">
            Tugas Baru
            @if(count($tugas_aktif) > 0)
                <span id="badge-aktif" class="badge bg-primary rounded-pill ms-1">{{ count($tugas_aktif) }}</span>
            @endif
        </div>
        <div class="tab-item ripple" style="border-radius: 12px;" onclick="switchTab('selesai', this)">
            Selesai
        </div>
        <div class="tab-item ripple" style="border-radius: 12px;" onclick="switchTab('terlewat', this)">
            Terlewat
            @if(count($tugas_terlewat) > 0)
                <span id="badge-terlewat" class="badge bg-danger rounded-pill ms-1">{{ count($tugas_terlewat) }}</span>
            @endif
        </div>
    </div>

    <div class="container py-3" style="padding-bottom: 100px;">

        
        <div id="tab-aktif" class="tab-content active">
            @if(empty($tugas_aktif))
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="Empty">
                    <p>Tidak ada tugas aktif. Kerja bagus!</p>
                </div>
            @else
                @foreach($tugas_aktif as $t)
                    @include('siswa.tugas.partials.task_card', ['t' => $t, 'type' => 'aktif'])
                @endforeach
            @endif
        </div>

        
        <div id="tab-selesai" class="tab-content">
            @if(empty($tugas_selesai))
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486803.png" alt="Empty">
                    <p>Belum ada riwayat tugas.</p>
                </div>
            @else
                @foreach($tugas_selesai as $t)
                    @include('siswa.tugas.partials.task_card', ['t' => $t, 'type' => 'selesai'])
                @endforeach
            @endif
        </div>

        
        <div id="tab-terlewat" class="tab-content">
            @if(empty($tugas_terlewat))
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty">
                    <p>Tidak ada tugas terlewat. Pertahankan!</p>
                </div>
            @else
                <div class="alert alert-warning small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Tugas di sini sudah melewati batas waktu.
                </div>
                @foreach($tugas_terlewat as $t)
                    @include('siswa.tugas.partials.task_card', ['t' => $t, 'type' => 'terlewat'])
                @endforeach
            @endif
        </div>

    </div>
@endsection

@section('modals')
    
    <div id="backdrop-view" class="offcanvas-backdrop" onclick="closeSheetView()"></div>
    <div id="sheet-view" class="bottom-sheet">
        <div class="sheet-handle-bar"></div>
        <div class="sheet-header">
            <h5 id="viewTitle" class="sheet-title m-0">Lampiran</h5>
            <div class="d-flex gap-2">
                <a id="btnExternalOpen" href="#" target="_blank" class="btn-icon-circle primary"><i
                        class="fas fa-external-link-alt"></i></a>
                <button class="btn-icon-circle" onclick="closeSheetView()"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="sheet-content" id="viewBody" style="background: #f8f9fa; height: 100%; min-height: 200px; display: flex; align-items: center; justify-content: center;"></div>
    </div>

    
    <div id="backdrop-form" class="offcanvas-backdrop" onclick="closeSheetForm()"></div>
    <div id="sheet-form" class="bottom-sheet" style="height: auto; max-height: 90vh;">
        <div class="sheet-handle-bar"></div>
        <div class="sheet-header">
            <h5 class="sheet-title m-0">Kumpulkan Tugas</h5>
            <button class="btn-icon-circle" onclick="closeSheetForm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="sheet-content" style="background: white; padding: 20px;">
            <form id="form-submit-tugas" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tugas_id" id="input_tugas_id">

                
                <div id="late-warning" class="alert alert-danger py-2 small d-none mb-3">
                    <i class="fas fa-clock me-1"></i> Anda mengumpulkan terlambat. Sistem akan mencatat waktu keterlambatan.
                </div>

                
                <div class="sheet-form-group mb-3">
                    <label class="sheet-form-label mb-2">Metode Pengumpulan</label>
                    <div class="d-flex gap-3">
                        <label class="radio-card active" onclick="toggleMetode('link')">
                            <input type="radio" name="metode" value="link" checked onchange="toggleMetode('link')">
                            <i class="fas fa-link"></i> Link / Tautan
                        </label>
                        <label class="radio-card" onclick="toggleMetode('file')">
                            <input type="radio" name="metode" value="file" onchange="toggleMetode('file')">
                            <i class="fas fa-file-upload"></i> Upload File
                        </label>
                    </div>
                </div>

                
                <div id="group-link" class="sheet-form-group">
                    <label class="sheet-form-label">Link Hasil Pekerjaan</label>
                    <input type="url" name="link_pengumpulan" id="input_link" class="sheet-form-input"
                        placeholder="https://...">
                    <div class="form-text mt-2"><i class="fas fa-info-circle"></i> Pastikan link bersifat Publik (Google
                        Drive/Docs).</div>
                </div>

                
                <div id="group-file" class="sheet-form-group d-none">
                    <label class="sheet-form-label">Upload File (Max 5MB)</label>
                    <div class="file-upload-box">
                        <input type="file" name="file_upload" id="input_file"
                            accept=".pdf, .doc, .docx, .ppt, .pptx, .jpg, .jpeg, .png, .webp">
                        <div class="file-content">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                            <p class="m-0 small text-muted">Klik untuk pilih file</p>
                            <p class="m-0 x-small text-muted mt-1">PDF, Word, PPT, Gambar</p>
                        </div>
                    </div>
                    <div id="file-name-display" class="mt-2 small text-primary font-weight-bold"></div>
                </div>

                <div class="sheet-form-group">
                    <label class="sheet-form-label">Catatan Tambahan</label>
                    <textarea name="catatan" id="input_catatan" class="sheet-form-input" rows="3"
                        placeholder="Pesan untuk guru..."></textarea>
                </div>

                <button type="submit" id="btn-submit-final" class="btn-app primary btn-app-block mb-3">
                    <i class="fas fa-paper-plane me-2"></i> Kirim Tugas
                </button>

                
                <button type="button" id="btn-delete-sub" class="btn btn-outline-danger btn-block w-100 d-none"
                    onclick="deleteSubmission()">
                    <i class="fas fa-trash me-2"></i> Batalkan Pengumpulan
                </button>
            </form>
        </div>
    </div>

    
    <div id="appToast" class="app-toast-container">
        <i class="fas fa-check-circle app-toast-icon"></i>
        <span id="toastMessage">Berhasil disimpan</span>
    </div>

@endsection

@push('js')
    <script>
        
        function updateTimers() {
            const now = new Date().getTime();
            document.querySelectorAll('.countdown-timer').forEach(el => {
                const deadline = new Date(el.dataset.deadline).getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    el.innerHTML = "Waktu Habis!";
                    el.closest('.app-card').classList.add('opacity-50');
                    if (!el.dataset.reloaded) {
                        el.dataset.reloaded = "true";
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    let timeStr = "";
                    if (days > 0) timeStr += days + "h ";
                    timeStr += hours + "j " + minutes + "m " + seconds + "d";
                    el.innerHTML = '<i class="far fa-clock me-1"></i> ' + timeStr;
                }
            });
        }
        setInterval(updateTimers, 1000);
        updateTimers();

        
        function switchTab(tabName, element) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            element.classList.add('active');
        }

        function toggleMetode(val) {
            document.querySelectorAll('.radio-card').forEach(el => el.classList.remove('active'));
            const selectedRadio = document.querySelector(`input[name="metode"][value="${val}"]`);
            if (selectedRadio) selectedRadio.closest('.radio-card').classList.add('active');

            if (val === 'link') {
                document.getElementById('group-link').classList.remove('d-none');
                document.getElementById('group-file').classList.add('d-none');
                document.getElementById('input_link').required = true;
                document.getElementById('input_file').value = '';
            } else {
                document.getElementById('group-link').classList.add('d-none');
                document.getElementById('group-file').classList.remove('d-none');
                document.getElementById('input_link').required = false;
            }
        }

        document.getElementById('input_file').addEventListener('change', function () {
            const file = this.files[0];
            const name = file ? file.name : '';
            document.getElementById('file-name-display').innerText = name ? 'File: ' + name : '';

            if (file && file.size > 5 * 1024 * 1024) {
                alert('File terlalu besar! Maksimal 5MB.');
                this.value = '';
                document.getElementById('file-name-display').innerText = '';
                return;
            }

            
            let previewContainer = document.getElementById('media-preview-container');
            if(!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.id = 'media-preview-container';
                previewContainer.className = 'mt-3 text-center';
                document.getElementById('group-file').appendChild(previewContainer);
            }
            previewContainer.innerHTML = '';
            
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: auto; border-radius: 12px; border: 2px dashed #0d6efd; object-fit: cover; aspect-ratio: 16/9; margin-top: 10px;">`;
                }
                reader.readAsDataURL(file);
            }
        });

        function openSubmitForm(id, isLateMode = false, existingLink = '', existingNote = '', existingMetode = 'link') {
            document.getElementById('input_tugas_id').value = id;
            document.getElementById('input_catatan').value = existingNote;

            if (!existingMetode) existingMetode = 'link';

            const radio = document.querySelector(`input[name="metode"][value="${existingMetode}"]`);
            if (radio) {
                radio.checked = true;
                toggleMetode(existingMetode);
            }

            if (existingMetode === 'link') {
                document.getElementById('input_link').value = existingLink;
                document.getElementById('file-name-display').innerText = '';
                
                let previewContainer = document.getElementById('media-preview-container');
                if (previewContainer) previewContainer.innerHTML = '';
            } else {
                document.getElementById('input_link').value = '';
                document.getElementById('file-name-display').innerText = 'File saat ini: ' + existingLink;

                let previewContainer = document.getElementById('media-preview-container');
                if(!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.id = 'media-preview-container';
                    previewContainer.className = 'mt-3 text-center';
                    document.getElementById('group-file').appendChild(previewContainer);
                }
                previewContainer.innerHTML = '';

                if (existingLink && existingLink.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
                    previewContainer.innerHTML = `<img src="/lms/uploads/tugas/${existingLink}" style="width: 100%; height: auto; border-radius: 12px; border: 2px dashed #0d6efd; object-fit: cover; aspect-ratio: 16/9; margin-top: 10px;">`;
                }
            }

            const warning = document.getElementById('late-warning');
            const btn = document.getElementById('btn-submit-final');
            const btnDel = document.getElementById('btn-delete-sub');

            if (isLateMode) {
                warning.classList.remove('d-none');
                btn.classList.remove('primary');
                btn.classList.add('btn-susulan');
                btn.innerHTML = '<i class="fas fa-history me-2"></i> Kirim Susulan';
            } else {
                warning.classList.add('d-none');
                btn.classList.remove('btn-susulan');
                btn.classList.add('primary');
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Kirim Tugas';
            }

            if (existingLink) {
                btnDel.classList.remove('d-none');
            } else {
                btnDel.classList.add('d-none');
            }

            document.getElementById('backdrop-form').classList.add('show');
            document.getElementById('sheet-form').classList.add('show');
        }

        function closeSheetForm() {
            document.getElementById('backdrop-form').classList.remove('show');
            document.getElementById('sheet-form').classList.remove('show');
        }

        
        document.getElementById('form-submit-tugas').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch("{{ route('student.tugas.submit') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        closeSheetForm();
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                        
                    } else {
                        showToast(data.message, 'error');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Gagal terhubung ke server', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });

        
        function deleteSubmission() {
            if (!confirm('Apakah Anda yakin ingin membatalkan pengumpulan? Anda harus mengumpulkan ulang.')) return;

            const tugasId = document.getElementById('input_tugas_id').value;
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('tugas_id', tugasId);

            fetch("{{ route('student.tugas.delete') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        closeSheetForm();
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        }

        
        function openLampiran(title, url) {
            const viewBody = document.getElementById('viewBody');
            document.getElementById('viewTitle').innerText = title;
            document.getElementById('btnExternalOpen').href = url;

            let embedUrl = url;
            if (url.includes('youtube') || url.includes('youtu.be')) {
                const id = url.match(/(?:v=|youtu\.be\/)([^&]+)/);
                if (id) embedUrl = `https://www.youtube.com/embed/${id[1]}`;
            } else if (url.includes('drive.google.com') || url.includes('docs.google.com')) {
                embedUrl = url.replace('/view', '/preview');
            }

            viewBody.innerHTML = `<iframe src="${embedUrl}" style="width:100%; height:100%; border:none;"></iframe>`;
            document.getElementById('backdrop-view').classList.add('show');
            document.getElementById('sheet-view').classList.add('show');
        }

        function closeSheetView() {
            document.getElementById('backdrop-view').classList.remove('show');
            document.getElementById('sheet-view').classList.remove('show');
            document.getElementById('viewBody').innerHTML = '';
        }

        function showToast(msg, type) {
            const t = document.getElementById('appToast');
            document.getElementById('toastMessage').innerText = msg;
            t.className = 'app-toast-container show ' + (type === 'error' ? 'app-toast-error' : 'app-toast-success');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        
        function markAsRead(tugasId) {
            const btn = document.getElementById('btn-read-' + tugasId);
            const originalHtml = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('tugas_id', tugasId);
            formData.append('metode', 'manual');
            formData.append('catatan', '');

            fetch("{{ route('student.tugas.submit') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast('Tugas ditandai sudah dibaca', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message, 'error');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Gagal terhubung ke server', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

    </script>
@endpush