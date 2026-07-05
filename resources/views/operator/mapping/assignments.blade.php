@extends('layouts.operator', ['page_title' => 'Penugasan Mengajar - Operator', 'active_menu' => 'mapping', 'active_submenu' => 'assignments'])

@section('title', 'Penugasan Mengajar - Operator')

@section('content')

    
    <div class="content-wrapper">
        
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Penugasan Guru</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Penugasan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        
        <section class="content">
            <div class="container-fluid">

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-0">
                            <div class="card-body p-2">
                                <ul class="nav nav-pills nav-justified">
                                    <li class="nav-item">
                                        <a class="nav-link text-muted" href="{{ route('operator.mapping.competency') }}">
                                            <i class="fas fa-certificate mr-1"></i> Kompetensi Guru
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-muted" href="{{ route('operator.mapping.curriculum') }}">
                                            <i class="fas fa-list-alt mr-1"></i> Kurikulum Kelas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active font-weight-bold" href="{{ route('operator.mapping.assignments') }}">
                                            <i class="fas fa-chalkboard-teacher mr-1"></i> Penugasan Mengajar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Penugasan Mengajar</h3>
                        <div class="card-tools">
                            <a href="{{ route('operator.mapping.competency') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus-circle me-1"></i> Penugasan Baru
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionAssignments">
                            @forelse($assignments as $guruName => $mapels)
                                @php $safeId = md5($guruName); @endphp
                                <div class="card mb-2 border">
                                    <div class="card-header bg-light p-2" id="heading_{{ $safeId }}">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse_{{ $safeId }}" aria-expanded="true" aria-controls="collapse_{{ $safeId }}">
                                                <i class="fas fa-chalkboard-teacher mr-2 text-primary"></i> {{ $guruName }}
                                                <span class="badge badge-primary float-right mt-1">{{ count($mapels) }} Mapel</span>
                                            </button>
                                        </h2>
                                    </div>

                                    <div id="collapse_{{ $safeId }}" class="collapse" aria-labelledby="heading_{{ $safeId }}" data-parent="#accordionAssignments">
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="bg-white">
                                                    <tr>
                                                        <th class="pl-4">Mata Pelajaran</th>
                                                        <th>Kelas yang Diajar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($mapels as $mapelName => $kelasArray)
                                                        <tr>
                                                            <td class="align-middle font-weight-bold w-25 pl-4">{{ $mapelName }}</td>
                                                            <td>
                                                                <div class="d-flex flex-wrap">
                                                                    @foreach($kelasArray as $kelas)
                                                                        <span class="badge badge-info p-2 m-1" style="font-size: 0.85rem;">
                                                                            {{ $kelas['nama_kelas'] }}
                                                                            <a href="javascript:void(0)" class="text-white ml-2 btn-delete" data-id="{{ $kelas['id'] }}" title="Hapus Penugasan">
                                                                                <i class="fas fa-times"></i>
                                                                            </a>
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info text-center">
                                    Belum ada data penugasan mengajar.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>


@endsection

@push('scripts')
    
    <link rel="stylesheet"
        href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script
        src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script
        src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.datatable').DataTable({
                "responsive": true, "autoWidth": false
            });

            
            $(document).on('click', '.btn-delete', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Penugasan?',
                    text: "Guru tidak akan mengajar kelas ini lagi.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/operator/mapping/assignments/' + id;
                        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                })
            });
        });
    </script>
@endpush