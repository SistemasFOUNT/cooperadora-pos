@extends('adminlte::page')

@section('title', $titulo ?? 'Postgrado - Cobros')

@section('content_header')
    <div class="row">
        <div class="col-sm-8">
            <h1><i class="fas fa-cash-register text-success"></i> {{ $titulo ?? 'Cobros de Postgrado' }}</h1>
            <p class="text-muted mb-0">{{ $subtitulo ?? 'Proceso unificado de cobro' }}</p>
        </div>
        <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('postgrado.dashboard') }}">Postgrado</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">{{ $programa ?? 'Programa' }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Conceptos Disponibles</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tabla-conceptos">
                            <thead>
                                <tr>
                                    <th width="130">Codigo</th>
                                    <th>Concepto</th>
                                    <th width="150">Monto</th>
                                    <th width="130">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($conceptos ?? []) as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['codigo'] }}</strong></td>
                                        <td>
                                            <div>{{ $concepto['nombre'] }}</div>
                                            @if(!empty($concepto['descripcion']))
                                                <small class="text-muted">{{ $concepto['descripcion'] }}</small>
                                            @endif
                                        </td>
                                        <td><strong>${{ number_format($concepto['precio'], 2, ',', '.') }}</strong></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm agregar-concepto"
                                                data-id="{{ $concepto['id'] }}"
                                                data-codigo="{{ $concepto['codigo'] }}"
                                                data-nombre="{{ $concepto['nombre'] }}"
                                                data-precio="{{ $concepto['precio'] }}"
                                            >
                                                <i class="fas fa-plus"></i> Agregar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i> No hay conceptos configurados para este programa.
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
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Carrito</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-basket fa-3x"></i>
                            <p class="mt-2">No hay conceptos seleccionados</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h4 class="mb-2">Total: $<span id="total-general">0,00</span></h4>
                    <button class="btn btn-success btn-block" id="btn-proceder-pago" disabled>
                        <i class="fas fa-credit-card"></i> Proceder al Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('components.payment-modals')
@stop

