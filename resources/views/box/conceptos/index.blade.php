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
<div id="alerta-resultado" class="alert alert-dismissible d-none" role="alert" aria-live="polite">
    <span id="alerta-texto"></span>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>

<div class="row mb-3" id="resumen-conceptos">
    <div class="col-md-6 mb-2 mb-md-0">
        <div class="small-box bg-info h-100">
            <div class="inner">
                <h3>{{ $carreras->count() }}</h3>
                <p>Carreras configurables</p>
            </div>
            <div class="icon"><i class="fas fa-graduation-cap"></i></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-success h-100">
            <div class="inner">
                <h3>{{ $productos->count() }}</h3>
                <p>Productos con precio editable</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
        </div>
    </div>
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
                    Definí por carrera los montos, el período de cobro del bono y los tramos de interés por vencimiento para las cuotas.
                </p>

                <div class="card card-outline card-secondary mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <label for="filtro-carreras" class="mb-1">Buscar carrera</label>
                                <input id="filtro-carreras" type="text" class="form-control" placeholder="Escribí nombre o tipo de carrera..." autocomplete="off">
                            </div>
                            <div class="col-md-6 text-md-right">
                                <button type="button" id="btn-expandir-carreras" class="btn btn-outline-primary btn-sm mr-1">
                                    <i class="fas fa-angle-double-down"></i> Expandir todas
                                </button>
                                <button type="button" id="btn-colapsar-carreras" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-angle-double-up"></i> Colapsar todas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $tiposSoloBono = ['grado_odontologia'];
                @endphp

                <div id="lista-carreras">
                @forelse($carreras as $carrera)
                @php $esSoloBono = in_array($carrera->tipo_carrera, $tiposSoloBono); @endphp
                <div class="card card-outline card-primary mb-3 carrera-card"
                     data-nombre="{{ strtolower($carrera->nombre_carrera) }}"
                     data-tipo="{{ strtolower($carrera->tipo_carrera) }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <button class="btn btn-tool p-0 text-left carrera-toggle"
                                type="button"
                                data-toggle="collapse"
                                data-target="#carrera-{{ $carrera->id }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                aria-controls="carrera-{{ $carrera->id }}">
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
                        </button>
                        <span class="text-muted small">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                    <div id="carrera-{{ $carrera->id }}" class="collapse {{ $loop->first ? 'show' : '' }}">
                    <div class="card-body">
                        <form class="form-carrera" data-id="{{ $carrera->id }}" data-solo-bono="{{ $esSoloBono ? '1' : '0' }}">
                            @csrf
                            @if($esSoloBono)
                                {{-- Grado: solo precio del bono --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-info concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-graduation-cap text-info"></i> Precio del bono de grado</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group mb-1">
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
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-secondary concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-alt text-secondary"></i> Inicio cobro bono</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="date" class="form-control"
                                                       name="bono_inicio_cobro"
                                                       value="{{ optional($carrera->bono_inicio_cobro)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-secondary concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-check text-secondary"></i> Fin cobro bono</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="date" class="form-control"
                                                       name="bono_fin_cobro"
                                                       value="{{ optional($carrera->bono_fin_cobro)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block py-3">
                                            <i class="fas fa-save"></i> Guardar configuración
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Tecnicaturas: configuración completa --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-success concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-dollar-sign text-success"></i> Cuota mensual</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                           name="cuota_mensual"
                                                           value="{{ number_format($carrera->cuota_mensual, 2, '.', '') }}"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-info concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-graduation-cap text-info"></i> Bono estudiantil</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                           name="cuota_bono"
                                                           value="{{ number_format($carrera->cuota_bono ?? 0, 2, '.', '') }}"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-secondary concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-alt text-secondary"></i> Inicio cobro bono</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="date" class="form-control"
                                                       name="bono_inicio_cobro"
                                                       value="{{ optional($carrera->bono_inicio_cobro)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-secondary concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-check text-secondary"></i> Fin cobro bono</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="date" class="form-control"
                                                       name="bono_fin_cobro"
                                                       value="{{ optional($carrera->bono_fin_cobro)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-warning concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-file-signature text-warning"></i> Matrícula</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                           name="cuota_inscripcion"
                                                           value="{{ number_format($carrera->cuota_inscripcion, 2, '.', '') }}"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-secondary concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-day text-secondary"></i> 1er vencimiento</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="number" min="1" max="28" class="form-control"
                                                       name="dia_vencimiento_1"
                                                       value="{{ $carrera->dia_vencimiento_1 ?? $carrera->dia_vencimiento ?? 10 }}"
                                                       required>
                                                <small class="text-muted">Tramo 1: día 1 hasta este día.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-dark concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-calendar-week text-dark"></i> 2do vencimiento</strong>
                                            </div>
                                            <div class="card-body">
                                                <input type="number" min="1" max="31" class="form-control"
                                                       name="dia_vencimiento_2"
                                                       value="{{ $carrera->dia_vencimiento_2 ?? $carrera->dia_vencimiento ?? 20 }}"
                                                       required>
                                                <small class="text-muted">Tramo 2: desde el día siguiente al 1er vencimiento hasta este día.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-danger concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-percent text-danger"></i> Interés tramo 1</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                           name="porcentaje_recargo_1"
                                                           value="{{ number_format($carrera->porcentaje_recargo_1 ?? $carrera->porcentaje_recargo ?? 0, 2, '.', '') }}"
                                                           required>
                                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-danger concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-percent text-danger"></i> Interés tramo 2</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                           name="porcentaje_recargo_2"
                                                           value="{{ number_format($carrera->porcentaje_recargo_2 ?? $carrera->porcentaje_recargo ?? 0, 2, '.', '') }}"
                                                           required>
                                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-danger concepto-card">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="fas fa-percent text-danger"></i> Interés tramo 3</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                           name="porcentaje_recargo_3"
                                                           value="{{ number_format($carrera->porcentaje_recargo_3 ?? $carrera->porcentaje_recargo ?? 0, 2, '.', '') }}"
                                                           required>
                                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                </div>
                                                <small class="text-muted">Tramo 3: desde el día siguiente al 2do vencimiento hasta fin de mes.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-stretch">
                                        <button type="submit" class="btn btn-primary btn-block py-3 h-100">
                                            <i class="fas fa-save"></i> Guardar configuración
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                    </div>
                </div>
                @empty
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No hay carreras configuradas.
                    </div>
                @endforelse
                </div>

                <div id="sin-resultados-carreras" class="alert alert-light border d-none">
                    <i class="fas fa-search"></i> No hay carreras que coincidan con la búsqueda.
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- TAB PRODUCTOS                             --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-productos">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle"></i>
                    Actualizá el precio de venta de cada producto. Los cambios se guardan individualmente o todos a la vez.
                </p>

                <div class="card card-outline card-secondary mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <label for="filtro-productos-modificados" class="mb-0">
                                    <input type="checkbox" id="filtro-productos-modificados"> Mostrar solo modificados
                                </label>
                            </div>
                            <div class="col-md-7 text-md-right">
                                <span class="badge badge-warning mr-2" id="contador-modificados">0 modificados</span>
                                <button id="btn-guardar-todos" class="btn btn-success" disabled>
                                    <i class="fas fa-save"></i> Guardar todos los cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tabla-productos" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nombre</th>
                                <th scope="col" class="text-right">Precio actual</th>
                                <th scope="col" class="text-center">Nuevo precio</th>
                                <th scope="col" class="text-center">Acción</th>
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
        .carrera-toggle { width: 100%; }
        .concepto-card { border-width: 2px; }
        .concepto-card .card-header { border-bottom: 1px solid rgba(0,0,0,.08); }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {

    const $filtroCarreras = $('#filtro-carreras');
    const $sinResultadosCarreras = $('#sin-resultados-carreras');
    const $btnGuardarTodos = $('#btn-guardar-todos');
    const $contadorModificados = $('#contador-modificados');
    const $filtroModificados = $('#filtro-productos-modificados');

    function normalizarTexto(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    // ── Inicializar DataTable de productos ──────────────────────
    const tablaProductos = $('#tabla-productos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [3, 4], orderable: false, searchable: false }
        ]
    });

    // ── Filtro de carreras por nombre/tipo ──────────────────────
    $filtroCarreras.on('input', function () {
        const filtro = normalizarTexto($(this).val());
        let visibles = 0;

        $('.carrera-card').each(function () {
            const nombre = normalizarTexto($(this).data('nombre'));
            const tipo = normalizarTexto($(this).data('tipo'));
            const coincide = !filtro || nombre.includes(filtro) || tipo.includes(filtro);
            $(this).toggle(coincide);
            if (coincide) visibles++;
        });

        $sinResultadosCarreras.toggleClass('d-none', visibles > 0);
    });

    // ── Expandir / colapsar todas las carreras ───────────────────
    $('#btn-expandir-carreras').on('click', function () {
        $('.carrera-card:visible .collapse').collapse('show');
    });

    $('#btn-colapsar-carreras').on('click', function () {
        $('.carrera-card:visible .collapse').collapse('hide');
    });

    // ── Actualizar contador y habilitación de guardado en lote ──
    function actualizarEstadoProductos() {
        const modificados = $('.precio-modificado').length;
        $contadorModificados.text(`${modificados} modificados`);
        $btnGuardarTodos.prop('disabled', modificados === 0);
    }

    actualizarEstadoProductos();

    // ── Mostrar solo filas modificadas (sobre DataTable) ────────
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-productos') {
            return true;
        }

        if (!$filtroModificados.is(':checked')) {
            return true;
        }

        const rowNode = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
        return $(rowNode).hasClass('precio-modificado');
    });

    $filtroModificados.on('change', function () {
        tablaProductos.draw();
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
        actualizarEstadoProductos();
        tablaProductos.draw(false);
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
                    actualizarEstadoProductos();
                    tablaProductos.draw(false);
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
                    actualizarEstadoProductos();
                    tablaProductos.draw(false);
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
