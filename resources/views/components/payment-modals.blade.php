<!-- MODAL PRINCIPAL DE PAGO -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPagoLabel">
                    <i class="fas fa-credit-card"></i> Procesar Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Resumen de la compra -->
                <div id="resumen-compra" class="mb-4">
                    <h6><i class="fas fa-list"></i> Resumen de la Compra</h6>
                    <div id="detalle-items"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="descuento">Descuento (%):</label>
                            <input type="number" id="descuento" class="form-control" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <div class="text-right">
                                <h5>Subtotal: $<span id="subtotal-modal">0,00</span></h5>
                                <h5>Descuento: $<span id="descuento-monto">0,00</span></h5>
                                <h4 class="text-success"><strong>Total: $<span id="total-modal">0,00</span></strong></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de pago -->
                <div class="payment-methods">
                    <h6><i class="fas fa-money-check-alt"></i> Método de Pago</h6>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-success">
                            <input type="radio" name="metodo_pago" value="efectivo" checked>
                            <i class="fas fa-money-bill-wave"></i> Efectivo
                        </label>
                        <label class="btn btn-outline-primary">
                            <input type="radio" name="metodo_pago" value="debito_credito">
                            <i class="fas fa-credit-card"></i> Débito/Crédito
                        </label>
                        <label class="btn btn-outline-warning">
                            <input type="radio" name="metodo_pago" value="cuotas_internas">
                            <i class="fas fa-calendar-alt"></i> Cuotas Internas
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btn-continuar-pago">
                    <i class="fas fa-arrow-right"></i> Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAGO EFECTIVO -->
<div class="modal fade" id="modalPagoEfectivo" tabindex="-1" aria-labelledby="modalPagoEfectivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalPagoEfectivoLabel">
                    <i class="fas fa-money-bill-wave"></i> Pago en Efectivo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Detalle de la venta -->
                <div id="detalle-venta-efectivo" class="p-3 rounded mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-shopping-cart"></i> Resumen del Pedido</h6>
                            <div id="items-efectivo"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Total a Pagar</h5>
                                <h2 class="text-success font-weight-bold">$<span id="total-efectivo">0,00</span></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculadora de efectivo -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="monto-cliente-efectivo" class="font-weight-bold">
                            <i class="fas fa-hand-holding-usd"></i> Monto Recibido del Cliente:
                        </label>
                        <input type="number"
                               id="monto-cliente-efectivo"
                               class="form-control form-control-lg"
                               placeholder="Ingrese el monto"
                               step="0,01"
                               min="0">
                    </div>
                    <div class="col-md-6">
                        <div id="resultado-calculo" class="mt-4">
                            <!-- Mensajes dinámicos -->
                            <div id="mensaje-insuficiente" class="alert alert-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong class="text-dark">Monto insuficiente</strong><br>
                                <span class="text-dark">Faltan: <span class="h6 text-dark">$<span id="falta-monto">0,00</span></span></span>
                            </div>

                            <div id="mensaje-exacto" class="alert alert-success" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <strong class="text-dark">Pago exacto</strong><br>
                                <span class="text-dark">Sin vuelto a entregar</span>
                            </div>

                            <div id="mensaje-vuelto" class="alert alert-info text-dark" style="display: none;">
                                <i class="fas fa-coins"></i>
                                <strong>Vuelto a entregar:</strong><br>
                                <span class="h4 font-weight-bold text-success">$<span id="vuelto-monto">0,00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <button type="button" class="btn btn-success" id="btn-confirmar-efectivo" disabled>
                    <i class="fas fa-check"></i> Confirmar Pago
                </button>
                <button type="button" class="btn btn-info" id="btn-imprimir-ticket-efectivo" style="display: none;">
                    <i class="fas fa-print"></i> Imprimir Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAGO DÉBITO/CRÉDITO -->
