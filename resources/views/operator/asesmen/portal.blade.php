@extends('layouts.operator', ['page_title' => 'Portal Ujian | Garuda Akademi', 'active_menu' => 'ujian', 'active_submenu' => 'jadwal_ujian'])

@section('title', 'Portal Ujian | Garuda Akademi')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-door-open mr-2 text-danger"></i>
                            Portal Ujian Aktif
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('operator.asesmen.jadwal') }}">Jadwal</a></li>
                            <li class="breadcrumb-item active">Portal</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">

                    
                    <div class="col-lg-4 col-md-5 mb-3">
                        <div class="card card-outline card-danger h-100">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Status Sesi
                                    Ujian</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h5 class="font-weight-bold text-uppercase mapel-title" id="lblMapel">Memuat...</h5>
                                    <span class="badge badge-secondary mapel-kelas" id="lblKelas">Kelas -</span>
                                </div>

                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                        <b>Jadwal</b> <a class="float-right text-dark" id="lblJadwal">-</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Waktu / Durasi</b> <a class="float-right text-dark" id="lblWaktu">- / - mnt</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Siswa Submit</b> <a class="float-right text-success font-weight-bold"
                                            id="lblSubmit">0 / 0</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Status</b> <a class="float-right" id="lblStatus">-</a>
                                    </li>
                                </ul>

                                <div class="text-center mt-4">
                                    <h6 class="text-muted font-weight-bold text-uppercase mb-2">Token Akses Ujian</h6>
                                    <div class="p-3 bg-light rounded" style="border: 2px dashed #ccc;">
                                        <h2 class="mb-0 text-primary token-display" id="lblToken">-----</h2>
                                    </div>
                                    <button onclick="regenToken()" class="btn btn-outline-danger btn-sm mt-3 w-100">
                                        <i class="fas fa-redo-alt mr-1"></i> Regenerate Token Sesi Ini
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer p-2 text-center">
                                <a href="{{ route('operator.asesmen.hasil') }}?id_jadwal={{ $id_jadwal }}"
                                    class="btn btn-primary btn-block">
                                    <i class="fas fa-poll-h mr-2"></i> Lihat Hasil Sementara
                                </a>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-8 col-md-7 mb-3">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Peserta Submit
                                    Terbaru</h3>
                                <div class="card-tools">
                                    <span class="badge badge-success"><i class="fas fa-circle blink-icon mr-1"></i>
                                        Live</span>
                                    <button onclick="loadDataPortal()" class="btn btn-tool" title="Refresh">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-2 bg-light border-bottom text-muted small text-center">
                                    Data ini auto-refresh setiap 10 detik. Menampilkan 10 peserta yang terakhir mensubmit
                                    soal.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover m-0">
                                        <thead>
                                            <tr>
                                                <th>NIS</th>
                                                <th>Nama Peserta</th>
                                                <th class="text-center">Mulai</th>
                                                <th class="text-center">Selesai</th>
                                                <th class="text-center">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pesertaBody">
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted"><i
                                                        class="fas fa-spinner fa-spin mr-2"></i> Menghubungkan ke server...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="{{ route('operator.asesmen.hasil') }}?id_jadwal={{ $id_jadwal }}"
                                    class="text-muted small">Lihat seluruh peserta <i
                                        class="fas fa-arrow-right ml-1"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <style>
        .token-display {
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 0.3rem;
            font-weight: 800;
            font-size: 2.2rem;
        }

        .mapel-title {
            font-size: 1.4rem;
            color: #0b57d0;
        }

        .mapel-kelas {
            font-size: 1rem;
            padding: 0.4em 0.8em;
        }

        .blink-icon {
            animation: blinker 1.5s linear infinite;
        }

        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        const API = '{{ route("operator.api.asesmen") }}';
        const ID_JADWAL = {{ $id_jadwal ?? 0 }};
        const DAYS_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        let refreshInterval;

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

        async function loadDataPortal() {
            if (!ID_JADWAL) {
                Swal.fire('Error', 'ID Jadwal tidak valid', 'error').then(() => window.location.href = '{{ route("operator.asesmen.jadwal") }}');
                return;
            }

            const [infoRes, hasilRes] = await Promise.all([
                post('get_jadwal_for_portal', { id_jadwal: ID_JADWAL }),
                post('get_hasil_by_jadwal', { id_jadwal: ID_JADWAL })
            ]);

            if (!infoRes.success) {
                document.getElementById('lblMapel').innerHTML = '<span class="text-danger">Jadwal tidak ditemukan</span>';
                clearInterval(refreshInterval);
                return;
            }

            const j = infoRes.data;
            document.getElementById('lblMapel').textContent = j.mapel;
            document.getElementById('lblKelas').textContent = 'Kelas ' + j.kelas;

            const tgl = new Date(j.tanggal_ujian + 'T00:00:00');
            document.getElementById('lblJadwal').textContent = `${DAYS_ID[tgl.getDay()]}, ${j.tanggal_ujian}`;
            document.getElementById('lblWaktu').textContent = `${j.jam_mulai.substring(0, 5)} – ${j.jam_selesai ? j.jam_selesai.substring(0, 5) : '?'} / ${j.durasi} mnt`;
            document.getElementById('lblSubmit').textContent = `${j.jumlah_submit} / ${j.jumlah_siswa}`;
            document.getElementById('lblToken').textContent = j.token || '-----';

            
            const now = new Date();
            const dateStr = j.tanggal_ujian;
            const mulaiDt = new Date(`${dateStr}T${j.jam_mulai}`);
            let jamSelesaiDisplay = j.jam_selesai;
            if (!jamSelesaiDisplay || jamSelesaiDisplay === '00:00:00') {
                const [h, m] = j.jam_mulai.split(':').map(Number);
                const totalSelesai = h * 60 + m + parseInt(j.durasi || 0);
                jamSelesaiDisplay = `${String(Math.floor(totalSelesai / 60) % 24).padStart(2, '0')}:${String(totalSelesai % 60).padStart(2, '0')}:00`;
            }
            const selesaiDt = new Date(`${dateStr}T${jamSelesaiDisplay}`);

            let statusHtml = '';
            if (now < mulaiDt) {
                statusHtml = '<span class="badge badge-info">Akan Datang</span>';
            } else if (now >= mulaiDt && now <= selesaiDt) {
                statusHtml = '<span class="badge badge-success"><i class="fas fa-circle blink-icon mr-1"></i>Berlangsung</span>';
            } else {
                statusHtml = '<span class="badge badge-dark">Selesai</span>';
                
                if ((now - selesaiDt) > 5 * 60000) clearInterval(refreshInterval);
            }
            document.getElementById('lblStatus').innerHTML = statusHtml;

            
            const tbody = document.getElementById('pesertaBody');
            if (!hasilRes.success || !hasilRes.data.hasil.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-inbox mr-2"></i> Belum ada peserta yang mensubmit.</td></tr>`;
                return;
            }

            
            const recent = hasilRes.data.hasil.sort((a, b) => new Date(b.waktu_selesai) - new Date(a.waktu_selesai)).slice(0, 10);

            const fmtDate = (str) => {
                if (!str) return '—';
                const d = new Date(str);
                return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}:${String(d.getSeconds()).padStart(2, '0')}`;
            };

            tbody.innerHTML = recent.map(r => {
                return `<tr>
                    <td>${r.nis || '—'}</td>
                    <td class="font-weight-bold">${r.nama || '—'}</td>
                    <td class="text-center font-monospace small">${fmtDate(r.waktu_mulai)}</td>
                    <td class="text-center font-monospace small"><span class="text-success">${fmtDate(r.waktu_selesai)}</span></td>
                    <td class="text-center"><span class="badge badge-primary font-weight-bold px-2 py-1" style="font-size:0.95rem;">${parseFloat(r.skor_akhir).toFixed(1)}</span></td>
                </tr>`;
            }).join('');
        }

        async function regenToken() {
            const conf = await Swal.fire({
                title: 'Regenerate Token?',
                text: 'Token lama akan mati. Siswa yang belum ujian harus pakai token baru ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Regenerate'
            });
            if (!conf.isConfirmed) return;

            const res = await post('generate_token_for_jadwal', { id_jadwal: ID_JADWAL });
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Token Diperbarui', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                loadDataPortal();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }

        window.onload = function () {
            loadDataPortal();
            
            refreshInterval = setInterval(loadDataPortal, 10000);
        };
    </script>
@endpush