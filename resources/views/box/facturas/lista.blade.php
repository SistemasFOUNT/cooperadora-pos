@extends('adminlte::page')

@section('title', 'Lista de Facturas - BOX Cooperadora')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-file-invoice"></i>
        Lista de Facturas - BOX Cooperadora
    </h1>
    <div>
        <a href="{{ route('box.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filtros -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i>
                    Filtros de Búsqueda
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('box.facturas.lista') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Desde:</label>
                                <input type="date" name="fecha_desde" class="form-control"
                                       value="{{ $filtros['fecha_desde'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Hasta:</label>
                                <input type="date" name="fecha_hasta" class="form-control"
                                       value="{{ $filtros['fecha_hasta'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tipo:</label>
                                <select name="tipo" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="local" {{ ($filtros['tipo'] ?? '') == 'local' ? 'selected' : '' }}>
                                        Local
                                    </option>
                                    <option value="arca" {{ ($filtros['tipo'] ?? '') == 'arca' ? 'selected' : '' }}>
                                        ARCA
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select name="estado" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="emitida" {{ ($filtros['estado'] ?? '') == 'emitida' ? 'selected' : '' }}>
                                        Emitida
                                    </option>
                                    <option value="autorizada" {{ ($filtros['estado'] ?? '') == 'autorizada' ? 'selected' : '' }}>
                                        Autorizada
                                    </option>
                                    <option value="anulada" {{ ($filtros['estado'] ?? '') == 'anulada' ? 'selected' : '' }}>
                                        Anulada
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="{{ route('box.facturas.lista') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Facturas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Facturas ({{ $facturas->total() }} registros)
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Venta</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facturas as $factura)
                            <tr>
                                <td>
                                    <strong>{{ $factura->numero_completo }}</strong>
                                    @if($factura->es_arca && $factura->cae)
                                        <br><small class="text-muted">CAE: {{ $factura->cae }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($factura->tipo == 'local')
                                        <span class="badge badge-info">
                                            <i class="fas fa-receipt"></i> Local
                                        </span>
                                    @else
                                        <span class="badge badge-primary">
                                            <i class="fas fa-stamp"></i> ARCA {{ $factura->tipo_comprobante ?? '' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $factura->formatearFecha() }}
                                    <br><small class="text-muted">{{ $factura->fecha_emision->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($factura->razon_social_cliente)
                                        <strong>{{ $factura->razon_social_cliente }}</strong>
                                        @if($factura->cuit_cliente)
                                            <br><small class="text-muted">{{ $factura->cuit_cliente }}</small>
                                        @endif
                                    @else
                                        {{ ($factura->datos_cliente['nombre'] ?? 'Consumidor Final') }}
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="text-primary">
                                        Venta #{{ $factura->sale_id }}
                                    </a>
                                    <br><small class="text-muted">
                                        {{ $factura->sale->user->name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-right">
                                    <strong>{{ $factura->formatearTotal() }}</strong>
                                    @if($factura->iva > 0)
                                        <br><small class="text-muted">IVA: ${{ number_format($factura->iva, 2, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @switch($factura->estado)
                                        @case('emitida')
                                            <span class="badge badge-success">Emitida</span>
                                            @break
                                        @case('autorizada')
                                            <span class="badge badge-success">Autorizada</span>
                                            @break
                                        @case('pendiente_arca')
                                            <span class="badge badge-warning">Pendiente ARCA</span>
                                            @break
                                        @case('rechazada')
                                            <span class="badge badge-danger">Rechazada</span>
                                            @break
                                        @case('anulada')
                                            <span class="badge badge-secondary">Anulada</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <!-- Ver/Imprimir Factura -->
                                        <a href="{{ route('box.facturas.ver', $factura) }}"
                                           class="btn btn-sm btn-info" title="Ver Factura" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Descargar PDF -->
                                        <a href="{{ route('box.facturas.ver', [$factura, 'formato' => 'pdf']) }}"
                                           class="btn btn-sm btn-primary" title="Descargar PDF" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Anular (solo si no está anulada) -->
                                        @if($factura->estado !== 'anulada')
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="anularFactura({{ $factura->id }})" title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                    No se encontraron facturas con los filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($facturas->hasPages())
            <div class="card-footer">
                {{ $facturas->appends($filtros)->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para anular factura -->
<div class="modal fade" id="modalAnular" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i>
                    Anular Factura
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formAnular" method="POST">
                @csrf
                <div class="modal-body">
                    <p><strong>¿Está seguro de que desea anular esta factura?</strong></p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>

                    <div class="form-group">
                        <label for="motivo">Motivo de anulación:</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required
                                  placeholder="Ingrese el motivo de la anulación..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i>
                        Anular Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function anularFactura(facturaId) {
    // Establecer la acción del formulario
    $('#formAnular').attr('action', `{{ route('box.facturas.lista') }}/../../facturas/anular/${facturaId}`);

    // Mostrar modal
    $('#modalAnular').modal('show');
}

// Manejar envío del formulario de anulación
$('#formAnular').on('submit', function(e) {
    e.preventDefault();

    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Anulando factura...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Enviar formulario
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Factura anulada correctamente',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error',
                text: 'Error al anular la factura',
                icon: 'error'
            });
        }
    });

    $('#modalAnular').modal('hide');
});
</script>
@stop
