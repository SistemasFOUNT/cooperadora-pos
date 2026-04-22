@extends('adminlte::page')

@section('title', 'Gestión de Productos')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-boxes"></i> Gestión de Productos</h1>
        <div>
            <a href="{{ route('productos.inventario') }}" class="btn btn-info">
                <i class="fas fa-warehouse"></i> Inventario
            </a>
            <a href="{{ route('productos.categorias') }}" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Categorías
            </a>
            <a href="{{ route('productos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Productos</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Buscar producto..." id="searchInput">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap" id="productosTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                    <tr class="{{ $producto->stock <= ($producto->min_stock ?? 5) ? 'table-warning' : '' }}">
                        <td>
                            <code>{{ $producto->code ?? $producto->barcode ?? 'Sin código' }}</code>
                        </td>
                        <td>
                            <strong>{{ $producto->name }}</strong>
                            @if($producto->description)
                                <br><small class="text-muted">{{ \Str::limit($producto->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($producto->category)
                                <span class="badge badge-secondary">{{ $producto->category }}</span>
                            @else
                                <span class="text-muted">Sin categoría</span>
                            @endif
                        </td>
                        <td>
                            <strong>${{ number_format($producto->price, 2) }}</strong>
                            @if($producto->cost)
                                <br><small class="text-muted">Costo: ${{ number_format($producto->cost, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $producto->stock <= ($producto->min_stock ?? 5) ? 'warning' : 'success' }}">
                                {{ $producto->stock }}
                            </span>
                            @if($producto->min_stock)
                                <br><small class="text-muted">Mín: {{ $producto->min_stock }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $producto->is_active ? 'success' : 'danger' }}">
                                {{ $producto->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" title="Actualizar Stock" 
                                        onclick="mostrarModalStock({{ $producto->id }}, '{{ $producto->name }}', {{ $producto->stock }})">
                                    <i class="fas fa-cube"></i>
                                </button>
                                <form method="POST" action="{{ route('productos.toggle-activo', $producto) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $producto->is_active ? 'outline-danger' : 'outline-success' }}" 
                                            title="{{ $producto->is_active ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $producto->is_active ? 'times' : 'check' }}"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                        onclick="confirmarEliminacion({{ $producto->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($productos->hasPages())
        <div class="card-footer clearfix">
            {{ $productos->links() }}
        </div>
        @endif
    </div>

    <!-- Modal para actualizar stock -->
    <div class="modal fade" id="stockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Stock</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formStock" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="stockProducto">Producto:</label>
                            <input type="text" class="form-control" id="stockProducto" readonly>
                        </div>
                        <div class="form-group">
                            <label for="stockActual">Stock Actual:</label>
                            <input type="text" class="form-control" id="stockActual" readonly>
                        </div>
                        <div class="form-group">
                            <label for="stockNuevo">Nuevo Stock:</label>
                            <input type="number" class="form-control" id="stockNuevo" name="stock" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="motivoStock">Motivo (Opcional):</label>
                            <input type="text" class="form-control" id="motivoStock" name="motivo" 
                                   placeholder="Ej: Ajuste de inventario, Compra, Venta manual">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este producto? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function mostrarModalStock(id, nombre, stockActual) {
    $('#stockProducto').val(nombre);
    $('#stockActual').val(stockActual);
    $('#stockNuevo').val(stockActual);
    $('#motivoStock').val('');
    $('#formStock').attr('action', '/productos/' + id + '/actualizar-stock');
    $('#stockModal').modal('show');
}

function confirmarEliminacion(id) {
    $('#formEliminar').attr('action', '/productos/' + id);
    $('#confirmarEliminacionModal').modal('show');
}

// Filtro de búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productosTable tbody tr');
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});
</script>
@stop

@section('css')
<style>
.table td {
    vertical-align: middle;
}

.table-warning {
    background-color: #fff3cd !important;
}
</style>
@stop