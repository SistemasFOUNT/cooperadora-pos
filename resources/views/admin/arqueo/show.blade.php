@extends('adminlte::page')

@section('title', 'Detalle Arqueo #{{ $arqueo->id }}')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>
                <i class="fas fa-cash-register mr-2"></i>
                Arqueo #{{ $arqueo->id }} — {{ $nombre_caja }}
            </h1>
            <p class="text-muted">
                Realizado el {{ $arqueo->fecha_arqueo->format('d/m/Y H:i') }}
                por <strong>{{ $arqueo->user->name ?? '—' }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-right">
            @if($arqueo->estaAbierto())
                <form action="{{ route('admin.arqueo.cerrar', [strtolower($codigo), $arqueo->id]) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('¿Confirmás cerrar este arqueo? No se podrá modificar.')">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-lock mr-1"></i>Cerrar Arqueo
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.arqueo.caja', strtolower($codigo)) }}" class="btn btn-outline-secondary ml-1">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(!empty($metodos_con_diferencia))
        <div class="alert alert-warning">
            <i class="fas fa-search-dollar mr-1"></i>
            Se detectaron diferencias en estos métodos de pago:
            <strong>
                @foreach($metodos_con_diferencia as $metodo => $valor)
                    {{ $loop->first ? '' : ' | ' }}{{ ucfirst($metodo) }} ({{ $valor > 0 ? '+' : '' }}${{ number_format($valor, 2, ',', '.') }})
                @endforeach
            </strong>
        </div>
    @endif

    <div class="row">
        {{-- Resumen del arqueo --}}
        <div class="col-md-5">
            <div class="card card-outline {{ $arqueo->estado === 'cerrado' ? 'card-secondary' : 'card-warning' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-check mr-1"></i>Resumen del Arqueo
                    </h3>
                    <div class="card-tools">
                        @if($arqueo->estado === 'cerrado')
                            <span class="badge badge-secondary"><i class="fas fa-lock mr-1"></i>Cerrado</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-unlock mr-1"></i>Abierto</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    {{-- Período --}}
                    <div class="mb-3">
                        <div class="text-muted small">Período</div>
                        <div>
                            {{ $arqueo->periodo_desde ? $arqueo->periodo_desde->format('d/m/Y H:i') : '—' }}
                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                            {{ $arqueo->periodo_hasta ? $arqueo->periodo_hasta->format('d/m/Y H:i') : '—' }}
                        </div>
                        <div class="text-muted small">{{ $arqueo->cantidad_transacciones }} transacciones</div>
                    </div>

                    {{-- Tabla comparativa --}}
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Método</th>
                                <th class="text-right">Sistema</th>
                                <th class="text-right">Declarado</th>
                                <th class="text-right">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-money-bill-wave text-success mr-1"></i>Efectivo</td>
                                <td class="text-right">${{ number_format($arqueo->total_efectivo_calculado, 2, ',', '.') }}</td>
                                <td class="text-right">${{ number_format($arqueo->total_efectivo_declarado, 2, ',', '.') }}</td>
                                @php $difEfectivo = $diferencias_por_metodo['efectivo'] ?? 0; @endphp
                                <td class="text-right {{ $difEfectivo > 0 ? 'text-success' : ($difEfectivo < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $difEfectivo > 0 ? '+' : '' }}${{ number_format($difEfectivo, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-credit-card text-primary mr-1"></i>Tarjeta</td>
                                <td class="text-right">${{ number_format($arqueo->total_tarjeta_calculado, 2, ',', '.') }}</td>
                                <td class="text-right">${{ number_format($arqueo->total_tarjeta_declarado, 2, ',', '.') }}</td>
                                @php $difTarjeta = $diferencias_por_metodo['tarjeta'] ?? 0; @endphp
                                <td class="text-right {{ $difTarjeta > 0 ? 'text-success' : ($difTarjeta < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $difTarjeta > 0 ? '+' : '' }}${{ number_format($difTarjeta, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-university text-warning mr-1"></i>Transferencia</td>
                                <td class="text-right">${{ number_format($arqueo->total_transferencia_calculado, 2, ',', '.') }}</td>
                                <td class="text-right">${{ number_format($arqueo->total_transferencia_declarado, 2, ',', '.') }}</td>
                                @php $difTransferencia = $diferencias_por_metodo['transferencia'] ?? 0; @endphp
                                <td class="text-right {{ $difTransferencia > 0 ? 'text-success' : ($difTransferencia < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $difTransferencia > 0 ? '+' : '' }}${{ number_format($difTransferencia, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>TOTAL</td>
                                <td class="text-right text-info">${{ number_format($arqueo->total_calculado, 2, ',', '.') }}</td>
                                <td class="text-right text-success">${{ number_format($arqueo->total_declarado, 2, ',', '.') }}</td>
                                <td class="text-right {{ $arqueo->diferencia > 0 ? 'text-success' : ($arqueo->diferencia < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $arqueo->diferencia > 0 ? '+' : '' }}${{ number_format($arqueo->diferencia, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Diferencia --}}
                    <div class="text-center py-2 {{ $arqueo->diferencia != 0 ? ($arqueo->diferencia > 0 ? 'bg-light-green' : 'bg-light-red') : '' }}">
                        <div class="text-muted small">Diferencia (Declarado − Sistema)</div>
                        <div class="h3 font-weight-bold {{ $arqueo->claseDiferencia() }}">
                            {{ $arqueo->diferencia > 0 ? '+' : '' }}${{ number_format($arqueo->diferencia, 2, ',', '.') }}
                        </div>
                        <div class="badge {{ $arqueo->diferencia > 0 ? 'badge-success' : ($arqueo->diferencia < 0 ? 'badge-danger' : 'badge-secondary') }}">
                            {{ $arqueo->textoDiferencia() }}
                        </div>
                    </div>

                    @if($arqueo->observaciones)
                        <hr>
                        <div class="text-muted small">Observaciones:</div>
                        <p class="mb-0">{{ $arqueo->observaciones }}</p>
                    @endif

                    @if($arqueo->cerrado_at)
                        <hr>
                        <div class="text-muted small">
                            <i class="fas fa-lock mr-1"></i>Cerrado el {{ $arqueo->cerrado_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Transacciones del período --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        Transacciones del Período ({{ $ventas->count() }})
                    </h3>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Operador</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $venta)
                                <tr>
                                    <td class="small">{{ optional($venta->fecha_venta ?? $venta->created_at)->format('d/m H:i') }}</td>
                                    <td class="small">{{ $venta->user->name ?? '—' }}</td>
                                    <td class="small">{{ $venta->type ?? '—' }}</td>
                                    <td class="small">
                                        @php
                                            $metodo = $venta->additional_data['metodo_pago'] ?? null;
                                            if (!$metodo) {
                                                $metodo = match((int) ($venta->payment_method_id ?? 0)) {
                                                    2 => 'tarjeta',
                                                    3 => 'transferencia',
                                                    default => 'efectivo',
                                                };
                                            }
                                        @endphp
                                        {{ ucfirst($metodo) }}
                                    </td>
                                    <td class="text-right small">${{ number_format($venta->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Sin transacciones en el período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
