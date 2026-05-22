@extends('adminlte::page')

@section('title', 'Reporte de Estudiantes')

@section('content_header')
    <h1 class="m-0">Reporte de Estudiantes</h1>
@stop

@section('content')
    <div class="card card-outline card-info">
        <div class="card-body">
            <p class="mb-0">Este reporte se mantiene disponible como acceso directo y se centraliza en Reportes de Postgrado.</p>
            <a href="{{ route('postgrado.reportes') }}" class="btn btn-primary btn-sm mt-3">Ir a reportes consolidados</a>
        </div>
    </div>
@stop
