@extends('adminlte::page')

@section('title', 'Autorizaciones Pendientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Autorizaciones Pendientes</h1>
            <p class="text-muted mb-0">Aprobaciones administrativas en espera</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Pendientes</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Solicitante</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($autorizaciones_pendientes ?? [] as $autorizacion)
                        <tr>
                            <td>{{ data_get($autorizacion, 'concepto', 'Sin concepto') }}</td>
                            <td>{{ data_get($autorizacion, 'solicitante', 'Sin dato') }}</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay autorizaciones pendientes por el momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
