@extends('adminlte::page')

@section('title', 'Libro Diario')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Libro Diario</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                <li class="breadcrumb-item active">Libro Diario</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Registro Cronológico de Asientos</h3>
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
                    <a href="{{ route('contable.libro-diario') }}" class="btn btn-secondary ml-2">Limpiar</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Asiento</th>
                                <th>Concepto</th>
                                <th>Punto Venta</th>
                                <th>Usuario</th>
                                <th style="text-align: center;">Debe</th>
                                <th style="text-align: center;">Haber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($libroDiario['asientos'] as $asiento)
                                <tr class="table-light font-weight-bold">
                                    <td>{{ $asiento['fecha']->format('d/m/Y') }}</td>
                                    <td><span class="badge badge-info">{{ $asiento['numero_asiento'] }}</span></td>
                                    <td>{{ $asiento['concepto'] }}</td>
                                    <td>{{ $asiento['punto_venta'] }}</td>
                                    <td>{{ $asiento['usuario'] }}</td>
                                    <td style="text-align: right;">$ {{ number_format($asiento['total_debe'], 2) }}</td>
                                    <td style="text-align: right;">$ {{ number_format($asiento['total_haber'], 2) }}</td>
                                </tr>
                                @foreach($asiento['movimientos'] as $mov)
                                    <tr>
                                        <td colspan="4"></td>
                                        <td style="padding-left: 40px;">
                                            <strong>{{ $mov['cuenta_codigo'] }}</strong> - {{ $mov['cuenta_nombre'] }}
                                            @if($mov['descripcion'])
                                                <br><small class="text-muted">{{ $mov['descripcion'] }}</small>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @if($mov['debe'] > 0)
                                                $ {{ number_format($mov['debe'], 2) }}
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @if($mov['haber'] > 0)
                                                $ {{ number_format($mov['haber'], 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay asientos en el período seleccionado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="5" style="text-align: right;">TOTALES:</th>
                                <th style="text-align: right;">
                                    $ {{ number_format(collect($libroDiario['asientos'])->sum('total_debe'), 2) }}
                                </th>
                                <th style="text-align: right;">
                                    $ {{ number_format(collect($libroDiario['asientos'])->sum('total_haber'), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Total de asientos en período:</strong> {{ $libroDiario['asientos_totales'] }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
