@extends('adminlte::page')

@section('title', 'Libro Caja')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Libro Caja</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                <li class="breadcrumb-item active">Libro Caja</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Movimientos de Efectivo</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <div class="form-group mr-2">
                        <label for="punto_venta_id" class="mr-2">Punto Venta:</label>
                        <select name="punto_venta_id" id="punto_venta_id" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($puntos as $punto)
                                <option value="{{ $punto->id }}" @if(request('punto_venta_id') == $punto->id) selected @endif>
                                    {{ $punto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="desde" class="mr-2">Desde:</label>
                        <input type="date" name="desde" id="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="hasta" class="mr-2">Hasta:</label>
                        <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <button type="submit" class="btn btn-info">Filtrar</button>
                    <a href="{{ route('contable.libro-caja') }}" class="btn btn-secondary ml-2">Limpiar</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Asiento</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th style="text-align: center;">Tipo</th>
                                <th style="text-align: right;">Monto</th>
                                <th style="text-align: right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($libroCaja['movimientos'] as $mov)
                                <tr class="@if($mov['tipo'] == 'INGRESO') table-success @else table-danger @endif">
                                    <td>{{ $mov['fecha']->format('d/m/Y') }}</td>
                                    <td><span class="badge badge-info">{{ $mov['numero_asiento'] }}</span></td>
                                    <td>{{ $mov['concepto'] }}</td>
                                    <td><small>{{ $mov['referencia'] }}</small></td>
                                    <td style="text-align: center;">
                                        <span class="badge @if($mov['tipo'] == 'INGRESO') badge-success @else badge-danger @endif">
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay movimientos en el período seleccionado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="6" style="text-align: right;">SALDO FINAL CAJA:</th>
                                <th style="text-align: right;">
                                    <h5 class="text-white">$ {{ number_format($libroCaja['saldo_final'], 2) }}</h5>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Total de movimientos:</strong> {{ $libroCaja['movimientos_totales'] }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
