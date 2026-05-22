@extends('adminlte::page')

@section('title', 'BOX - Cobros de Cuotas Estudiantiles - Cooperadora Odontología UNT')

@section('content')
    <div class="row">
        <!-- Lista de Estudiantes -->
        <div class="col-md-8">
            <div class="card card-primary" id="card-lista-estudiantes">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Estudiantes de Tecnicaturas</h3>
                </div>
                <div class="card-body">
                    <!-- Búsqueda rápida -->
                    <div class="form-group mb-3">
                        <input type="text" id="searchEstudiante" class="form-control form-control-lg"
                               placeholder="Buscar por nombre o DNI... (mínimo 2 caracteres)">
                        <small class="text-muted">Resultados en tiempo real</small>
                    </div>

                    <!-- Lista de resultados de búsqueda -->
                    <div id="estudiantesList" class="list-group" style="max-height: 400px; overflow-y: auto; display: none;">
                        <!-- Resultados dinámicos -->
                    </div>

                    <!-- Tabla de estudiantes (fallback) -->
                    <div id="tablaEstudiantes" class="table-responsive">
                        <table id="tabla-estudiantes" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Legajo</th>
                                    <th>Nombre</th>
                                    <th>DNI</th>
                                    <th>Carrera</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="estudiantes-tbody">
                                <!-- Se llenan dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información del Estudiante Seleccionado -->
            <div class="card card-info" id="card-estudiante" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Datos del Estudiante Seleccionado</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-light" id="btn-volver-lista">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nombre:</strong> <span id="estudiante-nombre"></span><br>
                            <strong>DNI:</strong> <span id="estudiante-dni"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Carrera:</strong> <span id="estudiante-carrera"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuotas Adeudadas -->
            <div class="card card-warning" id="card-cuotas" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Cuotas Adeudadas</h3>
                </div>
                <div class="card-body">
                    <div id="loadingIndicator" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th><input type="checkbox" id="select-all-cuotas"></th>
                                    <th>Período</th>
                                    <th>Vencimiento</th>
                                    <th class="text-right">Importe</th>
                                    <th class="text-right">Interés/Recargo</th>
                                    <th class="text-right">Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="cuotas-tbody">
                            </tbody>
                        </table>
                    </div>
                    <div id="noCuotasMsg" class="text-muted text-center py-3">
                        No hay cuotas adeudadas
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito de Cuotas Seleccionadas -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Cuotas Seleccionadas</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x"></i>
                            <p class="mt-2">No hay cuotas seleccionadas</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row mb-2">
                        <div class="col">
                            <h5 class="text-success">Subtotal: $<span id="subtotal-carrito">0.00</span></h5>
                            <h5 class="text-danger">Interés: $<span id="interes-carrito">0.00</span></h5>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col">
                            <h4 class="text-success">Total: $<span id="total-carrito">0.00</span></h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success" id="btn-proceder-pago" disabled>
                                <i class="fas fa-credit-card"></i> Proceder
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

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
        }
        .cuota-vencida {
            background-color: #fff3cd !important;
        }
        .cuota-mesactual {
            background-color: #d1ecf1 !important;
        }
        #tabla-estudiantes tbody tr {
            cursor: pointer;
        }
        #tabla-estudiantes tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        const estudiantesData = {!! $estudiantesJson !!};
        let cuotasActuales = [];
        let cuotasSeleccionadas = {};
        let totalSubtotal = 0;
        let totalInteres = 0;
        let mesActual = {{ now()->month }};

        $(document).ready(function() {
            // Búsqueda de estudiante
            $('#searchEstudiante').on('keyup', function() {
                const search = $(this).val().toLowerCase().trim();

                if (search.length < 2) {
                    $('#estudiantesList').hide();
                    return;
                }

                const resultados = estudiantesData.filter(est =>
                    est.nombre.toLowerCase().includes(search) ||
                    est.dni.includes(search)
                );

                if (resultados.length === 0) {
                    $('#estudiantesList').html('<div class="list-group-item text-muted">No hay resultados</div>').show();
                    return;
                }

                let html = '';
                resultados.forEach(est => {
                    const carreraText = est.carrera === 'tecnicatura_protesis' ? 'Prótesis Dental' : 'Asistencia Dental';
                    html += `
                        <a href="#" class="list-group-item list-group-item-action" onclick="seleccionarEstudiante(${est.id}, event)">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${est.nombre}</h6>
                                <small>${est.dni}</small>
                            </div>
                            <p class="mb-1 text-muted">${carreraText}</p>
                        </a>
                    `;
                });

                $('#estudiantesList').html(html).show();
            });

            // Checkbox "seleccionar todo"
            $(document).on('change', '#select-all-cuotas', function() {
                const isChecked = $(this).is(':checked');
                $('input[name="cuota_id"]').prop('checked', isChecked).trigger('change');
            });

            // Checkbox individual de cuota
            $(document).on('change', 'input[name="cuota_id"]', function() {
                actualizarCarrito();
            });

            // Volver a lista de estudiantes
            $('#btn-volver-lista').on('click', function() {
                volverALista();
            });

            // Proceder al pago
            $('#btn-proceder-pago').on('click', function() {
                if (Object.keys(cuotasSeleccionadas).length === 0) {
                    alert('Seleccione al menos una cuota');
                    return;
                }

                // Llenar resumen en el modal
                llenarResumenModal();

                window.estudianteCobroActual = window.estudianteCobroActual || {
                    nombre: $('#estudiante-nombre').text().trim(),
                    dni: $('#estudiante-dni').text().trim()
                };

                // Mostrar modal de pago
                $('#modalPago').modal('show');
            });

            // Al cerrar modal, limpiar
            $('#modalPago').on('hidden.bs.modal', function() {
                // Reset de selecciones para próxima compra
            });
        });

        function seleccionarEstudiante(estudianteId, event) {
            if (event) event.preventDefault();
            const est = estudiantesData.find(e => e.id === estudianteId);
            if (!est) return;

            window.estudianteCobroActual = {
                nombre: est.nombre || '',
                dni: est.dni || ''
            };

            $('#estudiante-nombre').text(est.nombre);
            $('#estudiante-dni').text(est.dni);
            $('#estudiante-carrera').text(est.carrera === 'tecnicatura_protesis' ? 'Prótesis Dental' : 'Asistencia Dental');

            $('#card-lista-estudiantes').hide();
            $('#card-estudiante').show();
            $('#searchEstudiante').val('');
            $('#estudiantesList').hide();

            // Cargar cuotas
            cargarCuotasEstudiante(estudianteId);

            $('html, body').animate({ scrollTop: $('#card-estudiante').offset().top - 70 }, 300);
        }

        function cargarCuotasEstudiante(estudianteId) {
            $('#loadingIndicator').show();
            $('#cuotas-tbody').html('');
            $('#noCuotasMsg').show();
            cuotasActuales = [];
            cuotasSeleccionadas = {};

            $.ajax({
                url: '{{ route("box.cobros.cuotas.buscar") }}',
                type: 'GET',
                data: { estudiante_id: estudianteId },
                success: function(response) {
                    $('#loadingIndicator').hide();
                    cuotasActuales = response.cuotas;

                    if (cuotasActuales.length === 0) {
                        $('#card-cuotas').show();
                        $('#noCuotasMsg').show();
                        return;
                    }

                    $('#noCuotasMsg').hide();
                    let html = '';

                    cuotasActuales.forEach(cuota => {
                        const rowClass = cuota.numero_cuota === mesActual ? 'cuota-mesactual' : (cuota.vencida ? 'cuota-vencida' : '');
                        const estadoBadge = cuota.vencida
                            ? '<span class="badge badge-warning">Vencida</span>'
                            : '<span class="badge badge-info">Mes Actual</span>';

                        html += `
                            <tr class="${rowClass}">
                                <td><input type="checkbox" name="cuota_id" value="${cuota.id}"></td>
                                <td>${cuota.periodo}</td>
                                <td>${cuota.vencimiento}</td>
                                <td class="text-right">$${cuota.importe.toFixed(2)}</td>
                                <td class="text-right">
                                    ${cuota.recargo > 0
                                        ? `<span class="badge badge-danger">$${cuota.recargo.toFixed(2)}</span>`
                                        : '-'
                                    }
                                </td>
                                <td class="text-right"><strong>$${cuota.total.toFixed(2)}</strong></td>
                                <td>${estadoBadge}</td>
                            </tr>
                        `;
                    });

                    $('#cuotas-tbody').html(html);
                    $('#card-cuotas').show();
                    $('#select-all-cuotas').prop('checked', false);
                },
                error: function(xhr) {
                    $('#loadingIndicator').hide();
                    alert('Error al cargar cuotas: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                }
            });
        }

        function actualizarCarrito() {
            sincronizarCuotasSeleccionadas();

            if (Object.keys(cuotasSeleccionadas).length === 0) {
                $('#carrito-items').html(`
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-invoice-dollar fa-3x"></i>
                        <p class="mt-2">No hay cuotas seleccionadas</p>
                    </div>
                `);
                $('#btn-proceder-pago').prop('disabled', true);
                $('#subtotal-carrito').text('0.00');
                $('#interes-carrito').text('0.00');
                $('#total-carrito').text('0.00');
                return;
            }

            let html = '<table class="table table-sm"><tbody>';
            totalSubtotal = 0;
            totalInteres = 0;

            Object.values(cuotasSeleccionadas).forEach(cuota => {
                totalSubtotal += cuota.importe;
                totalInteres += cuota.recargo;
                html += `
                    <tr>
                        <td>${cuota.periodo}</td>
                        <td class="text-right">$${cuota.importe.toFixed(2)}</td>
                        ${cuota.recargo > 0
                            ? `<td class="text-right text-danger">+$${cuota.recargo.toFixed(2)}</td>`
                            : '<td></td>'
                        }
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $('#carrito-items').html(html);

            $('#subtotal-carrito').text(totalSubtotal.toFixed(2));
            $('#interes-carrito').text(totalInteres.toFixed(2));
            $('#total-carrito').text((totalSubtotal + totalInteres).toFixed(2));

            $('#btn-proceder-pago').prop('disabled', false);

            // Actualizar función de totales para el modal
            actualizarTotales();
        }

        function sincronizarCuotasSeleccionadas() {
            cuotasSeleccionadas = {};
            $('input[name="cuota_id"]:checked').each(function() {
                const cuotaId = parseInt($(this).val());
                const cuota = cuotasActuales.find(c => c.id === cuotaId);
                if (cuota) {
                    cuotasSeleccionadas[cuotaId] = cuota;
                }
            });

            totalSubtotal = 0;
            totalInteres = 0;

            Object.values(cuotasSeleccionadas).forEach(cuota => {
                totalSubtotal += cuota.importe;
                totalInteres += cuota.recargo;
            });

            return cuotasSeleccionadas;
        }

        function llenarResumenModal() {
            sincronizarCuotasSeleccionadas();

            let html = '';
            const cuotasSeleccionadasLista = Object.values(cuotasSeleccionadas);

            if (cuotasSeleccionadasLista.length === 0) {
                $('#resumen-items').html('<div class="text-center text-muted py-3">No hay cuotas seleccionadas</div>');
                actualizarTotales();
                return;
            }

            cuotasSeleccionadasLista.forEach(cuota => {
                html += `
                    <div class="d-flex justify-content-between mb-1">
                        <span>${cuota.periodo}</span>
                        <span class="font-weight-bold">$${cuota.total.toFixed(2)}</span>
                    </div>
                `;
            });
            $('#resumen-items').html(html);

            actualizarTotales();
        }

        // Función que el modal espera
        window.actualizarTotales = function() {
            const totalConDescuento = calcularTotalConDescuento();
            const descuento = (totalSubtotal + totalInteres - totalConDescuento);
            $('#modal-subtotal').text(totalSubtotal.toFixed(2));
            $('#modal-interes').text(totalInteres.toFixed(2));
            $('#modal-descuento').text(descuento.toFixed(2));
            $('#modal-total').text(totalConDescuento.toFixed(2));
        };

        function calcularTotalConDescuento() {
            const tipoDescuento = $('input[name="tipoDescuento"]:checked').val();
            const valorDescuento = parseFloat($('#valor-descuento').val()) || 0;
            let total = totalSubtotal + totalInteres;

            if (tipoDescuento === 'porcentaje' && valorDescuento > 0) {
                total = total - (total * valorDescuento / 100);
            } else if (tipoDescuento === 'valor' && valorDescuento > 0) {
                total = total - valorDescuento;
            }

            return Math.max(0, total);
        }

        function volverALista() {
            $('#card-lista-estudiantes').show();
            $('#card-estudiante').hide();
            $('#card-cuotas').hide();
            $('#searchEstudiante').val('');
            $('#estudiantesList').hide();

            cuotasActuales = [];
            cuotasSeleccionadas = {};
            actualizarCarrito();
        }

        // Configurar el procesamiento del pago en el modal
        $(document).on('click', '#btn-procesar-pago', function() {
            sincronizarCuotasSeleccionadas();
            procesarPagoConFactura();
        });

        function procesarPagoConFactura() {
            console.log('Procesando pago de cuotas');

            const tipoComprobante = $('input[name="tipoComprobante"]:checked').val() || 'factura_local';
            const metodoPago = $('input[name="metodoPago"]:checked').val() || 'efectivo';
            const totalFinal = calcularTotalConDescuento();
            const observaciones = $('#observaciones').val() || '';
            const montoRecibido = parseFloat($('#monto-recibido').val() || '0') || 0;
            const montoVuelto = Math.max(montoRecibido - totalFinal, 0);
            const mixtoMetodo1 = ($('#mixto-metodo-1').val() || '').trim();
            const mixtoMetodo2 = ($('#mixto-metodo-2').val() || '').trim();
            const mixtoMonto1 = parseFloat($('#mixto-monto-1').val() || '0') || 0;
            const mixtoMonto2 = parseFloat($('#mixto-monto-2').val() || '0') || 0;

            // Datos de cliente para factura
            const clienteNombre = $('#cliente-nombre').val() || '';
            const clienteDocumento = $('#cliente-documento').val() || '';
            const clienteDireccion = $('#cliente-direccion').val() || '';
            const clienteCondicionIva = $('#condicion-iva').val() || 'consumidor_final';

            const cuotaIds = Object.keys(cuotasSeleccionadas);

            if (metodoPago === 'efectivo' && montoRecibido < totalFinal) {
                alert('El monto recibido es menor al total a pagar.');
                const campoMontoRecibido = document.getElementById('monto-recibido');
                if (campoMontoRecibido) {
                    campoMontoRecibido.focus();
                }
                return;
            }

            if (metodoPago === 'mixto') {
                const mediosValidos = ['efectivo', 'tarjeta', 'transferencia'];
                if (!mediosValidos.includes(mixtoMetodo1) || !mediosValidos.includes(mixtoMetodo2)) {
                    alert('Seleccione dos medios de pago válidos para el pago mixto.');
                    return;
                }

                if (mixtoMetodo1 === mixtoMetodo2) {
                    alert('En pago mixto debe elegir dos medios diferentes.');
                    return;
                }

                if (mixtoMonto1 <= 0 || mixtoMonto2 <= 0) {
                    alert('En pago mixto ambos montos deben ser mayores a 0.');
                    return;
                }

                const totalMixto = mixtoMonto1 + mixtoMonto2;
                if (Math.abs(totalMixto - totalFinal) > 0.01) {
                    alert('La suma del pago mixto debe ser exactamente igual al total.');
                    return;
                }
            }

            $('#btn-procesar-pago').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            $.ajax({
                url: '{{ route("box.cobros.cuotas.registrar") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cuota_ids: cuotaIds,
                    metodo_pago: metodoPago,
                    tipo_comprobante: tipoComprobante,
                    numero_comprobante: $('#numeroComprobante').val() || '',
                    cliente_nombre: clienteNombre,
                    cliente_documento: clienteDocumento,
                    cliente_direccion: clienteDireccion,
                    cliente_condicion_iva: clienteCondicionIva,
                    monto_recibido: montoRecibido,
                    monto_vuelto: montoVuelto,
                    mixto_metodo_1: mixtoMetodo1,
                    mixto_monto_1: mixtoMonto1,
                    mixto_metodo_2: mixtoMetodo2,
                    mixto_monto_2: mixtoMonto2,
                    observaciones: observaciones,
                    total: totalFinal
                },
                success: function(response) {
                    const pdfUrl = response?.datos?.pdf_url;

                    alert('Pago registrado correctamente.\nCuotas: ' + response.datos.cuotas_pagadas + '\nTotal: $' + response.datos.total_pagado.toFixed(2));

                    if (pdfUrl) {
                        window.open(pdfUrl, '_blank');
                    }

                    $('#modalPago').modal('hide');
                    volverALista();
                },
                error: function(xhr) {
                    alert('Error al registrar el pago: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                },
                complete: function() {
                    $('#btn-procesar-pago').prop('disabled', false).html('<i class="fas fa-check"></i> Procesar Pago');
                }
            });
        }
    </script>
@stop
