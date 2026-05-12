@extends('adminlte::page')

@section('title', 'Cuentas General')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Cuentas General</h1>
            <p class="text-muted mb-0">Herramientas de consulta contable global</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <p class="mb-0">Esta vista centraliza la consulta general de cuentas para el panel administrativo.</p>
        </div>
    </div>
@stop
