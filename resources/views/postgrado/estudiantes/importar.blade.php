@extends('adminlte::page')

@section('title', 'Importar Estudiantes de Postgrado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-upload"></i> {{ $sectionTitle }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('postgrado.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('postgrado.estudiantes') }}">Estudiantes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Importar CSV</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-csv"></i> Importar desde Archivo CSV</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <strong>Vista de demostración:</strong> Esta funcionalidad está en desarrollo.
                        </div>
                        
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Instrucciones:</h5>
                            <ul class="mb-0">
                                <li>El archivo debe estar en formato CSV separado por comas (,)</li>
                                <li>La primera fila debe contener los encabezados de las columnas</li>
                                <li>Las columnas requeridas son: <code>nombre, dni, carrera_type</code></li>
                                <li>Las columnas opcionales son: <code>email, telefono, observaciones</code></li>
                                <li>Para <code>carrera_type</code> use: postgrado, especialización, maestría, doctorado</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="csv_file">Archivo CSV <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('csv_file') is-invalid @enderror" 
                                           id="csv_file" name="csv_file" accept=".csv" required>
                                    <label class="custom-file-label" for="csv_file">Elegir archivo CSV...</label>
                                </div>
                            </div>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="validar_duplicados" name="validar_duplicados" value="1" checked>
                                <label class="custom-control-label" for="validar_duplicados">
                                    Validar duplicados por DNI
                                </label>
                            </div>
                            <small class="form-text text-muted">Si está marcado, no se importarán estudiantes que ya existan con el mismo DNI.</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="solo_postgrado" name="solo_postgrado" value="1" checked>
                                <label class="custom-control-label" for="solo_postgrado">
                                    Solo importar carreras de postgrado
                                </label>
                            </div>
                            <small class="form-text text-muted">Filtrará automáticamente solo las carreras de tipo postgrado, especialización, maestría y doctorado.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('postgrado.estudiantes') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-upload"></i> Importar Estudiantes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-download"></i> Plantilla CSV</h3>
                </div>
                <div class="card-body">
                    <p>Descarga una plantilla de ejemplo para importar estudiantes de postgrado:</p>
                    <a href="#" class="btn btn-primary btn-block">
                        <i class="fas fa-download"></i> Descargar Plantilla
                    </a>
                    <hr>
                    <h6>Ejemplo de formato:</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>nombre</th>
                                <th>dni</th>
                                <th>carrera_type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juan Pérez</td>
                                <td>12345678</td>
                                <td>postgrado</td>
                            </tr>
                            <tr>
                                <td>María García</td>
                                <td>87654321</td>
                                <td>especialización</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    <script>
        // Script para mostrar el nombre del archivo seleccionado
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Elegir archivo CSV...');
        });
    </script>
@stop