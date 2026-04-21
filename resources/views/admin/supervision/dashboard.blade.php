@extends('adminlte::page')

@section('title', 'Supervisión General Admin')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line mr-2"></i> Supervisión General</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard Admin</a></li>
                <li class="breadcrumb-item active">Supervisión General</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Resumen financiero de todos los puntos -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> BOX Cooperadora</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> ${{ number_format($box_data['ingresos_hoy'] ?? 0, 2) }}</p>
                    <p><strong>Transacciones:</strong> {{ $box_data['transacciones'] ?? 0 }}</p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $box_data['activo'] ? 'success' : 'warning' }}">
                            {{ $box_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <a href="{{ route('admin.supervision.box') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye mr-1"></i> Supervisar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Postgrado</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> ${{ number_format($postgrado_data['ingresos_hoy'] ?? 0, 2) }}</p>
                    <p><strong>Estudiantes:</strong> {{ $postgrado_data['estudiantes_activos'] ?? 0 }}</p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $postgrado_data['activo'] ? 'success' : 'warning' }}">
                            {{ $postgrado_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <a href="{{ route('admin.supervision.postgrado') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye mr-1"></i> Supervisar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tooth mr-1"></i> Centro Odontológico</h3>
                </div>
                <div class="card-body">
                    <p><strong>Ingresos Hoy:</strong> ${{ number_format($odonto_data['ingresos_hoy'] ?? 0, 2) }}</p>
                    <p><strong>Pacientes:</strong> {{ $odonto_data['pacientes_activos'] ?? 0 }}</p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $odonto_data['activo'] ? 'success' : 'warning' }}">
                            {{ $odonto_data['activo'] ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <a href="{{ route('admin.supervision.odonto') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-eye mr-1"></i> Supervisar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de estadísticas consolidadas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i> Consolidado Financiero Diario</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Punto de Venta</th>
                                <th>Ingresos</th>
                                <th>Transacciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BOX Cooperadora</td>
                                <td>${{ number_format($box_data['ingresos_hoy'] ?? 0, 2) }}</td>
                                <td>{{ $box_data['transacciones'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>Postgrado</td>
                                <td>${{ number_format($postgrado_data['ingresos_hoy'] ?? 0, 2) }}</td>
                                <td>{{ $postgrado_data['transacciones'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>Centro Odontológico</td>
                                <td>${{ number_format($odonto_data['ingresos_hoy'] ?? 0, 2) }}</td>
                                <td>{{ $odonto_data['transacciones'] ?? 0 }}</td>
                            </tr>
                            <tr class="bg-light font-weight-bold">
                                <td>TOTAL</td>
                                <td>${{ number_format(($box_data['ingresos_hoy'] ?? 0) + ($postgrado_data['ingresos_hoy'] ?? 0) + ($odonto_data['ingresos_hoy'] ?? 0), 2) }}</td>
                                <td>{{ ($box_data['transacciones'] ?? 0) + ($postgrado_data['transacciones'] ?? 0) + ($odonto_data['transacciones'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i> Alertas y Pendientes</h3>
                </div>
                <div class="card-body">
                    @if(count($alertas) > 0)
                        @foreach($alertas as $alerta)
                            <div class="alert alert-{{ $alerta['tipo'] }} alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>{{ $alerta['punto_venta'] }}:</strong> {{ $alerta['mensaje'] }}
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i> No hay alertas pendientes
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas administrativas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tools mr-2"></i> Acciones Rápidas Administrativas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.autorizaciones.index') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-key mr-2"></i>
                                Autorizaciones Pendientes
                                <span class="badge badge-light">{{ $pendientes_autorizacion ?? 0 }}</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.libro-caja.consolidado') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-book mr-2"></i>
                                Libro Caja Consolidado
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.cuentas.estado-general') }}" class="btn btn-info btn-block">
                                <i class="fas fa-calculator mr-2"></i>
                                Estado de Cuentas General
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.reportes.consolidado') }}" class="btn btn-success btn-block">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Reportes Consolidados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .alert {
            margin-bottom: 10px;
        }
    </style>
@stop

@section('menu')
    <!-- Sidebar específico para admin -->
    <li class="nav-header">ADMINISTRACIÓN</li>

    <li class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard Principal</p>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.supervision.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.supervision.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-eye"></i>
            <p>
                Supervisión
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.supervision.general') }}" class="nav-link {{ request()->routeIs('admin.supervision.general') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>General</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.supervision.box') }}" class="nav-link {{ request()->routeIs('admin.supervision.box') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>BOX Cooperadora</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.supervision.postgrado') }}" class="nav-link {{ request()->routeIs('admin.supervision.postgrado') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Postgrado</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.supervision.odonto') }}" class="nav-link {{ request()->routeIs('admin.supervision.odonto') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Centro Odontológico</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.ingresos-egresos.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.ingresos-egresos.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>
                Ingresos y Egresos
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.ingresos-egresos.consolidado') }}" class="nav-link {{ request()->routeIs('admin.ingresos-egresos.consolidado') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Consolidado</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.ingresos-egresos.box') }}" class="nav-link {{ request()->routeIs('admin.ingresos-egresos.box') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>BOX Cooperadora</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.ingresos-egresos.postgrado') }}" class="nav-link {{ request()->routeIs('admin.ingresos-egresos.postgrado') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Postgrado</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.ingresos-egresos.odonto') }}" class="nav-link {{ request()->routeIs('admin.ingresos-egresos.odonto') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Centro Odontológico</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.libro-caja.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.libro-caja.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>
                Libro Caja
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.libro-caja.consolidado') }}" class="nav-link {{ request()->routeIs('admin.libro-caja.consolidado') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Consolidado</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.libro-caja.box') }}" class="nav-link {{ request()->routeIs('admin.libro-caja.box') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>BOX Cooperadora</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.libro-caja.postgrado') }}" class="nav-link {{ request()->routeIs('admin.libro-caja.postgrado') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Postgrado</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.libro-caja.odonto') }}" class="nav-link {{ request()->routeIs('admin.libro-caja.odonto') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Centro Odontológico</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.autorizaciones.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.autorizaciones.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-key"></i>
            <p>
                Autorizaciones
                <i class="right fas fa-angle-left"></i>
                @if($pendientes_autorizacion ?? 0 > 0)
                    <span class="badge badge-danger right">{{ $pendientes_autorizacion }}</span>
                @endif
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.autorizaciones.index') }}" class="nav-link {{ request()->routeIs('admin.autorizaciones.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Pendientes</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.autorizaciones.historial') }}" class="nav-link {{ request()->routeIs('admin.autorizaciones.historial') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Historial</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.cuentas.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.cuentas.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calculator"></i>
            <p>
                Estado de Cuentas
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.cuentas.estado-general') }}" class="nav-link {{ request()->routeIs('admin.cuentas.estado-general') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>General</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.cuentas.particular') }}" class="nav-link {{ request()->routeIs('admin.cuentas.particular') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Particular</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-header">REPORTES</li>

    <li class="nav-item">
        <a href="{{ route('admin.reportes.consolidado') }}" class="nav-link {{ request()->routeIs('admin.reportes.consolidado') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reportes Consolidados</p>
        </a>
    </li>
@stop
