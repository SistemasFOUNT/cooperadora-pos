@extends('adminlte::page')

@section('title', 'Libro Caja General')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Libro Caja General</h1>
            <p class="text-muted mb-0">Vista administrativa global del libro de caja</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-info">
        <div class="card-body">
            <p class="mb-0">El libro caja general queda listo para integrar filtros, movimientos y exportaciones.</p>
        </div>
    </div>
@stop
