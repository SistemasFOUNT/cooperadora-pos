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
