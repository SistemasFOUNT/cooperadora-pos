@extends('adminlte::page')

@section('title', 'Secretaría Postgrado - Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap text-success"></i> Secretaría Postgrado</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Postgrado</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Información del usuario --}}
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                <h4><i class="fas fa-user-graduate"></i> Bienvenido {{ auth()->user()->name }}</h4>
                <p>Estás operando en: <strong>Secretaría de Postgrado</strong> | Rol: <strong>{{ auth()->user()->role_name }}</strong></p>
            </div>
        </div>
    </div>

    {{-- Estadísticas principales --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['matriculas_mes'] }}</h3>
                    <p>Matrículas del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <a href="{{ route('postgrado.matriculas') }}" class="small-box-footer">
                    Ver matrículas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['ingresos_mes'], 0, ',', '.') }}</h3>
                    <p>Ingresos del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('postgrado.reportes') }}" class="small-box-footer">
                    Ver reportes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['estudiantes_activos'] }}</h3>
                    <p>Estudiantes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('postgrado.estudiantes') }}" class="small-box-footer">
                    Ver estudiantes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['cursos_activos'] }}</h3>
                    <p>Cursos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <a href="{{ route('postgrado.cursos') }}" class="small-box-footer">
                    Ver cursos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas de Postgrado --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket"></i> Acciones Rápidas - Postgrado
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.matriculas') }}" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-user-plus"></i><br>
                                Nueva Matrícula
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.estudiantes') }}" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-users"></i><br>
                                Estudiantes
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.cursos') }}" class="btn btn-warning btn-lg btn-block">
                                <i class="fas fa-book-open"></i><br>
                                Gestión de Cursos
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.certificados') }}" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-certificate"></i><br>
                                Certificados
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.reportes') }}" class="btn btn-dark btn-lg btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                Reportes Académicos
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('postgrado.carreras') }}" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-graduation-cap"></i><br>
                                Configurar Carreras
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('postgrado.configuracion') }}" class="btn btn-secondary btn-lg btn-block">
                                <i class="fas fa-cog"></i><br>
                                Configuración General
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('carreras.cuotas') }}" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-dollar-sign"></i><br>
                                Gestionar Cuotas
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
                        <i class="fas fa-info-circle"></i> Información Postgrado
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Secretaría:</strong> Postgrado Odontología</p>
                    <p><strong>Ubicación:</strong> Primer Piso, Bloque A</p>
                    <p><strong>Horario:</strong> 09:00 - 17:00 (L-V)</p>
                    <p><strong>Especialidad:</strong> Programas de postgrado y educación continua</p>

                    <hr>

                    <h5><i class="fas fa-graduation-cap"></i> Programas Disponibles</h5>
                    <ul class="list-unstyled">
                        <li>🎓 Especializaciones</li>
                        <li>📚 Maestrías</li>
                        <li>🔬 Doctorados</li>
                        <li>📋 Diplomados</li>
                        <li>📖 Cursos de Actualización</li>
                    </ul>

                    <hr>

                    <h5><i class="fas fa-calendar"></i> Períodos Académicos</h5>
                    <ul class="list-unstyled">
                        <li><strong>2026-1:</strong> Marzo - Julio</li>
                        <li><strong>2026-2:</strong> Agosto - Diciembre</li>
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
        .alert-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
        }
    </style>
@stop

@section('js')
    <script>
        // Actualización automática de estadísticas cada 30 segundos
        setInterval(function() {
            console.log('Actualizando estadísticas académicas...');
        }, 30000);

        // Mensaje de bienvenida
        $(document).ready(function() {
            toastr.success('¡Bienvenido a la Secretaría de Postgrado!', 'Sistema Académico');
        });
    </script>
@stop
