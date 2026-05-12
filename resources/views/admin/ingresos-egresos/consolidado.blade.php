@extends('adminlte::page')

@section('title', 'Ingresos y Egresos - Consolidado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Ingresos y Egresos - Consolidado</h1>
            <p class="text-muted mb-0">Resumen general de los tres puntos de venta</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>${{ number_format($consolidado['ingresos_hoy']['box'] ?? 0, 2, ',', '.') }}</h3>
                    <p>BOX - Ingresos hoy</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($consolidado['ingresos_hoy']['postgrado'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Postgrado - Ingresos hoy</p>
                </div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($consolidado['ingresos_hoy']['odonto'] ?? 0, 2, ',', '.') }}</h3>
                    <p>Odonto - Ingresos hoy</p>
                </div>
                <div class="icon"><i class="fas fa-tooth"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Total general del día</h3>
                </div>
                <div class="card-body">
                    <h2 class="mb-0 text-success">${{ number_format($consolidado['total_general'] ?? 0, 2, ',', '.') }}</h2>
                    <p class="text-muted mb-0">Suma consolidada de ventas registradas hoy.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Comparativo mensual</h3>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Mes actual:</strong> ${{ number_format($consolidado['comparativo_mes']['mes_actual'] ?? 0, 2, ',', '.') }}</p>
                    <p class="mb-1"><strong>Mes anterior:</strong> ${{ number_format($consolidado['comparativo_mes']['mes_anterior'] ?? 0, 2, ',', '.') }}</p>
                    <p class="mb-0"><strong>Variación:</strong> {{ number_format($consolidado['comparativo_mes']['variacion'] ?? 0, 2, ',', '.') }}%</p>
                </div>
            </div>
        </div>
    </div>
@stop
