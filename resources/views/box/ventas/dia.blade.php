@extends('adminlte::page')

@section('title', 'BOX - Ventas del Día')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-receipt text-primary"></i> Ventas del Día - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Ventas del Día</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Resumen del día --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>
                        @if(isset($ventas))
                            {{ $ventas->count() }}
                        @else
                            0
                        @endif
                    </h3>
                    <p>Ventas Realizadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>
                        @if(isset($ventas))
                            ${{ number_format($ventas->sum('total'), 0, ',', '.') }}
                        @else
                            $0
                        @endif
                    </h3>
                    <p>Total Recaudado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>
                        @if(isset($ventas) && $ventas->count() > 0)
                            ${{ number_format($ventas->sum('total') / $ventas->count(), 0, ',', '.') }}
                        @else
                            $0
                        @endif
                    </h3>
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
                    <h3>{{ date('d/m/Y') }}</h3>
                    <p>Fecha Actual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cajero:</label>
                                <select class="form-control">
                                    <option value="">Todos los cajeros</option>
                                    @if(isset($ventas))
                                        @foreach($ventas->groupBy('user.name') as $cajero => $ventasCajero)
                                            <option value="{{ $cajero }}">{{ $cajero }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Método de Pago:</label>
                                <select class="form-control">
                                    <option value="">Todos</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Monto mínimo:</label>
                                <input type="number" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de ventas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Detalle de Ventas
            </h3>
            <div class="card-tools">
                <button class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Exportar Excel
                </button>
                <button class="btn btn-info btn-sm">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Nº Venta</th>
                        <th>Cajero</th>
                        <th>Cliente/Estudiante</th>
                        <th>Productos</th>
                        <th>Método Pago</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($ventas) && $ventas->count() > 0)
                        @foreach($ventas as $venta)
                        <tr>
                            <td>
                                <strong>{{ $venta->created_at->format('H:i:s') }}</strong>
                            </td>
                            <td>
                                <code>{{ $venta->sale_number ?? 'V-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</code>
                            </td>
                            <td>
                                {{ $venta->user->name ?? 'No disponible' }}
                            </td>
                            <td>
                                @if($venta->student_id)
                                    <i class="fas fa-user-graduate text-primary"></i>
                                    {{ $venta->student->name ?? 'Estudiante' }}
                                @else
                                    <i class="fas fa-user text-secondary"></i>
                                    Cliente general
                                @endif
                            </td>
                            <td>
                                @if(isset($venta->items) && $venta->items->count() > 0)
                                    <span class="badge badge-info">{{ $venta->items->count() }} items</span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $venta->items->pluck('product.name')->take(2)->join(', ') }}
                                        @if($venta->items->count() > 2)
                                            ...
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">Sin detalle</span>
                                @endif
                            </td>
                            <td>
                                @if($venta->payment_method_id)
                                    <span class="badge badge-secondary">{{ $venta->paymentMethod->name ?? 'No especificado' }}</span>
                                @else
                                    <span class="badge badge-secondary">Efectivo</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">
                                    ${{ number_format($venta->total ?? $venta->total_amount ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Imprimir ticket">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @if(auth()->user()->isAdmin())
                                    <button class="btn btn-sm btn-danger" title="Anular">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i><br>
                                No hay ventas registradas el día de hoy.<br>
                                <a href="{{ route('box.pos') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Realizar primera venta
                                </a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Gráfico de ventas por hora --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Ventas por Hora
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="ventasPorHora" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Métodos de Pago
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="metodosPago" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top cajeros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trophy"></i> Ranking de Cajeros (Hoy)
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @if(isset($ventas) && $ventas->count() > 0)
                    @foreach($ventas->groupBy('user.name')->sortByDesc->count()->take(3) as $cajero => $ventasCajero)
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                                <h5>{{ $cajero }}</h5>
                                <p>
                                    <strong>{{ $ventasCajero->count() }}</strong> ventas<br>
                                    <span class="text-success">
                                        ${{ number_format($ventasCajero->sum('total'), 0, ',', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-12 text-center text-muted">
                        No hay datos de ventas para mostrar ranking
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .inner h3 {
            font-size: 2rem;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Datos para los gráficos (en una implementación real vendrían del servidor)
        const ventasPorHoraData = {
            labels: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
            datasets: [{
                label: 'Ventas',
                data: [2, 4, 6, 8, 12, 15, 10, 8, 6, 4],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true
            }]
        };

        new Chart(document.getElementById('ventasPorHora'), {
            type: 'line',
            data: ventasPorHoraData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de métodos de pago
        const metodosPagoData = {
            labels: ['Efectivo', 'Tarjeta', 'Transferencia'],
            datasets: [{
                data: [60, 30, 10],
                backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56']
            }]
        };

        new Chart(document.getElementById('metodosPago'), {
            type: 'doughnut',
            data: metodosPagoData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@stop
