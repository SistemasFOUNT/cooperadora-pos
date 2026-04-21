@extends('adminlte::page')

@section('title', __('Plan de Cuentas'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-bar"></i> {{ __('Plan de Cuentas') }}</h1>
        <div>
            <button class="btn btn-primary btn-sm" onclick="expandirTodo()">
                <i class="fas fa-expand"></i> {{ __('Expandir Todo') }}
            </button>
            <button class="btn btn-secondary btn-sm" onclick="contraerTodo()">
                <i class="fas fa-compress"></i> {{ __('Contraer Todo') }}
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title">{{ __('Estructura de Cuentas Contables') }}</h3>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="{{ __('Buscar cuenta...') }}" id="buscarCuenta">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th width="200">{{ __('Código') }}</th>
                            <th>{{ __('Descripción') }}</th>
                            <th width="100">{{ __('Tipo') }}</th>
                            <th width="100">{{ __('Naturaleza') }}</th>
                            <th width="80">{{ __('Nivel') }}</th>
                            <th width="100">{{ __('Imputable') }}</th>
                        </tr>
                    </thead>
                    <tbody id="cuentasBody">
                        @foreach($cuentasJerarquicas as $cuentaItem)
                            @include('contabilidad.partials.cuenta-row', ['cuentaItem' => $cuentaItem, 'nivel' => 0])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Total Cuentas') }}</span>
                    <span class="info-box-number">{{ \App\Models\CuentaContable::count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Activos') }}</span>
                    <span class="info-box-number">{{ \App\Models\CuentaContable::porTipo('activo')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-minus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Pasivos') }}</span>
                    <span class="info-box-number">{{ \App\Models\CuentaContable::porTipo('pasivo')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Imputables') }}</span>
                    <span class="info-box-number">{{ \App\Models\CuentaContable::imputables()->count() }}</span>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function expandirTodo() {
    $('.cuenta-toggle').removeClass('fa-plus').addClass('fa-minus');
    $('.cuenta-hijos').show();
}

function contraerTodo() {
    $('.cuenta-toggle').removeClass('fa-minus').addClass('fa-plus');
    $('.cuenta-hijos').hide();
}

function toggleCuenta(id) {
    const toggle = $(`#toggle-${id}`);
    const hijos = $(`#hijos-${id}`);

    if (toggle.hasClass('fa-plus')) {
        toggle.removeClass('fa-plus').addClass('fa-minus');
        hijos.show();
    } else {
        toggle.removeClass('fa-minus').addClass('fa-plus');
        hijos.hide();
    }
}

// Búsqueda en tiempo real
$('#buscarCuenta').on('input', function() {
    const busqueda = $(this).val().toLowerCase();

    $('#cuentasBody tr').each(function() {
        const codigo = $(this).find('td:first').text().toLowerCase();
        const descripcion = $(this).find('td:nth-child(2)').text().toLowerCase();

        if (codigo.includes(busqueda) || descripcion.includes(busqueda)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Inicializar contraído
$(document).ready(function() {
    contraerTodo();
});
</script>
@stop

@section('css')
<style>
.cuenta-row {
    cursor: pointer;
}

.cuenta-row:hover {
    background-color: #f8f9fa;
}

.cuenta-toggle {
    cursor: pointer;
    margin-right: 5px;
}

.nivel-1 { padding-left: 0px; font-weight: bold; }
.nivel-2 { padding-left: 20px; }
.nivel-3 { padding-left: 40px; }
.nivel-4 { padding-left: 60px; }
.nivel-5 { padding-left: 80px; }

.badge-tipo {
    font-size: 0.8em;
}
</style>
@stop
