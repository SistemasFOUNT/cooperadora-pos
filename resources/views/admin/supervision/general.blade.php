@extends('adminlte::page')

@section('title', 'Supervisión General')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line mr-2"></i> Supervisión General</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard Admin</a></li>
                <li class="breadcrumb-item active">Supervisión General</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Alertas si hay pendientes de autorización -->
    @if($pendientes_autorizacion > 0)
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>{{ $pendientes_autorizacion }}</strong> autorización(es) pendiente(s)
        </div>
    @endif

    <!-- Resumen financiero de todos los puntos -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> BOX Cooperadora</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> <span class="float-right">${{ number_format($box_data['ingresos_hoy'] ?? 0, 2) }}</span></p>
                    <p><strong>Transacciones:</strong> <span class="float-right">{{ $box_data['transacciones'] ?? 0 }}</span></p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $box_data['activo'] ? 'success' : 'warning' }} float-right">
                            {{ $box_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('admin.supervision.box') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye mr-1"></i> Supervisar BOX
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Postgrado</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> <span class="float-right">${{ number_format($postgrado_data['ingresos_hoy'] ?? 0, 2) }}</span></p>
                    <p><strong>Transacciones:</strong> <span class="float-right">{{ $postgrado_data['transacciones'] ?? 0 }}</span></p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $postgrado_data['activo'] ? 'success' : 'warning' }} float-right">
                            {{ $postgrado_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('admin.supervision.postgrado') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye mr-1"></i> Supervisar Postgrado
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tooth mr-1"></i> Centro Odontológico</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> <span class="float-right">${{ number_format($odonto_data['ingresos_hoy'] ?? 0, 2) }}</span></p>
                    <p><strong>Transacciones:</strong> <span class="float-right">{{ $odonto_data['transacciones'] ?? 0 }}</span></p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $odonto_data['activo'] ? 'success' : 'warning' }} float-right">
                            {{ $odonto_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('admin.supervision.odonto') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-eye mr-1"></i> Supervisar Odonto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas del sistema si existen -->
    @if(count($alertas) > 0)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bell mr-1"></i> Alertas del Sistema</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            @foreach($alertas as $alerta)
                                <li class="mb-2">
                                    <i class="fas fa-exclamation-circle text-warning mr-2"></i>
                                    <strong>{{ $alerta['punto_venta'] ?? 'Sistema' }}:</strong>
                                    {{ $alerta['mensaje'] ?? $alerta }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop
