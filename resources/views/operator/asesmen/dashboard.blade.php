@extends('layouts.operator', ['page_title' => 'Ruang Asesmen | Garuda Akademi', 'active_menu' => 'ujian', 'active_submenu' => 'asesmen_dashboard'])

@section('title', 'Ruang Asesmen | Garuda Akademi')

@section('content')
    <div class="content-wrapper">
        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-shield-alt mr-2 text-primary"></i>
                            Ruang Asesmen
                            <small class="badge badge-primary ml-2" style="font-size:0.6em;vertical-align:middle;">Operator
                                Panel</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Ruang Asesmen</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                
                <div class="row mb-3">
                    
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <div class="card card-outline mb-0 h-100" id="gateGuruCard" style="border-color:#dc3545;">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 1.05rem; font-weight: 600;">
                                    <i class="fas fa-door-closed mr-2" id="gateGuruIconEl"></i>
                                    <span id="gateGuruTitle">Gate Guru</span>
                                </h3>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <p class="text-muted small mb-3">
                                    <strong>Tertutup:</strong> Guru ditangguhkan.<br>
                                    <strong>Terbuka:</strong> Guru bisa input soal.
                                </p>
                                <button onclick="toggleGate('guru')" id="btnGateGuru"
                                    class="btn btn-danger btn-block mt-auto font-weight-bold">
                                    <i class="fas fa-key mr-2"></i><span id="btnGateGuruText">Buka Gate Guru</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <div class="card card-outline mb-0 h-100" id="gateSiswaCard" style="border-color:#dc3545;">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 1.05rem; font-weight: 600;">
                                    <i class="fas fa-door-closed mr-2" id="gateSiswaIconEl"></i>
                                    <span id="gateSiswaTitle">Gate Siswa</span>
                                </h3>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <p class="text-muted small mb-3">
                                    <strong>Tertutup:</strong> Siswa ditangguhkan.<br>
                                    <strong>Terbuka:</strong> Siswa bisa akses ujian.
                                </p>
                                <button onclick="toggleGate('siswa')" id="btnGateSiswa"
                                    class="btn btn-danger btn-block mt-auto font-weight-bold">
                                    <i class="fas fa-key mr-2"></i><span id="btnGateSiswaText">Buka Gate Siswa</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-4 col-md-12">
                        <div class="card card-outline card-primary mb-0 h-100">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-graduation-cap mr-2"></i> Jenis Asesmen Aktif
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-primary" id="badgeJenisAsesmen"
                                        style="font-size:0.85rem;padding:0.3rem 0.7rem;">Memuat...</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <p class="text-muted small mb-0">
                                            Jenis asesmen berlaku <strong>secara global</strong> untuk semua jadwal ujian
                                            baru yang dibuat.
                                        </p>
                                    </div>
                                    <div class="col-4 text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button onclick="setJenisAsesmen('ASTS')" id="btnASTS"
                                                class="btn btn-outline-primary font-weight-bold">ASTS</button>
                                            <button onclick="setJenisAsesmen('ASAS')" id="btnASAS"
                                                class="btn btn-outline-primary font-weight-bold">ASAS</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted">
                                        <strong>ASTS</strong> = Sumatif Tengah Semester &nbsp;|&nbsp;
                                        <strong>ASAS</strong> = Sumatif Akhir Semester
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="accordionAsesmen">

                    
                    <div class="card card-primary card-outline mb-3">
                        <div class="card-header" id="headingJadwal" style="cursor:pointer;"
                            onclick="toggleAccordion('collapseJadwal', this)">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                1. Jadwal Ujian
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-primary" id="badgeJadwal">Memuat...</span>
                                <button type="button" class="btn btn-tool">
                                    <i class="fas fa-chevron-down" id="iconJadwal"></i>
                                </button>
                            </div>
                        </div>
                        <div id="collapseJadwal" class="collapse show">
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <a href="{{ route('operator.asesmen.jadwal') }}" class="btn btn-primary btn-lg mr-2">
                                        <i class="fas fa-external-link-alt mr-1"></i> Kelola Jadwal Ujian
                                    </a>
                                    <small class="text-muted">Atur jadwal, generate token, dan buka portal ujian per mata
                                        pelajaran &amp; kelas.</small>
                                </div>

                                <div class="px-3 pb-3">
                                    <h6 class="text-muted font-weight-bold text-uppercase mb-2">
                                        <i class="fas fa-clock mr-1"></i> Jadwal Hari Ini
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Mapel</th>
                                                    <th>Kelas</th>
                                                    <th>Waktu</th>
                                                    <th class="text-center">Token</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="todayJadwalBody">
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">—</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="card card-warning card-outline mb-3">
                        <div class="card-header" id="headingSoal" style="cursor:pointer;"
                            onclick="toggleAccordion('collapseSoal', this)">
                            <h3 class="card-title">
                                <i class="fas fa-tasks mr-2"></i>
                                2. Monitoring Soal Ujian
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning" id="badgeSoal">Memuat...</span>
                                <button type="button" class="btn btn-tool">
                                    <i class="fas fa-chevron-right" id="iconSoal"></i>
                                </button>
                            </div>
                        </div>
                        <div id="collapseSoal" class="collapse">
                            <div class="card-body p-0">
                                <div class="px-3 pt-3 pb-2 d-flex flex-wrap align-items-center" style="gap:8px;">
                                    <a href="{{ route('operator.asesmen.monitoring') }}" class="btn btn-warning btn-sm font-weight-bold">
                                        <i class="fas fa-chart-bar mr-1"></i> Buka Monitor Lengkap
                                    </a>
                                    <button class="btn btn-sm btn-outline-warning" onclick="loadGuruSoalStatus()">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh Ringkasan
                                    </button>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Monitor status input soal dari seluruh guru. Validasi soal agar bisa dipakai ujian.
                                    </small>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Guru</th>
                                                <th>Mapel</th>
                                                <th>Kelas</th>
                                                <th class="text-center">Total Soal</th>
                                                <th class="text-center">Validated</th>
                                                <th class="text-center">Draft</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="guruSoalBody">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-3">Klik untuk memuat data
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="card card-success card-outline mb-3">
                        <div class="card-header" id="headingHasil" style="cursor:pointer;"
                            onclick="toggleAccordion('collapseHasil', this)">
                            <h3 class="card-title">
                                <i class="fas fa-poll-h mr-2"></i>
                                3. Hasil Ujian
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success" id="badgeHasil">Lihat Nilai</span>
                                <button type="button" class="btn btn-tool">
                                    <i class="fas fa-chevron-right" id="iconHasil"></i>
                                </button>
                            </div>
                        </div>
                        <div id="collapseHasil" class="collapse">
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <a href="{{ route('operator.asesmen.hasil') }}" class="btn btn-success btn-lg mr-2">
                                        <i class="fas fa-external-link-alt mr-1"></i> Lihat Hasil Ujian
                                    </a>
                                    <small class="text-muted">Akses nilai siswa per mata pelajaran dan kelas, serta cetak
                                        rekap.</small>
                                </div>

                                <div class="px-3 pb-3">
                                    <h6 class="text-muted font-weight-bold text-uppercase mb-2">
                                        <i class="fas fa-chart-bar mr-1"></i> Rekap Cepat Ujian Selesai
                                    </h6>
                                    <div class="row" id="quickStatsRow">
                                        <div class="col-md-3 col-6">
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-info elevation-1"><i
                                                        class="fas fa-calendar-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Sesi Ujian</span>
                                                    <span class="info-box-number" id="statSesi">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-success elevation-1"><i
                                                        class="fas fa-user-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Submit</span>
                                                    <span class="info-box-number" id="statSubmit">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-warning elevation-1"><i
                                                        class="fas fa-star"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Rata-rata</span>
                                                    <span class="info-box-number" id="statAvg">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-danger elevation-1"><i
                                                        class="fas fa-exclamation-triangle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Di Bawah KKM</span>
                                                    <span class="info-box-number" id="statBelowKkm">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    
    <div class="modal fade" id="modalValidasi" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-clipboard-check mr-2"></i> Validasi Soal</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <small class="text-muted">Guru / Mapel</small>
                        <p class="font-weight-bold mb-0 lead" id="modalValidasiInfo">---</p>
                    </div>
                    <p>Apakah Anda yakin ingin memvalidasi semua soal dari guru/mapel ini?</p>
                    <p class="text-muted small">Soal yang divalidasi akan tersedia untuk ujian siswa.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="doValidasi()">
                        <i class="fas fa-check mr-1"></i> Validasi Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            user-select: none;
        }

        .card-header:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .status-berlangsung {
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(40, 167, 69, 0);
            }
        }
    </style>

