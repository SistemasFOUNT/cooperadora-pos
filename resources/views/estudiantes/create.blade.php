@extends('adminlte::page')

@section('title', 'Crear Estudiante')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-plus"></i> Crear Nuevo Estudiante</h1>
        <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Lista
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Estudiante</h3>
                </div>
                
                <form action="{{ route('estudiantes.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Información Personal -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-user"></i> Información Personal</h5>
                                
                                <div class="form-group">
                                    <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('apellido') is-invalid @enderror" 
                                           id="apellido" name="apellido" value="{{ old('apellido') }}" required>
                                    @error('apellido')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="dni">DNI <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                                           id="dni" name="dni" value="{{ old('dni') }}" required>
                                    @error('dni')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                           id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                                    @error('fecha_nacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Información de Contacto -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-address-card"></i> Información de Contacto</h5>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono') }}">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                              id="direccion" name="direccion" rows="3">{{ old('direccion') }}</textarea>
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <!-- Información Académica -->
                            <div class="col-12">
                                <h5 class="mb-3"><i class="fas fa-graduation-cap"></i> Información Académica</h5>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="carrera_id">Carrera <span class="text-danger">*</span></label>
                                    <select class="form-control @error('carrera_id') is-invalid @enderror" 
                                            id="carrera_id" name="carrera_id" required>
                                        <option value="">Seleccione una carrera</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>
                                                {{ $carrera->nombre_carrera }} ({{ $carrera->tipo_carrera }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('carrera_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="reinscripcion">Año de Reinscripción</label>
                                    <input type="number" class="form-control @error('reinscripcion') is-invalid @enderror" 
                                           id="reinscripcion" name="reinscripcion" value="{{ old('reinscripcion', now()->year) }}" 
                                           min="2000" max="{{ now()->year + 5 }}" placeholder="{{ now()->year }}">
                                    @error('reinscripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Año académico en que se reinscribió
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_inscripcion">Fecha de Inscripción <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('fecha_inscripcion') is-invalid @enderror" 
                                           id="fecha_inscripcion" name="fecha_inscripcion" 
                                           value="{{ old('fecha_inscripcion', date('Y-m-d')) }}" required>
                                    @error('fecha_inscripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control @error('estado') is-invalid @enderror" 
                                            id="estado" name="estado" required>
                                        <option value="">Seleccione un estado</option>
                                        <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        <option value="graduado" {{ old('estado') == 'graduado' ? 'selected' : '' }}>Graduado</option>
                                        <option value="abandonado" {{ old('estado') == 'abandonado' ? 'selected' : '' }}>Abandonado</option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Estudiante
                        </button>
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-formatear DNI (solo números)
    $('#dni').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Auto-formatear teléfono
    $('#telefono').on('input', function() {
        this.value = this.value.replace(/[^0-9\-\+\(\)\ ]/g, '');
    });
});
</script>
@stop