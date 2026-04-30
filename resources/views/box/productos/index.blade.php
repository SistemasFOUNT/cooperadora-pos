@extends('adminlte::page')

@section('title', 'BOX - Gestión de Productos')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-box text-primary"></i> Gestión de Productos - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Productos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Filtros y acciones --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Buscar producto:</label>
                                <input type="text" class="form-control" placeholder="Nombre o código...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Categoría:</label>
                                <select class="form-control">
                                    <option value="">Todas</option>
                                    <option value="laboratory">Laboratorio</option>
                                    <option value="dental_treatment">Tratamiento Dental</option>
                                    <option value="student_fee">Cuota Estudiantil</option>
                                    <option value="other">Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select class="form-control">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-success btn-block mb-2">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                    <button class="btn btn-info btn-block mb-2">
                        <i class="fas fa-upload"></i> Importar Excel
                    </button>
                    <button class="btn btn-secondary btn-block">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de productos --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Lista de Productos
            </h3>
            <div class="card-tools">
                <span class="badge badge-primary">
                    @if(isset($productos))
                        {{ $productos->total() }} productos
                    @else
                        Productos disponibles
                    @endif
                </span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($productos) && $productos->count() > 0)
                        @foreach($productos as $producto)
                        <tr>
                            <td>
                                <code>{{ $producto->code }}</code>
                            </td>
                            <td>
                                <strong>{{ $producto->name }}</strong>
                                @if($producto->description)
                                    <br><small class="text-muted">{{ Str::limit($producto->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst(str_replace('_', ' ', $producto->category)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $producto->type)) }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    ${{ number_format($producto->price, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                @if($producto->track_stock)
                                    <span class="badge badge-{{ $producto->stock > $producto->min_stock ? 'success' : 'warning' }}">
                                        {{ $producto->stock }}
                                    </span>
                                    @if($producto->stock <= $producto->min_stock)
                                        <i class="fas fa-exclamation-triangle text-warning" title="Stock bajo"></i>
                                    @endif
                                @else
                                    <span class="text-muted">No aplica</span>
                                @endif
                            </td>
                            <td>
                                @if($producto->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    @if($producto->is_active)
                                        <button class="btn btn-sm btn-danger" title="Desactivar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-success" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-3x mb-3"></i><br>
                                No hay productos disponibles.<br>
                                <a href="#" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Agregar primer producto
                                </a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if(isset($productos) && $productos->hasPages())
        <div class="card-footer clearfix">
            {{ $productos->links() }}
        </div>
        @endif
    </div>

    {{-- Estadísticas rápidas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>
                        @if(isset($productos))
                            {{ $productos->where('is_active', true)->count() }}
                        @else
                            0
                        @endif
                    </h3>
                    <p>Productos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>
                        @if(isset($productos))
                            {{ $productos->where('stock', '<=', DB::raw('min_stock'))->where('track_stock', true)->count() }}
                        @else
                            0
                        @endif
                    </h3>
                    <p>Stock Bajo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>
                        @if(isset($productos))
                            {{ $productos->where('category', 'laboratory')->count() }}
                        @else
                            0
                        @endif
                    </h3>
                    <p>Productos Laboratorio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-flask"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>
                        @if(isset($productos))
                            {{ $productos->where('is_active', false)->count() }}
                        @else
                            0
                        @endif
                    </h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .small-box .inner h3 {
            font-size: 2rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Funcionalidad de búsqueda en tiempo real
            $('input[placeholder="Nombre o código..."]').on('input', function() {
                // Aquí iría la lógica de filtrado en tiempo real
                console.log('Buscando:', $(this).val());
            });

            // Confirmación antes de desactivar productos
            $('.btn-danger[title="Desactivar"]').click(function() {
                Swal.fire({
                    title: '¿Desactivar producto?',
                    text: 'El producto no estará disponible para venta',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí iría la lógica para desactivar
                        console.log('Desactivando producto...');
                    }
                });
            });
        });
    </script>
@stop
