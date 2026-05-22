<!-- MODAL UNIFICADO DE PAGO CON ESTILOS MEJORADOS -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="modalPagoLabel">
                    <i class="fas fa-credit-card"></i> Procesar Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-pago">
                    <!-- Resumen de pago compacto -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-2 text-dark"><i class="fas fa-list-alt text-primary"></i> Resumen de Pago</h6>
                            <div id="resumen-items" class="border rounded p-2 bg-light max-height-150 overflow-auto">
                                <!-- Items dinámicos aquí -->
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Descuentos -->
                        <div class="col-md-5">
                            <h6 class="mb-2 text-dark"><i class="fas fa-percentage text-primary"></i> Descuentos</h6>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipoDescuento" value="ninguno" id="sin-descuento" checked>
                                <label class="form-check-label font-weight-bold text-dark" for="sin-descuento">
                                    Sin descuento
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipoDescuento" value="porcentaje" id="descuento-porcentaje">
                                <label class="form-check-label font-weight-bold text-dark" for="descuento-porcentaje">
                                    Porcentaje (%)
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipoDescuento" value="valor" id="descuento-valor">
                                <label class="form-check-label font-weight-bold text-dark" for="descuento-valor">
                                    Valor fijo ($)
                                </label>
                            </div>

                            <div id="campo-descuento" style="display: none;" class="mt-2">
                                <input type="number" class="form-control form-control-sm" id="valor-descuento" step="0.01" min="0" placeholder="Valor">
                            </div>
                        </div>

                        <!-- Métodos de pago con mejor visibilidad -->
                        <div class="col-md-4">
                            <h6 class="mb-2 text-dark"><i class="fas fa-credit-card text-primary"></i> Método de Pago</h6>

                            <div class="form-check mb-2 p-2 border rounded metodo-pago-option" style="background: rgba(40, 167, 69, 0.1);" data-metodo="efectivo">
                                <input class="form-check-input" type="radio" name="metodoPago" value="efectivo" id="efectivo" checked>
                                <label class="form-check-label w-100 font-weight-bold text-dark" for="efectivo">
                                    <i class="fas fa-money-bill-wave text-success mr-1"></i> Efectivo
                                </label>
                            </div>

                            <div class="form-check mb-2 p-2 border rounded metodo-pago-option" data-metodo="tarjeta">
                                <input class="form-check-input" type="radio" name="metodoPago" value="tarjeta" id="tarjeta">
                                <label class="form-check-label w-100 font-weight-bold text-dark" for="tarjeta">
                                    <i class="fas fa-credit-card text-primary mr-1"></i> Tarjeta
                                </label>
                            </div>

                            <div class="form-check mb-2 p-2 border rounded metodo-pago-option" data-metodo="transferencia">
                                <input class="form-check-input" type="radio" name="metodoPago" value="transferencia" id="transferencia">
                                <label class="form-check-label w-100 font-weight-bold text-dark" for="transferencia">
                                    <i class="fas fa-exchange-alt text-info mr-1"></i> Transferencia
                                </label>
                            </div>

                            <div class="form-check mb-2 p-2 border rounded metodo-pago-option" data-metodo="mixto">
                                <input class="form-check-input" type="radio" name="metodoPago" value="mixto" id="mixto">
                                <label class="form-check-label w-100 font-weight-bold text-dark" for="mixto">
                                    <i class="fas fa-coins text-warning mr-1"></i> Mixto
                                </label>
                            </div>

                            <!-- Campos para pago mixto -->
                            <div id="campos-mixto" style="display: none;" class="mt-2">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="mixto-metodo-1">Medio 1</label>
                                        <select class="form-control form-control-sm" id="mixto-metodo-1">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="mixto-monto-1">Monto medio 1</label>
                                        <input type="number" class="form-control form-control-sm" id="mixto-monto-1" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="mixto-metodo-2">Medio 2</label>
                                        <select class="form-control form-control-sm" id="mixto-metodo-2">
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="efectivo">Efectivo</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="mixto-monto-2">Monto medio 2</label>
                                        <input type="number" class="form-control form-control-sm" id="mixto-monto-2" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para pago en efectivo -->
                            <div id="campos-efectivo" style="display: none;" class="mt-2">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="monto-recibido">Monto recibido</label>
                                        <input type="number" class="form-control form-control-sm" id="monto-recibido" step="0.01" min="0" placeholder="Monto recibido">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-dark mb-1 d-block" for="monto-vuelto">Vuelto</label>
                                        <div class="form-control form-control-sm bg-light font-weight-bold" id="monto-vuelto" aria-live="polite">$0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totales con mejor visibilidad -->
                        <div class="col-md-3">
                            <h6 class="mb-2 text-dark"><i class="fas fa-calculator text-primary"></i> Resumen</h6>
                            <div class="border rounded p-3 bg-white shadow-sm">
                                <div class="mb-2"><span class="text-dark font-weight-bold">Cuotas:</span> <span class="float-right resumen-monto">$<span id="modal-subtotal">0.00</span></span></div>
                                <div class="mb-2"><span class="text-dark font-weight-bold">Intereses:</span> <span class="float-right resumen-monto">$<span id="modal-interes">0.00</span></span></div>
                                <div class="mb-2"><span class="text-dark font-weight-bold">Descuento:</span> <span class="float-right resumen-monto">-$<span id="modal-descuento">0.00</span></span></div>
                                <hr class="my-2">
                                <div class="h5 mb-0"><span class="text-dark font-weight-bold">TOTAL:</span> <span class="float-right resumen-monto-total">$<span id="modal-total">0.00</span></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Comprobante con mejor visibilidad -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-dark"><i class="fas fa-file-invoice text-primary"></i> Tipo de Comprobante</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check p-2 border rounded comprobante-option" style="background: rgba(108, 117, 125, 0.1);" data-comprobante="ticket">
                                            <input class="form-check-input" type="radio" name="tipoComprobante" value="ticket" id="ticket">
                                            <label class="form-check-label w-100 font-weight-bold text-center text-dark" for="ticket">
                                                <i class="fas fa-receipt text-secondary d-block mb-1" style="font-size: 1.2em;"></i>
                                                <strong>Ticket</strong><br>
                                                <small class="text-muted">Simple y rápido</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check p-2 border rounded comprobante-option" data-comprobante="factura_local">
                                            <input class="form-check-input" type="radio" name="tipoComprobante" value="factura_local" id="factura_local" checked>
                                            <label class="form-check-label w-100 font-weight-bold text-center text-dark" for="factura_local">
                                                <i class="fas fa-file-alt text-info d-block mb-1" style="font-size: 1.2em;"></i>
                                                <strong>Factura Local</strong><br>
                                                <small class="text-muted">Con datos cliente</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check p-2 border rounded comprobante-option" data-comprobante="factura_fiscal">
                                            <input class="form-check-input" type="radio" name="tipoComprobante" value="factura_fiscal" id="factura_fiscal">
                                            <label class="form-check-label w-100 font-weight-bold text-center text-dark" for="factura_fiscal">
                                                <i class="fas fa-stamp text-primary d-block mb-1" style="font-size: 1.2em;"></i>
                                                <strong>Factura Fiscal</strong><br>
                                                <small class="text-muted">Oficial AFIP</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos adicionales para facturación -->
                                <div id="campos-facturacion" style="display: none;">
                                    <hr class="my-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <input type="text" class="form-control form-control-sm" id="cliente-nombre" placeholder="Nombre/Razón Social *">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <input type="text" class="form-control form-control-sm" id="cliente-documento" placeholder="DNI/CUIT *">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <input type="text" class="form-control form-control-sm" id="cliente-direccion" placeholder="Dirección (opcional)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <select class="form-control form-control-sm" id="condicion-iva">
                                                    <option value="consumidor_final">Consumidor Final</option>
                                                    <option value="responsable_inscripto">Responsable Inscripto</option>
                                                    <option value="exento">Exento</option>
                                                    <option value="monotributo">Monotributo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones compactas -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <textarea class="form-control form-control-sm" id="observaciones" rows="2" placeholder="Observaciones adicionales (opcional)..."></textarea>
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
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS MEJORADOS -->
<style>
/* Estilos para métodos de pago */
.metodo-pago-option {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #dee2e6 !important;
}

