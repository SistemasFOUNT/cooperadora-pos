@extends('adminlte::page')

@section('title', 'Estado de Cuentas - General')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Estado de Cuentas - General</h1>
            <p class="text-muted mb-0">Resumen financiero global por punto de venta</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($estado_cuentas['caja_general'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Caja general</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estado_cuentas['pendientes_cobro'] ?? 0 }}</h3>
                    <p>Pendientes de cobro</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($estado_cuentas['gastos_periodo'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Gastos del período</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Resumen por punto de venta</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Punto de venta</th>
                        <th class="text-right">Total ingresos</th>
                        <th class="text-right">Ventas asociadas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($estado_cuentas['por_punto_venta'] ?? [] as $punto)
                        <tr>
                            <td><strong>{{ $punto->nombre ?? $punto->codigo ?? 'Sin nombre' }}</strong></td>
                            <td class="text-right">${{ number_format($punto->total_ingresos ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">{{ $punto->total_ventas ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay puntos de venta cargados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
