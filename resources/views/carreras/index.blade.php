@extends('adminlte::page')

@section('title', 'Gestión de Carreras')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-graduation-cap"></i> Gestión de Carreras</h1>
        <div>
            <a href="{{ route('carreras.cuotas') }}" class="btn btn-info">
                <i class="fas fa-dollar-sign"></i> Gestionar Cuotas
            </a>
            <a href="{{ route('carreras.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Carrera
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Carreras</h3>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Modalidad</th>
                        <th>Duración</th>
                        <th>Cuota Mensual</th>
                        <th>Estudiantes</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carreras as $carrera)
                    <tr>
                        <td>
                            <strong>{{ $carrera->nombre_carrera }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-{{ $carrera->tipo_carrera === 'Tecnicatura' ? 'info' : ($carrera->tipo_carrera === 'Grado' ? 'primary' : 'success') }}">
                                {{ $carrera->tipo_carrera }}
                            </span>
                        </td>
                        <td>{{ $carrera->modalidad }}</td>
                        <td>{{ $carrera->duracion_anios }} año{{ $carrera->duracion_anios > 1 ? 's' : '' }}</td>
                        <td>
                            <strong>${{ number_format($carrera->cuota_mensual, 2) }}</strong>
                            @if($carrera->cuota_inscripcion)
                                <br><small class="text-muted">Inscripción: ${{ number_format($carrera->cuota_inscripcion, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                {{ $carrera->estudiantes->count() }} estudiante{{ $carrera->estudiantes->count() !== 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $carrera->activa ? 'success' : 'danger' }}">
                                {{ $carrera->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('carreras.show', $carrera) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('carreras.edit', $carrera) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('carreras.toggle-activa', $carrera) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $carrera->activa ? 'secondary' : 'success' }}" 
                                            title="{{ $carrera->activa ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $carrera->activa ? 'times' : 'check' }}"></i>
                                    </button>
                                </form>
                                @if($carrera->estudiantes->count() === 0)
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                        onclick="confirmarEliminacion({{ $carrera->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($carreras->hasPages())
        <div class="card-footer clearfix">
            {{ $carreras->links() }}
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
                    <p>¿Está seguro de que desea eliminar esta carrera? Esta acción no se puede deshacer.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Solo se pueden eliminar carreras que no tengan estudiantes asociados.
                    </div>
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
    $('#formEliminar').attr('action', '/carreras/' + id);
    $('#confirmarEliminacionModal').modal('show');
}
</script>
@stop

@section('css')
<style>
.table td {
    vertical-align: middle;
}
</style>
@stop