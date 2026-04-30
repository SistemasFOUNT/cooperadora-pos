@extends('adminlte::page')

@section('title', 'BOX - Reportes')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-bar text-primary"></i> Reportes - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Reportes</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Menú de tipos de reportes --}}
    <div class="row">
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Tipos de Reportes
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#reporte-ventas" class="list-group-item list-group-item-action active" data-toggle="tab">
                        <i class="fas fa-chart-line"></i> Reporte de Ventas
                    </a>
                    <a href="#reporte-productos" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-box"></i> Productos Más Vendidos
                    </a>
                    <a href="#reporte-cajeros" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-users"></i> Performance de Cajeros
                    </a>
                    <a href="#reporte-inventario" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-warehouse"></i> Estado de Inventario
                    </a>
                    <a href="#reporte-financiero" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-calculator"></i> Resumen Financiero
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                {{-- Reporte de Ventas --}}
                <div class="tab-pane active" id="reporte-ventas">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Reporte de Ventas
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Exportar Excel
                                </button>
                                <button class="btn btn-info btn-sm">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Filtros de fecha --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Desde:</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-01') }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Hasta:</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Generar Reporte
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="btn-group btn-block">
                                        <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                            Períodos Rápidos
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" data-periodo="hoy">Hoy</a>
                                            <a class="dropdown-item" href="#" data-periodo="semana">Esta Semana</a>
                                            <a class="dropdown-item" href="#" data-periodo="mes">Este Mes</a>
                                            <a class="dropdown-item" href="#" data-periodo="trimestre">Trimestre</a>
                                            <a class="dropdown-item" href="#" data-periodo="ano">Este Año</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Métricas principales --}}
                            <div class="row mb-4">
                                <div class="col-lg-3 col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Ventas</span>
                                            <span class="info-box-number" id="total-ventas">
                                                @if(isset($reportes['ventas_mes']))
                                                    {{ $reportes['ventas_mes']->count() }}
                                                @else
                                                    0
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Ingresos</span>
                                            <span class="info-box-number" id="total-ingresos">
                                                @if(isset($reportes['ventas_mes']))
                                                    ${{ number_format($reportes['ventas_mes']->sum('total'), 0, ',', '.') }}
                                                @else
                                                    $0
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Ticket Promedio</span>
                                            <span class="info-box-number" id="ticket-promedio">
                                                @if(isset($reportes['ventas_mes']) && $reportes['ventas_mes']->count() > 0)
                                                    ${{ number_format($reportes['ventas_mes']->sum('total') / $reportes['ventas_mes']->count(), 0, ',', '.') }}
                                                @else
                                                    $0
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Crecimiento</span>
                                            <span class="info-box-number">+12%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Gráfico de ventas --}}
                            <div class="card card-outline card-primary">
                                <div class="card-body">
                                    <canvas id="graficoVentas" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Productos Más Vendidos --}}
                <div class="tab-pane" id="reporte-productos">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-box"></i> Productos Más Vendidos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Producto</th>
                                            <th>Código</th>
                                            <th>Categoría</th>
                                            <th>Cantidad Vendida</th>
                                            <th>Ingresos</th>
                                            <th>% del Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($reportes['productos_mas_vendidos']))
                                            @foreach($reportes['productos_mas_vendidos'] as $index => $producto)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $producto->name }}</td>
                                                <td><code>{{ $producto->codigo ?? 'N/A' }}</code></td>
                                                <td>
                                                    <span class="badge badge-info">{{ $producto->categoria ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $producto->cantidad_vendida }}</strong>
                                                </td>
                                                <td>
                                                    <strong class="text-success">${{ number_format($producto->ingresos ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar" style="width: {{ ($producto->cantidad_vendida / ($reportes['productos_mas_vendidos']->max('cantidad_vendida') ?: 1)) * 100 }}%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    No hay datos de productos vendidos
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Performance de Cajeros --}}
                <div class="tab-pane" id="reporte-cajeros">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Performance de Cajeros
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(isset($reportes['cajeros_performance']))
                                    @foreach($reportes['cajeros_performance'] as $cajero)
                                    <div class="col-md-6">
                                        <div class="card card-outline card-primary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h5>{{ $cajero->name }}</h5>
                                                        <p class="text-muted">Cajero</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <h4 class="text-primary">{{ $cajero->total_ventas }}</h4>
                                                        <p class="text-muted">ventas</p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Total Recaudado:</span>
                                                        <strong class="text-success">${{ number_format($cajero->monto_total, 0, ',', '.') }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Promedio por Venta:</span>
                                                        <strong>${{ number_format($cajero->monto_total / ($cajero->total_ventas ?: 1), 0, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="col-12 text-center text-muted">
                                        No hay datos de performance de cajeros
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estado de Inventario --}}
                <div class="tab-pane" id="reporte-inventario">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-warehouse"></i> Estado de Inventario
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Este reporte muestra el estado actual del inventario y productos con stock bajo.
                            </div>

                            {{-- Métricas de inventario --}}
                            <div class="row">
                                <div class="col-lg-4 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>0</h3>
                                            <p>Productos en Stock</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>0</h3>
                                            <p>Stock Bajo</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>0</h3>
                                            <p>Sin Stock</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-construction"></i>
                                <strong>En desarrollo:</strong> El reporte detallado de inventario se implementará próximamente.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resumen Financiero --}}
                <div class="tab-pane" id="reporte-financiero">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator"></i> Resumen Financiero
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-construction"></i>
                                <strong>En desarrollo:</strong> El reporte financiero se integrará con el módulo de contabilidad.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .list-group-item-action.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        .info-box-number {
            font-weight: bold;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Gráfico de ventas por fecha
        const ctx = document.getElementById('graficoVentas').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(isset($reportes['ventas_mes']) ? $reportes['ventas_mes']->pluck('fecha')->toArray() : []),
                datasets: [{
                    label: 'Ventas Diarias',
                    data: @json(isset($reportes['ventas_mes']) ? $reportes['ventas_mes']->pluck('total')->toArray() : []),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ventas: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Períodos rápidos
        $('[data-periodo]').click(function(e) {
            e.preventDefault();
            const periodo = $(this).data('periodo');
            const hoy = new Date();
            let desde, hasta = hoy.toISOString().split('T')[0];

            switch(periodo) {
                case 'hoy':
                    desde = hasta;
                    break;
                case 'semana':
                    desde = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                    break;
                case 'mes':
                    desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'trimestre':
                    desde = new Date(hoy.getFullYear(), Math.floor(hoy.getMonth() / 3) * 3, 1).toISOString().split('T')[0];
                    break;
                case 'ano':
                    desde = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                    break;
            }

            $('input[type="date"]').eq(0).val(desde);
            $('input[type="date"]').eq(1).val(hasta);
        });
    });
</script>
@stop
