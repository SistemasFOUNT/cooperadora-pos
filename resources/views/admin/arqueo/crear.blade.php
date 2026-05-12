@extends('adminlte::page')

@section('title', 'Nuevo Arqueo - {{ $nombre_caja }}')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1><i class="fas fa-plus-circle mr-2"></i>Nuevo Arqueo — {{ $nombre_caja }}</h1>
            <p class="text-muted">Completá los datos del conteo físico de caja</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.arqueo.caja', strtolower($codigo)) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.arqueo.guardar', strtolower($codigo)) }}" method="POST" id="form-arqueo">
        @csrf

        <div class="row">
            {{-- Columna izquierda: Período y declarado --}}
            <div class="col-md-7">
                {{-- Período --}}
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i>Período del Arqueo</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="periodo_desde" id="periodo_desde"
                                           class="form-control @error('periodo_desde') is-invalid @enderror"
                                           value="{{ old('periodo_desde', date('Y-m-d\TH:i', strtotime($periodo_desde))) }}"
                                           required>
                                    @error('periodo_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="periodo_hasta" id="periodo_hasta"
                                           class="form-control @error('periodo_hasta') is-invalid @enderror"
                                           value="{{ old('periodo_hasta', date('Y-m-d\TH:i', strtotime($periodo_hasta))) }}"
                                           required>
                                    @error('periodo_hasta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se calcularon <strong>{{ $cantidad_ventas }}</strong> transacciones en este período.
                            <a href="#" id="btn-recalcular" class="ml-2">
                                <i class="fas fa-sync-alt"></i> Recalcular
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Declarado (conteo físico) --}}
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Conteo Físico Declarado
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-money-bill-wave text-success mr-1"></i>Efectivo declarado
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="total_efectivo_declarado" id="efectivo_declarado"
                                       class="form-control @error('total_efectivo_declarado') is-invalid @enderror"
                                       step="0.01" min="0"
                                       value="{{ old('total_efectivo_declarado', 0) }}"
                                       required>
                                @error('total_efectivo_declarado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-credit-card text-primary mr-1"></i>Tarjeta declarada
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="total_tarjeta_declarado" id="tarjeta_declarada"
                                       class="form-control @error('total_tarjeta_declarado') is-invalid @enderror"
                                       step="0.01" min="0"
                                       value="{{ old('total_tarjeta_declarado', 0) }}"
                                       required>
                                @error('total_tarjeta_declarado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-university text-warning mr-1"></i>Transferencia declarada
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="total_transferencia_declarado" id="transferencia_declarada"
                                       class="form-control @error('total_transferencia_declarado') is-invalid @enderror"
                                       step="0.01" min="0"
                                       value="{{ old('total_transferencia_declarado', 0) }}"
                                       required>
                                @error('total_transferencia_declarado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Declarado:</strong>
                            <span class="h5 mb-0 text-success" id="total_declarado_display">$0,00</span>
                        </div>

                        <div class="form-group mt-3">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3"
                                      placeholder="Comentarios sobre el arqueo...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: Totales del sistema --}}
            <div class="col-md-5">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator mr-1"></i>Totales según el Sistema
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">Calculado automáticamente</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><i class="fas fa-money-bill-wave text-success mr-1"></i>Efectivo</td>
                                <td class="text-right font-weight-bold">
                                    ${{ number_format($totales_calculados['efectivo'], 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-credit-card text-primary mr-1"></i>Tarjeta</td>
                                <td class="text-right font-weight-bold">
                                    ${{ number_format($totales_calculados['tarjeta'], 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-university text-warning mr-1"></i>Transferencia</td>
                                <td class="text-right font-weight-bold">
                                    ${{ number_format($totales_calculados['transferencia'], 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Total Sistema</strong></td>
                                <td class="text-right">
                                    <strong class="text-info">
                                        ${{ number_format($totales_calculados['total'], 2, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <hr>
                        <div class="text-center">
                            <div class="text-muted small mb-1">Diferencia (Declarado − Sistema)</div>
                            <div id="diferencia_display" class="h4 font-weight-bold text-muted">$0,00</div>
                            <div id="diferencia_texto" class="small text-muted">—</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-save mr-2"></i>Guardar Arqueo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
<script>
    const sistemaPorMetodo = {
        efectivo: {{ $totales_calculados['efectivo'] }},
        tarjeta: {{ $totales_calculados['tarjeta'] }},
        transferencia: {{ $totales_calculados['transferencia'] }},
    };
    const totalSistema = {{ $totales_calculados['total'] }};

    function formatearMonto(monto) {
        return '$' + monto.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function actualizarResumen() {
        const efectivo      = parseFloat(document.getElementById('efectivo_declarado').value)      || 0;
        const tarjeta       = parseFloat(document.getElementById('tarjeta_declarada').value)       || 0;
        const transferencia = parseFloat(document.getElementById('transferencia_declarada').value)  || 0;
        const total         = efectivo + tarjeta + transferencia;
        const diferencia    = total - totalSistema;
        const difEfectivo   = efectivo - sistemaPorMetodo.efectivo;
        const difTarjeta    = tarjeta - sistemaPorMetodo.tarjeta;
        const difTransfer   = transferencia - sistemaPorMetodo.transferencia;

        document.getElementById('total_declarado_display').textContent = formatearMonto(total);

        const difEl    = document.getElementById('diferencia_display');
        const textoEl  = document.getElementById('diferencia_texto');

        difEl.textContent = (diferencia > 0 ? '+' : '') + formatearMonto(diferencia);
        difEl.className   = 'h4 font-weight-bold ' +
            (diferencia > 0 ? 'text-success' : diferencia < 0 ? 'text-danger' : 'text-muted');

        textoEl.textContent = diferencia > 0 ? 'Sobrante' : diferencia < 0 ? 'Faltante' : 'Exacto';

        // Mostrar diferencia por método para facilitar detección de comprobantes faltantes
        const detalle = [
            { key: 'Efectivo', value: difEfectivo },
            { key: 'Tarjeta', value: difTarjeta },
            { key: 'Transferencia', value: difTransfer },
        ];

        const metodosConDif = detalle.filter(item => Math.abs(item.value) >= 0.01);
        let aviso = document.getElementById('aviso_diferencias_metodo');

        if (!aviso) {
            aviso = document.createElement('div');
            aviso.id = 'aviso_diferencias_metodo';
            aviso.className = 'alert alert-warning mt-3 mb-0';
            document.querySelector('.card.card-outline.card-info .card-body').appendChild(aviso);
        }

        if (metodosConDif.length === 0) {
            aviso.classList.add('d-none');
            aviso.innerHTML = '';
        } else {
            aviso.classList.remove('d-none');
            aviso.innerHTML =
                '<div class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Diferencias por método detectadas</div>' +
                metodosConDif
                    .map(item => '<div class="small">' + item.key + ': ' + (item.value > 0 ? '+' : '') + formatearMonto(item.value) + '</div>')
                    .join('');
        }
    }

    ['efectivo_declarado', 'tarjeta_declarada', 'transferencia_declarada']
        .forEach(id => document.getElementById(id).addEventListener('input', actualizarResumen));

    // Recalcular al cambiar período (recarga la página con los nuevos parámetros)
    document.getElementById('btn-recalcular').addEventListener('click', function(e) {
        e.preventDefault();
        const desde  = document.getElementById('periodo_desde').value;
        const hasta  = document.getElementById('periodo_hasta').value;
        const url    = new URL(window.location.href);
        url.searchParams.set('periodo_desde', desde);
        url.searchParams.set('periodo_hasta', hasta);
        window.location.href = url.toString();
    });

    actualizarResumen();
</script>
@endpush
