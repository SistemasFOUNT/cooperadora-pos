@extends('adminlte::page')

@section('title', 'Detalle de Auditoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Detalle de Auditoría #{{ $audit->id }}</h1>
            <p class="text-muted mb-0">Evento {{ strtoupper($audit->event) }} sobre {{ class_basename($audit->auditable_type) }}</p>
        </div>
        <a href="{{ route('admin.auditoria.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Metadatos</h3></div>
                <div class="card-body">
                    <p><strong>Fecha:</strong> {{ optional($audit->created_at)->format('d/m/Y H:i:s') }}</p>
                    <p><strong>Evento:</strong> {{ strtoupper($audit->event) }}</p>
                    <p><strong>Modelo:</strong> {{ $audit->auditable_type }}</p>
                    <p><strong>ID modelo:</strong> {{ $audit->auditable_id }}</p>
                    <p><strong>Usuario:</strong> {{ $usuario->name ?? ('#' . $audit->user_id) }}</p>
                    <p><strong>IP:</strong> {{ $audit->ip_address ?? '-' }}</p>
                    <p><strong>URL:</strong> {{ $audit->url ?? '-' }}</p>
                    <p><strong>User Agent:</strong> {{ $audit->user_agent ?? '-' }}</p>
                    <p><strong>Tags:</strong> {{ $audit->tags ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title">Valores anteriores</h3></div>
                <div class="card-body">
                    <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($audit->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>

            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Valores nuevos</h3></div>
                <div class="card-body">
                    <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($audit->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
@stop
