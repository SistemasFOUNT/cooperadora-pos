@extends('adminlte::page')

@section('title', 'Gestionar Cuotas de Postgrado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-dollar-sign"></i> {{ $sectionTitle }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('postgrado.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('postgrado.carreras') }}">Carreras</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestionar Cuotas</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if($cuotas->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Sin Carreras de Postgrado</h3>
                    </div>
                    <div class="card-body text-center">
                        <p>No se encontraron carreras de postgrado configuradas en el sistema.</p>
                        <a href="{{ route('postgrado.carreras') }}" class="btn btn-primary">
                            <i class="fas fa-graduation-cap"></i> Configurar Carreras
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Cuotas por Carrera de Postgrado</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-nueva-cuota">
                                <i class="fas fa-plus"></i> Nueva Cuota
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Solo se muestran carreras de postgrado (no incluye grado ni tecnicaturas).
                        </div>
                        
                        @foreach($cuotas as $carrera)
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-graduation-cap"></i> 
                                        {{ $carrera->name }}
                                        <span class="badge badge-{{ $carrera->type === 'postgrado' ? 'success' : ($carrera->type === 'especialización' ? 'primary' : ($carrera->type === 'maestría' ? 'info' : 'warning')) }} ml-2">
                                            {{ ucfirst($carrera->type) }}
                                        </span>
                                    </h4>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarCuota({{ $carrera->id }}, '{{ $carrera->name }}')">
                                            <i class="fas fa-plus"></i> Agregar Cuota
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($carrera->fees && $carrera->fees->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Concepto</th>
                                                        <th>Monto</th>
                                                        <th>Vencimiento</th>
                                                        <th>Estado</th>
                                                        <th width="100">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($carrera->fees->sortBy('due_date') as $cuota)
                                                        <tr>
                                                            <td>{{ $cuota->description ?? 'Cuota ' . $cuota->period }}</td>
                                                            <td><strong>${{ number_format($cuota->amount, 0, ',', '.') }}</strong></td>
                                                            <td>{{ $cuota->due_date ? $cuota->due_date->format('d/m/Y') : 'Sin fecha' }}</td>
                                                            <td>
                                                                @if($cuota->is_active)
                                                                    <span class="badge badge-success">Activa</span>
                                                                @else
                                                                    <span class="badge badge-secondary">Inactiva</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-warning btn-xs" onclick="editarCuota({{ $cuota->id }})">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-xs" onclick="eliminarCuota({{ $cuota->id }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                            <p>No hay cuotas configuradas para esta carrera.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Nueva Cuota -->
<div class="modal fade" id="modal-nueva-cuota" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title"><i class="fas fa-plus"></i> Nueva Cuota de Postgrado</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Vista de demostración:</strong> Esta funcionalidad está en desarrollo.
                    </div>
                    <div class="form-group">
                        <label for="carrera_id">Carrera <span class="text-danger">*</span></label>
                        <select class="form-control" name="carrera_id" id="carrera_id" required>
                            <option value="">Seleccionar carrera...</option>
                            @foreach($cuotas as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->name }} ({{ ucfirst($carrera->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Concepto</label>
                        <input type="text" class="form-control" name="description" id="description" placeholder="Ej: Matrícula, Cuota Marzo, etc.">
                    </div>
                    <div class="form-group">
                        <label for="amount">Monto <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" name="due_date" id="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Crear Cuota</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
    <script>
        function agregarCuota(carreraId, carreraNombre) {
            $('#carrera_id').val(carreraId);
            $('#modal-nueva-cuota').modal('show');
        }

        function editarCuota(cuotaId) {
            // Implementar lógica de edición
            console.log('Editar cuota:', cuotaId);
        }

        function eliminarCuota(cuotaId) {
            if (confirm('¿Está seguro de que desea eliminar esta cuota?')) {
                // Implementar lógica de eliminación
                console.log('Eliminar cuota:', cuotaId);
            }
        }
    </script>
@stop