@endsection

@push('scripts')
    <script>
        const API = '{{ route("operator.api.asesmen") }}';

        async function post(action, data = {}) {
            const fd = new FormData();
            fd.append('action', action);
            
            
            
            fd.append('_token', '{{ csrf_token() }}');

            for (const k in data) fd.append(k, data[k]);
            const r = await fetch(API, {
                method: 'POST', body: fd, headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            return r.json();
        }

        
        function toggleAccordion(id, headerEl) {
            const el = document.getElementById(id);
            const icons = { collapseJadwal: 'iconJadwal', collapseSoal: 'iconSoal', collapseHasil: 'iconHasil' };
            const isOpen = el.classList.contains('show');

            
            ['collapseJadwal', 'collapseSoal', 'collapseHasil'].forEach(sid => {
                document.getElementById(sid).classList.remove('show');
                const icoId = icons[sid];
                if (icoId) { document.getElementById(icoId).className = 'fas fa-chevron-right'; }
            });

            if (!isOpen) {
                el.classList.add('show');
                const myIcon = icons[id];
                if (myIcon) document.getElementById(myIcon).className = 'fas fa-chevron-down';

                if (id === 'collapseSoal') loadGuruSoalStatus();
                if (id === 'collapseHasil') loadQuickStats();
            }
        }

        
        let GATE_GURU_STATUS = 0;
        let GATE_SISWA_STATUS = 0;
        let JENIS_ASESMEN = 'ASTS';

        async function loadGateStatus() {
            const res = await post('get_gate_status');
            if (!res.success) return;
            applyGateUI('guru', res.data.status_pintu == 1);
            applyGateUI('siswa', res.data.status_pintu_siswa == 1);
            applyJenisUI(res.data.jenis_asesmen || 'ASTS');
        }

        function applyGateUI(target, isOpen) {
            if (target === 'guru') { GATE_GURU_STATUS = isOpen ? 1 : 0; }
            else { GATE_SISWA_STATUS = isOpen ? 1 : 0; }

            const prefix = target === 'guru' ? 'gateGuru' : 'gateSiswa';
            const card = document.getElementById(prefix + 'Card');
            const icon = document.getElementById(prefix + 'IconEl');
            const title = document.getElementById(prefix + 'Title');
            const btn = document.getElementById('btn' + prefix.charAt(0).toUpperCase() + prefix.slice(1));
            const btnText = document.getElementById('btn' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'Text');

            const labelTarget = target === 'guru' ? 'Guru' : 'Siswa';

            if (isOpen) {
                card.style.borderColor = '#28a745';
                icon.className = 'fas fa-door-open mr-2 text-success';
                title.innerHTML = `Gate ${labelTarget} <span class="badge badge-success ml-2">TERBUKA</span>`;
                btn.className = 'btn btn-success btn-block mt-auto font-weight-bold';
                btnText.textContent = `Tutup Gate ${labelTarget}`;
            } else {
                card.style.borderColor = '#dc3545';
                icon.className = 'fas fa-door-closed mr-2 text-danger';
                title.innerHTML = `Gate ${labelTarget} <span class="badge badge-danger ml-2">TERTUTUP</span>`;
                btn.className = 'btn btn-danger btn-block mt-auto font-weight-bold';
                btnText.textContent = `Buka Gate ${labelTarget}`;
            }
        }

        function applyJenisUI(jenis) {
            JENIS_ASESMEN = jenis;
            const badge = document.getElementById('badgeJenisAsesmen');
            const btnASTS = document.getElementById('btnASTS');
            const btnASAS = document.getElementById('btnASAS');

            badge.textContent = jenis === 'ASTS' ? 'ASTS — Tengah Semester' : 'ASAS — Akhir Semester';
            badge.className = 'badge badge-primary';

            if (jenis === 'ASTS') {
                btnASTS.className = 'btn btn-primary font-weight-bold';
                btnASAS.className = 'btn btn-outline-primary font-weight-bold';
            } else {
                btnASTS.className = 'btn btn-outline-primary font-weight-bold';
                btnASAS.className = 'btn btn-primary font-weight-bold';
            }
        }

        async function toggleGate(target) {
            const currentStatus = target === 'guru' ? GATE_GURU_STATUS : GATE_SISWA_STATUS;
            const newStatus = currentStatus ? 0 : 1;
            const action = newStatus ? `Buka Gate ${target === 'guru' ? 'Guru' : 'Siswa'}?` : `Tutup Gate ${target === 'guru' ? 'Guru' : 'Siswa'}?`;

            let msg = '';
            if (target === 'guru') {
                msg = newStatus ? 'Guru dapat menginput soal.' : 'Guru <strong>tidak dapat</strong> menginput soal.';
            } else {
                msg = newStatus ? 'Siswa dapat mengakses ujian.' : 'Siswa <strong>tidak dapat</strong> mengakses ujian.';
            }

            const conf = await Swal.fire({
                title: action,
                html: msg,
                icon: newStatus ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: action.replace('?', ''),
                confirmButtonColor: newStatus ? '#28a745' : '#dc3545',
                cancelButtonText: 'Batal'
            });
            if (!conf.isConfirmed) return;

            const res = await post('toggle_gate', { status: newStatus, target: target });
            if (res.success) {
                applyGateUI(target, newStatus === 1);
                Swal.fire({
                    icon: 'success',
                    title: newStatus ? `Gate ${target === 'guru' ? 'Guru' : 'Siswa'} Terbuka!` : `Gate ${target === 'guru' ? 'Guru' : 'Siswa'} Tertutup.`,
                    toast: true, position: 'top-end', timer: 2000, showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        async function setJenisAsesmen(jenis) {
            if (jenis === JENIS_ASESMEN) return;

            const label = jenis === 'ASTS' ? 'ASTS — Sumatif Tengah Semester' : 'ASAS — Sumatif Akhir Semester';
            const conf = await Swal.fire({
                title: 'Ubah Jenis Asesmen?',
                html: `Semua jadwal ujian baru akan menggunakan <strong>${label}</strong>.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            });
            if (!conf.isConfirmed) return;

            const res = await post('update_jenis_asesmen', { jenis });
            if (res.success) {
                applyJenisUI(jenis);
                Swal.fire({ icon: 'success', title: 'Jenis asesmen diubah ke ' + jenis, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        
        async function loadTodayJadwal() {
            const res = await post('get_jadwal_list');
            const tbody = document.getElementById('todayJadwalBody');

            if (!res.success || !res.data.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-inbox mr-1"></i> Belum ada jadwal hari ini.</td></tr>`;
                document.getElementById('badgeJadwal').textContent = '0 Jadwal';
                return;
            }

            const today = new Date().toISOString().split('T')[0];
            const todayRows = res.data.filter(r => r.tanggal_ujian === today);
            document.getElementById('badgeJadwal').textContent = todayRows.length + ' Jadwal Hari Ini';

            if (!todayRows.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada jadwal untuk hari ini.</td></tr>`;
                return;
            }

            tbody.innerHTML = todayRows.map(r => {
                const statusMap = {
                    'belum_token': '<span class="badge badge-secondary">Belum Token</span>',
                    'akan_datang': '<span class="badge badge-info">Akan Datang</span>',
                    'berlangsung': '<span class="badge badge-success status-berlangsung">Sedang Berlangsung</span>',
                    'selesai': '<span class="badge badge-dark">Selesai</span>'
                };
                const tokenHtml = r.token
                    ? `<code class="font-weight-bold text-primary" style="letter-spacing:0.2rem;font-size:1rem;">${r.token}</code>`
                    : `<span class="text-muted">—</span>`;

                const portalUrl = `{{ route('operator.asesmen.portal') }}?id_jadwal=${r.id_jadwal}`;
                const portalBtn = (r.status_waktu === 'berlangsung' || r.token)
                    ? `<a href="${portalUrl}" class="btn btn-xs btn-success"><i class="fas fa-door-open mr-1"></i>Portal</a>`
                    : `<a href="${portalUrl}" class="btn btn-xs btn-outline-secondary"><i class="fas fa-eye mr-1"></i>Lihat</a>`;

                return `<tr>
                <td><strong>${r.mapel || '—'}</strong></td>
                <td><span class="badge badge-secondary">${r.kelas || '—'}</span></td>
                <td><small>${r.jam_mulai} – ${r.jam_selesai}</small></td>
                <td class="text-center">${tokenHtml}</td>
                <td class="text-center">${statusMap[r.status_waktu] || r.status_waktu}</td>
                <td class="text-center">${portalBtn}</td>
            </tr>`;
            }).join('');
        }

        
        let currentValidasiMapel = '';

        async function loadGuruSoalStatus() {
            const tbody = document.getElementById('guruSoalBody');
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</td></tr>`;

            const res = await post('get_guru_soal_status');
            if (!res.success || !res.data.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3"><i class="fas fa-inbox mr-1"></i> Belum ada data soal dari guru.</td></tr>`;
                document.getElementById('badgeSoal').textContent = '0 Guru';
                return;
            }

            const rows = res.data.filter(r => r.mapel); 
            document.getElementById('badgeSoal').textContent = rows.length + ' Entri';

            tbody.innerHTML = rows.map(r => {
                const validated = parseInt(r.soal_validated) || 0;
                const total = parseInt(r.total_soal) || 0;
                const draft = parseInt(r.soal_draft) || 0;
                const isComplete = total > 0 && validated === total;

                const statusBadge = isComplete
                    ? `<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Lengkap</span>`
                    : total > 0
                        ? `<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Perlu Validasi</span>`
                        : `<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Belum Input</span>`;

                const guruId = r.guru_user_id || 0;
                const aksiBtn = (!isComplete && total > 0)
                    ? `<button onclick="bukaValidasi('${r.guru_nama}', '${r.mapel}', '${r.kelas}', ${guruId})" class="btn btn-xs btn-outline-primary mr-1"><i class="fas fa-check-circle mr-1"></i>Validasi</button>`
                    : `<i class="fas fa-check-circle text-success mr-1" title="Sudah Divalidasi"></i>`;

                const qcBtn = total > 0
                    ? `<a href="{{ route('operator.asesmen.qc_soal') }}?guru_id=${guruId}&mapel=${encodeURIComponent(r.mapel||'')}&kelas=${encodeURIComponent(r.kelas||'')}&guru_nama=${encodeURIComponent(r.guru_nama||'')}"
                          target="_blank"
                          class="btn btn-xs btn-outline-success mr-1" title="QC Preview soal">
                          <i class="fas fa-search mr-1"></i>QC
                       </a>`
                    : '';

                const hapusBtn = total > 0
                    ? `<button onclick="hapusPaketSoal('${(r.mapel || '').replace(/'/g, "\\'")}', '${(r.kelas || '').replace(/'/g, "\\'")}', ${guruId}, '${(r.guru_nama || '').replace(/'/g, "\\'")}')" class="btn btn-xs btn-outline-danger" title="Hapus semua soal paket ini"><i class="fas fa-trash"></i></button>`
                    : `<span class="text-muted small">—</span>`;

                return `<tr>
                <td>${r.guru_nama || '<em class="text-muted">Unknown</em>'}</td>
                <td><strong>${r.mapel || '—'}</strong></td>
                <td>${r.kelas || '—'}</td>
                <td class="text-center">${total}</td>
                <td class="text-center"><span class="text-success font-weight-bold">${validated}</span></td>
                <td class="text-center"><span class="text-warning">${draft}</span></td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center text-nowrap">${qcBtn}${aksiBtn}${hapusBtn}</td>
            </tr>`;
            }).join('');
        }

        let currentValidasiPaket = {};

        function bukaValidasi(guru, mapel, kelas, guruId) {
            currentValidasiPaket = { mapel, kelas, guru_id: guruId };
            document.getElementById('modalValidasiInfo').textContent = `${guru} — ${mapel} / Kelas ${kelas}`;
            $('#modalValidasi').modal('show');
        }

        async function doValidasi() {
            const res = await post('validate_paket_soal', currentValidasiPaket);
            $('#modalValidasi').modal('hide');
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                loadGuruSoalStatus();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }

        async function hapusPaketSoal(mapel, kelas, idGuru, guruNama) {
            const conf = await Swal.fire({
                title: 'Hapus Paket Soal?',
                html: `Semua soal <strong>${mapel}</strong> — Kelas <strong>${kelas}</strong><br>
                       oleh guru <strong>${guruNama}</strong> akan dihapus permanen!<br>
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

            const res = await post('delete_paket_soal', { mapel, kelas, id_guru: idGuru });
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                loadGuruSoalStatus();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: res.message });
            }
        }

        
        async function loadQuickStats() {
            const res = await post('get_rekap_nilai');
            if (!res.success) return;
            const data = res.data || [];

            document.getElementById('statSubmit').textContent = data.length;

            const jadwalSet = new Set();
            let totalScore = 0, below = 0;
            data.forEach(r => {
                totalScore += parseFloat(r.skor_akhir);
                if (parseFloat(r.skor_akhir) < 75) below++;
            });
            if (data.length) {
                document.getElementById('statAvg').textContent = (totalScore / data.length).toFixed(1);
                document.getElementById('statBelowKkm').textContent = below;
            }

            const resSesi = await post('get_jadwal_list');
            if (resSesi.success) {
                const done = (resSesi.data || []).filter(r => r.status_waktu === 'selesai');
                document.getElementById('statSesi').textContent = done.length;
            }
        }

        
        window.onload = function () {
            loadGateStatus();
            loadTodayJadwal();
            loadGuruSoalStatus();
            loadQuickStats();
        };
    </script>
@endpush