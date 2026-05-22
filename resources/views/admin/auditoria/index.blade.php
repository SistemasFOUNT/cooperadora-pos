@extends('adminlte::page')

@section('title', 'Auditoría Interna')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Auditoría Interna</h1>
            <p class="text-muted mb-0">Trazabilidad de eventos críticos del sistema</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de auditoría</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auditoria.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label>Evento</label>
                        <select name="evento" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($eventos as $ev)
                                <option value="{{ $ev }}" @selected($evento === $ev)>{{ strtoupper($ev) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Modelo</label>
                        <select name="modelo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($modelos as $mod)
                                <option value="{{ $mod }}" @selected($modelo === $mod)>{{ class_basename($mod) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Usuario</label>
                        <select name="usuario_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($usuarios as $usr)
                                <option value="{{ $usr->id }}" @selected((string) $usuarioId === (string) $usr->id)>
                                    {{ $usr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-sm btn-block" type="submit">Aplicar</button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscar }}"
                            class="form-control form-control-sm"
                            placeholder="Buscar por ID auditado, URL, IP o tags"
                        >
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.auditoria.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Registros de auditoría</h3>
            <span class="badge badge-info float-right">{{ $audits->total() }} eventos</span>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Evento</th>
                        <th>Modelo</th>
                        <th>ID Modelo</th>
                        <th>Usuario</th>
                        <th>IP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>{{ optional($audit->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td><span class="badge badge-{{ $audit->event === 'deleted' ? 'danger' : ($audit->event === 'updated' ? 'warning' : 'success') }}">{{ strtoupper($audit->event) }}</span></td>
                            <td>{{ class_basename($audit->auditable_type) }}</td>
                            <td>{{ $audit->auditable_id }}</td>
                            <td>{{ $nombresUsuarios[$audit->user_id] ?? ('#' . $audit->user_id) }}</td>
                            <td>{{ $audit->ip_address ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.auditoria.show', $audit->id) }}" class="btn btn-xs btn-outline-primary">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay registros de auditoría para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $audits->links() }}
        </div>
    </div>
@stop
