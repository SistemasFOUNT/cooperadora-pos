@extends('adminlte::page')

@section('title', 'BOX - Configuración')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog text-primary"></i> Configuración - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Configuración</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Menú lateral de configuración --}}
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Configuraciones
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#config-general" class="list-group-item list-group-item-action active" data-toggle="tab">
                        <i class="fas fa-info-circle"></i> Información General
                    </a>
                    <a href="#config-horarios" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-clock"></i> Horarios de Atención
                    </a>
                    <a href="#config-descuentos" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-percent"></i> Descuentos
                    </a>
                    <a href="#config-impresion" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-print"></i> Configuración de Tickets
                    </a>
                    <a href="#config-usuarios" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-users"></i> Usuarios y Permisos
                    </a>
                    <a href="#config-backup" class="list-group-item list-group-item-action" data-toggle="tab">
                        <i class="fas fa-database"></i> Respaldo y Mantenimiento
                    </a>
                </div>
            </div>
        </div>

        {{-- Contenido de configuración --}}
        <div class="col-md-9">
            <div class="tab-content">
                {{-- Información General --}}
                <div class="tab-pane active" id="config-general">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información General del BOX
                            </h3>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nombre del Punto de Venta:</label>
                                            <input type="text" class="form-control" value="{{ isset($configuracion['punto_venta']) ? $configuracion['punto_venta']->nombre : 'BOX Cooperadora' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Código:</label>
                                            <input type="text" class="form-control" value="{{ isset($configuracion['punto_venta']) ? $configuracion['punto_venta']->codigo : 'BOX' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Ubicación:</label>
                                    <input type="text" class="form-control" value="Planta Baja, Facultad de Odontología" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <textarea class="form-control" rows="3" readonly>Punto de venta principal para productos del Laboratorio de Insumos, cuotas estudiantiles, bonos y servicios odontológicos de las cátedras clínicas.</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estado:</label>
                                            <div>
                                                <span class="badge badge-success">Activo</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Última Actualización:</label>
                                            <div>
                                                {{ now()->format('d/m/Y H:i:s') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Los datos generales del punto de venta son administrados desde el módulo de contabilidad.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Horarios de Atención --}}
                <div class="tab-pane" id="config-horarios">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock"></i> Horarios de Atención
                            </h3>
                        </div>
                        <div class="card-body">
                            <form>
                                @if(isset($configuracion['horarios']))
                                    @foreach($configuracion['horarios'] as $dia => $horario)
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $dia)) }}:</strong>
                                        </div>
                                        <div class="col-md-9">
                                            <span class="badge badge-primary">{{ $horario }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Lunes a Viernes:</strong></div>
                                        <div class="col-md-9"><span class="badge badge-primary">08:00 - 18:00</span></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Sábados:</strong></div>
                                        <div class="col-md-9"><span class="badge badge-primary">08:00 - 12:00</span></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Domingos:</strong></div>
                                        <div class="col-md-9"><span class="badge badge-danger">Cerrado</span></div>
                                    </div>
                                @endif

                                <hr>
                                <h5>Horarios Especiales</h5>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Período de Exámenes:</strong> Se extiende el horario hasta las 20:00 hrs.
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong>Receso de Verano:</strong> Horario reducido de 09:00 a 15:00 hrs.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Descuentos --}}
                <div class="tab-pane" id="config-descuentos">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-percent"></i> Configuración de Descuentos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(isset($configuracion['descuentos']))
                                    @foreach($configuracion['descuentos'] as $tipo => $porcentaje)
                                    <div class="col-md-4">
                                        <div class="card card-outline card-info">
                                            <div class="card-body text-center">
                                                <h4>{{ ucfirst($tipo) }}</h4>
                                                <h2 class="text-success">{{ $porcentaje }}%</h2>
                                                <p class="text-muted">de descuento</p>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Modificar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="col-md-4">
                                        <div class="card card-outline card-info">
                                            <div class="card-body text-center">
                                                <h4>Estudiantes</h4>
                                                <h2 class="text-success">10%</h2>
                                                <p class="text-muted">de descuento</p>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Modificar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card card-outline card-info">
                                            <div class="card-body text-center">
                                                <h4>Docentes</h4>
                                                <h2 class="text-success">15%</h2>
                                                <p class="text-muted">de descuento</p>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Modificar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card card-outline card-info">
                                            <div class="card-body text-center">
                                                <h4>Personal</h4>
                                                <h2 class="text-success">20%</h2>
                                                <p class="text-muted">de descuento</p>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Modificar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                Los descuentos se aplican automáticamente según el tipo de usuario registrado en el sistema.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Configuración de Impresión --}}
                <div class="tab-pane" id="config-impresion">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-print"></i> Configuración de Tickets
                            </h3>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Información del Encabezado</h5>
                                        <div class="form-group">
                                            <label>Nombre de la Institución:</label>
                                            <input type="text" class="form-control" value="Facultad de Odontología">
                                        </div>
                                        <div class="form-group">
                                            <label>Dirección:</label>
                                            <input type="text" class="form-control" value="Universidad Nacional de Córdoba">
                                        </div>
                                        <div class="form-group">
                                            <label>Teléfono:</label>
                                            <input type="text" class="form-control" value="(0351) 433-4660">
                                        </div>
                                        <div class="form-group">
                                            <label>CUIT:</label>
                                            <input type="text" class="form-control" value="30-54667284-0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Configuraciones de Impresión</h5>
                                        <div class="form-group">
                                            <label>Ancho del Ticket (caracteres):</label>
                                            <input type="number" class="form-control" value="40">
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" checked>
                                                <label class="form-check-label">Imprimir automáticamente</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" checked>
                                                <label class="form-check-label">Incluir código de barras</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input">
                                                <label class="form-check-label">Abrir gaveta de dinero</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Configuración
                                    </button>
                                    <button type="button" class="btn btn-secondary">
                                        <i class="fas fa-print"></i> Imprimir Ticket de Prueba
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Usuarios y Permisos --}}
                <div class="tab-pane" id="config-usuarios">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Usuarios del BOX
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                La gestión de usuarios se realiza desde el módulo de administración general.
                            </div>

                            <h5>Usuarios Activos en el BOX</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Último Acceso</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ auth()->user()->name }}</td>
                                            <td>
                                                <span class="badge badge-primary">{{ auth()->user()->role ?? 'Usuario' }}</span>
                                            </td>
                                            <td>{{ now()->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge badge-success">Activo</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Cargar desde base de datos...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Respaldo y Mantenimiento --}}
                <div class="tab-pane" id="config-backup">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-database"></i> Respaldo y Mantenimiento
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Respaldos Automáticos</h5>
                                    <div class="form-group">
                                        <label>Frecuencia de Respaldo:</label>
                                        <select class="form-control">
                                            <option value="daily" selected>Diario</option>
                                            <option value="weekly">Semanal</option>
                                            <option value="monthly">Mensual</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Hora de Respaldo:</label>
                                        <input type="time" class="form-control" value="02:00">
                                    </div>
                                    <div class="form-group">
                                        <label>Mantener Respaldos:</label>
                                        <input type="number" class="form-control" value="30" placeholder="días">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Acciones de Mantenimiento</h5>
                                    <div class="list-group">
                                        <button class="list-group-item list-group-item-action">
                                            <i class="fas fa-download"></i> Crear Respaldo Manual
                                            <small class="text-muted d-block">Genera un respaldo inmediato</small>
                                        </button>
                                        <button class="list-group-item list-group-item-action">
                                            <i class="fas fa-broom"></i> Limpiar Cache
                                            <small class="text-muted d-block">Limpia archivos temporales</small>
                                        </button>
                                        <button class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-bar"></i> Optimizar Base de Datos
                                            <small class="text-muted d-block">Mejora el rendimiento</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Las operaciones de mantenimiento requieren permisos de administrador.
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
        .list-group-item-action.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        .card-outline {
            border-top: 3px solid #007bff;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Simular guardado de configuración
            $('form').submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'success',
                    title: 'Configuración Guardada',
                    text: 'Los cambios se han aplicado correctamente',
                    timer: 2000
                });
            });

            // Botón de ticket de prueba
            $('button:contains("Imprimir Ticket de Prueba")').click(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Imprimiendo Ticket de Prueba',
                    text: 'Enviando ticket a la impresora...',
                    timer: 3000
                });
            });

            // Botones de mantenimiento
            $('.list-group-item-action').click(function() {
                const accion = $(this).find('i').next().text().trim();
                Swal.fire({
                    icon: 'question',
                    title: '¿Confirmar acción?',
                    text: `¿Deseas ejecutar: ${accion}?`,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ejecutar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Acción Completada',
                            text: `${accion} ejecutado correctamente`,
                            timer: 2000
                        });
                    }
                });
            });
        });
    </script>
@stop
