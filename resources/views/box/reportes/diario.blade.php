@extends('adminlte::page')

@section('title', 'BOX - Reporte Diario')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-calendar-day text-primary"></i> Reporte Diario - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Reporte Diario</li>
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
            <form method="GET" action="{{ route('box.reportes.diario') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha:</label>
                            <input type="date" name="fecha" class="form-control"
                                   value="{{ $fecha ?? date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cajero:</label>
                            <select name="cajero_id" class="form-control">
                                <option value="">Todos los cajeros</option>
                                @if(isset($cajeros))
                                    @foreach($cajeros as $cajero)
                                        <option value="{{ $cajero->id }}"
                                                @if(isset($cajero_id) && $cajero_id == $cajero->id) selected @endif>
                                            {{ $cajero->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Generar
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
                            <button type="button" class="btn btn-info btn-block" onclick="imprimir()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas principales --}}
    @if(isset($estadisticas))
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total_ventas'] }}</h3>
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
                    <h3>${{ number_format($estadisticas['total_recaudado'], 0, ',', '.') }}</h3>
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
                    <h3>{{ $estadisticas['total_productos_vendidos'] }}</h3>
                    <p>Productos Vendidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['ticket_promedio'], 0, ',', '.') }}</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Detalle de ventas --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Detalle de Ventas
                        @if(isset($fecha))
                            - {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        @endif
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if(isset($ventas) && $ventas->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Nº Venta</th>
                                    <th>Cajero</th>
                                    <th>Productos</th>
                                    <th>Método Pago</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $venta)
                                <tr>
                                    <td>
                                        <strong>{{ $venta->created_at->format('H:i:s') }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $venta->sale_number ?? 'V-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $venta->user->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($venta->items->count() > 0)
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-info dropdown-toggle"
                                                        data-toggle="dropdown">
                                                    {{ $venta->items->count() }} items
                                                </button>
                                                <div class="dropdown-menu">
                                                    @foreach($venta->items as $item)
                                                        <div class="dropdown-item-text">
                                                            <strong>{{ $item->quantity }}x</strong>
                                                            {{ $item->product_name ?? $item->product->name ?? 'Producto' }}
                                                            <br>
                                                            <small class="text-muted">
                                                                ${{ number_format($item->unit_price, 0, ',', '.') }} c/u
                                                                = ${{ number_format($item->total, 0, ',', '.') }}
                                                            </small>
                                                        </div>
                                                        @if(!$loop->last)
                                                            <div class="dropdown-divider"></div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Sin items</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $venta->paymentMethod->name ?? 'Efectivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            ${{ number_format($venta->total, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" title="Ver detalle"
                                                    onclick="verDetalle({{ $venta->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" title="Imprimir ticket"
                                                    onclick="imprimirTicket({{ $venta->id }})">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="5" class="text-right">TOTAL DEL DÍA:</th>
                                    <th>${{ number_format($ventas->sum('total'), 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay ventas registradas</h4>
                            <p class="text-muted">
                                No se encontraron ventas para
                                @if(isset($fecha))
                                    el {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                @else
                                    la fecha seleccionada
                                @endif
                            </p>
                            <a href="{{ route('box.pos') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Realizar Nueva Venta
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Resúmenes laterales --}}
        <div class="col-md-4">
            {{-- Productos más vendidos --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i> Top Productos
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if(isset($productos_vendidos) && $productos_vendidos->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($productos_vendidos->take(5) as $index => $producto)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-primary mr-2">{{ $index + 1 }}</span>
                                            <div>
                                                <h6 class="mb-1">{{ $producto['producto'] }}</h6>
                                                <small class="text-muted">{{ $producto['codigo'] }}</small>
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
                        <p class="text-center text-muted py-3">
                            No hay productos vendidos
                        </p>
                    @endif
                </div>
            </div>

            {{-- Métodos de pago --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card"></i> Métodos de Pago
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($metodos_pago) && $metodos_pago->count() > 0)
                        @php $total_general = $metodos_pago->sum('monto_total'); @endphp
                        @foreach($metodos_pago as $metodo)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $metodo['metodo'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $metodo['cantidad_transacciones'] }} transacciones</small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">
                                    ${{ number_format($metodo['monto_total'], 0, ',', '.') }}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    @if($total_general > 0)
                                        {{ number_format(($metodo['monto_total'] / $total_general) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </small>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                        @endforeach
                    @else
                        <p class="text-center text-muted">
                            No hay datos de métodos de pago
                        </p>
                    @endif
                </div>
            </div>

            {{-- Información adicional --}}
            @if(isset($estadisticas))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información
                    </h3>
                </div>
                <div class="card-body">
                    @if($estadisticas['primera_venta'])
                        <p><strong>Primera venta:</strong> {{ \Carbon\Carbon::parse($estadisticas['primera_venta'])->format('H:i:s') }}</p>
                    @endif
                    @if($estadisticas['ultima_venta'])
                        <p><strong>Última venta:</strong> {{ \Carbon\Carbon::parse($estadisticas['ultima_venta'])->format('H:i:s') }}</p>
                    @endif
                    <p><strong>Fecha del reporte:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                    <p><strong>Generado por:</strong> {{ auth()->user()->name }}</p>
                </div>
            </div>
            @endif
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
        .dropdown-menu {
            min-width: 300px;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        .list-group-item:first-child {
            border-top: none;
        }
        .list-group-item:last-child {
            border-bottom: none;
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
                title: 'Detalle de Venta #' + ventaId,
                html: '<p>Cargando información detallada...</p>',
                icon: 'info'
            });
        }

        function imprimirTicket(ventaId) {
            Swal.fire({
                title: '¿Reimprimir ticket?',
                text: 'Se enviará nuevamente el ticket a la impresora',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, imprimir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la lógica para reimprimir
                    Swal.fire({
                        icon: 'success',
                        title: 'Ticket Enviado',
                        text: 'El ticket se ha enviado a la impresora',
                        timer: 2000
                    });
                }
            });
        }

        function exportarExcel() {
            Swal.fire({
                icon: 'info',
                title: 'Exportando a Excel',
                text: 'Generando archivo Excel del reporte...',
                timer: 3000
            });

            // Simular descarga después de 3 segundos
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = 'data:application/vnd.ms-excel,';
                link.download = 'reporte_diario_box_' + new Date().toISOString().split('T')[0] + '.xls';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }, 3000);
        }

        function imprimir() {
            window.print();
        }

        // Auto-submit cuando cambie el cajero
        document.addEventListener('DOMContentLoaded', function() {
            const cajeroSelect = document.querySelector('select[name="cajero_id"]');
            if (cajeroSelect) {
                cajeroSelect.addEventListener('change', function() {
                    document.querySelector('form').submit();
                });
            }
        });
    </script>
@stop
