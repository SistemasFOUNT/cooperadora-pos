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
