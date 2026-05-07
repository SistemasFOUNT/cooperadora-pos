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
        cursor: pointer;
    }
    .producto-card:hover {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

    /* Estilos para tipo de comprobante */
    .form-check-label {
        cursor: pointer;
        padding-left: 0.5rem;
    }

    .form-check-label strong {
        display: block;
        margin-bottom: 0.2rem;
    }

    .form-check-label small {
        font-size: 0.75rem;
        color: #6c757d;
    }

    #campos-facturacion {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }

    .form-check-input:checked + .form-check-label {
        color: #0056b3;
        font-weight: 500;
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

        <!-- Carrito de Compras -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Carrito de Compras</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x"></i>
                            <p class="mt-2">No hay productos seleccionados</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <h4 class="text-success">Total: $<span id="total-general">0,00</span></h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success" id="btn-proceder-pago" disabled>
                                <i class="fas fa-credit-card"></i> Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Incluir componente de modal de pago unificado --}}
    @include('components.payment-modals')
@stop

@section('js')
<script>
// Esperar a que jQuery esté disponible
function waitForJQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 100);
    }
}

waitForJQuery(function() {
    $(document).ready(function() {
    let carrito = [];
    let total = 0;

    // Cargar DataTables dinámicamente
    $.getScript('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', function() {
        $.getScript('https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js', function() {
            initializeDataTable();
        });
    });

    function initializeDataTable() {
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
    }

    // ===== FUNCIONES DE MANEJO DEL CARRITO =====

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

    // ===== FUNCIONALIDAD DEL MODAL DE PAGO =====

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

    // Manejar cambios en tipo de comprobante
    $('input[name="tipoComprobante"]').on('change', function() {
        const tipo = $(this).val();
        const camposFacturacion = $('#campos-facturacion');

        if (tipo === 'factura_local' || tipo === 'factura_fiscal') {
            camposFacturacion.show();

            // Hacer obligatorios los campos requeridos
            $('#cliente-nombre, #cliente-documento').prop('required', true);

            // Si es factura fiscal, actualizar etiquetas para CUIT
            if (tipo === 'factura_fiscal') {
                $('#condicion-iva').prop('disabled', false);
                $('label[for="cliente-documento"]').html('CUIT *');
                $('#cliente-documento').attr('placeholder', '20-12345678-9');
            } else {
                $('#condicion-iva').prop('disabled', false);
                $('label[for="cliente-documento"]').html('DNI/CUIT *');
                $('#cliente-documento').attr('placeholder', '12345678 ó 20-12345678-9');
            }
        } else {
            camposFacturacion.hide();

            // Remover obligatoriedad
            $('#cliente-nombre, #cliente-documento').prop('required', false);
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
    // Procesar pago (botón "Procesar Pago" dentro del modal)
    $('#btn-procesar-pago').on('click', function() {
        if (!validarPago()) {
            return;
        }

        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();
        const tieneModalEfectivo = $('#modalPagoEfectivo').length > 0;

        // Solo usar modal de efectivo para ticket y cuando el modal exista en DOM.
        // Para facturas, el flujo debe ir directo a generación del comprobante.
        if (metodoPago === 'efectivo' && tipoComprobante === 'ticket' && tieneModalEfectivo) {
            mostrarModalEfectivo();
            return;
        }

        // Para otros métodos de pago, procesar directamente
        procesarPagoDirecto();
    });

    function mostrarModalEfectivo() {
        if ($('#modalPagoEfectivo').length === 0) {
            console.warn('modalPagoEfectivo no encontrado. Se continúa con procesamiento directo.');
            procesarPagoDirecto();
            return;
        }

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

        // Actualizar botón según tipo de comprobante
        const btnEfectivo = $('#btn-imprimir-ticket-efectivo');
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();

        switch(tipoComprobante) {
            case 'ticket':
                btnEfectivo.html('<i class="fas fa-print"></i> Imprimir Ticket');
                break;
            case 'factura_local':
                btnEfectivo.html('<i class="fas fa-file-alt"></i> Generar Factura Local');
                break;
            case 'factura_fiscal':
                btnEfectivo.html('<i class="fas fa-stamp"></i> Generar Factura Fiscal');
                break;
        }

        btnEfectivo.prop('disabled', true);

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
            tipoComprobante: $('input[name="tipoComprobante"]:checked').val(),
            datosCliente: {
                nombre: $('#cliente-nombre').val().trim(),
                documento: $('#cliente-documento').val().trim(),
                direccion: $('#cliente-direccion').val().trim(),
                condicionIva: $('#condicion-iva').val()
            },
            observaciones: $('#observaciones').val()
        };
    }

    function procesarPagoDirecto() {
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();
        const metodoPago = $('input[name="metodoPago"]:checked').val();

        // Si es factura (local o fiscal), procesar con nueva funcionalidad
        if (tipoComprobante === 'factura_local' || tipoComprobante === 'factura_fiscal') {
            const ventanaFactura = window.open('', '_blank');

            if (ventanaFactura) {
                ventanaFactura.document.write(`
                    <html>
                        <head><title>Generando factura...</title></head>
                        <body style="font-family: Arial, sans-serif; padding: 24px; color: #333;">
                            <h3>Generando factura...</h3>
                            <p>Espere un momento mientras se prepara el PDF.</p>
                        </body>
                    </html>
                `);
                ventanaFactura.document.close();
            }

            procesarPagoConFactura(tipoComprobante, metodoPago, ventanaFactura);
            return;
        }

        let mensaje = '¡Pago procesado exitosamente!\\n\\n';

        // Personalizar mensaje según tipo de comprobante
        switch(tipoComprobante) {
            case 'ticket':
                mensaje += 'Ticket generado.';
                break;
            case 'factura_local':
                mensaje += 'Factura Local generada.';
                break;
            case 'factura_fiscal':
                mensaje += 'Factura Fiscal ARCA generada.';
                break;
        }

        // Simular procesamiento para otros métodos
        $('#btn-procesar-pago').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        setTimeout(() => {
            alert(mensaje);

            // Guardar datos para posibles reimpresiones
            window.datosUltimaVenta = {
                carrito: carrito,
                metodoPago: metodoPago,
                tipoComprobante: tipoComprobante,
                subtotal: parseFloat($('#modal-subtotal').text()) || 0,
                descuento: parseFloat($('#modal-descuento').text()) || 0,
                totalFinal: parseFloat($('#modal-total').text()) || 0,
                datosCliente: {
                    nombre: $('#cliente-nombre').val().trim(),
                    documento: $('#cliente-documento').val().trim(),
                    direccion: $('#cliente-direccion').val().trim(),
                    condicionIva: $('#condicion-iva').val()
                },
                observaciones: $('#observaciones').val()
            };

            // Actualizar botón según tipo de comprobante
            const btnImprimir = $('#btn-imprimir-ticket');
            switch(tipoComprobante) {
                case 'ticket':
                    btnImprimir.html('<i class="fas fa-print"></i> Imprimir Ticket');
                    break;
                case 'factura_local':
                    btnImprimir.html('<i class="fas fa-file-alt"></i> Generar Factura Local');
                    break;
                case 'factura_fiscal':
                    btnImprimir.html('<i class="fas fa-stamp"></i> Generar Factura Fiscal');
                    break;
            }

            btnImprimir.show();
            $('#btn-procesar-pago').hide();

            // Limpiar carrito
            carrito = [];
            actualizarCarrito();
        }, 2000);
    }

    // Envío robusto de factura por POST real a nueva pestaña
    function enviarFacturaEnNuevaPestana(datos, ventanaFactura = null) {
        const nombreVentana = (ventanaFactura && !ventanaFactura.closed)
            ? (ventanaFactura.name || `factura_${Date.now()}`)
            : `factura_${Date.now()}`;

        if (ventanaFactura && !ventanaFactura.closed && !ventanaFactura.name) {
            ventanaFactura.name = nombreVentana;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("box.facturas.procesar-pago-factura") }}';
        form.target = nombreVentana;
        form.style.display = 'none';

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(token);

        Object.keys(datos).forEach((key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = (typeof datos[key] === 'object') ? JSON.stringify(datos[key]) : datos[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // Función SIMPLE para procesar pago con facturación directa
    function procesarPagoConFactura(tipoComprobante, metodoPago, ventanaFactura = null) {
        console.log('Procesando pago con factura:', tipoComprobante, metodoPago);

        // Datos COMPLETOS incluyendo método de pago
        const datos = {
            datosCliente: {
                nombre: $('#cliente-nombre').val() || 'Cliente Genérico',
                documento: $('#cliente-documento').val() || '00000000',
                direccion: $('#cliente-direccion').val() || '',
                condicionIva: $('#condicion-iva').val() || 'consumidor_final'
            },
            tipoComprobante: tipoComprobante,
            metodoPago: metodoPago,
            totalFinal: parseFloat($('#modal-total').text()) || 0,
            subtotal: parseFloat($('#modal-subtotal').text()) || 0,
            descuento: parseFloat($('#modal-descuento').text()) || 0,
            observaciones: $('#observaciones').val() || '',
            carrito: carrito
        };

        console.log('Enviando datos completos:', datos);

        try {
            enviarFacturaEnNuevaPestana(datos, ventanaFactura);
            $('#modalPago').modal('hide');
            carrito = [];
            actualizarCarrito();
        } catch (error) {
            console.error('Error enviando formulario de factura:', error);
            if (ventanaFactura && !ventanaFactura.closed) {
                ventanaFactura.close();
            }
            alert('No se pudo generar la factura. Intente nuevamente.');
        } finally {
            $('#btn-procesar-pago').prop('disabled', false).html('<i class="fas fa-check"></i> Procesar Pago');
        }
    }

    // Función auxiliar para validar carrito antes de pago
    function validarCarritoParaPago() {
        if (carrito.length === 0) {
            alert('Debe agregar al menos un producto al carrito.');
            return false;
        }
        return true;
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

        // Manejar según tipo de comprobante
        if (datos.tipoComprobante === 'factura_local' || datos.tipoComprobante === 'factura_fiscal') {
            generarFacturaEfectivo(datos);
        } else {
            // Generar ticket específico para efectivo
            generarTicketEfectivo(datos);
        }

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

    // Generar factura para pago en efectivo
    function generarFacturaEfectivo(datos) {
        console.log('Generando factura en efectivo:', datos.tipoComprobante, datos);

        // Validar que tengamos datos de cliente para facturación
        if (!datos.datosCliente.nombre || !datos.datosCliente.documento) {
            alert('Error: Faltan datos del cliente para generar la factura');
            return;
        }

        const tipoFactura = datos.tipoComprobante === 'factura_fiscal' ? 'Fiscal ARCA' : 'Local';
        const montoRecibido = datos.montoRecibido || 0;
        const vuelto = datos.vuelto || 0;

        let mensajeEfectivo = `Monto recibido: $${montoRecibido.toFixed(2)}`;
        if (vuelto > 0) {
            mensajeEfectivo += `\nVuelto: $${vuelto.toFixed(2)}`;
        }

        if (confirm(`¿Confirma generar ${tipoFactura}?\n\n` +
                   `Cliente: ${datos.datosCliente.nombre}\n` +
                   `Documento: ${datos.datosCliente.documento}\n` +
                   `Total: $${datos.totalFinal.toFixed(2)}\n\n` +
                   `${mensajeEfectivo}`)) {

            // Llamar al mismo método que tarjeta para generar PDF real
            console.log('Procesando factura efectivo con datos:', datos);
            procesarPagoConFactura(datos.tipoComprobante, 'efectivo');
        }
    }

    // Validar pago
    function validarPago() {
        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();
        const totalFinal = parseFloat($('#modal-total').text()) || 0;

        if (totalFinal <= 0) {
            alert('El total debe ser mayor a 0');
            return false;
        }

        // Validar campos de facturación si es necesario
        if (tipoComprobante === 'factura_local' || tipoComprobante === 'factura_fiscal') {
            const nombreCliente = $('#cliente-nombre').val().trim();
            const documentoCliente = $('#cliente-documento').val().trim();

            if (!nombreCliente) {
                alert('Debe ingresar el nombre del cliente para generar factura');
                $('#cliente-nombre').focus();
                return false;
            }

            if (!documentoCliente) {
                alert('Debe ingresar el DNI/CUIT del cliente para generar factura');
                $('#cliente-documento').focus();
                return false;
            }

            // Validar formato de CUIT para factura fiscal
            if (tipoComprobante === 'factura_fiscal') {
                const cuitRegex = /^\d{2}-\d{8}-\d{1}$/;
                if (!cuitRegex.test(documentoCliente)) {
                    alert('Para factura fiscal el CUIT debe tener formato: 20-12345678-9');
                    $('#cliente-documento').focus();
                    return false;
                }
            }
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
        // Usar datos guardados si están disponibles, sino tomar del modal
        const datos = window.datosUltimaVenta || {
            carrito: carrito,
            metodoPago: $('input[name="metodoPago"]:checked').val(),
            tipoComprobante: $('input[name="tipoComprobante"]:checked').val(),
            subtotal: parseFloat($('#modal-subtotal').text()) || 0,
            descuento: parseFloat($('#modal-descuento').text()) || 0,
            totalFinal: parseFloat($('#modal-total').text()) || 0,
            datosCliente: {
                nombre: $('#cliente-nombre').val().trim(),
                documento: $('#cliente-documento').val().trim(),
                direccion: $('#cliente-direccion').val().trim(),
                condicionIva: $('#condicion-iva').val()
            },
            observaciones: $('#observaciones').val()
        };

        // Manejar según tipo de comprobante
        if (datos.tipoComprobante === 'factura_local' || datos.tipoComprobante === 'factura_fiscal') {
            generarFactura(datos);
            return;
        }

        // Continuar con ticket normal
        const metodoPago = datos.metodoPago;
        const subtotal = datos.subtotal;
        const descuento = datos.descuento;
        const totalFinal = datos.totalFinal;
        const observaciones = datos.observaciones;

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

        const carritoData = datos.carrito || carrito;
        carritoData.forEach(producto => {
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
            $('#btn-imprimir-ticket').hide().html('<i class="fas fa-print"></i> Imprimir Ticket');
            $('#form-pago')[0].reset();
            $('#tipo-descuento').trigger('change');
            $('input[name="metodoPago"][value="efectivo"]').prop('checked', true).trigger('change');
            $('input[name="tipoComprobante"][value="ticket"]').prop('checked', true).trigger('change');
        }, 1000);
    }

    // Generar factura (local o fiscal) - MEJORADA para mostrar PDF directamente
    function generarFactura(datos) {
        console.log('Generando factura:', datos.tipoComprobante, datos);

        // Validar que tengamos datos de cliente para facturación
        if (!datos.datosCliente.nombre || !datos.datosCliente.documento) {
            alert('Error: Faltan datos del cliente para generar la factura');
            return;
        }

        // Simular proceso de facturación
        const tipoFactura = datos.tipoComprobante === 'factura_fiscal' ? 'Fiscal ARCA' : 'Local';

        if (confirm(`¿Confirma generar ${tipoFactura}?\n\nCliente: ${datos.datosCliente.nombre}\nDocumento: ${datos.datosCliente.documento}\nTotal: $${datos.totalFinal.toFixed(2)}`)) {
            const ventanaFactura = window.open('', '_blank');

            if (ventanaFactura) {
                ventanaFactura.document.write(`
                    <html>
                        <head><title>Generando factura...</title></head>
                        <body style="font-family: Arial, sans-serif; padding: 24px; color: #333;">
                            <h3>Generando factura...</h3>
                            <p>Espere un momento mientras se prepara el PDF.</p>
                        </body>
                    </html>
                `);
                ventanaFactura.document.close();
            }

            // Preparar datos para el endpoint de facturación
            const datosPago = {
                tipoComprobante: datos.tipoComprobante,
                metodoPago: datos.metodoPago,
                carrito: carrito.map(item => ({
                    producto_id: item.id,
                    cantidad: item.quantity,
                    precio_unitario: item.price
                })),
                subtotal: datos.subtotal,
                descuento: datos.descuento,
                totalFinal: datos.totalFinal,
                datosCliente: datos.datosCliente,
                observaciones: datos.observaciones
            };

            try {
                enviarFacturaEnNuevaPestana(datosPago, ventanaFactura);
                setTimeout(() => {
                    $('#modalPago').modal('hide');
                    carrito = [];
                    actualizarCarrito();
                }, 300);
            } catch (error) {
                if (ventanaFactura && !ventanaFactura.closed) {
                    ventanaFactura.close();
                }
                alert(`Error al generar factura ${tipoFactura}.`);
            }
        }
    }

    // Limpiar modal al cerrarse
    $('#modalPago').on('hidden.bs.modal', function() {
        // Resetear formulario
        $('#form-pago')[0].reset();

        // Resetear tipo de comprobante a ticket
        $('#ticket').prop('checked', true);

        // Ocultar campos de facturación
        $('#campos-facturacion').hide();

        // Limpiar campos adicionales
        $('#cliente-nombre, #cliente-documento, #cliente-direccion').val('');
        $('#condicion-iva').val('consumidor_final');

        // Ocultar campos de pago mixto
        $('#campos-mixto').hide();

        // Remover clases de validación
        $('.form-control').removeClass('is-valid is-invalid');

        // Resetear botones
        $('#btn-procesar-pago').show().prop('disabled', false).html('<i class="fas fa-check"></i> Procesar Pago');
        $('#btn-imprimir-ticket').hide().html('<i class="fas fa-print"></i> Imprimir Ticket');
    });

    // Limpiar modal de efectivo al cerrarse
    $('#modalPagoEfectivo').on('hidden.bs.modal', function() {
        $('#monto-cliente-efectivo').val('');
        $('#resultado-pago div').hide();
        $('#btn-imprimir-ticket-efectivo').prop('disabled', true).html('<i class="fas fa-print"></i> Imprimir Ticket');
    });

    // === NUEVAS FUNCIONES PARA SELECCIÓN MEJORADA ===

    // Manejar selección de métodos de pago
    $('.metodo-pago-option').on('click', function() {
        // Remover selección anterior
        $('.metodo-pago-option').removeClass('selected');

        // Agregar selección al elemento clickeado
        $(this).addClass('selected');

        // Marcar el radio button correspondiente
        $(this).find('input[type="radio"]').prop('checked', true);

        // Manejar campos específicos
        const metodo = $(this).data('metodo');
        if (metodo === 'mixto') {
            $('#campos-mixto').slideDown();
        } else {
            $('#campos-mixto').slideUp();
        }
    });

    // Manejar selección de tipos de comprobante
    $('.comprobante-option').on('click', function() {
        // Remover selección anterior
        $('.comprobante-option').removeClass('selected');

        // Agregar selección al elemento clickeado
        $(this).addClass('selected');

        // Marcar el radio button correspondiente
        $(this).find('input[type="radio"]').prop('checked', true);

        // Manejar campos específicos
        const comprobante = $(this).data('comprobante');
        if (comprobante === 'factura_local' || comprobante === 'factura_fiscal') {
            $('#campos-facturacion').slideDown();
        } else {
            $('#campos-facturacion').slideUp();
        }
    });

    // Inicializar selecciones por defecto
    function inicializarSelecciones() {
        // Seleccionar método de pago por defecto (efectivo)
        $('.metodo-pago-option[data-metodo="efectivo"]').addClass('selected');

        // Seleccionar tipo de comprobante por defecto (ticket)
        $('.comprobante-option[data-comprobante="ticket"]').addClass('selected');
    }

    // Llamar al inicializar
    inicializarSelecciones();

    // Reinicializar al abrir modal
    $('#modalPago').on('shown.bs.modal', function() {
        inicializarSelecciones();
    });

    // ===== FUNCIONES ESTÁNDAR DEL PROTOCOLO =====

    // Función para formatear precios
    function formatearPrecio(precio) {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(precio);
    }

    // Actualizar carrito visual
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
            $('#btn-proceder-pago').prop('disabled', true);
        } else {
            carrito.forEach(producto => {
                const subtotal = producto.price * producto.quantity;
                total += subtotal;

                carritoBody.append(`
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <small class="font-weight-bold">${producto.name}</small>
                            <br>
                            <small class="text-muted">${producto.code} - ${formatearPrecio(producto.price)}</small>
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
                        <small>Subtotal: ${formatearPrecio(subtotal)}</small>
                    </div>
                `);
            });
            $('#btn-proceder-pago').prop('disabled', false);
        }

        $('#total-carrito').text(formatearPrecio(total));
    }

    // Abrir modal de pago (evento estándar del protocolo)
    $('#btn-proceder-pago').on('click', function() {
        if (carrito.length === 0) {
            alert('El carrito está vacío');
            return;
        }

        mostrarResumenCompra();
        calcularTotalesModal();
        $('#modalPago').modal('show');
    });

    // Función estándar para mostrar resumen de compra en el modal
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
                    <small class="font-weight-bold">${formatearPrecio(subtotal)}</small>
                </div>
            `);
        });
    }

    // Función estándar para calcular totales del modal
    });
});
</script>
@stop
