@extends('adminlte::page')

@section('title', 'Editar Estudiante')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-edit"></i> Editar Estudiante</h1>
        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos del Estudiante</h3>
                </div>
                <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('apellido') is-invalid @enderror" 
                                           id="apellido" name="apellido" value="{{ old('apellido', $estudiante->apellido) }}" required>
                                    @error('apellido')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre', $estudiante->nombre) }}" required>
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dni">DNI <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                                           id="dni" name="dni" value="{{ old('dni', $estudiante->dni) }}" required>
                                    @error('dni')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $estudiante->email) }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono', $estudiante->telefono) }}">
                                    @error('telefono')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                           id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="{{ old('fecha_nacimiento', $estudiante->fecha_nacimiento ? $estudiante->fecha_nacimiento->format('Y-m-d') : '') }}">
                                    @error('fecha_nacimiento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="domicilio">Domicilio</label>
                            <input type="text" class="form-control @error('domicilio') is-invalid @enderror" 
                                   id="domicilio" name="domicilio" value="{{ old('domicilio', $estudiante->domicilio) }}">
                            @error('domicilio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="carrera_id">Carrera <span class="text-danger">*</span></label>
                                    <select class="form-control @error('carrera_id') is-invalid @enderror" id="carrera_id" name="carrera_id" required>
                                        <option value="">Seleccionar carrera</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}" 
                                                {{ old('carrera_id', $estudiante->carrera_id) == $carrera->id ? 'selected' : '' }}>
                                                {{ $carrera->nombre_carrera }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('carrera_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="anio_academico">Año Académico</label>
                                    <input type="text" class="form-control @error('anio_academico') is-invalid @enderror" 
                                           id="anio_academico" name="anio_academico" value="{{ old('anio_academico', $estudiante->anio_academico) }}">
                                    @error('anio_academico')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                        <option value="activo" {{ old('estado', $estudiante->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado', $estudiante->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        <option value="suspendido" {{ old('estado', $estudiante->estado) == 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                                    </select>
                                    @error('estado')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inscripcion">Fecha de Inscripción</label>
                                    <input type="date" class="form-control @error('fecha_inscripcion') is-invalid @enderror" 
                                           id="fecha_inscripcion" name="fecha_inscripcion" 
                                           value="{{ old('fecha_inscripcion', $estudiante->fecha_inscripcion ? $estudiante->fecha_inscripcion->format('Y-m-d') : '') }}">
                                    @error('fecha_inscripcion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $estudiante->observaciones) }}</textarea>
                            @error('observaciones')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Información de Ayuda</h3>
                </div>
                <div class="card-body">
                    <p><strong>Campos obligatorios:</strong></p>
                    <ul>
                        <li>Apellido</li>
                        <li>Nombre</li>
                        <li>DNI</li>
                        <li>Carrera</li>
                        <li>Estado</li>
                    </ul>
                    
                    <p><strong>Estados disponibles:</strong></p>
                    <ul>
                        <li><span class="badge badge-success">Activo</span> - Estudiante regular</li>
                        <li><span class="badge badge-warning">Inactivo</span> - Temporalmente inactivo</li>
                        <li><span class="badge badge-secondary">Suspendido</span> - Suspendido administrativamente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.form-group label {
    font-weight: 600;
}
.text-danger {
    color: #dc3545 !important;
}
</style>
@stop