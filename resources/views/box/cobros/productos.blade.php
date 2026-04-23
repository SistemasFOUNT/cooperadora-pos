@extends('adminlte::page')

@section('title', 'BOX - Cobros de Productos')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .carrito-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        max-height: 70vh;
        overflow-y: auto;
    }
    .producto-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .producto-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Estilos específicos para modal de efectivo */
    #modalPagoEfectivo .modal-header {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
    }

    #detalle-venta-efectivo {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 2px solid #e9ecef;
    }

    #monto-cliente-efectivo {
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
    }

    #monto-cliente-efectivo:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    #mensaje-vuelto {
        background: linear-gradient(135deg, #d1ecf1, #bee5eb);
        border-color: #17a2b8;
    }

    #mensaje-exacto {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border-color: #28a745;
    }

    #mensaje-insuficiente {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border-color: #ffc107;
    }

    .text-success.h5 {
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    #btn-imprimir-ticket-efectivo:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* DATATABLES - Mejoras de bordes específicas para esta vista */
    table.dataTable,
    #productosTable {
        border: 1px solid #6c757d !important;
        border-radius: 4px;
    }

    table.dataTable thead th,
    table.dataTable thead td,
    #productosTable thead th,
    #productosTable thead td {
        border-bottom: 2px solid #6c757d !important;
        background-color: #f8f9fa !important;
    }

    table.dataTable tbody td,
    #productosTable tbody td {
        border-top: 1px solid #6c757d !important;
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
    }

    table.dataTable tbody tr:hover td,
    #productosTable tbody tr:hover td {
        background-color: #f1f3f4 !important;
    }

    /* Controles de DataTables */
    .dataTables_filter input {
        border: 1px solid #6c757d !important;
        border-radius: 4px;
        padding: 6px 12px;
    }

    .dataTables_length select {
        border: 1px solid #6c757d !important;
        border-radius: 4px;
        padding: 2px 24px 2px 8px;
        width: auto !important;
        min-width: 60px;
        max-width: 100px;
        font-size: 14px;
    }
