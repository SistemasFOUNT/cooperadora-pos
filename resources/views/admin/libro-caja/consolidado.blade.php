@extends('adminlte::page')

@section('title', 'Libro Caja - Consolidado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Libro Caja - Consolidado</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filtro de periodo</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.libro-caja.consolidado') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" class="form-control"
                           value="{{ $fechaDesde ?? now()->subDays(30)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control"
                           value="{{ $fechaHasta ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Aplicar filtro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($resumen['total_ingresos'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-circle-up"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $resumen['total_transacciones'] ?? 0 }}</h3>
                    <p>Total Transacciones</p>
                </div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Movimientos consolidados</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nro. Venta</th>
                        <th>Punto de Venta</th>
                        <th>Usuario</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos_consolidados as $movimiento)
                        <tr>
                            <td>{{ optional($movimiento->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $movimiento->sale_number ?? '-' }}</td>
                            <td>{{ $movimiento->puntoVenta->nombre ?? 'Sin punto de venta' }}</td>
                            <td>{{ $movimiento->user->name ?? 'Sin usuario' }}</td>
                            <td class="text-right">${{ number_format($movimiento->total ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay movimientos en el periodo seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($movimientos_consolidados, 'links'))
            <div class="card-footer">
                {{ $movimientos_consolidados->withQueryString()->links() }}
            </div>
        @endif
    </div>
@stop
