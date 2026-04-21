@extends('adminlte::page')

@section('title', 'Dashboard Administrativo - Sistema Cooperadora')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>Dashboard Administrativo</h1>
            <p class="text-muted">Panel de control general - Acceso completo al sistema</p>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-success badge-lg">Superusuario</span>
        </div>
    </div>
@stop

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('menu')
{{-- Sidebar específico para Admin - NAVEGACIÓN FIJA --}}
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        {{-- Dashboard Principal --}}
        <li class="nav-header">PANEL ADMINISTRATIVO</li>
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard Admin</p>
            </a>
        </li>

        {{-- Supervisión de Puntos de Venta --}}
        <li class="nav-header">SUPERVISIÓN PUNTOS DE VENTA</li>
        <li class="nav-item {{ request()->routeIs('admin.supervision.*') ? 'menu-open' : '' }} has-treeview">
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

        {{-- Control Financiero --}}
        <li class="nav-header">CONTROL FINANCIERO</li>
        <li class="nav-item {{ request()->routeIs('admin.ingresos-egresos.*') ? 'menu-open' : '' }} has-treeview">
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

        <li class="nav-item {{ request()->routeIs('admin.libro-caja.*') ? 'menu-open' : '' }} has-treeview">
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

        {{-- Autorizaciones --}}
        <li class="nav-header">AUTORIZACIONES</li>
        <li class="nav-item {{ request()->routeIs('admin.autorizaciones.*') ? 'menu-open' : '' }} has-treeview">
            <a href="#" class="nav-link {{ request()->routeIs('admin.autorizaciones.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-key"></i>
                <p>
                    Autorizaciones
                    <i class="right fas fa-angle-left"></i>
                    @if(($pendientes_autorizacion ?? 0) > 0)
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

        {{-- Estados de Cuenta --}}
        <li class="nav-header">ESTADOS DE CUENTA</li>
        <li class="nav-item {{ request()->routeIs('admin.cuentas.*') ? 'menu-open' : '' }} has-treeview">
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

        {{-- Reportes --}}
        <li class="nav-header">REPORTES</li>
        <li class="nav-item">
            <a href="{{ route('admin.reportes.consolidado') }}" class="nav-link {{ request()->routeIs('admin.reportes.consolidado') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Reportes Consolidados</p>
            </a>
        </li>
    </ul>
</nav>
                    BOX Cooperadora
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.box.ingresos-egresos') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Ingresos/Egresos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.box.libro-caja') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Libro Caja</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.box.supervision') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Supervisión General</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-graduation-cap"></i>
                <p>
                    Postgrado
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.postgrado.ingresos-egresos') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Ingresos/Egresos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.postgrado.libro-caja') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Libro Caja</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.postgrado.supervision') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Supervisión General</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-tooth"></i>
                <p>
                    Centro Odontológico
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.odonto.ingresos-egresos') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Ingresos/Egresos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.odonto.libro-caja') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Libro Caja</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.odonto.supervision') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Supervisión General</p>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Herramientas Administrativas --}}
        <li class="nav-header">HERRAMIENTAS ADMINISTRATIVAS</li>
        <li class="nav-item">
            <a href="{{ route('admin.cuentas.general') }}" class="nav-link">
                <i class="nav-icon fas fa-calculator"></i>
                <p>Estado de Cuentas</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.libro-caja-general') }}" class="nav-link">
                <i class="nav-icon fas fa-book"></i>
                <p>Libro Caja General</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.autorizaciones-pagos') }}" class="nav-link">
                <i class="nav-icon fas fa-check-circle"></i>
                <p>Autorizaciones de Pago</p>
                @if(isset($pagos_pendientes) && $pagos_pendientes > 0)
                    <span class="badge badge-warning right">{{ $pagos_pendientes }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.estadisticas') }}" class="nav-link">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Estadísticas Generales</p>
            </a>
        </li>

        {{-- Gestión General --}}
        <li class="nav-header">GESTIÓN GENERAL</li>
        <li class="nav-item">
            <a href="{{ route('products.index') }}" class="nav-link">
                <i class="nav-icon fas fa-boxes"></i>
                <p>Gestión Productos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('students.index') }}" class="nav-link">
                <i class="nav-icon fas fa-user-graduate"></i>
                <p>Gestión Estudiantes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.usuarios') }}" class="nav-link">
                <i class="nav-icon fas fa-users-cog"></i>
                <p>Gestión Usuarios</p>
            </a>
        </li>

        {{-- Configuración --}}
        <li class="nav-header">CONFIGURACIÓN</li>
        <li class="nav-item">
            <a href="{{ route('admin.profile') }}" class="nav-link">
                <i class="nav-icon fas fa-user-cog"></i>
                <p>Mi Perfil</p>
            </a>
        </li>
    </ul>
</nav>
@stop

@section('content')
    {{-- Estadísticas Generales --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas_generales['total_productos'] }}</h3>
                    <p>Total Productos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas_generales['total_estudiantes'] }}</h3>
                    <p>Total Estudiantes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas_generales['total_puntos_venta'] }}</h3>
                    <p>Puntos de Venta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas_generales['usuarios_activos'] }}</h3>
                    <p>Usuarios Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Acceso a Puntos de Venta --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        BOX Cooperadora
                    </h3>
                </div>
                <div class="card-body">
                    <p>Ventas de productos del Laboratorio de Insumos, cobro de cuotas y bonos estudiantiles, kits especializados.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('box.dashboard') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-eye"></i> Ver Dashboard
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.box.supervision') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-clipboard-check"></i> Supervisar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Postgrado
                    </h3>
                </div>
                <div class="card-body">
                    <p>Gestión académica y financiera de programas de postgrado, cursos especializados, honorarios docentes.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('postgrado.dashboard') }}" class="btn btn-success btn-block">
                                <i class="fas fa-eye"></i> Ver Dashboard
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.postgrado.supervision') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-clipboard-check"></i> Supervisar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tooth mr-2"></i>
                        Centro Odontológico
                    </h3>
                </div>
                <div class="card-body">
                    <p>Prestaciones clínicas, estudios radiográficos, tarifarios diferenciados, gestión de pacientes.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('odonto.dashboard') }}" class="btn btn-info btn-block">
                                <i class="fas fa-eye"></i> Ver Dashboard
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.odonto.supervision') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-clipboard-check"></i> Supervisar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Herramientas Administrativas --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-2"></i>
                        Herramientas Administrativas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.estadisticas') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                Estadísticas Generales
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('contabilidad.puntos-venta.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-calculator"></i><br>
                                Contabilidad General
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-boxes"></i><br>
                                Gestión Productos
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('students.index') }}" class="btn btn-success btn-block">
                                <i class="fas fa-user-graduate"></i><br>
                                Gestión Estudiantes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Información del Sistema --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información del Sistema
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Rol:</strong> {{ $user->getRoleNameAttribute() }}</p>
                            <p><strong>Último acceso:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                            <p><strong>Entorno:</strong> {{ app()->environment() }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-shield-alt"></i> Acceso de Superusuario</h5>
                                Como administrador, tienes acceso completo a todos los puntos de venta para supervisión, análisis estadístico y control contable general.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .icon {
            font-size: 70px;
        }
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Dashboard Administrativo cargado correctamente');

        // Auto-refresh de estadísticas cada 5 minutos
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
@stop
