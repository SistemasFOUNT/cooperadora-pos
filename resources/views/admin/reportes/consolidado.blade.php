@extends('adminlte::page')

@section('title', 'Reportes Consolidados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Reportes Consolidados</h1>
            <p class="text-muted mb-0">Indicadores históricos y actividad operativa</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ collect($reportes['ventas_por_mes'] ?? [])->count() }}</h3>
                    <p>Meses analizados</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ collect($reportes['usuarios_mas_activos'] ?? [])->count() }}</h3>
                    <p>Usuarios activos</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ collect($reportes['productos_mas_vendidos'] ?? [])->count() }}</h3>
                    <p>Productos top</p>
                </div>
                <div class="icon"><i class="fas fa-box-open"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Ventas por mes</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportes['ventas_por_mes'] ?? [] as $venta)
                                <tr>
                                    <td>{{ $venta->mes ?? '-' }}/{{ $venta->año ?? '-' }}</td>
                                    <td class="text-right">${{ number_format($venta->total ?? 0, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Todavía no hay ventas historizadas para reportar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Usuarios más activos</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th class="text-right">Ventas</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportes['usuarios_mas_activos'] ?? [] as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? 'Sin usuario' }}</td>
                                    <td class="text-right">{{ $item->total_ventas ?? 0 }}</td>
                                    <td class="text-right">${{ number_format($item->total_monto ?? 0, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No hay datos de actividad suficientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
