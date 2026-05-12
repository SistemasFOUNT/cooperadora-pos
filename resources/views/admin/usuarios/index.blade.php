@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Gestión de Usuarios</h1>
            <p class="text-muted mb-0">Administración centralizada de accesos y roles</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h5>Alta de usuarios</h5>
                    <p class="text-muted mb-0">Preparado para crear nuevos accesos administrativos.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-3x text-success mb-3"></i>
                    <h5>Roles y permisos</h5>
                    <p class="text-muted mb-0">Control de privilegios por punto de venta.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-user-cog fa-3x text-warning mb-3"></i>
                    <h5>Estado de cuentas</h5>
                    <p class="text-muted mb-0">Seguimiento de usuarios activos e inactivos.</p>
                </div>
            </div>
        </div>
    </div>
@stop
