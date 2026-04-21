@extends('adminlte::page')

@section('title', 'BOX Cooperadora - Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-store text-primary"></i> BOX Cooperadora</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">BOX Cooperadora</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Información del usuario --}}
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-user"></i> Bienvenido {{ auth()->user()->name }}</h4>
                <p>Estás operando en: <strong>BOX Cooperadora</strong> | Rol: <strong>{{ auth()->user()->role_name }}</strong></p>
            </div>
        </div>
    </div>

    {{-- Estadísticas principales --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['ventas_hoy'], 0, ',', '.') }}</h3>
                    <p>Ventas Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <a href="{{ route('box.ventas-del-dia') }}" class="small-box-footer">
                    Ver detalle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['ventas_mes'], 0, ',', '.') }}</h3>
                    <p>Ventas del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('box.reportes') }}" class="small-box-footer">
                    Ver reportes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['productos_activos'] }}</h3>
                    <p>Productos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="{{ route('box.productos') }}" class="small-box-footer">
                    Ver productos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['cajeros_activos'] }}</h3>
                    <p>Cajeros Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Personal <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas del BOX --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket"></i> Acciones Rápidas - BOX
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('box.pos') }}" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-cash-register"></i><br>
                                Punto de Venta
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('box.productos') }}" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-box"></i><br>
                                Productos
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('box.ventas-del-dia') }}" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-receipt"></i><br>
                                Ventas del Día
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('box.reportes') }}" class="btn btn-warning btn-lg btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                Reportes
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('box.configuracion') }}" class="btn btn-secondary btn-lg btn-block">
                                <i class="fas fa-cog"></i><br>
                                Configuración
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('contabilidad.puntos-venta.index') }}" class="btn btn-dark btn-lg btn-block">
                                <i class="fas fa-calculator"></i><br>
                                Contabilidad
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
                        <i class="fas fa-info-circle"></i> Información BOX
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Punto de Venta:</strong> BOX Cooperadora</p>
                    <p><strong>Ubicación:</strong> Planta Baja, Facultad</p>
                    <p><strong>Horario:</strong> 08:00 - 18:00 (L-V)</p>
                    <p><strong>Especialidad:</strong> Venta general de materiales odontológicos</p>

                    <hr>

                    <h5><i class="fas fa-tags"></i> Descuentos Disponibles</h5>
                    <ul class="list-unstyled">
                        <li>👨‍🎓 Estudiantes: 10%</li>
                        <li>👩‍🏫 Docentes: 15%</li>
                        <li>👥 Personal: 20%</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .inner h3 {
            font-size: 2.2rem;
        }
        .btn-lg {
            padding: 15px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-info {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
            border: none;
        }
    </style>
@stop

@section('js')
    <script>
        // Actualización automática de estadísticas cada 30 segundos
        setInterval(function() {
            // Aquí se puede agregar lógica para actualizar estadísticas en tiempo real
            console.log('Actualizando estadísticas...');
        }, 30000);

        // Mensaje de bienvenida
        $(document).ready(function() {
            toastr.success('¡Bienvenido al BOX Cooperadora!', 'FOUNT Contable');
        });
    </script>
@stop
