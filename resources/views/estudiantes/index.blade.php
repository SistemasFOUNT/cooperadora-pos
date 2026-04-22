@extends('adminlte::page')

@section('title', 'Gestión de Estudiantes')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-graduate"></i> Gestión de Estudiantes</h1>
        <div>
            <a href="{{ route('estudiantes.importar') }}" class="btn btn-info">
                <i class="fas fa-upload"></i> Importar CSV
            </a>
            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Estudiante
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Estudiantes</h3>
        </div>
        
        <div class="card-body">
            <table class="table table-striped table-hover" id="estudiantesTable">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellido, Nombre</th>
                        <th>Carrera</th>
                        <th>Año Académico</th>
                        <th>Estado</th>
                        <th>Fecha Inscripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estudiantes as $estudiante)
                    <tr>
                        <td>{{ $estudiante->dni }}</td>
                        <td>
                            <strong>{{ $estudiante->apellido }}, {{ $estudiante->nombre }}</strong>
                            @if($estudiante->email)
                                <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $estudiante->email }}</small>
                            @endif
                        </td>
                        <td>
                            @if($estudiante->configuracionCarrera)
                                <span class="badge badge-info">
                                    {{ $estudiante->configuracionCarrera->nombre_carrera }}
                                </span>
                            @else
                                <span class="badge badge-warning">Sin carrera asignada</span>
                            @endif
                        </td>
                        <td>{{ $estudiante->anio_academico }}</td>
                        <td>
                            <span class="badge badge-{{ $estudiante->estado === 'activo' ? 'success' : ($estudiante->estado === 'inactivo' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($estudiante->estado) }}
                            </span>
                        </td>
                        <td>{{ $estudiante->fecha_inscripcion ? $estudiante->fecha_inscripcion->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" title="Eliminar" onclick="confirmarEliminacion({{ $estudiante->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este estudiante? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#estudiantesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 1, "asc" ]], // Ordenar por apellido, nombre (columna 1)
        "columnDefs": [
            {
                "targets": [6], // Columna de acciones
                "orderable": false,
                "searchable": false
            }
        ],
        "pageLength": 25,
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function confirmarEliminacion(id) {
    $('#formEliminar').attr('action', '/estudiantes/' + id);
    $('#confirmarEliminacionModal').modal('show');
}
</script>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css">
<style>
.table td {
    vertical-align: middle;
}
.badge {
    font-size: 0.85em;
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function() {
    $('#estudiantesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 1, "asc" ]], // Ordenar por apellido, nombre (columna 1)
        "columnDefs": [
            {
                "targets": [6], // Columna de acciones
                "orderable": false,
                "searchable": false
            }
        ],
        "pageLength": 25,
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function confirmarEliminacion(id) {
    $('#formEliminar').attr('action', '/estudiantes/' + id);
    $('#confirmarEliminacionModal').modal('show');
}
</script>
@stop