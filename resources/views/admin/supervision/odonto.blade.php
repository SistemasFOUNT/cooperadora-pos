@extends('adminlte::page')

@section('title', 'Supervisión - Centro Odontológico')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-tooth mr-2"></i> Supervisión Centro Odontológico</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.supervision.general') }}">Supervisión General</a></li>
                <li class="breadcrumb-item active">Centro Odontológico</li>
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
                    <span class="info-box-icon bg-success"><i class="fas fa-user-injured"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pacientes Activos</span>
                        <span class="info-box-number">{{ $datos_supervision['pacientes_activos'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-tasks"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Servicios Pendientes</span>
                        <span class="info-box-number">{{ $datos_supervision['servicios_pendientes'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Estadísticas Clínicas -->
    @if(isset($datos_supervision['estadisticas_clinicas']))
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Estadísticas Clínicas</h3>
                    </div>
                    <div class="card-body">
                        @if(is_array($datos_supervision['estadisticas_clinicas']))
                            <div class="row">
                                @foreach($datos_supervision['estadisticas_clinicas'] as $clave => $valor)
                                    <div class="col-md-12 mb-2">
                                        <div class="card bg-light">
                                            <div class="card-body p-2">
                                                <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $clave)) }}:</span>
                                                <strong class="text-primary float-right">{{ is_numeric($valor) ? number_format($valor, 2) : $valor }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Radiografías Pendientes -->
                @if(isset($datos_supervision['radiografias_pendientes']))
                    <div class="card">
                        <div class="card-header with-border">
                            <h3 class="card-title"><i class="fas fa-x-ray mr-2"></i>Radiografías Pendientes</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Total pendientes:</strong> {{ $datos_supervision['radiografias_pendientes'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                @endif
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
