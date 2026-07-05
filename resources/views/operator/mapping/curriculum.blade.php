@extends('layouts.operator', ['page_title' => 'Struktur Kurikulum - Operator', 'active_menu' => 'mapping', 'active_submenu' => 'curriculum'])

@section('title', 'Struktur Kurikulum - Operator')

@section('content')

<style>
    .list-group-item.active {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    .cursor-pointer { cursor: pointer; }
</style>


<div class="content-wrapper">
    
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Struktur Kurikulum</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('operator.mapping.assignments') }}">Penugasan</a></li>
                        <li class="breadcrumb-item active">Kurikulum</li>
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
                                    <a class="nav-link active font-weight-bold" href="{{ route('operator.mapping.curriculum') }}">
                                        <i class="fas fa-list-alt mr-1"></i> Kurikulum Kelas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-muted" href="{{ route('operator.mapping.assignments') }}">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i> Penugasan Mengajar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                
                <div class="col-md-4">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Kelas</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                @foreach($kelases as $k)
                                    <a href="{{ route('operator.mapping.curriculum', ['class_id' => $k->id]) }}" 
                                       class="list-group-item list-group-item-action {{ ($selected_class_id == $k->id) ? 'active' : '' }}">
                                        <i class="fas fa-users me-2"></i> Kelas {{ $k->nama_kelas }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-8">
                    @if($selected_class_id)
                        <form action="{{ route('operator.mapping.curriculum.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $selected_class_id }}">

                            <div class="card card-outline card-warning shadow-sm">
                                <div class="card-header d-flex p-0">
                                    <h3 class="card-title p-3">Struktur Mata Pelajaran Kelas</h3>
                                    <ul class="nav nav-pills ml-auto p-2">
                                        <li class="nav-item">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($mapels as $m)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check p-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="subjects[]" 
                                                           value="{{ $m->id }}" 
                                                           id="mapel_{{ $m->id }}"
                                                           {{ in_array($m->id, $class_curriculum) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100 cursor-pointer" for="mapel_{{ $m->id }}">
                                                        {{ $m->nama_mapel }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> Petunjuk</h5>
                            <p>Silakan pilih kelas di menu sebelah kiri untuk mengatur kurikulum mata pelajaran.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