</style>
@stop

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-shopping-bag text-primary"></i> Cobros de Productos</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">Productos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle"></i> Funcionalidad en Desarrollo</h4>
                <p>Esta sección permitirá realizar cobros de productos de manera eficiente.</p>
                <p><strong>Características próximas:</strong></p>
                <ul>
                    <li>Búsqueda rápida de productos</li>
                    <li>Carrito de compras</li>
                    <li>Aplicación de descuentos</li>
                    <li>Métodos de pago múltiples</li>
                    <li>Impresión de tickets</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Productos Disponibles</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="productosTable">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $producto)
                                <tr>
                                    <td><strong>{{ $producto->code }}</strong></td>
                                    <td>
                                        {{ $producto->name }}
                                        @if($producto->description)
                                            <br><small class="text-muted">{{ Str::limit($producto->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($producto->type)
                                            @case('product')
                                                <span class="badge badge-primary">Producto</span>
                                                @break
                                            @case('service')
                                                <span class="badge badge-info">Servicio</span>
                                                @break
                                            @case('treatment')
                                                <span class="badge badge-success">Tratamiento</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $producto->type }}</span>
                                        @endswitch
                                    </td>
                                    <td><strong>${{ number_format($producto->price, 2) }}</strong></td>
                                    <td>
                                        @if($producto->track_stock)
                                            <span class="badge {{ $producto->stock <= $producto->min_stock ? 'badge-danger' : 'badge-success' }}">
                                                {{ $producto->stock }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm agregar-producto"
                                                data-id="{{ $producto->id }}"
                                                data-code="{{ $producto->code }}"
                                                data-name="{{ $producto->name }}"
                                                data-price="{{ $producto->price }}"
                                                data-stock="{{ $producto->stock }}"
                                                data-track-stock="{{ $producto->track_stock }}">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> No hay productos disponibles
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Carrito de Compras</h3>
                </div>
                <div class="card-body" id="carrito-items">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-shopping-cart fa-3x"></i>
                        <p class="mt-2">El carrito está vacío</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <strong id="total-carrito">Total: $0.00</strong>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success btn-sm" id="btn-cobrar" disabled>
                                <i class="fas fa-credit-card"></i> Cobrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago -->
    <div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="modalPagoLabel">
                        <i class="fas fa-cash-register"></i> Procesar Pago
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-pago">
                        <div class="row">
                            <!-- Resumen de compra -->
                            <div class="col-md-6">
                                <h6><i class="fas fa-shopping-cart"></i> Resumen de Compra</h6>
                                <div id="resumen-compra" class="border rounded p-2 mb-3" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Se llena dinámicamente -->
                                </div>

                                <!-- Descuentos -->
                                <div class="form-group">
                                    <label for="tipo-descuento">Aplicar Descuento</label>
                                    <select class="form-control" id="tipo-descuento">
                                        <option value="">Sin descuento</option>
                                        <option value="porcentaje">Porcentaje (%)</option>
                                        <option value="fijo">Monto fijo ($)</option>
                                        <option value="estudiante">Descuento estudiante (10%)</option>
                                        <option value="empleado">Descuento empleado (15%)</option>
                                    </select>
                                </div>

                                <div class="form-group" id="grupo-valor-descuento" style="display: none;">
                                    <label for="valor-descuento">Valor del descuento</label>
                                    <input type="number" class="form-control" id="valor-descuento" step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Métodos de pago -->
                            <div class="col-md-6">
                                <h6><i class="fas fa-credit-card"></i> Método de Pago</h6>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodoPago" value="efectivo" id="efectivo" checked>
                                    <label class="form-check-label" for="efectivo">
                                        <i class="fas fa-money-bill-wave text-success"></i> Efectivo
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodoPago" value="tarjeta" id="tarjeta">
                                    <label class="form-check-label" for="tarjeta">
                                        <i class="fas fa-credit-card text-primary"></i> Tarjeta Débito/Crédito
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodoPago" value="transferencia" id="transferencia">
                                    <label class="form-check-label" for="transferencia">
                                        <i class="fas fa-exchange-alt text-info"></i> Transferencia
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="metodoPago" value="mixto" id="mixto">
                                    <label class="form-check-label" for="mixto">
                                        <i class="fas fa-coins text-warning"></i> Pago Mixto
                                    </label>
                                </div>

                                <!-- Campos para pago mixto -->
                                <div id="campos-mixto" style="display: none;">
                                    <div class="row">
                                        <div class="col-6">
                                            <label>Efectivo</label>
                                            <input type="number" class="form-control" id="mixto-efectivo" step="0.01" min="0">
                                        </div>
                                        <div class="col-6">
                                            <label>Tarjeta</label>
                                            <input type="number" class="form-control" id="mixto-tarjeta" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Subtotal: $<span id="modal-subtotal">0.00</span></strong>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Descuento: $<span id="modal-descuento">0.00</span></strong>
                                            </div>
                                            <div class="col-md-3">
                                                <strong class="text-success">Total: $<span id="modal-total">0.00</span></strong>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label for="observaciones">Observaciones</label>
                                                    <textarea class="form-control form-control-sm" id="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="btn-procesar-pago">
                        <i class="fas fa-check"></i> Procesar Pago
                    </button>
                    <button type="button" class="btn btn-info" id="btn-imprimir-ticket" style="display: none;">
                        <i class="fas fa-print"></i> Imprimir Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal específico para Pago en Efectivo -->
    <div class="modal fade" id="modalPagoEfectivo" tabindex="-1" aria-labelledby="modalPagoEfectivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="modalPagoEfectivoLabel">
                        <i class="fas fa-money-bill-wave"></i> Pago en Efectivo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Detalle de la venta -->
                        <div class="col-md-6">
                            <h6><i class="fas fa-receipt"></i> Detalle de la Venta</h6>
                            <div id="detalle-venta-efectivo" class="border rounded p-3 mb-3" style="max-height: 300px; overflow-y: auto; background-color: #f8f9fa;">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>

                        <!-- Cálculos y pago -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calculator"></i> Resumen de Cobro</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6"><strong>Subtotal:</strong></div>
                                        <div class="col-6 text-right">$<span id="efectivo-subtotal">0.00</span></div>
                                    </div>
                                    <div class="row mb-2" id="fila-descuento-efectivo" style="display: none;">
                                        <div class="col-6"><strong>Descuento:</strong></div>
                                        <div class="col-6 text-right text-danger">-$<span id="efectivo-descuento">0.00</span></div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong class="text-success">TOTAL A COBRAR:</strong></div>
                                        <div class="col-6 text-right"><strong class="text-success h5">$<span id="efectivo-total">0.00</span></strong></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="monto-cliente-efectivo" class="font-weight-bold">Monto recibido del cliente:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control form-control-lg" id="monto-cliente-efectivo"
                                                   step="0.01" min="0" placeholder="0.00" autofocus>
                                        </div>
                                    </div>

                                    <!-- Resultado del cálculo -->
                                    <div id="resultado-pago" class="mt-3">
                                        <div id="mensaje-insuficiente" class="alert alert-warning" style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong class="text-dark">Monto insuficiente</strong><br>
                                            <span class="text-dark">Faltan: <span class="h6 text-dark">$<span id="falta-monto">0.00</span></span></span>
                                        </div>

                                        <div id="mensaje-exacto" class="alert alert-success" style="display: none;">
                                            <i class="fas fa-check-circle"></i>
                                            <strong class="text-dark">Pago exacto</strong><br>
                                            <span class="text-dark">Sin vuelto a entregar</span>
                                        </div>

                                        <div id="mensaje-vuelto" class="alert alert-info" style="display: none;">
                                            <i class="fas fa-hand-holding-usd"></i>
                                            <strong class="text-dark">Vuelto a entregar:</strong><br>
                                            <span class="h4 text-dark">$<span id="vuelto-cliente">0.00</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-imprimir-ticket-efectivo" disabled>
                        <i class="fas fa-print"></i> Imprimir Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    let carrito = [];
    let total = 0;

    // Agregar producto al carrito
    $(document).on('click', '.agregar-producto', function() {
        const producto = {
            id: $(this).data('id'),
            code: $(this).data('code'),
            name: $(this).data('name'),
            price: parseFloat($(this).data('price')),
            stock: parseInt($(this).data('stock')),
            trackStock: $(this).data('track-stock'),
            quantity: 1
        };

        // Verificar si el producto ya está en el carrito
        const productoExistente = carrito.find(item => item.id === producto.id);

        if (productoExistente) {
            // Verificar stock si es necesario
            if (producto.trackStock && productoExistente.quantity >= producto.stock) {
                alert('Stock insuficiente');
                return;
            }
            productoExistente.quantity++;
        } else {
            carrito.push(producto);
        }

        actualizarCarrito();
    });

    // Eliminar producto del carrito
    $(document).on('click', '.eliminar-producto', function() {
        const productId = $(this).data('id');
        carrito = carrito.filter(item => item.id !== productId);
        actualizarCarrito();
    });

    // Cambiar cantidad
    $(document).on('change', '.cantidad-input', function() {
        const productId = $(this).data('id');
        const nuevaCantidad = parseInt($(this).val());
        const producto = carrito.find(item => item.id === productId);

        if (producto) {
            if (producto.trackStock && nuevaCantidad > producto.stock) {
                alert('Stock insuficiente');
                $(this).val(producto.quantity);
                return;
            }

            if (nuevaCantidad <= 0) {
                carrito = carrito.filter(item => item.id !== productId);
            } else {
                producto.quantity = nuevaCantidad;
            }
            actualizarCarrito();
        }
    });

    function actualizarCarrito() {
        const carritoBody = $('#carrito-items');
        carritoBody.empty();
        total = 0;

        if (carrito.length === 0) {
            carritoBody.append(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <p class="mt-2">El carrito está vacío</p>
                </div>
            `);
            $('#btn-cobrar').prop('disabled', true);
        } else {
            carrito.forEach(producto => {
                const subtotal = producto.price * producto.quantity;
                total += subtotal;

                carritoBody.append(`
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <small class="font-weight-bold">${producto.name}</small>
                            <br>
                            <small class="text-muted">${producto.code} - $${producto.price.toFixed(2)}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control form-control-sm cantidad-input mr-1"
                                   style="width: 60px;" value="${producto.quantity}" min="1"
                                   data-id="${producto.id}">
                            <button type="button" class="btn btn-danger btn-sm eliminar-producto"
                                    data-id="${producto.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right mb-2">
                        <small>Subtotal: $${subtotal.toFixed(2)}</small>
                    </div>
                `);
            });
            $('#btn-cobrar').prop('disabled', false);
        }

        $('#total-carrito').text('$' + total.toFixed(2));
    }

    // Configurar DataTables con búsqueda desde el inicio
    $('#productosTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 10,
        "order": [[1, "asc"]], // Ordenar por nombre de producto
        "columnDefs": [
            {
                "targets": [5], // Columna de acciones
                "orderable": false,
                "searchable": false
            }
        ],
        "search": {
            "regex": true,
            "smart": false
        },
        "initComplete": function() {
            // Función de búsqueda desde el inicio personalizada
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'productosTable') {
                        return true;
                    }

                    var searchTerm = $('#productosTable_filter input').val().toLowerCase();
                    if (searchTerm === '') {
                        return true;
                    }

                    // Buscar desde el inicio en todas las columnas
                    for (var i = 0; i < data.length; i++) {
                        var columnData = data[i].toLowerCase().replace(/<[^>]*>/g, ''); // Limpiar HTML
                        if (columnData.startsWith(searchTerm)) {
                            return true;
                        }
                    }
                    return false;
                }
            );

            // Actualizar búsqueda en tiempo real
            $('#productosTable_filter input').unbind().bind('keyup', function(e) {
                $('#productosTable').DataTable().draw();
            });
        }
    });

    // ===== FUNCIONALIDAD DEL MODAL DE PAGO =====

    // Abrir modal de pago
    $('#btn-cobrar').on('click', function() {
        if (carrito.length === 0) {
            alert('El carrito está vacío');
            return;
        }

        mostrarResumenCompra();
        calcularTotalesModal();
        $('#modalPago').modal('show');
    });

    // Mostrar resumen de compra en el modal
    function mostrarResumenCompra() {
        const resumenDiv = $('#resumen-compra');
        resumenDiv.empty();

        carrito.forEach(producto => {
            const subtotal = producto.price * producto.quantity;
            resumenDiv.append(`
                <div class="d-flex justify-content-between mb-1">
                    <div>
                        <small class="font-weight-bold">${producto.name}</small><br>
                        <small class="text-muted">${producto.code} x ${producto.quantity}</small>
                    </div>
                    <small class="font-weight-bold">$${subtotal.toFixed(2)}</small>
                </div>
            `);
        });
    }

    // Manejar cambios en tipo de descuento
    $('#tipo-descuento').on('change', function() {
        const tipo = $(this).val();
        const grupoValor = $('#grupo-valor-descuento');
        const inputValor = $('#valor-descuento');

        if (tipo === 'porcentaje' || tipo === 'fijo') {
            grupoValor.show();
            inputValor.attr('placeholder', tipo === 'porcentaje' ? 'Porcentaje (%)' : 'Monto ($)');
            if (tipo === 'porcentaje') {
                inputValor.attr('max', 100);
            } else {
                inputValor.removeAttr('max');
            }
        } else {
            grupoValor.hide();
            inputValor.val('');
        }

        calcularTotalesModal();
    });

    // Recalcular cuando cambia el valor del descuento
    $('#valor-descuento').on('input', calcularTotalesModal);

    // Manejar cambios en método de pago
    $('input[name="metodoPago"]').on('change', function() {
        const metodo = $(this).val();

        $('#campos-mixto').hide();

        if (metodo === 'mixto') {
            $('#campos-mixto').show();
        }
    });


    // Validar pago mixto
    $('#mixto-efectivo, #mixto-tarjeta').on('input', function() {
        const efectivo = parseFloat($('#mixto-efectivo').val()) || 0;
        const tarjeta = parseFloat($('#mixto-tarjeta').val()) || 0;
        const total = parseFloat($('#modal-total').text()) || 0;
        const suma = efectivo + tarjeta;

        if (Math.abs(suma - total) < 0.01) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // Calcular totales en el modal
    function calcularTotalesModal() {
        let subtotal = total; // Total del carrito
        let descuento = 0;
        const tipoDescuento = $('#tipo-descuento').val();
        const valorDescuento = parseFloat($('#valor-descuento').val()) || 0;

        // Calcular descuento
        switch (tipoDescuento) {
            case 'porcentaje':
                descuento = subtotal * (valorDescuento / 100);
                break;
            case 'fijo':
                descuento = Math.min(valorDescuento, subtotal);
                break;
            case 'estudiante':
                descuento = subtotal * 0.10;
                break;
            case 'empleado':
                descuento = subtotal * 0.15;
                break;
        }

        const totalFinal = subtotal - descuento;

        $('#modal-subtotal').text(subtotal.toFixed(2));
        $('#modal-descuento').text(descuento.toFixed(2));
        $('#modal-total').text(totalFinal.toFixed(2));
    }

    // Procesar pago
    $('#btn-procesar-pago').on('click', function() {
        if (!validarPago()) {
            return;
        }

        const metodoPago = $('input[name="metodoPago"]:checked').val();

        if (metodoPago === 'efectivo') {
            // Abrir modal específico para efectivo
            mostrarModalEfectivo();
            return;
        }

        // Para otros métodos de pago, procesar directamente
        procesarPagoDirecto();
    });

    function mostrarModalEfectivo() {
        // Obtener datos actuales del modal de pago
        const subtotal = parseFloat($('#modal-subtotal').text()) || 0;
        const descuento = parseFloat($('#modal-descuento').text()) || 0;
        const totalFinal = parseFloat($('#modal-total').text()) || 0;

        // Llenar detalle de venta en el modal de efectivo
        var detalleHTML = '';
        carrito.forEach(function(item) {
            var subtotalItem = item.price * item.quantity;
            detalleHTML += `
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">${item.code} - $${parseFloat(item.price).toFixed(2)} x ${item.quantity}</small>
                    </div>
                    <div class="text-right">
                        $${subtotalItem.toFixed(2)}
                    </div>
                </div>
            `;
        });
        $('#detalle-venta-efectivo').html(detalleHTML);

        // Llenar totales
        $('#efectivo-subtotal').text(subtotal.toFixed(2));
        $('#efectivo-total').text(totalFinal.toFixed(2));

        if (descuento > 0) {
            $('#efectivo-descuento').text(descuento.toFixed(2));
            $('#fila-descuento-efectivo').show();
        } else {
            $('#fila-descuento-efectivo').hide();
        }

        // Limpiar campos
        $('#monto-cliente-efectivo').val('');
        $('#resultado-pago div').hide();
        $('#btn-imprimir-ticket-efectivo').prop('disabled', true);

        // Cerrar modal de pago y abrir modal de efectivo
        $('#modalPago').modal('hide');

        setTimeout(() => {
            $('#modalPagoEfectivo').modal('show');
            $('#monto-cliente-efectivo').focus();
        }, 300);

        // Guardar datos para el ticket
        window.datosVentaEfectivo = {
            carrito: carrito,
            subtotal: subtotal,
            descuento: descuento,
            totalFinal: totalFinal,
            metodoPago: 'efectivo',
            observaciones: $('#observaciones').val()
        };
    }

    function procesarPagoDirecto() {
        // Simular procesamiento para otros métodos
        $('#btn-procesar-pago').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        setTimeout(() => {
            alert('¡Pago procesado exitosamente!\\n\\nTicket generado.');

            // Mostrar botón de imprimir
            $('#btn-imprimir-ticket').show();
            $('#btn-procesar-pago').hide();

            // Limpiar carrito
            carrito = [];
            actualizarCarrito();
        }, 2000);
    }

    // Manejar cambios en el monto recibido (modal efectivo)
    $('#monto-cliente-efectivo').on('input', function() {
        var montoCliente = parseFloat($(this).val()) || 0;
        var total = parseFloat($('#efectivo-total').text()) || 0;

        // Ocultar todos los mensajes
        $('#resultado-pago div').hide();
        $('#btn-imprimir-ticket-efectivo').prop('disabled', true);

        if (montoCliente === 0) {
            return;
        }

        if (montoCliente < total) {
            // Monto insuficiente
            var falta = total - montoCliente;
            $('#falta-monto').text(falta.toFixed(2));
            $('#mensaje-insuficiente').show();
        } else if (Math.abs(montoCliente - total) < 0.01) {
            // Pago exacto (considerando decimales)
            $('#mensaje-exacto').show();
            $('#btn-imprimir-ticket-efectivo').prop('disabled', false);
        } else {
            // Hay vuelto
            var vuelto = montoCliente - total;
            $('#vuelto-cliente').text(vuelto.toFixed(2));
            $('#mensaje-vuelto').show();
            $('#btn-imprimir-ticket-efectivo').prop('disabled', false);
        }
    });

    // Permitir Enter para procesar cuando el botón esté habilitado
    $('#monto-cliente-efectivo').on('keypress', function(e) {
        if (e.which === 13 && !$('#btn-imprimir-ticket-efectivo').prop('disabled')) {
            $('#btn-imprimir-ticket-efectivo').click();
        }
    });

    // Procesar ticket desde modal de efectivo
    $('#btn-imprimir-ticket-efectivo').click(function() {
        if (window.datosVentaEfectivo) {
            var montoRecibido = parseFloat($('#monto-cliente-efectivo').val());
            var vuelto = montoRecibido - window.datosVentaEfectivo.totalFinal;

            // Agregar datos de efectivo
            window.datosVentaEfectivo.montoRecibido = montoRecibido;
            window.datosVentaEfectivo.vuelto = vuelto > 0 ? vuelto : 0;

            finalizarVentaEfectivo(window.datosVentaEfectivo);
        }
    });

    function finalizarVentaEfectivo(datos) {
        // Aquí puedes agregar la lógica para guardar en la base de datos
        console.log('Finalizando venta en efectivo:', datos);

        // Generar ticket específico para efectivo
        generarTicketEfectivo(datos);

        // Limpiar carrito y cerrar modal
        carrito = [];
        actualizarCarrito();
        $('#modalPagoEfectivo').modal('hide');

        // Reset del modal principal
        $('#btn-procesar-pago').show().prop('disabled', false).html('<i class="fas fa-check"></i> Procesar Pago');
        $('#btn-imprimir-ticket').hide();
        $('#form-pago')[0].reset();
        $('#tipo-descuento').trigger('change');
        $('input[name="metodoPago"][value="efectivo"]').prop('checked', true).trigger('change');

        // Mensaje de éxito
        alert('¡Venta procesada exitosamente!');
    }

    function generarTicketEfectivo(datos) {
        // Hacer llamada AJAX al servidor para generar el PDF
        console.log('Generando ticket PDF para efectivo:', datos);

        // Mostrar indicador de carga
        var btnImprimir = $('#btn-imprimir-ticket-efectivo');
        var textoOriginal = btnImprimir.html();
        btnImprimir.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando PDF...');

        // Preparar datos para enviar al servidor
        var datosEnvio = {
            carrito: datos.carrito,
            subtotal: datos.subtotal,
            descuento: datos.descuento,
            totalFinal: datos.totalFinal,
            metodoPago: datos.metodoPago,
            montoRecibido: datos.montoRecibido,
            vuelto: datos.vuelto,
            observaciones: datos.observaciones,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Hacer la petición AJAX
        $.ajax({
            url: '{{ route("box.cobros.ticket-pdf") }}',
            method: 'POST',
            data: datosEnvio,
            xhrFields: {
                responseType: 'blob' // Para manejar el PDF como blob
            },
            success: function(response, status, xhr) {
                // Obtener el tipo de contenido
                var contentType = xhr.getResponseHeader('content-type');

                if (contentType && contentType.indexOf('application/pdf') !== -1) {
                    // Es un PDF - crear blob y abrir en nueva ventana
                    var blob = new Blob([response], { type: 'application/pdf' });
                    var url = window.URL.createObjectURL(blob);

                    // Abrir en nueva ventana con opciones de PDF
                    var ventanaTicket = window.open(url, '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes,toolbar=yes');

                    if (ventanaTicket) {
                        // La ventana se abrió correctamente
                        console.log('Ticket PDF abierto en nueva ventana');

                        // Opcional: Revocar la URL después de un tiempo
                        setTimeout(function() {
                            window.URL.revokeObjectURL(url);
                        }, 60000); // 1 minuto
                    } else {
                        // La ventana fue bloqueada - ofrecer descarga alternativa
                        alert('El navegador bloqueó la ventana emergente. El PDF se descargará automáticamente.');
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'ticket_' + new Date().getTime() + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    }
                } else {
                    // No es un PDF, probablemente HTML - abrir como HTML
                    var blob = new Blob([response], { type: 'text/html' });
                    var url = window.URL.createObjectURL(blob);
                    var ventanaTicket = window.open(url, '_blank', 'width=800,height=900,scrollbars=yes,resizable=yes');

                    setTimeout(function() {
                        window.URL.revokeObjectURL(url);
                    }, 60000);
                }

                // Restaurar botón
                btnImprimir.prop('disabled', false).html(textoOriginal);

                console.log('Ticket generado y abierto exitosamente');
            },
            error: function(xhr, status, error) {
                console.error('Error al generar el ticket:', error);

                // Intentar leer el mensaje de error del servidor
                var errorMessage = 'Error desconocido al generar el ticket.';
                if (xhr.responseText) {
                    try {
                        var errorData = JSON.parse(xhr.responseText);
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        errorMessage = 'Error del servidor: ' + xhr.status;
                    }
                }

                alert(errorMessage + ' Por favor, intente nuevamente.');

                // Restaurar botón
                btnImprimir.prop('disabled', false).html(textoOriginal);
            }
        });
    }

    // Validar pago
    function validarPago() {
        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const totalFinal = parseFloat($('#modal-total').text()) || 0;

        if (totalFinal <= 0) {
            alert('El total debe ser mayor a 0');
            return false;
        }

        if (metodoPago === 'mixto') {
            const efectivo = parseFloat($('#mixto-efectivo').val()) || 0;
            const tarjeta = parseFloat($('#mixto-tarjeta').val()) || 0;
            const suma = efectivo + tarjeta;

            if (Math.abs(suma - totalFinal) >= 0.01) {
                alert('La suma de efectivo y tarjeta debe ser igual al total');
                return false;
            }
        }

        return true;
    }

    // Imprimir ticket
    $('#btn-imprimir-ticket').on('click', function() {
        generarTicket();
    });

    // Generar ticket para impresión
    function generarTicket() {
        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const subtotal = parseFloat($('#modal-subtotal').text()) || 0;
        const descuento = parseFloat($('#modal-descuento').text()) || 0;
        const totalFinal = parseFloat($('#modal-total').text()) || 0;
        const observaciones = $('#observaciones').val();

        let ticketContent = `
            <div style="width: 300px; margin: 0 auto; font-family: monospace; font-size: 12px;">
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <h3 style="margin: 0;">COOPERADORA</h3>
                    <p style="margin: 0;">BOX - Punto de Venta</p>
                    <p style="margin: 0;">${new Date().toLocaleString()}</p>
                </div>

                <div style="margin-bottom: 10px;">
                    <strong>DETALLE DE COMPRA</strong><br>
        `;

        carrito.forEach(producto => {
            const subtotalProd = producto.price * producto.quantity;
            ticketContent += `
                ${producto.name}<br>
                ${producto.code} x ${producto.quantity} @ $${producto.price.toFixed(2)}<br>
                <div style="text-align: right;">$${subtotalProd.toFixed(2)}</div>
                <br>
            `;
        });

        ticketContent += `
                </div>

                <div style="border-top: 1px dashed #000; padding-top: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    ${descuento > 0 ? `
                    <div style="display: flex; justify-content: space-between;">
                        <span>Descuento:</span>
                        <span>-$${descuento.toFixed(2)}</span>
                    </div>
                    ` : ''}
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
                        <span>TOTAL:</span>
                        <span>$${totalFinal.toFixed(2)}</span>
                    </div>
                </div>

                <div style="margin-top: 10px; text-align: center;">
                    <strong>Método de Pago: ${metodoPago.toUpperCase()}</strong>
                    ${observaciones ? `<br><br>Observaciones: ${observaciones}` : ''}
                </div>

                <div style="text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px;">
                    <p style="margin: 0;">¡Gracias por su compra!</p>
                    <p style="margin: 0; font-size: 10px;">Cajero: ${$('body').data('user-name') || 'Sistema'}</p>
                </div>
            </div>
        `;

        // Abrir ventana de impresión
        const ventanaImpresion = window.open('', '_blank', 'width=400,height=600');
        ventanaImpresion.document.write(ticketContent);
        ventanaImpresion.document.close();
        ventanaImpresion.print();

        // Cerrar modal después de imprimir
        setTimeout(() => {
            $('#modalPago').modal('hide');
            // Reset del modal
            $('#btn-procesar-pago').show().prop('disabled', false).html('<i class="fas fa-check"></i> Procesar Pago');
            $('#btn-imprimir-ticket').hide();
            $('#form-pago')[0].reset();
            $('#tipo-descuento').trigger('change');
            $('input[name="metodoPago"][value="efectivo"]').prop('checked', true).trigger('change');
        }, 1000);
    }
});
</script>
@stop