.metodo-pago-option:hover {
    border-color: #007bff !important;
    box-shadow: 0 2px 4px rgba(0,123,255,0.25);
}

.metodo-pago-option.selected {
    border-color: #28a745 !important;
    background: rgba(40, 167, 69, 0.15) !important;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

/* Estilos para tipos de comprobante */
.comprobante-option {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #dee2e6 !important;
    min-height: 90px;
    display: flex;
    align-items: center;
}

.comprobante-option:hover {
    border-color: #007bff !important;
    box-shadow: 0 2px 4px rgba(0,123,255,0.25);
    transform: translateY(-2px);
}

.comprobante-option.selected {
    border-color: #007bff !important;
    background: rgba(0, 123, 255, 0.1) !important;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    transform: translateY(-2px);
}

.comprobante-option input[type="radio"] {
    display: none;
}

.metodo-pago-option input[type="radio"] {
    display: none;
}

/* Mejorar visibilidad de texto */
.text-dark {
    color: #343a40 !important;
}

.font-weight-bold {
    font-weight: 600 !important;
}

.resumen-monto {
    color: #000 !important;
    font-weight: 700 !important;
}

.resumen-monto-total {
    color: #000 !important;
    font-weight: 700 !important;
}

/* Animación para elementos seleccionados */
.selected {
    animation: pulseSelection 0.3s ease-out;
}

@keyframes pulseSelection {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Altura máxima para resumen de items */
.max-height-150 {
    max-height: 150px;
}

.overflow-auto {
    overflow: auto;
}

/* Mejoras en el gradiente del header */
.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}
</style>

<!-- JAVASCRIPT PARA MANEJO DE INTERACCIONES - VERSION JAVASCRIPT PURO -->
<script>
// Usar JavaScript puro para evitar problemas de dependencias
document.addEventListener('DOMContentLoaded', function() {
    let ultimoCampoMixtoEditado = 'mixto-monto-1';

    function obtenerTotalModalActual() {
        return parseFloat(document.getElementById('modal-total')?.textContent || '0') || 0;
    }

    function calcularComplementoMixto(campoOrigen) {
        const campoMonto1 = document.getElementById('mixto-monto-1');
        const campoMonto2 = document.getElementById('mixto-monto-2');

        if (!campoMonto1 || !campoMonto2) {
            return;
        }

        const total = obtenerTotalModalActual();

        if (campoOrigen === 'mixto-monto-1') {
            const monto1 = parseFloat(campoMonto1.value || '0') || 0;
            const monto2 = Math.max(total - monto1, 0);
            campoMonto2.value = monto2.toFixed(2);
            ultimoCampoMixtoEditado = 'mixto-monto-1';
            return;
        }

        const monto2 = parseFloat(campoMonto2.value || '0') || 0;
        const monto1 = Math.max(total - monto2, 0);
        campoMonto1.value = monto1.toFixed(2);
        ultimoCampoMixtoEditado = 'mixto-monto-2';
    }

    function recalcularMixtoDesdeTotal() {
        calcularComplementoMixto(ultimoCampoMixtoEditado);
    }

    // Manejar selección de métodos de pago
    function configurarMetodosPago() {
        const opcionesPago = document.querySelectorAll('.metodo-pago-option');
        const camposMixto = document.getElementById('campos-mixto');
        const camposEfectivo = document.getElementById('campos-efectivo');
        const mixtoMonto1 = document.getElementById('mixto-monto-1');
        const mixtoMonto2 = document.getElementById('mixto-monto-2');
        opcionesPago.forEach(opcion => {
            opcion.addEventListener('click', function() {
                // Remover selección anterior
                opcionesPago.forEach(op => op.classList.remove('selected'));

                // Agregar selección al elemento clickeado
                this.classList.add('selected');

                // Marcar el radio button correspondiente
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;

                // Manejar campos específicos
                const metodo = this.dataset.metodo;

                if (metodo === 'mixto' && camposMixto) {
                    camposMixto.style.display = 'block';
                    setTimeout(() => {
                        if (mixtoMonto1) mixtoMonto1.focus();
                    }, 50);
                } else if (camposMixto) {
                    camposMixto.style.display = 'none';
                }

                if (metodo === 'efectivo' && camposEfectivo) {
                    camposEfectivo.style.display = 'block';
                } else if (camposEfectivo) {
                    camposEfectivo.style.display = 'none';
                }

                actualizarVueltoEfectivo();
            });
        });

        const montoRecibido = document.getElementById('monto-recibido');
        if (montoRecibido) {
            montoRecibido.addEventListener('input', actualizarVueltoEfectivo);
        }

        if (mixtoMonto1) {
            mixtoMonto1.addEventListener('input', function() {
                calcularComplementoMixto('mixto-monto-1');
            });
        }

        if (mixtoMonto2) {
            mixtoMonto2.addEventListener('input', function() {
                calcularComplementoMixto('mixto-monto-2');
            });
        }
    }

    function actualizarVueltoEfectivo() {
        const campoMontoRecibido = document.getElementById('monto-recibido');
        const campoVuelto = document.getElementById('monto-vuelto');
        const metodoSeleccionado = document.querySelector('input[name="metodoPago"]:checked');

        if (!campoMontoRecibido || !campoVuelto || !metodoSeleccionado || metodoSeleccionado.value !== 'efectivo') {
            return;
        }

        const total = parseFloat(document.getElementById('modal-total')?.textContent || '0') || 0;
        const recibido = parseFloat(campoMontoRecibido.value || '0') || 0;
        const vuelto = recibido - total;

        campoVuelto.textContent = '$' + Math.max(vuelto, 0).toFixed(2);
        campoVuelto.classList.toggle('text-danger', vuelto < 0);
        campoVuelto.classList.toggle('text-success', vuelto >= 0);
    }

    function sincronizarCamposMetodoPago() {
        const metodoSeleccionado = document.querySelector('input[name="metodoPago"]:checked');
        const camposMixto = document.getElementById('campos-mixto');
        const camposEfectivo = document.getElementById('campos-efectivo');

        if (!metodoSeleccionado) {
            return;
        }

        if (camposMixto) {
            camposMixto.style.display = metodoSeleccionado.value === 'mixto' ? 'block' : 'none';
        }

        if (camposEfectivo) {
            camposEfectivo.style.display = metodoSeleccionado.value === 'efectivo' ? 'block' : 'none';
        }

        actualizarVueltoEfectivo();

        if (metodoSeleccionado.value === 'efectivo') {
            const montoRecibido = document.getElementById('monto-recibido');
            if (montoRecibido) {
                setTimeout(() => montoRecibido.focus(), 50);
            }
        }
    }

    function obtenerEstudianteCobroActual() {
        const estudiante = window.estudianteCobroActual || {};
        return {
            nombre: (estudiante.nombre || '').trim(),
            dni: (estudiante.dni || '').trim()
        };
    }

    function autocompletarDatosFacturaDesdeEstudiante() {
        const comprobanteSeleccionado = document.querySelector('input[name="tipoComprobante"]:checked');
        if (!comprobanteSeleccionado) {
            return;
        }

        const tipoComprobante = comprobanteSeleccionado.value;
        if (tipoComprobante !== 'factura_local' && tipoComprobante !== 'factura_fiscal') {
            return;
        }

        const estudiante = obtenerEstudianteCobroActual();
        const clienteNombre = document.getElementById('cliente-nombre');
        const clienteDocumento = document.getElementById('cliente-documento');

        if (clienteNombre && estudiante.nombre) {
            clienteNombre.value = estudiante.nombre;
        }

        if (clienteDocumento && estudiante.dni) {
            clienteDocumento.value = estudiante.dni;
        }
    }

    // Manejar selección de tipos de comprobante
    function configurarTiposComprobante() {
        const opcionesComprobante = document.querySelectorAll('.comprobante-option');
        opcionesComprobante.forEach(opcion => {
            opcion.addEventListener('click', function() {
                // Remover selección anterior
                opcionesComprobante.forEach(op => op.classList.remove('selected'));

                // Agregar selección al elemento clickeado
                this.classList.add('selected');

                // Marcar el radio button correspondiente
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;

                // Manejar campos específicos
                const comprobante = this.dataset.comprobante;
                const camposFacturacion = document.getElementById('campos-facturacion');

                if ((comprobante === 'factura_local' || comprobante === 'factura_fiscal') && camposFacturacion) {
                    camposFacturacion.style.display = 'block';
                    autocompletarDatosFacturaDesdeEstudiante();
                } else if (camposFacturacion) {
                    camposFacturacion.style.display = 'none';
                }
            });
        });
    }

    // Manejar descuentos
    function configurarDescuentos() {
        const radioDescuentos = document.querySelectorAll('input[name="tipoDescuento"]');
        const campoDescuento = document.getElementById('campo-descuento');
        const valorDescuento = document.getElementById('valor-descuento');

        radioDescuentos.forEach(radio => {
            radio.addEventListener('change', function() {
                const tipo = this.value;
                if (tipo === 'ninguno') {
                    if (campoDescuento) campoDescuento.style.display = 'none';
                    if (valorDescuento) valorDescuento.value = 0;
                } else {
                    if (campoDescuento) campoDescuento.style.display = 'block';
                    if (valorDescuento) {
                        valorDescuento.placeholder = tipo === 'porcentaje' ? 'Porcentaje (%)' : 'Valor ($)';
                    }
                }
                actualizarTotales();
                recalcularMixtoDesdeTotal();
            });
        });

        // Calcular totales dinámicamente
        if (valorDescuento) {
            valorDescuento.addEventListener('input', function() {
                actualizarTotales();
                recalcularMixtoDesdeTotal();
            });
        }
    }

    // Inicializar selecciones por defecto
    function inicializarSelecciones() {
        // Seleccionar método de pago por defecto (efectivo)
        const efectivo = document.querySelector('.metodo-pago-option[data-metodo="efectivo"]');
        if (efectivo) {
            efectivo.classList.add('selected');
            const radio = efectivo.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        // Seleccionar tipo de comprobante por defecto (factura local)
        const facturaLocal = document.querySelector('.comprobante-option[data-comprobante="factura_local"]');
        if (facturaLocal) {
            facturaLocal.classList.add('selected');
            const radio = facturaLocal.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        const camposFacturacion = document.getElementById('campos-facturacion');
        if (camposFacturacion) {
            camposFacturacion.style.display = 'block';
        }

        sincronizarCamposMetodoPago();
        autocompletarDatosFacturaDesdeEstudiante();
    }

    // Función para actualizar totales (implementada en cada vista específica)
    if (typeof window.actualizarTotales !== 'function') {
        window.actualizarTotales = function() {
            console.log('actualizarTotales() - implementar en cada vista');
        }
    }

    // Función para limpiar modal
    function limpiarModal() {
        const camposMixto = document.getElementById('campos-mixto');
        const camposEfectivo = document.getElementById('campos-efectivo');
        const camposFacturacion = document.getElementById('campos-facturacion');
        const observaciones = document.getElementById('observaciones');
        const valorDescuento = document.getElementById('valor-descuento');
        const sinDescuento = document.querySelector('input[name="tipoDescuento"][value="ninguno"]');

        if (camposMixto) camposMixto.style.display = 'none';
        if (camposEfectivo) camposEfectivo.style.display = 'none';
        if (camposFacturacion) camposFacturacion.style.display = 'none';
        if (observaciones) observaciones.value = '';
        if (valorDescuento) valorDescuento.value = 0;
        if (sinDescuento) sinDescuento.checked = true;

        // Limpiar campos de cliente
        const clienteNombre = document.getElementById('cliente-nombre');
        const clienteDocumento = document.getElementById('cliente-documento');
        const clienteDireccion = document.getElementById('cliente-direccion');
        const condicionIva = document.getElementById('condicion-iva');
        const montoRecibido = document.getElementById('monto-recibido');
        const montoVuelto = document.getElementById('monto-vuelto');
        const mixtoMetodo1 = document.getElementById('mixto-metodo-1');
        const mixtoMetodo2 = document.getElementById('mixto-metodo-2');
        const mixtoMonto1 = document.getElementById('mixto-monto-1');
        const mixtoMonto2 = document.getElementById('mixto-monto-2');

        if (clienteNombre) clienteNombre.value = '';
        if (clienteDocumento) clienteDocumento.value = '';
        if (clienteDireccion) clienteDireccion.value = '';
        if (condicionIva) condicionIva.value = 'consumidor_final';
        if (montoRecibido) montoRecibido.value = '';
        if (montoVuelto) montoVuelto.textContent = '$0.00';
        if (mixtoMetodo1) mixtoMetodo1.value = 'efectivo';
        if (mixtoMetodo2) mixtoMetodo2.value = 'tarjeta';
        if (mixtoMonto1) mixtoMonto1.value = '';
        if (mixtoMonto2) mixtoMonto2.value = '';
        ultimoCampoMixtoEditado = 'mixto-monto-1';

        // Remover clases de validación
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.classList.remove('is-valid', 'is-invalid');
        });

        inicializarSelecciones();
    }

    // Configurar todos los event listeners
    configurarMetodosPago();
    configurarTiposComprobante();
    configurarDescuentos();
    inicializarSelecciones();

    // Manejo del modal (solo si Bootstrap está disponible)
    const modalPago = document.getElementById('modalPago');
    if (modalPago && window.$ && $.fn.modal) {
        // Si jQuery y Bootstrap están disponibles, usar sus eventos
        $(modalPago).on('shown.bs.modal', function() {
            inicializarSelecciones();
            sincronizarCamposMetodoPago();
            autocompletarDatosFacturaDesdeEstudiante();
        });

        $(modalPago).on('hidden.bs.modal', function() {
            limpiarModal();
        });
    } else if (modalPago) {
        // Fallback para manejo básico del modal sin Bootstrap
        console.log('Modal configurado sin dependencias de Bootstrap/jQuery');
    }
});
</script>
