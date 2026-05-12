@extends('adminlte::page')

@section('title', 'Arqueos - {{ $nombre_caja }}')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>
                <i class="fas fa-cash-register mr-2"></i>
                Arqueos de Caja — {{ $nombre_caja }}
            </h1>
            <p class="text-muted">Historial de arqueos registrados</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.arqueo.crear', strtolower($codigo)) }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Nuevo Arqueo
            </a>
            <a href="{{ route('admin.arqueo.index') }}" class="btn btn-outline-secondary ml-1">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Resumen --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $resumen['total_arqueos'] }}</h3>
                    <p>Total arqueos</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $resumen['arqueos_con_diff'] }}</h3>
                    <p>Arqueos con diferencia</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box {{ $resumen['suma_diferencias'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner">
                    <h3>${{ number_format(abs($resumen['suma_diferencias']), 2, ',', '.') }}</h3>
                    <p>{{ $resumen['suma_diferencias'] >= 0 ? 'Sobrante acumulado' : 'Faltante acumulado' }}</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabla de arqueos --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1"></i>Historial de Arqueos</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Período</th>
                        <th>Realizado por</th>
                        <th class="text-right">Total Sistema</th>
                        <th class="text-right">Total Declarado</th>
                        <th class="text-right">Diferencia</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $arqueo)
                        <tr>
                            <td>{{ $arqueo->id }}</td>
                            <td>{{ $arqueo->fecha_arqueo->format('d/m/Y H:i') }}</td>
                            <td class="small">
                                {{ $arqueo->periodo_desde ? $arqueo->periodo_desde->format('d/m/Y H:i') : '—' }}
                                →
                                {{ $arqueo->periodo_hasta ? $arqueo->periodo_hasta->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td>{{ $arqueo->user->name ?? '—' }}</td>
                            <td class="text-right">${{ number_format($arqueo->total_calculado, 2, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($arqueo->total_declarado, 2, ',', '.') }}</td>
                            <td class="text-right {{ $arqueo->claseDiferencia() }}">
                                <strong>
                                    {{ $arqueo->diferencia > 0 ? '+' : '' }}${{ number_format($arqueo->diferencia, 2, ',', '.') }}
                                </strong>
                                <br><small>{{ $arqueo->textoDiferencia() }}</small>
                            </td>
                            <td>
                                @if($arqueo->estado === 'cerrado')
                                    <span class="badge badge-secondary">Cerrado</span>
                                @else
                                    <span class="badge badge-warning">Abierto</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.arqueo.show', [strtolower($codigo), $arqueo->id]) }}"
                                   class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox mr-1"></i>No hay arqueos registrados para esta caja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($arqueos->hasPages())
            <div class="card-footer">
                {{ $arqueos->links() }}
            </div>
        @endif
    </div>
@stop
