@extends('adminlte::page')

@section('title', 'BOX - Cobros de Cuotas Estudiantiles')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate text-primary"></i> Cobros de Cuotas Estudiantiles</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">Cuotas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Buscar Estudiante</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>DNI o Nombre del Estudiante</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Ingrese DNI o nombre...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Datos del Estudiante</h3>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-search fa-3x"></i>
                        <p class="mt-2">Busque un estudiante para ver sus datos</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Cuotas Adeudadas</h3>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-invoice-dollar fa-3x"></i>
                        <p class="mt-2">Seleccione un estudiante para ver las cuotas adeudadas</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <strong>Total a Pagar: $0.00</strong>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success btn-sm" disabled>
                                <i class="fas fa-credit-card"></i> Cobrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Métodos de Pago</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodoPago" value="efectivo" checked>
                                <label class="form-check-label">
                                    <i class="fas fa-money-bill-wave"></i> Efectivo
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodoPago" value="tarjeta">
                                <label class="form-check-label">
                                    <i class="fas fa-credit-card"></i> Tarjeta
                                </label>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodoPago" value="transferencia">
                                <label class="form-check-label">
                                    <i class="fas fa-exchange-alt"></i> Transferencia
                                </label>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodoPago" value="cheque">
                                <label class="form-check-label">
                                    <i class="fas fa-money-check"></i> Cheque
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
