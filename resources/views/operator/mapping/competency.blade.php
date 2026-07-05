@extends('layouts.operator', ['page_title' => 'Kompetensi Guru - Operator', 'active_menu' => 'mapping', 'active_submenu' => 'competency'])

@section('title', 'Kompetensi Guru - Operator')

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
                    <h1 class="m-0">Kompetensi Guru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('operator.mapping.assignments') }}">Penugasan</a></li>
                        <li class="breadcrumb-item active">Kompetensi</li>
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
                                    <a class="nav-link active font-weight-bold" href="{{ route('operator.mapping.competency') }}">
                                        <i class="fas fa-certificate mr-1"></i> Kompetensi Guru
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-muted" href="{{ route('operator.mapping.curriculum') }}">
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
                            <h3 class="card-title">Pilih Guru</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                @foreach($gurus as $g)
                                    <a href="{{ route('operator.mapping.competency', ['guru_id' => $g->id]) }}" 
                                       class="list-group-item list-group-item-action {{ ($selected_guru_id == $g->id) ? 'active' : '' }}">
                                        <i class="fas fa-user-tie me-2"></i> {{ $g->nama_lengkap }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-8">
                    @if($selected_guru_id)
                        <form action="{{ route('operator.mapping.competency.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="teacher_id" value="{{ $selected_guru_id }}">

                            <div class="card card-outline card-success shadow-sm">
                                <div class="card-header d-flex p-0">
                                    <h3 class="card-title p-3">Kompetensi Mata Pelajaran</h3>
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
                                            <div class="col-md-12 mb-3">
                                                <div class="card bg-light border-0">
                                                    <div class="card-body p-3">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input mapel-checkbox" type="checkbox" 
                                                                   name="subjects[]" 
                                                                   value="{{ $m->id }}" 
                                                                   id="mapel_{{ $m->id }}"
                                                                   {{ in_array($m->id, $guru_competencies) ? 'checked' : '' }}>
                                                            <label class="form-check-label font-weight-bold w-100 cursor-pointer" for="mapel_{{ $m->id }}">
                                                                {{ $m->nama_mapel }}
                                                            </label>
                                                        </div>
                                                        
                                                        
                                                        <div class="class-list-container mt-3 {{ in_array($m->id, $guru_competencies) ? '' : 'd-none' }}" id="class_container_{{ $m->id }}">
                                                            <hr class="mt-0 mb-2">
                                                            <p class="text-muted small mb-2"><i class="fas fa-info-circle"></i> Pilih kelas yang diajar:</p>
                                                            <div class="row">
                                                                @if(isset($allClasses) && count($allClasses) > 0)
                                                                    @foreach($allClasses as $cls)
                                                                        @php
                                                                            $isChecked = isset($teacherAssignments[$m->id]) && in_array($cls->id, $teacherAssignments[$m->id]);
                                                                            $isAssignedToOther = isset($otherAssignments[$m->id]) && in_array($cls->id, $otherAssignments[$m->id]);
                                                                        @endphp
                                                                        <div class="col-md-4 col-sm-6 mb-1">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input class-checkbox" type="checkbox" 
                                                                                       name="classes[{{ $m->id }}][]" 
                                                                                       value="{{ $cls->id }}" 
                                                                                       id="class_{{ $m->id }}_{{ $cls->id }}"
                                                                                       data-subject-id="{{ $m->id }}"
                                                                                       data-class-id="{{ $cls->id }}"
                                                                                       data-teacher-id="{{ $selected_guru_id }}"
                                                                                       {{ $isChecked ? 'checked' : '' }}
                                                                                       {{ $isAssignedToOther ? 'disabled' : '' }}>
                                                                                <label class="form-check-label {{ $isAssignedToOther ? 'text-muted' : 'cursor-pointer' }}" style="{{ $isAssignedToOther ? 'text-decoration: line-through;' : '' }}" for="class_{{ $m->id }}_{{ $cls->id }}">
                                                                                    {{ $cls->nama_kelas }}
                                                                                    @if($isAssignedToOther)
                                                                                        <small class="text-danger ml-1" style="text-decoration: none !important; display: inline-block;">(Terisi)</small>
                                                                                    @endif
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div class="col-12">
                                                                        <small class="text-danger">Data kelas belum tersedia.</small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
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
                            <p>Silakan pilih guru di menu sebelah kiri untuk mengatur kompetensi mata pelajaran yang diampu.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const mapelCheckboxes = document.querySelectorAll('.mapel-checkbox');
    mapelCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const subjectId = this.value;
            const container = document.getElementById('class_container_' + subjectId);
            if (container) {
                if (this.checked) {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                    
                    const classCheckboxes = container.querySelectorAll('.class-checkbox');
                    classCheckboxes.forEach(function(cb) {
                        cb.checked = false;
                    });
                }
            }
        });
    });

    
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    classCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                const cb = this;
                const subjectId = cb.dataset.subjectId;
                const classId = cb.dataset.classId;
                const teacherId = cb.dataset.teacherId;

                fetch(`{{ route('operator.mapping.api.check-assignment') }}?subject_id=${subjectId}&class_id=${classId}&teacher_id=${teacherId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.available) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Konflik Penugasan',
                                text: data.message,
                                confirmButtonColor: '#3085d6'
                            });
                            cb.checked = false; 
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    });
});
</script>
@endpush
