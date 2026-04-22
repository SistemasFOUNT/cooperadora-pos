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
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Buscar estudiante..." id="searchInput">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap" id="estudiantesTable">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellido y Nombre</th>
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
                                <br><small class="text-muted">{{ $estudiante->email }}</small>
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
                            <div class="btn-group">
                                <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminacion({{ $estudiante->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($estudiantes->hasPages())
        <div class="card-footer clearfix">
            {{ $estudiantes->links() }}
        </div>
        @endif
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
function confirmarEliminacion(id) {
    $('#formEliminar').attr('action', '/estudiantes/' + id);
    $('#confirmarEliminacionModal').modal('show');
}

// Filtro de búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#estudiantesTable tbody tr');
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});
</script>
@stop

@section('css')
<style>
.table td {
    vertical-align: middle;
}
</style>
@stop