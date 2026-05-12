@extends('adminlte::page')

@section('title', 'Supervisión - BOX Cooperadora')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-shopping-cart mr-2"></i> Supervisión BOX Cooperadora</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.supervision.general') }}">Supervisión General</a></li>
                <li class="breadcrumb-item active">BOX Cooperadora</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Alertas si existen -->
    @if(isset($datos_supervision['alertas_sistema']) && is_array($datos_supervision['alertas_sistema']))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Alertas del Sistema</strong>
            <ul class="mt-2 mb-0">
                @if(($datos_supervision['alertas_sistema']['productos_bajo_stock'] ?? 0) > 0)
                    <li>{{ $datos_supervision['alertas_sistema']['productos_bajo_stock'] }} producto(s) con stock bajo</li>
                @endif
                @if(($datos_supervision['alertas_sistema']['facturas_pendientes'] ?? 0) > 0)
                    <li>{{ $datos_supervision['alertas_sistema']['facturas_pendientes'] }} factura(s) pendiente(s) de ARCA</li>
                @endif
                @if(($datos_supervision['alertas_sistema']['productos_bajo_stock'] ?? 0) == 0 && ($datos_supervision['alertas_sistema']['facturas_pendientes'] ?? 0) == 0)
                    <li class="text-success"><i class="fas fa-check mr-1"></i>No hay alertas activas</li>
                @endif
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Resumen Financiero -->
        @if(isset($datos_supervision['resumen_financiero']))
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ingresos del Día</span>
                        <span class="info-box-number">${{ number_format($datos_supervision['resumen_financiero']['ingresos_dia'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-shopping-bag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Transacciones Hoy</span>
                        <span class="info-box-number">{{ $datos_supervision['ventas_del_dia'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ingresos del Mes</span>
                        <span class="info-box-number">${{ number_format($datos_supervision['resumen_financiero']['ingresos_mes'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Ventas del Día (Resumen) -->
    @if(isset($datos_supervision['ventas_del_dia']))
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Resumen de Ventas del Día</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Total de transacciones hoy:</strong> {{ $datos_supervision['ventas_del_dia'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Productos Más Vendidos -->
    @if(isset($datos_supervision['productos_mas_vendidos']))
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title"><i class="fas fa-star mr-2"></i>Top 10 Productos Vendidos</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datos_supervision['productos_mas_vendidos'] as $producto)
                                        <tr>
                                            <td>{{ $producto->name ?? 'N/A' }}</td>
                                            <td><span class="badge badge-primary">{{ $producto->cantidad ?? 0 }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Sin datos</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                @if(isset($datos_supervision['estadisticas_completas']))
                    <div class="card">
                        <div class="card-header with-border">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Estadísticas Generales</h3>
                        </div>
                        <div class="card-body">
                            @if(is_array($datos_supervision['estadisticas_completas']))
                                <dl class="row">
                                    @foreach($datos_supervision['estadisticas_completas'] as $clave => $valor)
                                        <dt class="col-sm-6">{{ ucfirst(str_replace('_', ' ', $clave)) }}:</dt>
                                        <dd class="col-sm-6">
                                            <strong>{{ is_numeric($valor) ? number_format($valor, 2) : $valor }}</strong>
                                        </dd>
                                    @endforeach
                                </dl>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@stop