<div class="modal fade" id="modalPagoTarjeta" tabindex="-1" aria-labelledby="modalPagoTarjetaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPagoTarjetaLabel">
                    <i class="fas fa-credit-card"></i> Pago con Tarjeta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <h4>Total a Cobrar</h4>
                    <h2 class="text-primary font-weight-bold">$<span id="total-tarjeta">0,00</span></h2>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                    <strong>Instrucciones:</strong><br>
                    1. Inserte o pase la tarjeta en la terminal<br>
                    2. Solicite al cliente que ingrese su PIN<br>
                    3. Espere la confirmación de la transacción
                </div>

                <div class="form-group">
                    <label for="tipo-tarjeta"><strong>Tipo de Tarjeta:</strong></label>
                    <select id="tipo-tarjeta" class="form-control">
                        <option value="debito">Débito</option>
                        <option value="credito">Crédito</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero-autorizacion"><strong>Número de Autorización:</strong></label>
                    <input type="text" id="numero-autorizacion" class="form-control" placeholder="Opcional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-tarjeta">
                    <i class="fas fa-check"></i> Confirmar Transacción
                </button>
                <button type="button" class="btn btn-info" id="btn-imprimir-ticket-tarjeta" style="display: none;">
                    <i class="fas fa-print"></i> Imprimir Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CUOTAS INTERNAS -->
<div class="modal fade" id="modalCuotasInternas" tabindex="-1" aria-labelledby="modalCuotasInternasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalCuotasInternasLabel">
                    <i class="fas fa-calendar-alt"></i> Financiamiento Interno
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Información del cliente -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Sistema de Financiamiento Interno</strong><br>
                    Permite financiar el tratamiento odontológico en cuotas con interés aplicado según normativas vigentes.
                </div>

                <!-- Selección/búsqueda de cliente -->
                <div class="form-group">
                    <label for="buscar-cliente"><strong><i class="fas fa-user"></i> Cliente (Paciente):</strong></label>
                    <div class="input-group">
                        <input type="text"
                               id="buscar-cliente"
                               class="form-control"
                               placeholder="Buscar por DNI, nombre o apellido..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btn-buscar-cliente">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" id="btn-nuevo-cliente">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <div id="resultados-busqueda" class="mt-2" style="display: none;"></div>
                </div>

                <!-- Cliente seleccionado -->
                <div id="cliente-seleccionado" class="alert alert-success" style="display: none;">
                    <h6><i class="fas fa-user-check"></i> Cliente Seleccionado:</h6>
                    <div id="datos-cliente-seleccionado"></div>
                </div>

                <!-- Configuración del financiamiento -->
                <div id="configuracion-financiamiento" style="display: none;">
                    <hr>
                    <h6><i class="fas fa-calculator"></i> Configuración del Financiamiento</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="cantidad-cuotas"><strong>Cantidad de Cuotas:</strong></label>
                            <select id="cantidad-cuotas" class="form-control">
                                <option value="3">3 cuotas (30% interés)</option>
                                <option value="6">6 cuotas (45% interés)</option>
                                <option value="9">9 cuotas (60% interés)</option>
                                <option value="12">12 cuotas (75% interés)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label><strong>Monto Original:</strong></label>
                            <div class="form-control-plaintext h5 text-success">$<span id="monto-original-cuotas">0,00</span></div>
                        </div>
                        <div class="col-md-4">
                            <label><strong>Monto Total c/Interés:</strong></label>
                            <div class="form-control-plaintext h5 text-primary">$<span id="monto-total-cuotas">0,00</span></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="fecha-primera-cuota"><strong>Primera Cuota:</strong></label>
                            <input type="date" id="fecha-primera-cuota" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label><strong>Valor de cada cuota:</strong></label>
                            <div class="form-control-plaintext h4 text-warning">$<span id="valor-cada-cuota">0,00</span></div>
                        </div>
                    </div>

                    <!-- Vista previa del plan de cuotas -->
                    <div class="mt-4">
                        <h6><i class="fas fa-list-ol"></i> Plan de Cuotas</h6>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Cuota N°</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-plan-cuotas">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btn-confirmar-financiamiento" style="display: none;">
                    <i class="fas fa-file-contract"></i> Generar Financiamiento
                </button>
            </div>
        </div>
    </div>
</div>
