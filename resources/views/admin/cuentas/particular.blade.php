@extends('adminlte::page')

@section('title', 'Estado de Cuenta - Particular')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Estado de Cuenta - Particular</h1>
            <p class="text-muted mb-0">Consulta individual de cuentas y movimientos</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Consulta por cuenta contable</h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="row" method="GET" action="{{ route('admin.cuentas.particular') }}">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cuenta del plan de cuentas</label>
                    <select name="cuenta_id" class="form-control" required>
                        <option value="">Seleccione una cuenta...</option>
                        @foreach($cuentas as $cuenta)
                            <option value="{{ $cuenta->id }}" {{ (string) request('cuenta_id') === (string) $cuenta->id ? 'selected' : '' }}>
                                {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="desde" class="form-control" value="{{ $desde }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" class="form-control" value="{{ $buscar ?? request('buscar') }}" placeholder="Asiento, concepto...">
                </div>
                <div class="col-md-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                    @if(request('cuenta_id'))
                        <a href="{{ route('admin.cuentas.particular.pdf', request()->query()) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <a href="{{ route('admin.cuentas.particular.excel', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($cuentaSeleccionada && $resumen)
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h4>${{ number_format($resumen['saldo_inicial'], 2) }}</h4>
                        <p>Saldo Inicial</p>
                    </div>
                    <div class="icon"><i class="fas fa-flag"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4>${{ number_format($resumen['total_debe'], 2) }}</h4>
                        <p>Total Debe</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h4>${{ number_format($resumen['total_haber'], 2) }}</h4>
                        <p>Total Haber</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box {{ $resumen['saldo_actual'] >= 0 ? 'bg-info' : 'bg-warning' }}">
                    <div class="inner">
                        <h4>${{ number_format($resumen['saldo_actual'], 2) }}</h4>
                        <p>Saldo Actual</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    {{ $cuentaSeleccionada->codigo }} - {{ $cuentaSeleccionada->nombre }}
                    <small class="text-muted ml-2">({{ ucfirst($cuentaSeleccionada->naturaleza) }})</small>
                </h3>
                <span class="badge badge-secondary">{{ $resumen['movimientos'] }} movimientos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Asiento</th>
                                <th>Punto de venta</th>
                                <th>Descripción</th>
                                <th class="text-right">Debe</th>
                                <th class="text-right">Haber</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimientos as $mov)
                                <tr>
                                    <td>{{ optional($mov->asiento->fecha_asiento)->format('d/m/Y') }}</td>
                                    <td>{{ $mov->asiento->numero_asiento ?? '-' }}</td>
                                    <td>General</td>
                                    <td>{{ $mov->descripcion ?: '-' }}</td>
                                    <td class="text-right">${{ number_format($mov->debe, 2) }}</td>
                                    <td class="text-right">${{ number_format($mov->haber, 2) }}</td>
                                    <td class="text-right font-weight-bold">${{ number_format($mov->saldo_acumulado, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay movimientos para esta cuenta en el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($movimientos, 'links'))
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $movimientos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    @endif
@stop
