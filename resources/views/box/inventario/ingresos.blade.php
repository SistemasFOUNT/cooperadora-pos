@extends('adminlte::page')

@section('title', 'BOX - Ingreso de Productos')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-truck-loading text-primary"></i> Ingreso de Productos</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Inventario</a></li>
                <li class="breadcrumb-item active">Ingresos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Registro de Ingreso</h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control">
                                        <option>Seleccionar proveedor...</option>
                                        <option>Proveedor A</option>
                                        <option>Proveedor B</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nº de Factura/Remito</label>
                                    <input type="text" class="form-control" placeholder="Ingrese número...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Ingreso</label>
                                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Responsable</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4>Productos</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="productosTable">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control">
                                                <option>Seleccionar producto...</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control" min="1"></td>
                                        <td><input type="number" class="form-control" step="0.01"></td>
                                        <td>$0.00</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <h4>Total: $0.00</h4>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Ingreso
                    </button>
                    <button type="button" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Información</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Instrucciones</h4>
                        <ul class="mb-0">
                            <li>Verifique los datos del proveedor</li>
                            <li>Ingrese el número de factura/remito</li>
                            <li>Agregue todos los productos recibidos</li>
                            <li>Verifique cantidades y precios</li>
                            <li>Guarde el registro para actualizar el stock</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
