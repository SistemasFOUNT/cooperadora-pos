/*
 * ========================================================================
 * MÓDULO COMÚN DE PAGOS - JAVASCRIPT
 * ========================================================================
 *
 * Este módulo maneja toda la lógica de pagos para diferentes tipos de cobro:
 * - Productos
 * - Servicios Odontológicos
 * - Cuotas Estudiantiles
 *
 * Funcionalidades incluidas:
 * - Cálculo de descuentos
 * - Pago en efectivo con cálculo de vuelto
 * - Pago con tarjeta (débito/crédito)
 * - Generación de tickets PDF
 * - Validaciones de pagos
 */

// Función para formatear números al estilo argentino (1.234,56)
function formatearPrecio(numero) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
}

// Función para formatear números sin símbolo de moneda
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
}

// Función para formatear fecha y hora en formato 24h argentino
function formatearFechaHora(fecha) {
    return new Intl.DateTimeFormat('es-AR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(fecha);
}

class ModuloPagos {
    constructor() {
        console.log('ModuloPagos: Constructor iniciado');
        this.carrito = [];
        this.totalGeneral = 0;
        this.subtotal = 0;
        this.descuentoAplicado = 0;
        this.tipoModulo = 'general'; // puede ser 'productos', 'servicios', 'cuotas'

        this.initEventListeners();
        console.log('ModuloPagos: Inicialización completada');
    }

    // Configurar el tipo de módulo (productos, servicios, cuotas)
    setTipoModulo(tipo) {
        console.log('ModuloPagos: setTipoModulo llamado con:', tipo);
        this.tipoModulo = tipo;
    }

    // Inicializar event listeners
    initEventListeners() {
        // Aplicar descuento
        $('#descuento').on('input', () => {
            this.calcularTotales();
        });

        // Continuar con el pago
        $('#btn-continuar-pago').click(() => {
            this.continuarPago();
        });

        // Métodos de pago
        $('input[name="metodo_pago"]').change(() => {
            this.cambiarMetodoPago();
        });

        // Pago en efectivo
        $('#monto-cliente-efectivo').on('input', () => {
            this.calcularVuelto();
        });

        $('#btn-confirmar-efectivo').click(() => {
            this.confirmarPagoEfectivo();
        });

        // Pago con tarjeta
        $('#btn-confirmar-tarjeta').click(() => {
            this.confirmarPagoTarjeta();
        });

        // Cuotas internas
        $('#btn-buscar-cliente').click(() => {
            this.buscarCliente();
        });

        $('#btn-nuevo-cliente').click(() => {
            this.mostrarFormularioNuevoCliente();
        });

        $('#buscar-cliente').on('input', () => {
            this.buscarClienteAutocompletado();
        });

        $('#cantidad-cuotas').change(() => {
            this.calcularPlanCuotas();
        });

        $('#fecha-primera-cuota').change(() => {
            this.calcularPlanCuotas();
        });

        $('#btn-confirmar-financiamiento').click(() => {
            this.confirmarFinanciamiento();
        });

        // Imprimir tickets
        $('#btn-imprimir-ticket-efectivo, #btn-imprimir-ticket-tarjeta').click((e) => {
            this.imprimirTicket($(e.target).attr('id').includes('efectivo') ? 'efectivo' : 'tarjeta');
        });
    }

    // Abrir modal de pago
    abrirModalPago(carrito, total) {
        console.log('ModuloPagos: abrirModalPago llamado con:', { carrito, total });

        this.carrito = carrito;
        this.subtotal = total;
        this.totalGeneral = total;

        this.actualizarResumenModal();
        $('#modalPago').modal('show');
        console.log('ModuloPagos: Modal de pago mostrado');
    }

    // Actualizar resumen en el modal
    actualizarResumenModal() {
        let html = '';
        this.carrito.forEach(item => {
            const subtotalItem = item.precio * item.cantidad;
            html += `
                <div class="row mb-2">
                    <div class="col-8">
                        <strong>${item.nombre}</strong><br>
                        <small>${formatearPrecio(item.precio)} x ${item.cantidad}</small>
                    </div>
                    <div class="col-4 text-right">
                        <strong>${formatearPrecio(subtotalItem)}</strong>
                    </div>
                </div>
            `;
        });

        $('#detalle-items').html(html);
        this.calcularTotales();
    }

    // Calcular totales con descuento
    calcularTotales() {
        const descuentoPorcentaje = parseFloat($('#descuento').val()) || 0;
        this.descuentoAplicado = (this.subtotal * descuentoPorcentaje) / 100;
        this.totalGeneral = this.subtotal - this.descuentoAplicado;

        $('#subtotal-modal').text(formatearNumero(this.subtotal));
        $('#descuento-monto').text(formatearNumero(this.descuentoAplicado));
        $('#total-modal').text(formatearNumero(this.totalGeneral));
    }

    // Continuar con el pago según método seleccionado
    continuarPago() {
        const metodoPago = $('input[name="metodo_pago"]:checked').val();

        $('#modalPago').modal('hide');

        if (metodoPago === 'efectivo') {
            this.abrirModalEfectivo();
        } else if (metodoPago === 'debito_credito') {
            this.abrirModalTarjeta();
        } else if (metodoPago === 'cuotas_internas') {
            this.abrirModalCuotasInternas();
        }
    }

    // Abrir modal de pago en efectivo
    abrirModalEfectivo() {
        // Llenar datos del modal
        let itemsHtml = '';
        this.carrito.forEach(item => {
            itemsHtml += `<div><strong>${item.nombre}</strong> x${item.cantidad}</div>`;
        });

        $('#items-efectivo').html(itemsHtml);
        $('#total-efectivo').text(this.totalGeneral.toLocaleString());

        // Limpiar campos
        $('#monto-cliente-efectivo').val('').focus();
        this.limpiarMensajesEfectivo();
        $('#btn-confirmar-efectivo').prop('disabled', true);

        $('#modalPagoEfectivo').modal('show');
    }

    // Calcular vuelto en pago efectivo
    calcularVuelto() {
        const montoPagado = parseFloat($('#monto-cliente-efectivo').val()) || 0;
        const totalAPagar = this.totalGeneral;

        this.limpiarMensajesEfectivo();

        if (montoPagado === 0) {
            $('#btn-confirmar-efectivo').prop('disabled', true);
            return;
        }

        if (montoPagado < totalAPagar) {
            // Monto insuficiente
            const faltante = totalAPagar - montoPagado;
            $('#falta-monto').text(formatearNumero(faltante));
            $('#mensaje-insuficiente').show();
            $('#btn-confirmar-efectivo').prop('disabled', true);
        } else if (montoPagado === totalAPagar) {
            // Pago exacto
            $('#mensaje-exacto').show();
            $('#btn-confirmar-efectivo').prop('disabled', false);
        } else {
            // Hay vuelto
            const vuelto = montoPagado - totalAPagar;
            $('#vuelto-monto').text(formatearNumero(vuelto));
            $('#mensaje-vuelto').show();
            $('#btn-confirmar-efectivo').prop('disabled', false);
        }
    }

    // Limpiar mensajes de efectivo
    limpiarMensajesEfectivo() {
        $('#mensaje-insuficiente, #mensaje-exacto, #mensaje-vuelto').hide();
    }

    // Abrir modal de tarjeta
    abrirModalTarjeta() {
        $('#total-tarjeta').text(formatearNumero(this.totalGeneral));
        $('#numero-autorizacion').val('');
        $('#btn-imprimir-ticket-tarjeta').hide();
        $('#modalPagoTarjeta').modal('show');
    }

    // Confirmar pago en efectivo
    confirmarPagoEfectivo() {
        const montoPagado = parseFloat($('#monto-cliente-efectivo').val());

        if (montoPagado >= this.totalGeneral) {
            // Simular procesamiento
            this.procesarVenta('efectivo', {
                monto_pagado: montoPagado,
                vuelto: montoPagado - this.totalGeneral
            });

            $('#btn-confirmar-efectivo').hide();
            $('#btn-imprimir-ticket-efectivo').show();

            this.mostrarMensajeExito('Pago en efectivo procesado correctamente');
        }
    }

    // Confirmar pago con tarjeta
    confirmarPagoTarjeta() {
        const tipoTarjeta = $('#tipo-tarjeta').val();
        const numeroAutorizacion = $('#numero-autorizacion').val();

        // Simular procesamiento de tarjeta
        this.procesarVenta('tarjeta', {
            tipo: tipoTarjeta,
            autorizacion: numeroAutorizacion || 'N/A'
        });

        $('#btn-confirmar-tarjeta').hide();
        $('#btn-imprimir-ticket-tarjeta').show();

        this.mostrarMensajeExito('Transacción con tarjeta aprobada');
    }

    // Procesar venta (aquí se enviaría al backend)
    procesarVenta(metodoPago, detallesPago) {
        const datosVenta = {
            items: this.carrito,
            carrito: this.carrito, // Compatibilidad con PDFTicket existente
            subtotal: this.subtotal,
            descuento: this.descuentoAplicado,
            total: this.totalGeneral,
            metodo_pago: metodoPago,
            detalles_pago: detallesPago,
            tipo_modulo: this.tipoModulo
        };

        console.log('Datos de venta:', datosVenta);

        // Aquí se enviaría al backend via AJAX
        // $.post('/box/procesar-venta', datosVenta)...

        // Por ahora solo guardamos en localStorage para el ticket
        localStorage.setItem('ultima_venta', JSON.stringify(datosVenta));
    }

    // Imprimir ticket
    imprimirTicket(tipo) {
        const ultimaVenta = JSON.parse(localStorage.getItem('ultima_venta'));

        if (!ultimaVenta) {
            alert('No hay datos de venta para imprimir');
            return;
        }

        // Enviar datos para generar PDF
        const datosTicket = {
            ...ultimaVenta,
            fecha: new Date().toISOString(),
            numero_ticket: this.generarNumeroTicket()
        };

        // Crear formulario temporal para POST
        const form = $('<form>', {
            method: 'POST',
            action: '/box/generar-ticket',
            target: '_blank'
        });

        // Token CSRF
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));

        // Datos del ticket
        form.append($('<input>', {
            type: 'hidden',
            name: 'datos_ticket',
            value: JSON.stringify(datosTicket)
        }));

        $('body').append(form);
        form.submit();
        form.remove();
    }

    // Generar número de ticket único
    generarNumeroTicket() {
        const fecha = new Date();
        const timestamp = fecha.getTime();
        return `BOX-${fecha.getFullYear()}${(fecha.getMonth()+1).toString().padStart(2,'0')}${fecha.getDate().toString().padStart(2,'0')}-${timestamp.toString().slice(-6)}`;
    }

    // Mostrar mensaje de éxito
    mostrarMensajeExito(mensaje) {
        // Crear toast o alert temporal
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <i class="fas fa-check-circle"></i> ${mensaje}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        $('body').append(alertHtml);

        // Auto-dismiss después de 5 segundos
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }

    // Limpiar carrito después del pago
    limpiarCarrito() {
        this.carrito = [];
        this.totalGeneral = 0;
        this.subtotal = 0;
        this.descuentoAplicado = 0;
    }

    // Cambiar método de pago (para futuras personalizaciones)
    cambiarMetodoPago() {
        const metodo = $('input[name="metodo_pago"]:checked').val();
        console.log('Método de pago seleccionado:', metodo);
    }

    // =============================================================================
    // MÉTODOS PARA CUOTAS INTERNAS
    // =============================================================================

    // Abrir modal de cuotas internas
    abrirModalCuotasInternas() {
        // Limpiar formulario
        $('#buscar-cliente').val('');
        $('#resultados-busqueda').hide();
        $('#cliente-seleccionado').hide();
        $('#configuracion-financiamiento').hide();
        $('#btn-confirmar-financiamiento').hide();

        // Configurar monto original
        $('#monto-original-cuotas').text(formatearNumero(this.totalGeneral));

        // Fecha por defecto: mañana
        const mañana = new Date();
        mañana.setDate(mañana.getDate() + 1);
        const fechaFormateada = mañana.toISOString().split('T')[0];
        $('#fecha-primera-cuota').val(fechaFormateada);

        this.clienteSeleccionado = null;

        $('#modalCuotasInternas').modal('show');
    }

    // Buscar cliente por término de búsqueda
    async buscarCliente() {
        const termino = $('#buscar-cliente').val().trim();

        if (termino.length < 3) {
            alert('Ingrese al menos 3 caracteres para buscar');
            return;
        }

        try {
            const response = await fetch('/api/clientes-deudores/buscar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ termino })
            });

            const data = await response.json();

            if (data.success && data.clientes.length > 0) {
                this.mostrarResultadosBusqueda(data.clientes);
            } else {
                this.mostrarNoEncontrado();
            }
        } catch (error) {
            console.error('Error al buscar cliente:', error);
            alert('Error al buscar cliente. Intente nuevamente.');
        }
    }

    // Mostrar resultados de búsqueda
    mostrarResultadosBusqueda(clientes) {
        let html = '<div class="list-group">';

        clientes.forEach(cliente => {
            const limiteFactor = cliente.limite_credito > 0 ?
                `Límite: ${formatearPrecio(cliente.limite_credito)}` :
                'Sin límite definido';

            html += `
                <div class="list-group-item list-group-item-action" onclick="moduloPagos.seleccionarCliente(${cliente.id})">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${cliente.nombre} ${cliente.apellido}</h6>
                        <small class="text-muted">${cliente.dni}</small>
                    </div>
                    <p class="mb-1">${cliente.email || 'Sin email'}</p>
                    <small class="text-muted">${limiteFactor} | Deuda actual: ${formatearPrecio(cliente.deuda_total_actual)}</small>
                </div>
            `;
        });

        html += '</div>';

        $('#resultados-busqueda').html(html).show();
    }

    // Mostrar mensaje de no encontrado
    mostrarNoEncontrado() {
        const html = `
            <div class="alert alert-warning">
                <i class="fas fa-search"></i>
                No se encontraron clientes con ese criterio de búsqueda.
                <button class="btn btn-sm btn-primary ml-2" onclick="moduloPagos.mostrarFormularioNuevoCliente()">
                    <i class="fas fa-plus"></i> Crear nuevo cliente
                </button>
            </div>
        `;
        $('#resultados-busqueda').html(html).show();
    }

    // Seleccionar cliente de la búsqueda
    async seleccionarCliente(clienteId) {
        try {
            const response = await fetch(`/api/clientes-deudores/${clienteId}`);
            const data = await response.json();

            if (data.success) {
                this.clienteSeleccionado = data.cliente;
                this.mostrarClienteSeleccionado();
                this.validarCapacidadCredito();
                $('#resultados-busqueda').hide();
            }
        } catch (error) {
            console.error('Error al obtener cliente:', error);
        }
    }

    // Mostrar información del cliente seleccionado
    mostrarClienteSeleccionado() {
        if (!this.clienteSeleccionado) return;

        const cliente = this.clienteSeleccionado;
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <strong>${cliente.nombre} ${cliente.apellido}</strong><br>
                    <small>DNI: ${cliente.dni} | Email: ${cliente.email || 'No proporcionado'}</small>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-info">Límite: ${formatearPrecio(cliente.limite_credito)}</span><br>
                    <span class="badge badge-${cliente.deuda_total_actual > 0 ? 'warning' : 'success'}">
                        Deuda actual: ${formatearPrecio(cliente.deuda_total_actual)}
                    </span>
                </div>
            </div>
        `;

        $('#datos-cliente-seleccionado').html(html);
        $('#cliente-seleccionado').show();
    }

    // Validar capacidad de crédito del cliente
    validarCapacidadCredito() {
        if (!this.clienteSeleccionado) return;

        const cliente = this.clienteSeleccionado;
        const deudaActual = parseFloat(cliente.deuda_total_actual);
        const limiteCredito = parseFloat(cliente.limite_credito);
        const nuevoMonto = this.totalGeneral;
        const deudaTotal = deudaActual + nuevoMonto;

        if (limiteCredito > 0 && deudaTotal > limiteCredito) {
            // Excede el límite
            const exceso = deudaTotal - limiteCredito;
            const html = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Límite de crédito excedido</strong><br>
                    El monto solicitado (${formatearPrecio(nuevoMonto)}) más la deuda actual (${formatearPrecio(deudaActual)})
                    excede el límite de crédito en ${formatearPrecio(exceso)}.
                </div>
            `;
            $('#configuracion-financiamiento').html(html).show();
            $('#btn-confirmar-financiamiento').hide();
        } else {
            // Puede financiar
            $('#configuracion-financiamiento').show();
            this.calcularPlanCuotas();
            $('#btn-confirmar-financiamiento').show();
        }
    }

    // Calcular plan de cuotas
    calcularPlanCuotas() {
        if (!this.clienteSeleccionado) return;

        const cantidadCuotas = parseInt($('#cantidad-cuotas').val());
        const montoOriginal = this.totalGeneral;

        // Calcular interés según cantidad de cuotas
        const tasasInteres = {
            3: 0.30,   // 30%
            6: 0.45,   // 45%
            9: 0.60,   // 60%
            12: 0.75   // 75%
        };

        const tasaInteres = tasasInteres[cantidadCuotas] || 0;
        const montoConInteres = montoOriginal * (1 + tasaInteres);
        const valorCuota = montoConInteres / cantidadCuotas;

        // Actualizar valores en pantalla
        $('#monto-original-cuotas').text(formatearNumero(montoOriginal));
        $('#monto-total-cuotas').text(formatearNumero(montoConInteres));
        $('#valor-cada-cuota').text(formatearNumero(valorCuota));

        // Generar tabla de cuotas
        this.generarTablaCuotas(cantidadCuotas, valorCuota);
    }

    // Generar tabla visual del plan de cuotas
    generarTablaCuotas(cantidadCuotas, valorCuota) {
        const fechaInicial = new Date($('#fecha-primera-cuota').val());
        let html = '';

        for (let i = 1; i <= cantidadCuotas; i++) {
            const fechaVencimiento = new Date(fechaInicial);
            fechaVencimiento.setMonth(fechaVencimiento.getMonth() + (i - 1));

            html += `
                <tr>
                    <td>${i}</td>
                    <td>${fechaVencimiento.toLocaleDateString('es-AR')}</td>
                    <td>${formatearPrecio(valorCuota)}</td>
                    <td><span class="badge badge-secondary">Pendiente</span></td>
                </tr>
            `;
        }

        $('#tabla-plan-cuotas').html(html);
    }

    // Confirmar y generar financiamiento
    async confirmarFinanciamiento() {
        if (!this.clienteSeleccionado) {
            alert('Debe seleccionar un cliente');
            return;
        }

        const datosFinanciamiento = {
            cliente_deudor_id: this.clienteSeleccionado.id,
            venta_original: {
                items: this.carrito,
                subtotal: this.subtotal,
                descuento: this.descuentoAplicado,
                total: this.totalGeneral
            },
            cantidad_cuotas: parseInt($('#cantidad-cuotas').val()),
            fecha_primera_cuota: $('#fecha-primera-cuota').val(),
            monto_total_con_interes: parseFloat($('#monto-total-cuotas').text().replace(/[$.]/g, '').replace(',', '.')),
            valor_cada_cuota: parseFloat($('#valor-cada-cuota').text().replace(/[$.]/g, '').replace(',', '.')),
            detalle_tratamiento: this.generarDetalleTratamiento()
        };

        try {
            // Mostrar loading
            $('#btn-confirmar-financiamiento')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            const response = await fetch('/api/financiamientos/crear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(datosFinanciamiento)
            });

            const data = await response.json();

            if (data.success) {
                $('#modalCuotasInternas').modal('hide');
                this.mostrarResultadoFinanciamiento(data.financiamiento);
                this.limpiarCarrito();
            } else {
                alert('Error al generar financiamiento: ' + data.message);
            }
        } catch (error) {
            console.error('Error al confirmar financiamiento:', error);
            alert('Error de conexión. Intente nuevamente.');
        } finally {
            $('#btn-confirmar-financiamiento')
                .prop('disabled', false)
                .html('<i class="fas fa-file-contract"></i> Generar Financiamiento');
        }
    }

    // Generar detalle del tratamiento
    generarDetalleTratamiento() {
        return this.carrito.map(item =>
            `${item.nombre} x${item.cantidad} = ${formatearPrecio(item.precio * item.cantidad)}`
        ).join(' | ');
    }

    // Mostrar resultado del financiamiento generado
    mostrarResultadoFinanciamiento(financiamiento) {
        const mensaje = `
            <div class="alert alert-success alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 400px;">
                <i class="fas fa-check-circle"></i>
                <h5>Financiamiento Generado Exitosamente</h5>
                <strong>Número:</strong> #${financiamiento.id}<br>
                <strong>Cliente:</strong> ${this.clienteSeleccionado.nombre} ${this.clienteSeleccionado.apellido}<br>
                <strong>Total financiado:</strong> ${formatearPrecio(financiamiento.monto_total_con_interes)}<br>
                <strong>Cuotas:</strong> ${financiamiento.cantidad_cuotas}<br>
                <hr>
                <button class="btn btn-sm btn-primary" onclick="window.open('/documentos/compromiso-pago/${financiamiento.id}', '_blank')">
                    <i class="fas fa-file-pdf"></i> Generar Compromiso de Pago
                </button>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        $('body').append(mensaje);

        // Auto-dismiss después de 10 segundos
        setTimeout(() => {
            $('.alert').alert('close');
        }, 10000);
    }

    // Mostrar formulario para nuevo cliente
    mostrarFormularioNuevoCliente() {
        // Por ahora redirigir a una página de registro
        if (confirm('¿Desea abrir el formulario de registro de nuevo cliente?\nEsto lo llevará a una nueva página.')) {
            window.open('/clientes-deudores/crear', '_blank');
        }
    }

    // Autocompletado mientras escribe
    buscarClienteAutocompletado() {
        const termino = $('#buscar-cliente').val().trim();

        if (termino.length >= 3) {
            // Debounce para evitar demasiadas consultas
            clearTimeout(this.busquedaTimeout);
            this.busquedaTimeout = setTimeout(() => {
                this.buscarCliente();
            }, 500);
        } else {
            $('#resultados-busqueda').hide();
        }
    }
}

// Instancia global del módulo de pagos
$(document).ready(function() {
    console.log('Documento listo, inicializando ModuloPagos');
    window.moduloPagos = new ModuloPagos();
    console.log('ModuloPagos instanciado y disponible globalmente');
});
