@extends('adminlte::page')

@section('title', 'BOX - Reporte de Inventario')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-boxes text-primary"></i> Inventario de Productos - BOX</h1>
            <small class="text-muted">Control de stock de productos físicos (excluye servicios odontológicos)</small>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item"><a href="{{ route('box.reportes') }}">Reportes</a></li>
                <li class="breadcrumb-item active">Inventario</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filtros de Productos Físicos
            </h3>
            <div class="card-tools">
                <span class="badge badge-info">Solo productos con stock físico</span>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('box.reportes.inventario') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Categoría:</label>
                            <select name="categoria" class="form-control">
                                <option value="todas" @if($categoria == 'todas') selected @endif>Todas las categorías</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat }}" @if($categoria == $cat) selected @endif>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Filtro de Stock:</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="stock_minimo" value="1"
                                       class="form-check-input" id="stock_minimo"
                                       @if($stock_minimo) checked @endif>
                                <label class="form-check-label" for="stock_minimo">
                                    Solo productos bajo stock mínimo
                                </label>
                            </div>
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

    {{-- Estadísticas de inventario --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total_productos'] }}</h3>
                    <p>Productos Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['valor_total_inventario'], 0, ',', '.') }}</h3>
                    <p>Valor Total Inventario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['productos_bajo_stock'] }}</h3>
                    <p>Bajo Stock Mínimo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['productos_sin_stock'] }}</h3>
                    <p>Sin Stock</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Información sobre servicios --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> Información sobre Inventario</h5>
                Este reporte incluye <strong>únicamente productos físicos</strong> que manejan stock.
                Los servicios odontológicos ({{ $total_servicios }} servicios activos) no aparecen en este inventario
                ya que no requieren control de stock físico.
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Lista de productos --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Inventario de Productos
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($productos->count() > 0)
                        <table class="table table-hover text-nowrap table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mín.</th>
                                    <th>Costo Unit.</th>
                                    <th>Valor Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                <tr class="@if($producto->stock <= 0) table-danger @elseif($producto->stock <= $producto->min_stock) table-warning @endif">
                                    <td>
                                        <strong>{{ $producto->code }}</strong>
                                    </td>
                                    <td>
                                        {{ $producto->name }}
                                        @if($producto->description)
                                            <br><small class="text-muted">{{ Str::limit($producto->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ ucfirst($producto->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="@if($producto->stock <= 0) text-danger @elseif($producto->stock <= $producto->min_stock) text-warning @else text-success @endif">
                                            {{ $producto->stock }}
                                        </strong>
                                    </td>
                                    <td>{{ $producto->min_stock }}</td>
                                    <td>${{ number_format($producto->cost, 0, ',', '.') }}</td>
                                    <td>
                                        <strong>${{ number_format($producto->stock * $producto->cost, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @if($producto->stock <= 0)
                                            <span class="badge badge-danger">Sin Stock</span>
                                        @elseif($producto->stock <= $producto->min_stock)
                                            <span class="badge badge-warning">Bajo Stock</span>
                                        @else
                                            <span class="badge badge-success">OK</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="6">TOTAL INVENTARIO:</th>
                                    <th>${{ number_format($productos->sum(function($p) { return $p->stock * $p->cost; }), 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay productos</h4>
                            <p class="text-muted">No se encontraron productos con los filtros aplicados</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Movimientos recientes --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Movimientos Recientes de Stock
                    </h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 300px;">
                    @if($movimientos_recientes->count() > 0)
                        <table class="table table-hover text-nowrap table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movimientos_recientes as $movimiento)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->created_at)->format('d/m H:i') }}</td>
                                    <td>
                                        <strong>{{ $movimiento->producto_name }}</strong><br>
                                        <small class="text-muted">{{ $movimiento->producto_code }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $movimiento->type == 'entrada' ? 'success' : 'danger' }}">
                                            {{ ucfirst($movimiento->type ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="{{ $movimiento->type == 'entrada' ? 'text-success' : 'text-danger' }}">
                                            {{ $movimiento->type == 'entrada' ? '+' : '-' }}{{ $movimiento->quantity ?? 0 }}
                                        </strong>
                                    </td>
                                    <td>
                                        <small>{{ $movimiento->reason ?? 'Sin especificar' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-3">
                            <small class="text-muted">No hay movimientos recientes de stock</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panel lateral con estadísticas --}}
        <div class="col-md-4">
            {{-- Análisis por categorías --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Análisis por Categorías
                    </h3>
                </div>
                <div class="card-body">
                    @if($analisis_categorias->count() > 0)
                        @foreach($analisis_categorias as $categoria => $datos)
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 border-bottom">
                            <div>
                                <strong>{{ ucfirst($categoria) }}</strong><br>
                                <small class="text-muted">
                                    {{ $datos['cantidad_productos'] }} productos
                                    @if($datos['productos_bajo_stock'] > 0)
                                        <span class="text-warning">({{ $datos['productos_bajo_stock'] }} bajo stock)</span>
                                    @endif
                                </small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">
                                    ${{ number_format($datos['valor_total'], 0, ',', '.') }}
                                </strong><br>
                                <small class="text-muted">{{ $datos['stock_total'] }} unidades</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No hay categorías para mostrar</p>
                    @endif
                </div>
            </div>

            {{-- Productos más vendidos --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-fire"></i> Más Vendidos (30 días)
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($productos_mas_vendidos->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($productos_mas_vendidos->take(8) as $index => $producto)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-primary mr-2">{{ $index + 1 }}</span>
                                            <div>
                                                <h6 class="mb-1">{{ $producto->name }}</h6>
                                                <small class="text-muted">{{ $producto->code }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success">{{ $producto->total_vendido }} vendido</span>
                                        <br>
                                        <small class="text-{{ $producto->stock <= $producto->min_stock ? 'warning' : 'muted' }}">
                                            Stock: {{ $producto->stock }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted py-3">No hay datos de ventas</p>
                    @endif
                </div>
            </div>

            {{-- Información del reporte --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información del Reporte
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Filtros aplicados:</strong></p>
                    <ul class="list-unstyled ml-3">
                        <li><strong>Categoría:</strong> {{ $categoria == 'todas' ? 'Todas' : ucfirst($categoria) }}</li>
                        <li><strong>Stock bajo mínimo:</strong> {{ $stock_minimo ? 'Sí' : 'No' }}</li>
                    </ul>
                    <hr>
                    <p><strong>Productos mostrados:</strong> {{ $productos->count() }}</p>
                    <p><strong>Valor promedio por producto:</strong>
                        ${{ $productos->count() > 0 ? number_format($productos->sum(function($p) { return $p->stock * $p->cost; }) / $productos->count(), 0, ',', '.') : '0' }}
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
        .table td {
            vertical-align: middle;
        }
        .table-sm th, .table-sm td {
            padding: 0.3rem;
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
        function exportarExcel() {
            Swal.fire({
                icon: 'info',
                title: 'Exportando Inventario',
                text: 'Generando archivo Excel del inventario...',
                timer: 3000
            });
        }

        // Resaltar productos con alertas
        document.addEventListener('DOMContentLoaded', function() {
            const alertasBajoStock = {{ $estadisticas['productos_bajo_stock'] }};
            const alertasSinStock = {{ $estadisticas['productos_sin_stock'] }};

            if (alertasBajoStock > 0 || alertasSinStock > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alertas de Inventario',
                    html: `
                        ${alertasSinStock > 0 ? `<p><strong>${alertasSinStock}</strong> productos sin stock</p>` : ''}
                        ${alertasBajoStock > 0 ? `<p><strong>${alertasBajoStock}</strong> productos bajo stock mínimo</p>` : ''}
                    `,
                    confirmButtonText: 'Entendido'
                });
            }
        });
    </script>
@stop
