@extends('adminlte::page')

@section('title', 'BOX - Pagos de Asignaciones')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-warning"></i> Pagos de Asignaciones</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Pagos</a></li>
                <li class="breadcrumb-item active">Asignaciones</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-check-alt"></i> Nueva Asignacion</h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Beneficiario</label>
                                    <input type="text" class="form-control" placeholder="Nombre y apellido">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Documento</label>
                                    <input type="text" class="form-control" placeholder="DNI / CUIT">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Periodo</label>
                                    <input type="month" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha de pago</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Importe</label>
                                    <input type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Concepto</label>
                            <textarea class="form-control" rows="3" placeholder="Detalle de la asignacion..."></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <button type="button" class="btn btn-warning">
                                <i class="fas fa-save"></i> Guardar Asignacion
                            </button>
                            <button type="button" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Checklist</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-secondary mb-0">
                        <ul class="mb-0 pl-3">
                            <li>Verifique identidad del beneficiario.</li>
                            <li>Controle periodo e importe.</li>
                            <li>Adjunte documentacion de respaldo.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
