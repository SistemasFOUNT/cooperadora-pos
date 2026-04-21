@extends('adminlte::page')

@section('title', 'Centro Odontológico - Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-tooth text-info"></i> Centro Odontológico</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Centro Odontológico</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Información del usuario --}}
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-user-md"></i> Bienvenido {{ auth()->user()->name }}</h4>
                <p>Estás operando en: <strong>Centro Odontológico</strong> | Rol: <strong>{{ auth()->user()->role_name }}</strong></p>
            </div>
        </div>
    </div>

    {{-- Estadísticas principales --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['citas_hoy'] }}</h3>
                    <p>Citas Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('odonto.agenda') }}" class="small-box-footer">
                    Ver agenda <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['tratamientos_activos'] }}</h3>
                    <p>Tratamientos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-procedures"></i>
                </div>
                <a href="{{ route('odonto.tratamientos') }}" class="small-box-footer">
                    Ver tratamientos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['ingresos_mes'], 0, ',', '.') }}</h3>
                    <p>Ingresos del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('odonto.facturacion') }}" class="small-box-footer">
                    Ver facturación <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['pacientes_atendidos_mes'] }}</h3>
                    <p>Pacientes del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-injured"></i>
                </div>
                <a href="{{ route('odonto.pacientes') }}" class="small-box-footer">
                    Ver pacientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas del Centro Odontológico --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket"></i> Acciones Rápidas - Centro Odontológico
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('odonto.agenda') }}" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-calendar-alt"></i><br>
                                Agenda de Citas
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('odonto.pacientes') }}" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-user-injured"></i><br>
                                Pacientes
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('odonto.tratamientos') }}" class="btn btn-warning btn-lg btn-block">
                                <i class="fas fa-procedures"></i><br>
                                Tratamientos
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('odonto.historiales') }}" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-clipboard-list"></i><br>
                                Historiales
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('odonto.inventario') }}" class="btn btn-secondary btn-lg btn-block">
                                <i class="fas fa-boxes"></i><br>
                                Inventario
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('odonto.facturacion') }}" class="btn btn-dark btn-lg btn-block">
                                <i class="fas fa-file-invoice-dollar"></i><br>
                                Facturación
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('odonto.reportes') }}" class="btn btn-danger btn-lg btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                Reportes Clínicos
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('odonto.configuracion') }}" class="btn btn-outline-secondary btn-lg btn-block">
                                <i class="fas fa-cog"></i><br>
                                Configuración
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
                        <i class="fas fa-info-circle"></i> Información Centro
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Centro:</strong> Odontológico UNT</p>
                    <p><strong>Ubicación:</strong> Planta Baja, Ala Sur</p>
                    <p><strong>Horario:</strong> 08:00 - 20:00 (L-V)<br>
                       <small>Sábados: 08:00 - 14:00</small><br>
                       <small>Urgencias Domingos: 10:00 - 14:00</small>
                    </p>

                    <hr>

                    <h5><i class="fas fa-stethoscope"></i> Especialidades</h5>
                    <ul class="list-unstyled">
                        <li>🦷 Odontología General</li>
                        <li>🔧 Ortodoncia</li>
                        <li>💉 Endodoncia</li>
                        <li>⚕️ Cirugía Oral</li>
                        <li>🦴 Periodoncia</li>
                        <li>👑 Prótesis Dental</li>
                        <li>👶 Odontopediatría</li>
                        <li>🔩 Implantología</li>
                    </ul>

                    <hr>

                    <h5><i class="fas fa-money-bill"></i> Tratamientos Principales</h5>
                    <ul class="list-unstyled">
                        <li><strong>Consulta:</strong> $3,000</li>
                        <li><strong>Limpieza:</strong> $5,000</li>
                        <li><strong>Empaste:</strong> $8,000</li>
                        <li><strong>Endodoncia:</strong> $25,000</li>
                        <li><strong>Implante:</strong> $80,000</li>
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
            background: linear-gradient(45deg, #17a2b8, #007bff);
            color: white;
            border: none;
        }
        .card-info .card-header {
            background: linear-gradient(45deg, #17a2b8, #20c997);
        }
    </style>
@stop

@section('js')
    <script>
        // Actualización automática de estadísticas cada 30 segundos
        setInterval(function() {
            console.log('Actualizando estadísticas del centro...');
        }, 30000);

        // Mensaje de bienvenida
        $(document).ready(function() {
            toastr.success('¡Bienvenido al Centro Odontológico!', 'Sistema Clínico');
        });

        // Función para marcar urgencias
        function marcarUrgencia() {
            Swal.fire({
                title: '⚠️ Emergencia Odontológica',
                text: '¿Desea registrar una cita de urgencia?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, registrar urgencia',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir a nueva cita de urgencia
                    window.location.href = "{{ route('odonto.agenda') }}?urgencia=true";
                }
            });
        }
    </script>
@stop
