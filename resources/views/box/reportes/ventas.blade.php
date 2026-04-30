@extends('adminlte::page')

@section('title', 'BOX - Reportes de Ventas')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line text-primary"></i> Reportes de Ventas - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item"><a href="{{ route('box.reportes') }}">Reportes</a></li>
                <li class="breadcrumb-item active">Ventas por Período</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Seleccionar Período
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('box.reportes.ventas') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período Rápido:</label>
                            <select name="periodo" class="form-control" onchange="toggleCustomDates(this)">
                                <option value="hoy" @if($periodo == 'hoy') selected @endif>Hoy</option>
                                <option value="semana_actual" @if($periodo == 'semana_actual') selected @endif>Semana Actual</option>
                                <option value="mes_actual" @if($periodo == 'mes_actual') selected @endif>Mes Actual</option>
                                <option value="ano_actual" @if($periodo == 'ano_actual') selected @endif>Año Actual</option>
                                <option value="personalizado" @if($periodo == 'personalizado') selected @endif>Personalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" id="fecha_desde_col">
                        <div class="form-group">
                            <label>Fecha Desde:</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                   value="{{ $fecha_desde }}">
                        </div>
                    </div>
                    <div class="col-md-2" id="fecha_hasta_col">
                        <div class="form-group">
                            <label>Fecha Hasta:</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                   value="{{ $fecha_hasta }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-chart-line"></i> Generar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-success btn-block" onclick="exportarExcel()">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas principales --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $analisis_ventas['total_ventas'] }}</h3>
                    <p>Total Ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($analisis_ventas['total_ingresos'], 0, ',', '.') }}</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($analisis_ventas['ticket_promedio'], 0, ',', '.') }}</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $analisis_ventas['mejor_dia'] ? \Carbon\Carbon::parse($analisis_ventas['mejor_dia'])->format('d/m') : 'N/A' }}</h3>
                    <p>Mejor Día</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-star"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Gráfico de ventas por día --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Evolución de Ventas por Día
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="ventasChart" height="100"></canvas>
                </div>
            </div>

            {{-- Lista de ventas detallada --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Detalle de Ventas del Período
                    </h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 400px;">
                    @if($ventas->count() > 0)
                        <table class="table table-hover text-nowrap table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Usuario</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas->take(50) as $venta)
                                <tr>
                                    <td>{{ $venta->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $venta->created_at->format('H:i') }}</td>
                                    <td>
                                        <small>{{ $venta->user->name ?? 'Sistema' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $venta->items->count() }} items</small>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            ${{ number_format($venta->total, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($ventas->count() > 50)
                            <div class="p-2 text-center text-muted">
                                <small>Mostrando las primeras 50 de {{ $ventas->count() }} ventas</small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay ventas en este período</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top productos y estadísticas --}}
        <div class="col-md-4">
            {{-- Top productos vendidos --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i> Top Productos
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($top_productos->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($top_productos->take(8) as $index => $producto)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-primary mr-2">{{ $index + 1 }}</span>
                                            <div>
                                                <h6 class="mb-1">{{ $producto['producto'] }}</h6>
                                                <small class="text-muted">{{ $producto['ventas_count'] }} ventas</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success">{{ $producto['cantidad_total'] }} uds</span>
                                        <br>
                                        <small class="text-success">
                                            ${{ number_format($producto['ingreso_total'], 0, ',', '.') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted py-3">No hay productos vendidos</p>
                    @endif
                </div>
            </div>

            {{-- Información del período --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información del Período
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}</p>
                    <p><strong>Días analizados:</strong> {{ \Carbon\Carbon::parse($fecha_desde)->diffInDays($fecha_hasta) + 1 }}</p>
                    <p><strong>Ventas promedio/día:</strong>
                        {{ round($analisis_ventas['total_ventas'] / max(1, \Carbon\Carbon::parse($fecha_desde)->diffInDays($fecha_hasta) + 1), 1) }}
                    </p>
                    <p><strong>Ingresos promedio/día:</strong>
                        ${{ number_format($analisis_ventas['total_ingresos'] / max(1, \Carbon\Carbon::parse($fecha_desde)->diffInDays($fecha_hasta) + 1), 0, ',', '.') }}
                    </p>
                    <hr>
                    <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                    <p><strong>Por:</strong> {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .inner h3 {
            font-size: 2rem;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        @media print {
            .btn, .card-header, .breadcrumb {
                display: none !important;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de ventas por día
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasData = @json($ventas_por_dia);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ventasData.map(v => {
                    const date = new Date(v.fecha);
                    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Ventas ($)',
                    data: ventasData.map(v => v.total),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y'
                }, {
                    label: 'Cantidad',
                    data: ventasData.map(v => v.cantidad),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Evolución de Ventas'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Monto ($)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Cantidad'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        function toggleCustomDates(select) {
            const fechaDesde = document.getElementById('fecha_desde_col');
            const fechaHasta = document.getElementById('fecha_hasta_col');

            if (select.value === 'personalizado') {
                fechaDesde.style.display = 'block';
                fechaHasta.style.display = 'block';
            } else {
                fechaDesde.style.display = 'none';
                fechaHasta.style.display = 'none';
            }
        }

        function exportarExcel() {
            Swal.fire({
                icon: 'info',
                title: 'Exportando Reporte',
                text: 'Generando archivo Excel de ventas...',
                timer: 3000
            });
        }

        // Inicializar estado de fechas
        document.addEventListener('DOMContentLoaded', function() {
            toggleCustomDates(document.querySelector('select[name="periodo"]'));
        });
    </script>
@stop
