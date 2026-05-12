@extends('adminlte::page')

@section('title', 'Libro Banco')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Libro Banco</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                <li class="breadcrumb-item active">Libro Banco</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Movimientos Bancarios</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <div class="form-group mr-2">
                        <label for="desde" class="mr-2">Desde:</label>
                        <input type="date" name="desde" id="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="hasta" class="mr-2">Hasta:</label>
                        <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <button type="submit" class="btn btn-info">Filtrar</button>
                    <a href="{{ route('contable.libro-banco') }}" class="btn btn-secondary ml-2">Limpiar</a>
                </form>

                @forelse($libroBanco['libros'] as $banco)
                    <div class="card mt-3 card-outline card-info">
                        <div class="card-header">
                            <h5 class="card-title">
                                {{ $banco['cuenta_codigo'] }} - {{ $banco['cuenta_nombre'] }}
                                <small class="float-right">Saldo Final: $ {{ number_format($banco['saldo'], 2) }}</small>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Asiento</th>
                                        <th>Concepto</th>
                                        <th style="text-align: center;">Tipo</th>
                                        <th style="text-align: right;">Monto</th>
                                        <th style="text-align: right;">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banco['movimientos'] as $mov)
                                        <tr class="@if($mov['tipo'] == 'DEPÓSITO') table-success @else table-warning @endif">
                                            <td>{{ $mov['fecha']->format('d/m/Y') }}</td>
                                            <td><span class="badge badge-info">{{ $mov['numero_asiento'] }}</span></td>
                                            <td>{{ $mov['concepto'] }}</td>
                                            <td style="text-align: center;">
                                                <span class="badge @if($mov['tipo'] == 'DEPÓSITO') badge-success @else badge-warning @endif">
                                                    {{ $mov['tipo'] }}
                                                </span>
                                            </td>
                                            <td style="text-align: right;">$ {{ number_format($mov['monto'], 2) }}</td>
                                            <td style="text-align: right;">
                                                <strong>$ {{ number_format($mov['saldo_acumulado'], 2) }}</strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-2">
                                                No hay movimientos para esta cuenta
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info mt-3">
                        No hay cuentas bancarias configuradas o sin movimientos
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@stop
