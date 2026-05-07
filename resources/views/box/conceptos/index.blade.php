@extends('adminlte::page')

@section('title', 'BOX - Conceptos y Precios')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-tags text-primary"></i> Conceptos y Precios</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item active">Conceptos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

{{-- Alerta de resultado --}}
<div id="alerta-resultado" class="alert alert-dismissible d-none" role="alert">
    <span id="alerta-texto"></span>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>

<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="conceptos-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-cuotas" role="tab">
                    <i class="fas fa-user-graduate"></i> Cuotas y Bonos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-productos" role="tab">
                    <i class="fas fa-shopping-bag"></i> Productos
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ══════════════════════════════════════════ --}}
            {{-- TAB CUOTAS Y BONOS POR CARRERA            --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab-cuotas">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle"></i>
                    Definí los montos de cuota mensual, bono estudiantil, matrícula, la fecha de vencimiento y el recargo por mora para cada carrera.
                </p>

                @php
                    $tiposSoloBono = ['grado_odontologia'];
                @endphp

                @forelse($carreras as $carrera)
                @php $esSoloBono = in_array($carrera->tipo_carrera, $tiposSoloBono); @endphp
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-graduation-cap text-primary"></i>
                            {{ $carrera->nombre_carrera }}
                            @if($esSoloBono)
                                <span class="badge badge-info ml-2">Solo bono</span>
                            @endif
                            @if(!$carrera->activo)
                                <span class="badge badge-secondary ml-2">Inactiva</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <form class="form-carrera" data-id="{{ $carrera->id }}" data-solo-bono="{{ $esSoloBono ? '1' : '0' }}">
                            @csrf
                            @if($esSoloBono)
                                {{-- Grado: solo precio del bono --}}
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label><i class="fas fa-graduation-cap text-info"></i> Precio del bono de grado</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control"
                                                       name="cuota_bono"
                                                       value="{{ number_format($carrera->cuota_bono ?? 0, 2, '.', '') }}"
                                                       required>
                                            </div>
                                            <small class="text-muted">Precio único del bono. No aplica cuotas mensuales.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Tecnicaturas: configuración completa --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-dollar-sign text-success"></i> Cuota mensual</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                       name="cuota_mensual"
                                                       value="{{ number_format($carrera->cuota_mensual, 2, '.', '') }}"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-graduation-cap text-info"></i> Bono estudiantil</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                       name="cuota_bono"
                                                       value="{{ number_format($carrera->cuota_bono ?? 0, 2, '.', '') }}"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-file-signature text-warning"></i> Matrícula</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                       name="cuota_inscripcion"
                                                       value="{{ number_format($carrera->cuota_inscripcion, 2, '.', '') }}"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar-day text-secondary"></i> Día de vencimiento</label>
                                            <input type="number" min="1" max="28" class="form-control"
                                                   name="dia_vencimiento"
                                                   value="{{ $carrera->dia_vencimiento ?? 15 }}"
                                                   required>
                                            <small class="text-muted">Día del mes (1-28)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-clock text-muted"></i> Días de gracia</label>
                                            <input type="number" min="0" max="30" class="form-control"
                                                   name="dias_gracia"
                                                   value="{{ $carrera->dias_gracia ?? 5 }}"
                                                   required>
                                            <small class="text-muted">Días antes de aplicar recargo</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-percent text-danger"></i> Recargo por mora</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                       name="porcentaje_recargo"
                                                       value="{{ number_format($carrera->porcentaje_recargo ?? 10, 2, '.', '') }}"
                                                       required>
                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-group w-100">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-save"></i> Guardar configuración
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
                @empty
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No hay carreras configuradas.
                    </div>
                @endforelse
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- TAB PRODUCTOS                             --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-productos">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle"></i>
                    Actualizá el precio de venta de cada producto. Los cambios se guardan individualmente o todos a la vez.
                </p>

                <div class="mb-3 text-right">
                    <button id="btn-guardar-todos" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar todos los cambios
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="tabla-productos" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th class="text-right">Precio actual</th>
                                <th class="text-center">Nuevo precio</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $producto)
                            <tr data-id="{{ $producto->id }}">
                                <td><code>{{ $producto->code ?? '-' }}</code></td>
                                <td>{{ $producto->name }}</td>
                                <td class="text-right precio-actual">
                                    ${{ number_format($producto->price, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <input type="number" step="0.01" min="0"
                                           class="form-control form-control-sm precio-input text-right"
                                           value="{{ number_format($producto->price, 2, '.', '') }}"
                                           data-original="{{ number_format($producto->price, 2, '.', '') }}">
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btn-guardar-producto"
                                            data-id="{{ $producto->id }}">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay productos registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .precio-input { max-width: 140px; margin: 0 auto; }
        tr.precio-modificado td { background-color: #fff9e6 !important; }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {

    // ── Inicializar DataTable de productos ──────────────────────
    $('#tabla-productos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [3, 4], orderable: false, searchable: false }
        ]
    });

    // ── Marcar fila modificada al cambiar precio ─────────────────
    $(document).on('input', '.precio-input', function () {
        const $tr = $(this).closest('tr');
        const original = $(this).data('original');
        if ($(this).val() !== String(original)) {
            $tr.addClass('precio-modificado');
        } else {
            $tr.removeClass('precio-modificado');
        }
    });

    // ── Guardar producto individual ─────────────────────────────
    $(document).on('click', '.btn-guardar-producto', function () {
        const $tr   = $(this).closest('tr');
        const id    = $tr.data('id');
        const price = $tr.find('.precio-input').val();

        $.ajax({
            url: `/box/conceptos/producto/${id}`,
            method: 'PUT',
            data: { _token: '{{ csrf_token() }}', price },
            success: function (resp) {
                if (resp.success) {
                    const formatted = parseFloat(price).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                    $tr.find('.precio-actual').text('$' + formatted);
                    $tr.find('.precio-input').data('original', price);
                    $tr.removeClass('precio-modificado');
                    mostrarAlerta('success', resp.mensaje);
                }
            },
            error: function () {
                mostrarAlerta('danger', 'Error al actualizar el precio.');
            }
        });
    });

    // ── Guardar todos los productos modificados ─────────────────
    $('#btn-guardar-todos').on('click', function () {
        const productos = [];
        $('.precio-modificado').each(function () {
            productos.push({
                id:    $(this).data('id'),
                price: $(this).find('.precio-input').val()
            });
        });

        if (productos.length === 0) {
            mostrarAlerta('info', 'No hay precios modificados para guardar.');
            return;
        }

        $.ajax({
            url: '/box/conceptos/productos/lote',
            method: 'PUT',
            data: { _token: '{{ csrf_token() }}', productos },
            success: function (resp) {
                if (resp.success) {
                    $('.precio-modificado').each(function () {
                        const price    = $(this).find('.precio-input').val();
                        const formatted = parseFloat(price).toLocaleString('es-AR', { minimumFractionDigits: 2 });
                        $(this).find('.precio-actual').text('$' + formatted);
                        $(this).find('.precio-input').data('original', price);
                        $(this).removeClass('precio-modificado');
                    });
                    mostrarAlerta('success', resp.mensaje);
                }
            },
            error: function () {
                mostrarAlerta('danger', 'Error al guardar los precios.');
            }
        });
    });

    // ── Guardar configuración de carrera ────────────────────────
    $(document).on('submit', '.form-carrera', function (e) {
        e.preventDefault();
        const $form = $(this);
        const id    = $form.data('id');
        const data  = $form.serialize();

        $.ajax({
            url: `/box/conceptos/carrera/${id}`,
            method: 'PUT',
            data: data + '&_method=PUT',
            success: function (resp) {
                if (resp.success) {
                    mostrarAlerta('success', resp.mensaje);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = 'Error al guardar la configuración.';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                mostrarAlerta('danger', msg);
            }
        });
    });

    // ── Helper: mostrar alerta ──────────────────────────────────
    function mostrarAlerta(tipo, mensaje) {
        const $alert = $('#alerta-resultado');
        $alert
            .removeClass('d-none alert-success alert-danger alert-info alert-warning')
            .addClass('alert-' + tipo);
        $('#alerta-texto').html(mensaje);
        $alert.show();
        $('html, body').animate({ scrollTop: 0 }, 300);
        setTimeout(() => $alert.fadeOut(400, () => $alert.addClass('d-none').show()), 5000);
    }

});
</script>
@stop
