@extends('adminlte::page')

@section('title', 'Supervisión - Postgrado')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap mr-2"></i> Supervisión Postgrado</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.supervision.general') }}">Supervisión General</a></li>
                <li class="breadcrumb-item active">Postgrado</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Ingresos del Mes -->
        @if(isset($datos_supervision['ingresos_del_mes']))
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ingresos del Mes</span>
                        <span class="info-box-number">${{ number_format($datos_supervision['ingresos_del_mes'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estudiantes Activos</span>
                        <span class="info-box-number">{{ $datos_supervision['estudiantes_activos'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Honorarios Pendientes</span>
                        <span class="info-box-number">${{ number_format($datos_supervision['honorarios_pendientes'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Estadísticas Académicas -->
    @if(isset($datos_supervision['estadisticas_academicas']))
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Estadísticas Académicas</h3>
                    </div>
                    <div class="card-body">
                        @if(is_array($datos_supervision['estadisticas_academicas']))
                            <div class="row">
                                @foreach($datos_supervision['estadisticas_academicas'] as $clave => $valor)
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ ucfirst(str_replace('_', ' ', $clave)) }}</h6>
                                                <p class="card-text">
                                                    <strong class="text-primary" style="font-size: 1.5rem;">
                                                        {{ is_numeric($valor) ? number_format($valor, 2) : $valor }}
                                                    </strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Resumen General -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información de Supervisión</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-clock mr-2"></i>
                        Última actualización: {{ now()->format('d/m/Y H:i:s') }}
                    </p>
                    <div class="alert alert-info">
                        <strong>Nota:</strong> Puedes acceder a Libro de Caja y Arqueo de Caja desde el menú de Supervisión para más detalles.
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