@section('js')
<script>
$(document).ready(function () {
    let carrito = [];

    function formatearPrecio(precio) {
        return Number(precio).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function totalCarrito() {
        return carrito.reduce(function (acc, item) {
            return acc + (item.precio * item.cantidad);
        }, 0);
    }

    function actualizarTotalesModal() {
        const subtotal = totalCarrito();
        const tipoDescuento = $('input[name="tipoDescuento"]:checked').val() || 'ninguno';
        const valorDescuento = parseFloat($('#valor-descuento').val()) || 0;
        let descuento = 0;

        if (tipoDescuento === 'porcentaje') {
            descuento = subtotal * (valorDescuento / 100);
        } else if (tipoDescuento === 'valor') {
            descuento = valorDescuento;
        }

        descuento = Math.min(descuento, subtotal);
        const total = Math.max(0, subtotal - descuento);

        $('#modal-subtotal').text(subtotal.toFixed(2));
        $('#modal-interes').text('0.00');
        $('#modal-descuento').text(descuento.toFixed(2));
        $('#modal-total').text(total.toFixed(2));

        const metodoPago = $('input[name="metodoPago"]:checked').val() || 'efectivo';
        if (metodoPago === 'efectivo') {
            const montoRecibido = parseFloat($('#monto-recibido').val()) || 0;
            const vuelto = Math.max(0, montoRecibido - total);
            $('#monto-vuelto').text('$' + vuelto.toFixed(2));
        }
    }

    window.actualizarTotales = actualizarTotalesModal;

    function actualizarCarrito() {
        if (carrito.length === 0) {
            $('#carrito-items').html(
                '<div class="text-center text-muted py-4">' +
                '<i class="fas fa-shopping-basket fa-3x"></i>' +
                '<p class="mt-2">No hay conceptos seleccionados</p>' +
                '</div>'
            );
            $('#total-general').text('0,00');
            $('#btn-proceder-pago').prop('disabled', true);
            actualizarTotalesModal();
            return;
        }

        let html = '<div class="list-group">';
        carrito.forEach(function (item) {
            html += '<div class="list-group-item py-2">';
            html += '<div class="d-flex justify-content-between align-items-start">';
            html += '<div>';
            html += '<strong>' + item.nombre + '</strong><br>';
            html += '<small class="text-muted">' + item.codigo + '</small>';
            html += '</div>';
            html += '<button class="btn btn-sm btn-outline-danger eliminar-item" data-id="' + item.id + '"><i class="fas fa-times"></i></button>';
            html += '</div>';
            html += '<div class="d-flex justify-content-between align-items-center mt-2">';
            html += '<div class="btn-group btn-group-sm" role="group">';
            html += '<button class="btn btn-outline-secondary disminuir-item" data-id="' + item.id + '">-</button>';
            html += '<button class="btn btn-outline-secondary" disabled>' + item.cantidad + '</button>';
            html += '<button class="btn btn-outline-secondary aumentar-item" data-id="' + item.id + '">+</button>';
            html += '</div>';
            html += '<strong>$' + formatearPrecio(item.precio * item.cantidad) + '</strong>';
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';

        $('#carrito-items').html(html);
        $('#total-general').text(formatearPrecio(totalCarrito()));
        $('#btn-proceder-pago').prop('disabled', false);
        actualizarTotalesModal();
    }

    function actualizarResumenModal() {
        if (carrito.length === 0) {
            $('#resumen-items').html('<div class="text-muted">No hay items seleccionados</div>');
            return;
        }

        let html = '';
        carrito.forEach(function (item) {
            html += '<div class="d-flex justify-content-between mb-1">';
            html += '<span>' + item.nombre + ' x ' + item.cantidad + '</span>';
            html += '<strong>$' + formatearPrecio(item.precio * item.cantidad) + '</strong>';
            html += '</div>';
        });

        $('#resumen-items').html(html);
    }

    $(document).on('click', '.agregar-concepto', function () {
        const id = String($(this).data('id'));
        const existente = carrito.find(function (item) { return item.id === id; });

        if (existente) {
            existente.cantidad += 1;
        } else {
            carrito.push({
                id: id,
                codigo: $(this).data('codigo'),
                nombre: $(this).data('nombre'),
                precio: Number($(this).data('precio')),
                cantidad: 1
            });
        }

        actualizarCarrito();
    });

    $(document).on('click', '.eliminar-item', function () {
        const id = String($(this).data('id'));
        carrito = carrito.filter(function (item) { return item.id !== id; });
        actualizarCarrito();
    });

    $(document).on('click', '.aumentar-item', function () {
        const id = String($(this).data('id'));
        const item = carrito.find(function (it) { return it.id === id; });
        if (item) {
            item.cantidad += 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.disminuir-item', function () {
        const id = String($(this).data('id'));
        const item = carrito.find(function (it) { return it.id === id; });
        if (!item) {
            return;
        }

        if (item.cantidad > 1) {
            item.cantidad -= 1;
        } else {
            carrito = carrito.filter(function (it) { return it.id !== id; });
        }

        actualizarCarrito();
    });

    $('#btn-proceder-pago').on('click', function () {
        actualizarResumenModal();
        actualizarTotalesModal();
        $('#modalPago').modal('show');
    });

    $('input[name="tipoDescuento"]').on('change', function () {
        const tipo = $(this).val();
        if (tipo === 'ninguno') {
            $('#campo-descuento').hide();
            $('#valor-descuento').val('');
        } else {
            $('#campo-descuento').show();
        }
        actualizarTotalesModal();
    });

    $('#valor-descuento, #monto-recibido, #mixto-monto-1, #mixto-monto-2').on('input', function () {
        actualizarTotalesModal();
    });

    $('input[name="metodoPago"]').on('change', function () {
        const metodo = $(this).val();
        $('#campos-mixto').toggle(metodo === 'mixto');
        $('#campos-efectivo').toggle(metodo === 'efectivo');

        $('.metodo-pago-option').removeClass('selected');
        $(this).closest('.metodo-pago-option').addClass('selected');

        actualizarTotalesModal();
    });

    $('input[name="tipoComprobante"]').on('change', function () {
        const tipo = $(this).val();
        const mostrarFacturacion = (tipo === 'factura_local' || tipo === 'factura_fiscal');
        $('#campos-facturacion').toggle(mostrarFacturacion);

        $('.comprobante-option').removeClass('selected');
        $(this).closest('.comprobante-option').addClass('selected');
    });

    $('#btn-procesar-pago').on('click', function () {
        if (carrito.length === 0) {
            alert('No hay conceptos para cobrar.');
            return;
        }

        const $botonProcesar = $(this);
        const textoOriginal = $botonProcesar.html();
        const total = parseFloat($('#modal-total').text()) || 0;
        const subtotal = parseFloat($('#modal-subtotal').text()) || 0;
        const descuento = parseFloat($('#modal-descuento').text()) || 0;
        const metodoPago = $('input[name="metodoPago"]:checked').val() || 'efectivo';
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val() || 'ticket';

        const datosCliente = {
            nombre: $('#cliente-nombre').val() || 'Consumidor Final',
            documento: $('#cliente-documento').val() || '00000000',
            direccion: $('#cliente-direccion').val() || '',
            condicionIva: $('#condicion-iva').val() || 'consumidor_final'
        };

        const requiereDatosCliente = (tipoComprobante === 'factura_local' || tipoComprobante === 'factura_fiscal');
        if (requiereDatosCliente) {
            if (!datosCliente.nombre.trim() || !datosCliente.documento.trim()) {
                alert('Complete nombre y documento para emitir factura.');
                return;
            }
        }

        if (metodoPago === 'efectivo') {
            const montoRecibido = parseFloat($('#monto-recibido').val()) || 0;
            if (montoRecibido < total) {
                alert('El monto recibido es menor al total.');
                return;
            }
        }

        if (metodoPago === 'mixto') {
            const mixtoMetodo1 = $('#mixto-metodo-1').val() || '';
            const mixtoMetodo2 = $('#mixto-metodo-2').val() || '';
            const mixtoMonto1 = parseFloat($('#mixto-monto-1').val()) || 0;
            const mixtoMonto2 = parseFloat($('#mixto-monto-2').val()) || 0;

            if (!mixtoMetodo1 || !mixtoMetodo2 || mixtoMetodo1 === mixtoMetodo2 || mixtoMonto1 <= 0 || mixtoMonto2 <= 0) {
                alert('Complete correctamente los datos del pago mixto.');
                return;
            }

            if (Math.abs((mixtoMonto1 + mixtoMonto2) - total) > 0.01) {
                alert('La suma del pago mixto debe coincidir con el total.');
                return;
            }
        }

        const payload = {
            _token: '{{ csrf_token() }}',
            carrito: carrito.map(function (item) {
                return {
                    id: parseInt(item.id, 10),
                    cantidad: item.cantidad,
                    precio: item.precio
                };
            }),
            metodoPago: metodoPago,
            tipoComprobante: tipoComprobante,
            subtotal: subtotal,
            descuento: descuento,
            totalFinal: total,
            datosCliente: datosCliente,
            observaciones: $('#observaciones').val() || '',
            montoRecibido: parseFloat($('#monto-recibido').val()) || 0,
            mixtoMetodo1: $('#mixto-metodo-1').val() || null,
            mixtoMonto1: parseFloat($('#mixto-monto-1').val()) || 0,
            mixtoMetodo2: $('#mixto-metodo-2').val() || null,
            mixtoMonto2: parseFloat($('#mixto-monto-2').val()) || 0
        };

        $botonProcesar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: '{{ route('postgrado.procesar-venta') }}',
            type: 'POST',
            data: payload,
            success: function (response) {
                const numeroVenta = response?.data?.sale_number || 'N/A';
                const ticketUrl = response?.data?.ticket_url || null;
                alert('Pago registrado correctamente. Numero de venta: ' + numeroVenta);

                if (ticketUrl) {
                    window.open(ticketUrl, '_blank');
                }

                $('#modalPago').modal('hide');

                carrito = [];
                $('#form-pago')[0].reset();
                $('#campos-mixto, #campos-efectivo, #campo-descuento, #campos-facturacion').hide();
                $('.metodo-pago-option, .comprobante-option').removeClass('selected');
                actualizarCarrito();
            },
            error: function (xhr) {
                const mensaje = xhr?.responseJSON?.message || 'No se pudo registrar el pago en Postgrado.';
                alert(mensaje);
            },
            complete: function () {
                $botonProcesar.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // Estado inicial del modal
    $('#efectivo').trigger('change');
    $('#factura_local').trigger('change');
    actualizarCarrito();
});
</script>
@stop
