@extends('adminlte::page')

@section('title', 'Agregar Estudiante de Postgrado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-plus"></i> {{ $sectionTitle }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('postgrado.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('postgrado.estudiantes') }}">Estudiantes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Agregar</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Formulario de Registro - Postgrado</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <strong>Vista de demostración:</strong> Esta funcionalidad está en desarrollo.
                        </div>
                        
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="dni">DNI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                                       id="dni" name="dni" value="{{ old('dni') }}" required>
                                @error('dni')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" name="telefono" value="{{ old('telefono') }}">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="carrera_id">Carrera de Postgrado <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('carrera_id') is-invalid @enderror" 
                                    id="carrera_id" name="carrera_id" required>
                                <option value="">Seleccionar carrera...</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>
                                        {{ $carrera->name }} ({{ ucfirst($carrera->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('carrera_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="acepta_terminos" name="acepta_terminos" value="1" {{ old('acepta_terminos') ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="acepta_terminos">
                                    Acepto los términos y condiciones para estudiantes de postgrado <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('postgrado.estudiantes') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Registrar Estudiante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
                </div>
                <div class="card-body">
                    <p><strong>Carreras disponibles para Postgrado:</strong></p>
                    <ul class="list-unstyled">
                        @foreach($carreras as $carrera)
                            <li><i class="fas fa-graduation-cap text-success"></i> {{ $carrera->name }}</li>
                        @endforeach
                    </ul>
                    <hr>
                    <p class="text-muted"><small>Los campos marcados con (*) son obligatorios.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('vendor/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccionar...',
                allowClear: true
            });
        });
    </script>
@stop