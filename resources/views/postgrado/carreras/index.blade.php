@extends('adminlte::page')

@section('title', 'Carreras de Postgrado')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-graduation-cap"></i> Gestión de Carreras de Postgrado</h1>
        <div>
            <a href="{{ route('postgrado.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="{{ route('carreras.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Carrera Postgrado
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title"><i class="fas fa-university"></i> Carreras de Postgrado Disponibles</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="card-body">
            @if($carreras->count() > 0)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Se muestran únicamente las carreras de postgrado (especializaciones, maestrías, doctorados, diplomados y cursos de postgrado).
                </div>

                <table class="table table-striped table-hover" id="carrerasPostgradoTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Programa</th>
                            <th>Tipo</th>
                            <th>Duración</th>
                            <th>Cuota Mensual</th>
                            <th>Matrícula</th>
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
                                @if($carrera->cuotas_adicionales)
                                    <br><small class="text-muted">
                                        <i class="fas fa-plus-circle"></i> Con aranceles adicionales
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $carrera->tipo_carrera }}
                                </span>
                            </td>
                            <td>
                                @if($carrera->duracion_meses)
                                    <span class="badge badge-info">
                                        {{ $carrera->duracion_meses }} meses
                                    </span>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </td>
                            <td>
                                <strong>${{ number_format($carrera->cuota_mensual, 2) }}</strong>
                            </td>
                            <td>
                                @if($carrera->cuota_inscripcion)
                                    <strong>${{ number_format($carrera->cuota_inscripcion, 2) }}</strong>
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $carrera->estudiantes->count() }} estudiante{{ $carrera->estudiantes->count() !== 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $carrera->activo ? 'success' : 'danger' }}">
                                    {{ $carrera->activo ? 'Activa' : 'Inactiva' }}
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
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5>No hay carreras de postgrado configuradas</h5>
                    <p>No se encontraron carreras de postgrado en el sistema.</p>
                    <a href="{{ route('carreras.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Crear Primera Carrera de Postgrado
                    </a>
                </div>
            @endif
        </div>

        @if($carreras->count() > 0)
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <strong>Total de programas:</strong> {{ $carreras->count() }}
                    </p>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Solo se muestran programas de postgrado
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Card con información adicional -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Tipos de Programas</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Especializaciones</li>
                        <li><i class="fas fa-check text-success"></i> Maestrías</li>
                        <li><i class="fas fa-check text-success"></i> Doctorados</li>
                        <li><i class="fas fa-check text-success"></i> Diplomados</li>
                        <li><i class="fas fa-check text-success"></i> Cursos de Postgrado</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
                </div>
                <div class="card-body">
                    <p><strong>Filtros aplicados:</strong></p>
                    <ul>
                        <li>Solo carreras de tipo "Postgrado"</li>
                        <li>Programas de especialización</li>
                        <li>Maestrías y doctorados</li>
                        <li>Diplomados y cursos</li>
                    </ul>
                    <p class="text-muted">
                        <small>Las carreras de grado y tecnicaturas no se muestran en esta vista.</small>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('carreras.cuotas') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-dollar-sign"></i> Gestionar Cuotas
                    </a>
                    <a href="{{ route('carreras.create') }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-plus"></i> Nueva Carrera
                    </a>
                    <a href="{{ route('postgrado.configuracion') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-cog"></i> Configuración General
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .table th {
        border-top: none;
    }
    .card-header.bg-success {
        color: white;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#carrerasPostgradoTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']]
    });
});
</script>
@stop
