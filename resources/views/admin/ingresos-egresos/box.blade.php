@extends('adminlte::page')

@section('title', 'Ingresos y Egresos - BOX Cooperadora')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Ingresos y Egresos - BOX Cooperadora</h1>
            <p class="text-muted mb-0">Detalle operativo de los ingresos y egresos del día</p>
        </div>
        <a href="{{ route('admin.ingresos-egresos.consolidado') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Consolidado
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>${{ number_format($ingresos_egresos['ingresos_hoy']['ventas_productos'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Ventas de productos</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($ingresos_egresos['ingresos_hoy']['cuotas_tecnicaturas'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Cuotas tecnicaturas</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($ingresos_egresos['ingresos_hoy']['bonos_grado'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Bonos grado</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($ingresos_egresos['ingresos_hoy']['prestaciones_clinicas'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Prestaciones clínicas</p>
                </div>
                <div class="icon"><i class="fas fa-tooth"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Egresos del día</h3>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Pagos a proveedores:</strong> ${{ number_format($ingresos_egresos['egresos_hoy']['pagos_proveedores'] ?? 0, 2, ',', '.') }}</p>
                    <p class="mb-1"><strong>Sueldos contratados:</strong> ${{ number_format($ingresos_egresos['egresos_hoy']['sueldos_contratados'] ?? 0, 2, ',', '.') }}</p>
                    <p class="mb-0"><strong>Otros pagos:</strong> ${{ number_format($ingresos_egresos['egresos_hoy']['otros_pagos'] ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Detalle de transacciones</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Nro. Venta</th>
                                <th>Usuario</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($ingresos_egresos['detalle_transacciones'] ?? []) as $transaccion)
                                <tr>
                                    <td>{{ optional($transaccion->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $transaccion->sale_number ?? '-' }}</td>
                                    <td>{{ data_get($transaccion, 'user.name', 'Sin usuario') }}</td>
                                    <td class="text-right">${{ number_format($transaccion->total ?? 0, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay transacciones registradas hoy.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
