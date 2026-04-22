@extends('adminlte::page')

@section('title', 'Detalles del Estudiante')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-graduate"></i> Detalles del Estudiante</h1>
        <div>
            <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información Personal</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Apellido y Nombre:</dt>
                        <dd class="col-sm-9">
                            <strong>{{ $estudiante->apellido }}, {{ $estudiante->nombre }}</strong>
                        </dd>

                        <dt class="col-sm-3">DNI:</dt>
                        <dd class="col-sm-9">{{ $estudiante->dni }}</dd>

                        @if($estudiante->email)
                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">
                            <a href="mailto:{{ $estudiante->email }}">
                                <i class="fas fa-envelope"></i> {{ $estudiante->email }}
                            </a>
                        </dd>
                        @endif

                        @if($estudiante->telefono)
                        <dt class="col-sm-3">Teléfono:</dt>
                        <dd class="col-sm-9">
                            <i class="fas fa-phone"></i> {{ $estudiante->telefono }}
                        </dd>
                        @endif

                        @if($estudiante->domicilio)
                        <dt class="col-sm-3">Domicilio:</dt>
                        <dd class="col-sm-9">
                            <i class="fas fa-home"></i> {{ $estudiante->domicilio }}
                        </dd>
                        @endif

                        @if($estudiante->fecha_nacimiento)
                        <dt class="col-sm-3">Fecha de Nacimiento:</dt>
                        <dd class="col-sm-9">{{ $estudiante->fecha_nacimiento->format('d/m/Y') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información Académica</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Carrera:</dt>
                        <dd>
                            @if($estudiante->configuracionCarrera)
                                <span class="badge badge-info p-2">
                                    {{ $estudiante->configuracionCarrera->nombre_carrera }}
                                </span>
                            @else
                                <span class="badge badge-warning p-2">Sin carrera asignada</span>
                            @endif
                        </dd>

                        <dt>Año de Reinscripción:</dt>
                        <dd>
                            {{ $estudiante->reinscripcion ?? 'No especificado' }}
                            @if($estudiante->reinscripcion)
                                @if($estudiante->reinscripcion == $estudiante->obtenerAnioAcademicoActual())
                                    <span class="badge badge-success ml-2">Cursando</span>
                                @else
                                    <span class="badge badge-warning ml-2">Revisión requerida</span>
                                @endif
                            @endif
                        </dd>

                        <dt>Estado:</dt>
                        <dd>
                            <span class="badge badge-{{ $estudiante->estado === 'activo' ? 'success' : ($estudiante->estado === 'inactivo' ? 'warning' : 'secondary') }} p-2">
                                {{ ucfirst($estudiante->estado) }}
                            </span>
                        </dd>

                        <dt>Fecha de Inscripción:</dt>
                        <dd>{{ $estudiante->fecha_inscripcion ? $estudiante->fecha_inscripcion->format('d/m/Y') : 'No registrada' }}</dd>
                    </dl>
                </div>
            </div>

            @if($estudiante->reinscripcion && $estudiante->reinscripcion < $estudiante->obtenerAnioAcademicoActual())
                @php $deuda = $estudiante->calcularCuotasAdeudadas(); @endphp
                @if($deuda['cantidad'] > 0)
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">⚠️ Alertas Académicas</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Posible Situación de Deuda</h5>
                            <p><strong>Año de reinscripción:</strong> {{ $estudiante->reinscripcion }}</p>
                            <p><strong>Año académico actual:</strong> {{ $deuda['anio_academico_actual'] ?? $estudiante->obtenerAnioAcademicoActual() }}</p>
                            <p><strong>Cuotas estimadas adeudadas:</strong> {{ $deuda['cantidad'] }}</p>
                            <p><strong>Monto estimado:</strong> ${{ number_format($deuda['monto_total'], 2) }}</p>
                            <p><strong>Período:</strong> {{ $deuda['detalle'] }}</p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Cálculo basado en año académico (1/4 - 31/3).
                                Verificar manualmente si el estudiante egresó o abandonó la carrera.
                            </small>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            @if($estudiante->observaciones)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Observaciones</h3>
                </div>
                <div class="card-body">
                    <p>{{ $estudiante->observaciones }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Acciones</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar Estudiante
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()">
                            <i class="fas fa-trash"></i> Eliminar Estudiante
                        </button>
                    </div>
                </div>
            </div>
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
                    <p>¿Está seguro de que desea eliminar a <strong>{{ $estudiante->apellido }}, {{ $estudiante->nombre }}</strong>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" style="display: inline;">
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
function confirmarEliminacion() {
    $('#confirmarEliminacionModal').modal('show');
}
</script>
@stop

@section('css')
<style>
.badge {
    font-size: 0.9em;
}
dt {
    font-weight: 600;
}
</style>
@stop
