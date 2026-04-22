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
        </div>

        <div class="card-body">
            <table class="table table-striped table-hover" id="productosTable">
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

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css">
<style>
.table td {
    vertical-align: middle;
}
.table-warning {
    background-color: #fff3cd !important;
}
.badge {
    font-size: 0.85em;
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>

<script>
// Función de búsqueda personalizada - busca desde el inicio de los campos
$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
        var searchTerm = $('.dataTables_filter input').val();
        if (!searchTerm) return true;
        
        // Buscar solo al inicio de cualquier columna (excluyendo HTML)
        return data.some(function(cellData) {
            // Remover HTML y espacios extra
            var cleanData = cellData.replace(/<[^>]*>/g, '').trim();
            return cleanData.toLowerCase().indexOf(searchTerm.toLowerCase()) === 0;
        });
    }
);

$(document).ready(function() {
    $('#productosTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 1, "asc" ]], // Ordenar por nombre (columna 1)
        "columnDefs": [
            {
                "targets": [6], // Columna de acciones
                "orderable": false,
                "searchable": false
            }
        ],
        "pageLength": 20,
        "lengthMenu": [[20, 50, 100, -1], [20, 50, 100, "Todos"]],
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "searching": true,
        "paging": true,
        "info": true
    });
});

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
</script>
@stop
