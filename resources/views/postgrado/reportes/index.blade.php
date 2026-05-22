@extends('adminlte::page')

@section('title', 'Postgrado - Reportes de Ventas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-chart-bar text-primary"></i> Reportes de Postgrado</h1>
            <p class="text-muted mb-0">Ventas por período y ranking de conceptos vendidos</p>
        </div>
        <a href="{{ route('postgrado.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('postgrado.reportes') }}" class="row">
                <div class="col-md-3">
                    <label>Fecha desde</label>
                    <input type="date" name="fecha_desde" value="{{ data_get($reportes, 'periodo.desde') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Fecha hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ data_get($reportes, 'periodo.hasta') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar período</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format((float) data_get($reportes, 'totales.ventas', 0), 0, ',', '.') }}</h3>
                    <p>Total de ventas</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format((float) data_get($reportes, 'totales.ingresos', 0), 0, ',', '.') }}</h3>
                    <p>Ingresos del período</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format((float) data_get($reportes, 'totales.ticket_promedio', 0), 0, ',', '.') }}</h3>
                    <p>Ticket promedio</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Top conceptos vendidos</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Monto total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(data_get($reportes, 'top_productos', []) as $item)
                        <tr>
                            <td>{{ $item->nombre }}</td>
                            <td class="text-right">{{ number_format((float) $item->cantidad, 0, ',', '.') }}</td>
                            <td class="text-right text-success">${{ number_format((float) $item->monto_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay ventas registradas en el período seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
