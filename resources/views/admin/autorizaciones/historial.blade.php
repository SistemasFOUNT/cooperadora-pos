@extends('adminlte::page')

@section('title', 'Historial de Autorizaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Historial de Autorizaciones</h1>
            <p class="text-muted mb-0">Registro de aprobaciones y rechazos</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Historial</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Referencia</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial_autorizaciones ?? [] as $autorizacion)
                        <tr>
                            <td>{{ data_get($autorizacion, 'created_at', '-') }}</td>
                            <td>{{ data_get($autorizacion, 'referencia', 'Sin referencia') }}</td>
                            <td><span class="badge badge-secondary">{{ data_get($autorizacion, 'estado', 'Sin estado') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Todavía no hay historial de autorizaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
