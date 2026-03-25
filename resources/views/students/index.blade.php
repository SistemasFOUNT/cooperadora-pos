@extends('layouts.app')

@section('title', 'Gestión de Estudiantes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Estudiantes</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Estudiantes</li>
            </ol>
        </nav>
    </div>
    @can('create_students')
    <a href="{{ route('students.create') }}" class="btn btn-success">
        <i class="fas fa-user-plus"></i> Nuevo Estudiante
    </a>
    @endcan
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    Lista de Estudiantes
                </h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="studentsTable" class="table table-striped table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>N° Estudiante</th>
                                <th>Nombre Completo</th>
                                <th>Documento</th>
                                <th>Carrera</th>
                                <th>Año Académico</th>
                                <th>Cuota</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $student->student_number }}</span>
                                </td>
                                <td>
                                    <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                                    @if($student->email)
                                    <br><small class="text-muted">
                                        <i class="fas fa-envelope"></i> {{ $student->email }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ strtoupper($student->document_type) }}</span>
                                    <br><strong>{{ $student->document_number }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        @switch($student->career_type)
                                            @case('tecnicatura_protesis')
                                                Tec. Prótesis
                                                @break
                                            @case('tecnicatura_asistencia')
                                                Tec. Asistencia
                                                @break
                                            @case('grado_odontologia')
                                                Odontología
                                                @break
                                            @case('postgrado')
                                                Postgrado
                                                @break
                                            @default
                                                {{ ucfirst($student->career_type) }}
                                        @endswitch
                                    </span>
                                    @if($student->career_name)
                                    <br><small class="text-muted">{{ $student->career_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($student->academic_year)
                                    <span class="badge bg-secondary">{{ $student->academic_year }}° Año</span>
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ${{ number_format($student->fee_amount, 2) }}
                                    </span>
                                    <br><small class="text-muted">
                                        @switch($student->fee_frequency)
                                            @case('monthly')
                                                Mensual
                                                @break
                                            @case('annual')
                                                Anual
                                                @break
                                            @case('biannual')
                                                Semestral
                                                @break
                                            @default
                                                {{ ucfirst($student->fee_frequency) }}
                                        @endswitch
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $student->status === 'active' ? 'bg-success' :
                                                       ($student->status === 'inactive' ? 'bg-secondary' :
                                                       ($student->status === 'graduated' ? 'bg-primary' : 'bg-danger')) }}">
                                        @switch($student->status)
                                            @case('active')
                                                Activo
                                                @break
                                            @case('inactive')
                                                Inactivo
                                                @break
                                            @case('graduated')
                                                Graduado
                                                @break
                                            @case('dropout')
                                                Deserción
                                                @break
                                            @default
                                                {{ ucfirst($student->status) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('view_students')
                                        <a href="{{ route('students.show', $student) }}"
                                           class="btn btn-outline-info"
                                           title="Ver detalles"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan

                                        @can('edit_students')
                                        <a href="{{ route('students.edit', $student) }}"
                                           class="btn btn-outline-primary"
                                           title="Editar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can('delete_students')
                                        <button type="button"
                                                class="btn btn-outline-danger btn-delete"
                                                data-student-id="{{ $student->id }}"
                                                data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                                title="Eliminar"
                                                data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan

                                        <!-- Botón de historial de pagos -->
                                        @can('view_payments')
                                        <a href="#"
                                           class="btn btn-outline-success"
                                           title="Historial de pagos"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable con configuración estándar para estudiantes
    const table = DataTableConfig.initTable('#studentsTable', 'students', {
        columnDefs: [
            {
                targets: 7, // Columna de acciones
                orderable: false,
                searchable: false
            },
            {
                targets: [5], // Columna de cuota
                type: 'currency'
            }
        ],
        order: [[1, 'asc']] // Ordenar por nombre completo por defecto
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Manejar eliminación de estudiantes
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');

        // Confirmación con SweetAlert2 si está disponible, sino usar confirm nativo
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar estudiante?',
                text: `¿Estás seguro de eliminar a "${studentName}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteStudent(studentId);
                }
            });
        } else {
            if (confirm(`¿Estás seguro de eliminar a "${studentName}"? Esta acción no se puede deshacer.`)) {
                deleteStudent(studentId);
            }
        }
    });

    // Función para eliminar estudiante
    function deleteStudent(studentId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/students/${studentId}`;
        deleteForm.submit();
    }
});
</script>
@endpush
