{{-- Modal de Facturación --}}
<div class="modal fade" id="modalFacturacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-invoice"></i>
                    Generar Factura - Venta #{{ $venta->sale_number ?? 'V-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Información de la venta --}}
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Fecha:</strong> {{ $venta->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Cajero:</strong> {{ $venta->user->name ?? 'No disponible' }}<br>
                            <strong>Total:</strong> ${{ number_format($venta->total ?? $venta->total_amount ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Items:</strong> {{ $venta->items->count() ?? 0 }}<br>
                            <strong>Método pago:</strong> {{ $venta->paymentMethod->name ?? 'Efectivo' }}<br>
                            @if($venta->student_id)
                                <strong>Estudiante:</strong> {{ $venta->student->name ?? 'Estudiante' }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tabs de tipo de factura --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#local" role="tab">
                            <i class="fas fa-receipt"></i> Factura Local
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#arca" role="tab">
                            <i class="fas fa-stamp"></i> Factura ARCA
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- Factura Local --}}
                    <div class="tab-pane fade show active" id="local" role="tabpanel">
                        <form id="formFacturaLocal">
                            @csrf
                            <input type="hidden" name="venta_id" value="{{ $venta->id }}">
                            <input type="hidden" name="tipo" value="local">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="local_cliente_nombre">Nombre/Razón Social <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="local_cliente_nombre"
                                               name="cliente_nombre"
                                               class="form-control"
                                               value="{{ $venta->student->name ?? '' }}"
                                               placeholder="Nombre del cliente"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="local_cliente_documento">DNI/CUIT</label>
                                        <input type="text"
                                               id="local_cliente_documento"
                                               name="cliente_documento"
                                               class="form-control"
                                               placeholder="Ej: 12345678 o 20-12345678-9">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="local_cliente_direccion">Dirección</label>
                                        <input type="text"
                                               id="local_cliente_direccion"
                                               name="cliente_direccion"
                                               class="form-control"
                                               placeholder="Dirección del cliente">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="local_cliente_telefono">Teléfono</label>
                                        <input type="text"
                                               id="local_cliente_telefono"
                                               name="cliente_telefono"
                                               class="form-control"
                                               placeholder="Teléfono">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="local_observaciones">Observaciones</label>
                                <textarea id="local_observaciones"
                                          name="observaciones"
                                          class="form-control"
                                          rows="2"
                                          placeholder="Observaciones adicionales (opcional)"></textarea>
                            </div>
                        </form>
                    </div>

                    {{-- Factura ARCA --}}
                    <div class="tab-pane fade" id="arca" role="tabpanel">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Esta factura será enviada a ARCA (AFIP) para obtener autorización oficial.
                        </div>

                        <form id="formFacturaARCA">
                            @csrf
                            <input type="hidden" name="venta_id" value="{{ $venta->id }}">
                            <input type="hidden" name="tipo" value="arca">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_tipo_comprobante">Tipo de Comprobante <span class="text-danger">*</span></label>
                                        <select id="arca_tipo_comprobante"
                                                name="tipo_comprobante"
                                                class="form-control"
                                                required>
                                            <option value="">Seleccionar...</option>
                                            <option value="B">Factura B (Consumidor Final)</option>
                                            <option value="A">Factura A (Responsable Inscripto)</option>
                                            <option value="C">Factura C (Exento)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_condicion_iva">Condición IVA <span class="text-danger">*</span></label>
                                        <select id="arca_condicion_iva"
                                                name="condicion_iva"
                                                class="form-control"
                                                required>
                                            <option value="">Seleccionar...</option>
                                            <option value="CF">Consumidor Final</option>
                                            <option value="RI">Responsable Inscripto</option>
                                            <option value="EX">Exento</option>
                                            <option value="MT">Monotributista</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_nombre">Razón Social/Nombre <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="arca_cliente_nombre"
                                               name="cliente_nombre"
                                               class="form-control"
                                               value="{{ $venta->student->name ?? '' }}"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_cuit">CUIT/CUIL</label>
                                        <input type="text"
                                               id="arca_cliente_cuit"
                                               name="cliente_cuit"
                                               class="form-control"
                                               placeholder="20-12345678-9"
                                               pattern="[0-9]{2}-[0-9]{8}-[0-9]{1}">
                                        <small class="text-muted">Formato: XX-XXXXXXXX-X</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="arca_cliente_direccion">Dirección <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="arca_cliente_direccion"
                                       name="cliente_direccion"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_localidad">Localidad <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="arca_cliente_localidad"
                                               name="cliente_localidad"
                                               class="form-control"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="arca_cliente_provincia">Provincia <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="arca_cliente_provincia"
                                               name="cliente_provincia"
                                               class="form-control"
                                               value="Buenos Aires"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="arca_cliente_cp">Código Postal</label>
                                        <input type="text"
                                               id="arca_cliente_cp"
                                               name="cliente_cp"
                                               class="form-control"
                                               placeholder="1000">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="procesarFacturacion()">
                    <i class="fas fa-file-invoice"></i> Generar Factura
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Procesar facturación según el tab activo
 */
function procesarFacturacion() {
    const tabActivo = $('#modalFacturacion .nav-link.active').attr('href');
    const formId = tabActivo === '#local' ? '#formFacturaLocal' : '#formFacturaARCA';
    const form = $(formId)[0];

    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const tipo = formData.get('tipo');

    // Mostrar loading
    const btnGenerar = $('.modal-footer .btn-success');
    const textoOriginal = btnGenerar.html();
    btnGenerar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');

    // Enviar solicitud
    $.ajax({
        url: '{{ route("box.facturas.generar") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Factura Generada',
                    text: response.message,
                    confirmButtonText: 'Ver Factura'
                }).then((result) => {
                    if (result.isConfirmed && response.factura_url) {
                        window.open(response.factura_url, '_blank');
                    }
                });

                $('#modalFacturacion').modal('hide');

                // Actualizar la página si es necesario
                if (typeof actualizarVentas === 'function') {
                    actualizarVentas();
                }
            } else {
                Swal.fire('Error', response.message || 'Error al generar la factura', 'error');
            }
        },
        error: function(xhr) {
            let mensaje = 'Error inesperado al generar la factura';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errores = Object.values(xhr.responseJSON.errors).flat();
                mensaje = errores.join('<br>');
            }

            Swal.fire('Error', mensaje, 'error');
        },
        complete: function() {
            btnGenerar.prop('disabled', false).html(textoOriginal);
        }
    });
}

