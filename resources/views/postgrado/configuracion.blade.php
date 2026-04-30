@extends('adminlte::page')

@section('title', 'Configuración Postgrado')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-cog"></i> Configuración General - Postgrado</h1>
        <a href="{{ route('postgrado.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Punto de Venta</h3>
                </div>
                <div class="card-body">
                    <p><strong>Código:</strong> {{ $configuracion['punto_venta']->codigo }}</p>
                    <p><strong>Nombre:</strong> {{ $configuracion['punto_venta']->nombre }}</p>
                    <p><strong>Descripción:</strong> {{ $configuracion['punto_venta']->descripcion }}</p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $configuracion['punto_venta']->activo ? 'success' : 'danger' }}">
                            {{ $configuracion['punto_venta']->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar"></i> Períodos Académicos</h3>
                </div>
                <div class="card-body">
                    @foreach($configuracion['periodos_academicos'] as $codigo => $descripcion)
                        <p><strong>{{ $codigo }}:</strong> {{ $descripcion }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Aranceles de Referencia</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($configuracion['aranceles'] as $tipo => $monto)
                        <div class="col-md-6">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ ucfirst($tipo) }}</span>
                                    <span class="info-box-number">${{ number_format($monto, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tools"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('postgrado.carreras') }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-graduation-cap"></i> Configurar Carreras
                    </a>
                    <a href="{{ route('carreras.cuotas') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-dollar-sign"></i> Gestionar Cuotas
                    </a>
                    <a href="{{ route('postgrado.estudiantes') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-users"></i> Ver Estudiantes
                    </a>
                    <a href="{{ route('postgrado.reportes') }}" class="btn btn-dark btn-block">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Programas Disponibles</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Especializaciones</li>
                                <li><i class="fas fa-check text-success"></i> Maestrías</li>
                                <li><i class="fas fa-check text-success"></i> Doctorados</li>
                                <li><i class="fas fa-check text-success"></i> Diplomados</li>
                                <li><i class="fas fa-check text-success"></i> Cursos de Actualización</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h5>Modalidades</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-info"></i> Presencial</li>
                                <li><i class="fas fa-check text-info"></i> Virtual</li>
                                <li><i class="fas fa-check text-info"></i> Mixta</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h5>Características</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-warning"></i> Gestión de matrículas</li>
                                <li><i class="fas fa-check text-warning"></i> Control de pagos</li>
                                <li><i class="fas fa-check text-warning"></i> Certificaciones</li>
                                <li><i class="fas fa-check text-warning"></i> Reportes académicos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
