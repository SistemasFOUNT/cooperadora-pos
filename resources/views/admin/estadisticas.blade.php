@extends('adminlte::page')

@section('title', 'Estadísticas Generales')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Estadísticas Generales</h1>
            <p class="text-muted mb-0">Visión global del sistema administrativo</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $estadisticas['ventas_box'] ?? 0 }}</h3>
                    <p>Ventas BOX</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['estudiantes_postgrado'] ?? 0 }}</h3>
                    <p>Estudiantes Postgrado</p>
                </div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['pacientes_odonto'] ?? 0 }}</h3>
                    <p>Pacientes Odonto</p>
                </div>
                <div class="icon"><i class="fas fa-tooth"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['total_ingresos'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
@stop
