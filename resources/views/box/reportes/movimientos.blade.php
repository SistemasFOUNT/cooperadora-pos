@extends('adminlte::page')

@section('title', 'BOX - Movimientos de Caja')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt text-primary"></i> Movimientos de Caja - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item"><a href="{{ route('box.reportes') }}">Reportes</a></li>
                <li class="breadcrumb-item active">Movimientos de Caja</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('box.reportes.movimientos') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Desde:</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                   value="{{ $fecha_desde }}">
                        </div>
                    </div>
                    <div class="col-md-3">
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
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-success btn-block" onclick="exportarExcel()">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" onclick="window.print()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total_movimientos'] }}</h3>
                    <p>Movimientos Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['total_ingresos'], 0, ',', '.') }}</h3>
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
                    <h3>${{ number_format($estadisticas['promedio_diario'], 0, ',', '.') }}</h3>
                    <p>Promedio Diario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['movimiento_mayor'], 0, ',', '.') }}</h3>
                    <p>Movimiento Mayor</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Lista de movimientos --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Listado de Movimientos
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($ventas->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Tipo</th>
                                    <th>Usuario</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $venta)
                                <tr>
                                    <td>
                                        <strong>{{ $venta->created_at->format('d/m/Y') }}</strong><br>
                                        <small>{{ $venta->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-arrow-down"></i> Ingreso
                                        </span>
                                    </td>
                                    <td>
                                        {{ $venta->user->name ?? 'Sistema' }}
                                    </td>
                                    <td>
                                        <strong>Venta #{{ $venta->id }}</strong><br>
                                        <small>{{ $venta->items->count() }} productos</small>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            ${{ number_format($venta->total, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                                onclick="verDetalle({{ $venta->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="4">TOTAL PERÍODO:</th>
                                    <th>${{ number_format($ventas->sum('total'), 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay movimientos</h4>
                            <p class="text-muted">No se encontraron movimientos en el período seleccionado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Resumen por día --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar"></i> Resumen por Día
                    </h3>
                </div>
                <div class="card-body">
                    @if($movimientos_por_dia->count() > 0)
                        @foreach($movimientos_por_dia as $fecha => $resumen)
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 border-bottom">
                            <div>
                                <strong>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</strong><br>
                                <small class="text-muted">{{ $resumen['cantidad'] }} movimientos</small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">
                                    ${{ number_format($resumen['total'], 0, ',', '.') }}
                                </strong><br>
                                <small class="text-muted">
                                    Prom: ${{ number_format($resumen['promedio'], 0, ',', '.') }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No hay datos para mostrar</p>
                    @endif
                </div>
            </div>

            {{-- Información adicional --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información del Reporte
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}</p>
                    <p><strong>Días:</strong> {{ \Carbon\Carbon::parse($fecha_desde)->diffInDays($fecha_hasta) + 1 }} días</p>
                    @if($estadisticas['movimiento_menor'])
                    <p><strong>Movimiento Menor:</strong> ${{ number_format($estadisticas['movimiento_menor'], 0, ',', '.') }}</p>
                    @endif
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
        .table td {
            vertical-align: middle;
        }
        @media print {
            .btn, .card-header, .breadcrumb {
                display: none !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        function verDetalle(ventaId) {
            Swal.fire({
                title: 'Detalle del Movimiento #' + ventaId,
                html: '<p>Cargando información detallada...</p>',
                icon: 'info'
            });
        }

        function exportarExcel() {
            Swal.fire({
                icon: 'info',
                title: 'Exportando a Excel',
                text: 'Generando archivo de movimientos...',
                timer: 3000
            });
        }
    </script>
@stop
