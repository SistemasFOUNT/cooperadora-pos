@extends('adminlte::page')

@section('title', __('Puntos de Venta'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-store"></i> {{ __('Puntos de Venta') }}</h1>
        <button class="btn btn-info btn-sm" onclick="verEstadisticas()">
            <i class="fas fa-chart-bar"></i> {{ __('Estadísticas') }}
        </button>
    </div>
@stop

@section('content')
    {{-- Información del usuario actual --}}
    @if(auth()->user()->isAdmin())
        <div class="alert alert-info">
            <h5><i class="fas fa-crown"></i> Modo Administrador</h5>
            <p>Puedes ver y gestionar todos los puntos de venta del sistema.</p>
        </div>
    @else
        <div class="alert alert-warning">
            <h5><i class="fas fa-store"></i> Punto de Venta Asignado: {{ auth()->user()->puntoVenta->nombre }}</h5>
            <p>Tienes acceso solo a tu punto de venta asignado.</p>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Configuración de Puntos de Venta') }}</h3>
                </div>
                <div class="card-body">
                    @if($puntosVenta->isEmpty())
                        <div class="alert alert-warning text-center">
                            <h5><i class="fas fa-exclamation-triangle"></i> Sin acceso</h5>
                            <p>No tienes acceso a ningún punto de venta o no hay puntos de venta configurados.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($puntosVenta as $punto)
                                <div class="col-md-4 mb-4">
                                    <div class="card card-outline card-{{ $punto->activo ? 'success' : 'danger' }}">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-{{ $punto->codigo == 'BOX' ? 'box' : ($punto->codigo == 'POSTGRADO' ? 'graduation-cap' : 'tooth') }}"></i>
                                                {{ $punto->nombre }}
                                            </h3>
                                            <div class="card-tools">
                                                <span class="badge badge-{{ $punto->activo ? 'success' : 'danger' }}">
                                                    {{ $punto->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                        <p class="text-muted">{{ $punto->descripcion }}</p>

                                        <h6><i class="fas fa-link"></i> Cuentas Asociadas:</h6>

                                        @if($punto->cuentaCaja)
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-cash-register"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Caja</span>
                                                    <span class="info-box-number">{{ $punto->cuentaCaja->codigo }}</span>
                                                    <small>{{ $punto->cuentaCaja->nombre }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        @if($punto->cuentaVentas)
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-success"><i class="fas fa-shopping-cart"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Ventas</span>
                                                    <span class="info-box-number">{{ $punto->cuentaVentas->codigo }}</span>
                                                    <small>{{ $punto->cuentaVentas->nombre }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        @if($punto->cuentaDeudores)
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Deudores</span>
                                                    <span class="info-box-number">{{ $punto->cuentaDeudores->codigo }}</span>
                                                    <small>{{ $punto->cuentaDeudores->nombre }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        @if($punto->cuentaFondoFijo)
                                            <div class="info-box mb-2">
                                                <span class="info-box-icon bg-info"><i class="fas fa-piggy-bank"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Fondo Fijo</span>
                                                    <span class="info-box-number">{{ $punto->cuentaFondoFijo->codigo }}</span>
                                                    <small>{{ $punto->cuentaFondoFijo->nombre }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-primary" onclick="simularVenta({{ $punto->id }})">
                                                <i class="fas fa-play"></i> Simular Venta
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="verDetalle({{ $punto->id }})">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Información adicional -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> {{ __('Información del Sistema') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success"><i class="fas fa-check"></i></span>
                                <h5 class="description-header">{{ $puntosVenta->where('activo', true)->count() }}</h5>
                                <span class="description-text">PUNTOS ACTIVOS</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-primary"><i class="fas fa-link"></i></span>
                                <h5 class="description-header">{{ $puntosVenta->whereNotNull('cuenta_caja_id')->count() }}</h5>
                                <span class="description-text">CUENTAS CONFIGURADAS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> {{ __('Integración Contable') }}</h3>
                </div>
                <div class="card-body">
                    <p>Cada punto de venta está vinculado a cuentas contables específicas:</p>
                    <ul>
                        <li><strong>Caja:</strong> Donde se registran los ingresos</li>
                        <li><strong>Ventas:</strong> Cuenta de ingresos por servicios</li>
                        <li><strong>Deudores:</strong> Clientes con pagos pendientes</li>
                        <li><strong>Fondo Fijo:</strong> Dinero disponible para gastos menores</li>
                    </ul>
                    <p class="text-info">
                        <i class="fas fa-info-circle"></i>
                        Las ventas generan automáticamente asientos contables en las cuentas correspondientes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para simulación de ventas -->
    <div class="modal fade" id="modalSimulacion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Simulación de Asiento Contable</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="contenidoSimulacion">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function simularVenta(puntoVentaId) {
    fetch(`/contabilidad/puntos-venta/${puntoVentaId}/asiento-demo`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-calculator"></i> Simulación de Venta - ${data.punto_venta}</h6>
                    <p>Ejemplo de asiento contable para una venta de $500</p>
                </div>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cuenta</th>
                            <th>Concepto</th>
                            <th class="text-right">Debe</th>
                            <th class="text-right">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.movimientos.forEach(mov => {
                html += `
                    <tr>
                        <td>Cuenta ID: ${mov.cuenta_id}</td>
                        <td>${mov.concepto}</td>
                        <td class="text-right">${mov.debe > 0 ? '$' + mov.debe.toFixed(2) : ''}</td>
                        <td class="text-right">${mov.haber > 0 ? '$' + mov.haber.toFixed(2) : ''}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
                <div class="alert alert-success">
                    <strong>Resultado:</strong> Debe = Haber = $500.00 ✓
                </div>
            `;

            document.getElementById('contenidoSimulacion').innerHTML = html;
            $('#modalSimulacion').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al simular la venta');
        });
}

function verEstadisticas() {
    fetch('/contabilidad/puntos-venta/estadisticas')
        .then(response => response.json())
        .then(data => {
            toastr.success(`Total: ${data.total_puntos} | Activos: ${data.puntos_activos} | Configurados: ${data.cuentas_configuradas}`);
        });
}

function verDetalle(puntoVentaId) {
    window.location.href = `/contabilidad/puntos-venta/${puntoVentaId}`;
}
</script>
@stop

@section('css')
<style>
.info-box {
    min-height: 70px;
}
.info-box-number {
    font-size: 12px;
    font-weight: bold;
}
.description-block {
    text-align: center;
}
</style>
@stop
