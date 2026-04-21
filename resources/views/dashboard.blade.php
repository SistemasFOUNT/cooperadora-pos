@extends('adminlte::page')

@section('title', 'Dashboard - FOUNT Contable')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-tachometer-alt"></i> Dashboard
            @if($isAdmin)
                - <span class="badge badge-danger">Administrador</span>
            @else
                - {{ $puntoVenta ? $puntoVenta->nombre : 'Usuario' }}
            @endif
        </h1>
        <div class="text-muted">
            Bienvenido, {{ $user->name }}
        </div>
    </div>
@stop

@section('content')
    {{-- Información específica del usuario --}}
    @if($isAdmin)
        {{-- Vista para administrador --}}
        <div class="alert alert-info">
            <h5><i class="fas fa-crown"></i> Panel de Administrador</h5>
            <p>Tienes acceso completo a todos los módulos y puntos de venta del sistema.</p>
        </div>

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\Product::count() }}</h3>
                        <p>Productos Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <a href="{{ route('products.index') }}" class="small-box-footer">
                        Ver productos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\Student::count() }}</h3>
                        <p>Estudiantes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('students.index') }}" class="small-box-footer">
                        Ver estudiantes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\PuntoVenta::count() }}</h3>
                        <p>Puntos de Venta</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <a href="{{ route('contabilidad.puntos-venta.index') }}" class="small-box-footer">
                        Ver puntos de venta <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\User::where('role', '!=', 'admin')->count() }}</h3>
                        <p>Usuarios Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Gestionar usuarios <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

    @else
        {{-- Vista para usuarios específicos de punto de venta --}}
        <div class="alert alert-{{ $user->role == 'usuario_box' ? 'primary' : ($user->role == 'usuario_postgrado' ? 'success' : 'warning') }}">
            <h5><i class="fas fa-store"></i> Panel: {{ $puntoVenta ? $puntoVenta->nombre : 'Tu Punto de Venta' }}</h5>
            <p>Bienvenido a tu panel de trabajo. Desde aquí puedes acceder a las funciones disponibles para tu punto de venta.</p>
        </div>

        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-{{ $user->role == 'usuario_box' ? 'primary' : ($user->role == 'usuario_postgrado' ? 'success' : 'warning') }}">
                    <div class="inner">
                        <h3><i class="fas fa-cash-register"></i></h3>
                        <p>Punto de Venta</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <a href="{{ route('pos.index') }}" class="small-box-footer">
                        Ir al POS <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\Product::count() }}</h3>
                        <p>Productos Disponibles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <a href="{{ route('products.index') }}" class="small-box-footer">
                        Ver productos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><i class="fas fa-cog"></i></h3>
                        <p>Mi Configuración</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <a href="{{ route('contabilidad.puntos-venta.index') }}" class="small-box-footer">
                        Ver mi punto de venta <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Información del punto de venta del usuario --}}
        @if($puntoVenta)
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-{{ $puntoVenta->codigo == 'BOX' ? 'box' : ($puntoVenta->codigo == 'POSTGRADO' ? 'graduation-cap' : 'tooth') }}"></i>
                            Mi Punto de Venta
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $puntoVenta->nombre }}</dd>

                            <dt class="col-sm-4">Código:</dt>
                            <dd class="col-sm-8"><code>{{ $puntoVenta->codigo }}</code></dd>

                            <dt class="col-sm-4">Estado:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-{{ $puntoVenta->activo ? 'success' : 'danger' }}">
                                    {{ $puntoVenta->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Descripción:</dt>
                            <dd class="col-sm-8">{{ $puntoVenta->descripcion }}</dd>
                        </dl>

                        <a href="{{ route('contabilidad.puntos-venta.index') }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Usuario</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $user->name }}</dd>

                            <dt class="col-sm-4">Usuario:</dt>
                            <dd class="col-sm-8"><code>{{ $user->username }}</code></dd>

                            <dt class="col-sm-4">Rol:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-info">{{ $user->getRoleNameAttribute() }}</span>
                            </dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>
                        </dl>

                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                            <i class="fas fa-user-edit"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @endif

    {{-- Accesos rápidos comunes --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-rocket"></i> Accesos Rápidos</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('pos.index') }}" class="btn btn-app">
                                <i class="fas fa-cash-register"></i>
                                POS
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('products.index') }}" class="btn btn-app">
                                <i class="fas fa-box"></i>
                                Productos
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('students.index') }}" class="btn btn-app">
                                <i class="fas fa-users"></i>
                                Estudiantes
                            </a>
                        </div>
                        @if($isAdmin)
                        <div class="col-md-3">
                            <a href="{{ route('contabilidad.plan-cuentas') }}" class="btn btn-app">
                                <i class="fas fa-chart-bar"></i>
                                Contabilidad
                            </a>
                        </div>
                        @else
                        <div class="col-md-3">
                            <a href="{{ route('contabilidad.puntos-venta.index') }}" class="btn btn-app">
                                <i class="fas fa-store"></i>
                                Mi Punto
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.info-box.bg-primary .info-box-text,
.info-box.bg-success .info-box-text,
.info-box.bg-warning .info-box-text {
    color: white !important;
}
.progress-description {
    font-size: 12px;
}
.btn-app {
    margin: 5px;
}
</style>
@stop

@section('js')
<script>
$(function() {
    // Inicializar cualquier funcionalidad JavaScript específica del dashboard
    console.log('Dashboard cargado para usuario:', '{{ $user->name }}');

    @if(!$isAdmin)
    console.log('Punto de venta:', '{{ $puntoVenta ? $puntoVenta->codigo : "No asignado" }}');
    @endif
});
</script>
@stop