// Validaciones en tiempo real
$(document).ready(function() {
    // Formateo automático de CUIT
    $('#arca_cliente_cuit').on('input', function() {
        let valor = $(this).val().replace(/\D/g, '');
        if (valor.length >= 2) {
            valor = valor.substring(0, 2) + '-' + valor.substring(2, 10) + (valor.length > 10 ? '-' + valor.substring(10, 11) : '');
        }
        $(this).val(valor);
    });

    // Sincronizar condición IVA con tipo de comprobante
    $('#arca_tipo_comprobante').on('change', function() {
        const tipo = $(this).val();
        const condicionSelect = $('#arca_condicion_iva');

        condicionSelect.html('<option value="">Seleccionar...</option>');

        if (tipo === 'A') {
            condicionSelect.append('<option value="RI">Responsable Inscripto</option>');
        } else if (tipo === 'B') {
            condicionSelect.append('<option value="CF">Consumidor Final</option>');
            condicionSelect.append('<option value="MT">Monotributista</option>');
        } else if (tipo === 'C') {
            condicionSelect.append('<option value="EX">Exento</option>');
        }
    });
});
</script>
<div class="modal fade" id="modalFacturacion" tabindex="-1" role="dialog" aria-labelledby="modalFacturacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFacturacionLabel">
                    <i class="fas fa-file-invoice"></i>
                    Generar Factura - Venta #{{ $sale->id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Información de la venta -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><strong>Información de la Venta</strong></h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total:</strong> ${{ number_format($sale->total, 2, ',', '.') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Cajero:</strong> {{ $sale->user->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Productos:</strong> {{ $sale->items->count() }} items
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs para tipos de factura -->
                <ul class="nav nav-tabs" id="facturacionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="local-tab" data-toggle="tab" href="#local" role="tab" aria-controls="local" aria-selected="true">
                            <i class="fas fa-receipt"></i>
                            Factura Local (Interna)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="arca-tab" data-toggle="tab" href="#arca" role="tab" aria-controls="arca" aria-selected="false">
                            <i class="fas fa-file-invoice-dollar"></i>
                            Factura ARCA (Oficial)
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="facturacionTabsContent">
                    <!-- Factura Local -->
                    <div class="tab-pane fade show active" id="local" role="tabpanel" aria-labelledby="local-tab">
                        <form id="formFacturaLocal" method="POST" action="{{ route('box.facturas.local', $sale) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-user"></i>
                                        Datos del Cliente (Opcional para factura local)
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="local_cliente_nombre">Nombre/Razón Social</label>
                                        <input type="text" class="form-control" id="local_cliente_nombre"
                                               name="cliente_nombre" value="Consumidor Final">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="local_cliente_cuit">CUIT/CUIL (opcional)</label>
                                        <input type="text" class="form-control" id="local_cliente_cuit"
                                               name="cliente_cuit" placeholder="00-00000000-0">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="local_cliente_domicilio">Domicilio (opcional)</label>
                                        <input type="text" class="form-control" id="local_cliente_domicilio"
                                               name="cliente_domicilio">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-success">
                                <i class="fas fa-info-circle"></i>
                                <strong>Factura Local:</strong> Para control interno. No requiere autorización externa.
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    Generar Factura Local
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Factura ARCA -->
                    <div class="tab-pane fade" id="arca" role="tabpanel" aria-labelledby="arca-tab">
                        <form id="formFacturaARCA" method="POST" action="{{ route('box.facturas.arca', $sale) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-user-tie"></i>
                                        Datos del Cliente (Requeridos para ARCA)
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_nombre">Nombre/Razón Social <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="arca_cliente_nombre"
                                               name="cliente_nombre" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_cuit">CUIT/CUIL <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="arca_cliente_cuit"
                                               name="cliente_cuit" placeholder="00-00000000-0" required>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="arca_cliente_domicilio">Domicilio</label>
                                        <input type="text" class="form-control" id="arca_cliente_domicilio"
                                               name="cliente_domicilio">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="arca_tipo_comprobante">Tipo Comprobante <span class="text-danger">*</span></label>
                                        <select class="form-control" id="arca_tipo_comprobante" name="tipo_comprobante" required>
                                            <option value="B" selected>Factura B (Consumidor Final)</option>
                                            <option value="A">Factura A (Responsable Inscripto)</option>
                                            <option value="C">Factura C (Exento)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_condicion_iva">Condición IVA</label>
                                        <select class="form-control" id="arca_cliente_condicion_iva" name="cliente_condicion_iva">
                                            <option value="Consumidor Final">Consumidor Final</option>
                                            <option value="Responsable Inscripto">Responsable Inscripto</option>
                                            <option value="Monotributista">Monotributista</option>
                                            <option value="Exento">Exento</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="arca_cliente_email">Email (opcional)</label>
                                        <input type="email" class="form-control" id="arca_cliente_email"
                                               name="cliente_email" placeholder="cliente@email.com">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Factura ARCA:</strong> Requiere conexión con AFIP. Se generará CAE automáticamente.
                            </div>

                            @if(!config('facturacion.arca.habilitado'))
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i>
                                <strong>ARCA Deshabilitado:</strong> La facturación electrónica no está configurada.
                            </div>
                            @endif

                            <div class="text-right">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary"
                                        {{ !config('facturacion.arca.habilitado') ? 'disabled' : '' }}>
                                    <i class="fas fa-stamp"></i>
                                    Generar Factura ARCA
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Formatear CUIT automáticamente
    $('input[name="cliente_cuit"]').on('input', function() {
        let value = this.value.replace(/[^\d]/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d{8})(\d{1})/, '$1-$2-$3');
        }
        this.value = value;
    });

    // Cambiar condición IVA automáticamente según tipo de comprobante
    $('#arca_tipo_comprobante').on('change', function() {
        const tipoComprobante = $(this).val();
        const condicionIva = $('#arca_cliente_condicion_iva');

        switch(tipoComprobante) {
            case 'A':
                condicionIva.val('Responsable Inscripto');
                break;
            case 'B':
                condicionIva.val('Consumidor Final');
                break;
            case 'C':
                condicionIva.val('Exento');
                break;
        }
    });

    // Manejar envío del formulario local
    $('#formFacturaLocal').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.mensaje,
                        icon: 'success',
                        confirmButtonText: 'Ver Factura'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`{{ route('box.facturas.ver', '') }}/${response.factura_id}`, '_blank');
                        }
                        $('#modalFacturacion').modal('hide');
                        location.reload(); // Recargar para mostrar botón de factura generada
                    });
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error al generar la factura local', 'error');
            }
        });
    });

    // Manejar envío del formulario ARCA
    $('#formFacturaARCA').on('submit', function(e) {
        e.preventDefault();

        // Mostrar loading
        Swal.fire({
            title: 'Procesando...',
            text: 'Enviando datos a ARCA. Por favor espere.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.close();

                if (response.success) {
                    Swal.fire({
                        title: '¡Factura Autorizada!',
                        html: `
                            <p>${response.mensaje}</p>
                            <hr>
                            <strong>CAE:</strong> ${response.cae}<br>
                            <strong>Vencimiento:</strong> ${response.fecha_vto_cae}
                        `,
                        icon: 'success',
                        confirmButtonText: 'Ver Factura'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`{{ route('box.facturas.ver', '') }}/${response.factura_id}`, '_blank');
                        }
                        $('#modalFacturacion').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = 'Error al generar la factura ARCA';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });
});
</script>
