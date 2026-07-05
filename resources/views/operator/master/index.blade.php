@extends('layouts.operator', ['page_title' => 'Data Master - Operator', 'active_menu' => 'master'])

@section('title', 'Data Master - Operator')

@section('content')
    
    <div class="content-wrapper">
        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Master</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Data Master</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        
        <section class="content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Pengaturan Rombel</h3>
                            </div>
                            <form action="{{ route('operator.settings.update') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group row mb-0">
                                        <label for="format_rombel" class="col-sm-3 col-form-label">Format Rombel Auto</label>
                                        <div class="col-sm-7">
                                            <select class="form-control" id="format_rombel" name="settings[format_rombel]">
                                                <option value="abjad" {{ ($settingsDB['format_rombel'] ?? 'abjad') == 'abjad' ? 'selected' : '' }}>Abjad Berurutan (A, B, C)</option>
                                                <option value="angka" {{ ($settingsDB['format_rombel'] ?? '') == 'angka' ? 'selected' : '' }}>Angka Berurutan (1, 2, 3)</option>
                                            </select>
                                            <small class="form-text text-muted">Format penamaan otomatis saat menambah rombel baru.</small>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Simpan</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary card-outline card-outline-tabs shadow-sm">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="master-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="tab-kelas" data-toggle="pill" href="#content-kelas" role="tab" aria-controls="content-kelas" aria-selected="true">
                                            <i class="fas fa-building mr-1"></i> Data Kelas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab-mapel" data-toggle="pill" href="#content-mapel" role="tab" aria-controls="content-mapel" aria-selected="false">
                                            <i class="fas fa-book mr-1"></i> Data Mata Pelajaran
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-0">
                                <div class="tab-content" id="master-tabs-content">
                                    
                                    <div class="tab-pane fade show active" id="content-kelas" role="tabpanel" aria-labelledby="tab-kelas">
                                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                            <h5 class="m-0 text-primary font-weight-bold">Daftar Kelas</h5>
                                            <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalAddKelas">
                                                <i class="fas fa-plus mr-1"></i> Tambah Kelas
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="bg-white">
                                                    <tr>
                                                        <th style="width: 80px" class="text-center">No</th>
                                                        <th>Nama Kelas</th>
                                                        <th style="width: 150px" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $noKelas = 1; @endphp
                                                    @foreach($kelases->groupBy('tingkat') as $tingkat => $kelasGroup)
                                                        <tr class="table-secondary">
                                                            <td colspan="3" class="font-weight-bold">
                                                                <i class="fas fa-layer-group mr-1"></i> Tingkat {{ $tingkat ?: 'Tanpa Tingkat' }}
                                                            </td>
                                                        </tr>
                                                        @foreach($kelasGroup as $k)
                                                            <tr>
                                                                <td class="text-center align-middle">{{ $noKelas++ }}</td>
                                                                <td class="align-middle fw-bold pl-4">{{ $k->nama_kelas }}</td>
                                                                <td class="text-center align-middle">
                                                                    <button class="btn btn-sm btn-outline-warning btn-edit-kelas" data-id="{{ $k->id }}"
                                                                        data-nama="{{ $k->nama_kelas }}" data-tingkat="{{ $k->tingkat }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-type="kelas"
                                                                        data-id="{{ $k->id }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="tab-pane fade" id="content-mapel" role="tabpanel" aria-labelledby="tab-mapel">
                                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                            <h5 class="m-0 text-success font-weight-bold">Daftar Mata Pelajaran</h5>
                                            <button class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalAddMapel">
                                                <i class="fas fa-plus mr-1"></i> Tambah Mapel
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="bg-white">
                                                    <tr>
                                                        <th style="width: 80px" class="text-center">No</th>
                                                        <th>Nama Mata Pelajaran</th>
                                                        <th style="width: 150px" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $no = 1; @endphp
                                                    @foreach($mapels->groupBy('kategori') as $kategori => $mapelGroup)
                                                        <tr class="table-secondary">
                                                            <td colspan="3" class="font-weight-bold">
                                                                <i class="fas fa-layer-group mr-1"></i> {{ $kategori ?: 'Tanpa Kategori' }}
                                                            </td>
                                                        </tr>
                                                        @foreach($mapelGroup as $m)
                                                            <tr>
                                                                <td class="text-center align-middle">{{ $no++ }}</td>
                                                                <td class="align-middle fw-bold pl-4">{{ $m->nama_mapel }}</td>
                                                                <td class="text-center align-middle">
                                                                    <button class="btn btn-sm btn-outline-warning btn-edit-mapel" data-id="{{ $m->id }}"
                                                                        data-nama="{{ $m->nama_mapel }}" data-kategori="{{ $m->kategori }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-type="mapel"
                                                                        data-id="{{ $m->id }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
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

    
    <div class="modal fade" id="modalAddKelas">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('operator.master.manage') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="type" value="kelas">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kelas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Tingkat Kelas</label>
                        <select name="tingkat" class="form-control" required>
                            <option value="">Pilih Tingkat</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Nama Rombel akan d-generate otomatis secara berurutan sesuai pengaturan (A, B, C / 1, 2, 3).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalEditKelas">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('operator.master.manage') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="type" value="kelas">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editKelasId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kelas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tingkat Kelas</label>
                        <select name="tingkat" id="editKelasTingkat" class="form-control" required>
                            <option value="">Pilih Tingkat</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Nama Rombel</label>
                        <input type="text" name="nama_kelas" id="editKelasNama" class="form-control" placeholder="Nama Rombel"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalAddMapel">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('operator.master.manage') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="type" value="mapel">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mapel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Kelompok A (Wajib)">Kelompok A (Wajib)</option>
                            <option value="Kelompok B (Wajib)">Kelompok B (Wajib)</option>
                            <option value="Kelompok C (Peminatan)">Kelompok C (Peminatan)</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Nama Mapel</label>
                        <input type="text" name="nama_mapel" class="form-control" placeholder="Nama Mapel" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="modalEditMapel">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('operator.master.manage') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="type" value="mapel">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editMapelId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mapel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" id="editMapelKategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Kelompok A (Wajib)">Kelompok A (Wajib)</option>
                            <option value="Kelompok B (Wajib)">Kelompok B (Wajib)</option>
                            <option value="Kelompok C (Peminatan)">Kelompok C (Peminatan)</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Nama Mapel</label>
                        <input type="text" name="nama_mapel" id="editMapelNama" class="form-control" placeholder="Nama Mapel"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            
            $('.btn-edit-kelas').click(function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const tingkat = $(this).data('tingkat');
                $('#editKelasId').val(id);
                $('#editKelasNama').val(nama);
                $('#editKelasTingkat').val(tingkat);
                $('#modalEditKelas').modal('show');
            });

            
            $('.btn-edit-mapel').click(function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const kategori = $(this).data('kategori');
                $('#editMapelId').val(id);
                $('#editMapelNama').val(nama);
                $('#editMapelKategori').val(kategori);
                $('#modalEditMapel').modal('show');
            });

            
            $('.btn-delete').click(function () {
                const type = $(this).data('type');
                const id = $(this).data('id');
                const msg = type === 'kelas' ? "Semua siswa di kelas ini akan kehilangan data kelasnya!" : "Semua RPP, Tugas, dan Diskusi mapel ini akan terhapus!";

                Swal.fire({
                    title: 'Hapus Data?', text: msg, icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('operator.master.manage') }}';
                        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="type" value="${type}"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush