@extends('adminlte::page')

@section('title', 'Resumen de Caja')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Resumen de Caja</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                <li class="breadcrumb-item active">Resumen de Caja</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Estado de Cajas por Período</h3>
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
                    <a href="{{ route('contable.resumen-caja') }}" class="btn btn-secondary ml-2">Limpiar</a>
                </form>

                <div class="row">
                    @if($resumen['saldo_total'] >= 0)
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>$ {{ number_format($resumen['saldo_total'], 2) }}</h3>
                                    <p>Saldo Total Disponible</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-4">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>$ {{ number_format($resumen['saldo_total'], 2) }}</h3>
                                    <p>Saldo en Rojo</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>$ {{ number_format($resumen['total_ingresos'], 2) }}</h3>
                                <p>Total de Ingresos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>$ {{ number_format($resumen['total_egresos'], 2) }}</h3>
                                <p>Total de Egresos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Caja</th>
                                <th style="text-align: right;">Ingresos</th>
                                <th style="text-align: right;">Egresos</th>
                                <th style="text-align: right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumen['resumen_cajas'] as $caja)
                                <tr>
                                    <td><strong>{{ $caja['codigo'] }}</strong></td>
                                    <td>{{ $caja['nombre'] }}</td>
                                    <td style="text-align: right;" class="text-success">
                                        $ {{ number_format($caja['ingresos'], 2) }}
                                    </td>
                                    <td style="text-align: right;" class="text-danger">
                                        $ {{ number_format($caja['egresos'], 2) }}
                                    </td>
                                    <td style="text-align: right;">
                                        <strong @if($caja['saldo'] < 0) class="text-danger" @else class="text-success" @endif>
                                            $ {{ number_format($caja['saldo'], 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hay cajas configuradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="2">TOTALES</th>
                                <th style="text-align: right;">
                                    $ {{ number_format($resumen['total_ingresos'], 2) }}
                                </th>
                                <th style="text-align: right;">
                                    $ {{ number_format($resumen['total_egresos'], 2) }}
                                </th>
                                <th style="text-align: right;">
                                    <h5 class="text-white mb-0">
                                        $ {{ number_format($resumen['saldo_total'], 2) }}
                                    </h5>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
