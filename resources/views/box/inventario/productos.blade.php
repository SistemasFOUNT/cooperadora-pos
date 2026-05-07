@extends('adminlte::page')

@section('title', 'BOX - Editar Productos')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-box-open text-primary"></i> Editar Productos</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item active">Editar Productos</li>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Lista de productos
        </h3>
        <div class="card-tools">
            <span class="badge badge-info" id="badge-modificados" style="display:none;">
                <span id="count-modificados">0</span> modificados
            </span>
            <button id="btn-guardar-todos" class="btn btn-success btn-sm ml-2" style="display:none;">
                <i class="fas fa-save"></i> Guardar todos los cambios
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabla-productos" class="table table-striped table-hover table-sm mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:120px">Código</th>
                        <th>Nombre</th>
                        <th style="width:120px" class="text-right">Precio</th>
                        <th style="width:100px" class="text-center">Stock</th>
                        <th style="width:90px" class="text-center">Estado</th>
                        <th style="width:130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr data-id="{{ $producto->id }}">
                        <td>
                            <input type="text" class="form-control form-control-sm campo-editable"
                                   name="code"
                                   value="{{ $producto->code }}"
                                   data-original="{{ $producto->code }}"
                                   placeholder="Sin código"
                                   maxlength="100">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm campo-editable"
                                   name="name"
                                   value="{{ $producto->name }}"
                                   data-original="{{ $producto->name }}"
                                   required
                                   maxlength="255">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0"
                                       class="form-control form-control-sm campo-editable text-right"
                                       name="price"
                                       value="{{ number_format($producto->price, 2, '.', '') }}"
                                       data-original="{{ number_format($producto->price, 2, '.', '') }}"
                                       required>
                            </div>
                        </td>
                        <td>
                            <input type="number" min="0"
                                   class="form-control form-control-sm campo-editable text-center"
                                   name="stock"
                                   value="{{ $producto->stock ?? 0 }}"
                                   data-original="{{ $producto->stock ?? 0 }}">
                        </td>
                        <td class="text-center">
                            @if($producto->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary btn-guardar-fila" title="Guardar">
                                <i class="fas fa-save"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar-fila ml-1"
                                    title="Eliminar"
                                    data-nombre="{{ $producto->name }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x d-block mb-2"></i>
                            No hay productos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal de confirmación de ELIMINACIÓN --}}
<div class="modal fade" id="modal-confirmar-eliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Eliminar producto</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>¿Seguro que querés eliminar <strong id="txt-nombre-eliminar"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">
                    <i class="fas fa-trash"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación para guardado masivo --}}
<div class="modal fade" id="modal-confirmar-lote" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-save"></i> Confirmar guardado</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Vas a guardar <strong id="txt-cantidad-modificados"></strong> productos modificados.</p>
                <p class="text-muted">¿Confirmar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-lote">
                    <i class="fas fa-check"></i> Sí, guardar todo
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .campo-editable { border: 1px solid transparent; background: transparent; transition: all .15s; }
        .campo-editable:focus { border-color: #80bdff; background: #fff; }
        tr.fila-modificada td { background-color: #fff9e6 !important; }
        tr.fila-guardada td  { background-color: #e8f5e9 !important; transition: background-color 1.5s; }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {

    const CSRF = '{{ csrf_token() }}';

    // ── DataTable ────────────────────────────────────────────────
    const dt = $('#tabla-productos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [0,1,2,3,4,5], orderable: false, searchable: false },
            { targets: [1], orderable: true, searchable: true },
        ]
    });

    // ── Detectar cambios en campos ───────────────────────────────
    $(document).on('input', '.campo-editable', function () {
        const $tr = $(this).closest('tr');
        const estaModificada = $tr.find('.campo-editable').toArray().some(el => {
            return $(el).val() != $(el).data('original');
        });
        $tr.toggleClass('fila-modificada', estaModificada);
        actualizarContador();
    });

    function actualizarContador() {
        const n = $('.fila-modificada').length;
        if (n > 0) {
            $('#count-modificados').text(n);
            $('#badge-modificados, #btn-guardar-todos').show();
        } else {
            $('#badge-modificados, #btn-guardar-todos').hide();
        }
    }

    // ── Guardar fila individual ──────────────────────────────────
    $(document).on('click', '.btn-guardar-fila', function () {
        const $tr = $(this).closest('tr');
        guardarFila($tr, true);
    });

    function guardarFila($tr, showAlert) {
        const id    = $tr.data('id');
        const data  = {
            _token : CSRF,
            name   : $tr.find('[name=name]').val(),
            code   : $tr.find('[name=code]').val(),
            price  : $tr.find('[name=price]').val(),
            stock  : $tr.find('[name=stock]').val(),
        };

        if (!data.name.trim()) {
            mostrarAlerta('warning', 'El nombre del producto no puede estar vacío.');
            return Promise.reject();
        }

        return $.ajax({
            url    : `/box/inventario/producto/${id}`,
            method : 'PUT',
            data   : data,
        }).done(function (resp) {
            if (resp.success) {
                // Actualizar data-original para no marcar como modificado de nuevo
                $tr.find('.campo-editable').each(function () {
                    $(this).data('original', $(this).val());
                });
                $tr.removeClass('fila-modificada').addClass('fila-guardada');
                setTimeout(() => $tr.removeClass('fila-guardada'), 2000);
                actualizarContador();
                if (showAlert) mostrarAlerta('success', resp.mensaje);
            }
        }).fail(function (xhr) {
            const errors = xhr.responseJSON?.errors;
            const msg = errors
                ? Object.values(errors).flat().join('<br>')
                : 'Error al guardar el producto.';
            mostrarAlerta('danger', msg);
        });
    }

    // ── Guardar todos los modificados ────────────────────────────
    $('#btn-guardar-todos').on('click', function () {
        const n = $('.fila-modificada').length;
        $('#txt-cantidad-modificados').text(n + (n === 1 ? ' producto' : ' productos'));
        $('#modal-confirmar-lote').modal('show');
    });

    $('#btn-confirmar-lote').on('click', function () {
        $('#modal-confirmar-lote').modal('hide');
        const $filas = $('.fila-modificada').toArray();
        const promesas = $filas.map(fila => guardarFila($(fila), false));
        Promise.allSettled(promesas).then(function (resultados) {
            const ok      = resultados.filter(r => r.status === 'fulfilled').length;
            const errores = resultados.filter(r => r.status === 'rejected').length;
            if (errores === 0) {
                mostrarAlerta('success', `${ok} producto(s) guardado(s) correctamente.`);
            } else {
                mostrarAlerta('warning', `${ok} guardado(s), ${errores} con error.`);
            }
        });
    });

    // ── Eliminar producto ─────────────────────────────────────────
    let $filaAEliminar = null;

    $(document).on('click', '.btn-eliminar-fila', function () {
        $filaAEliminar = $(this).closest('tr');
        const nombre = $(this).data('nombre');
        $('#txt-nombre-eliminar').text(nombre);
        $('#modal-confirmar-eliminar').modal('show');
    });

    $('#btn-confirmar-eliminar').on('click', function () {
        if (!$filaAEliminar) return;
        const id = $filaAEliminar.data('id');
        $('#modal-confirmar-eliminar').modal('hide');

        $.ajax({
            url    : `/box/inventario/producto/${id}`,
            method : 'DELETE',
            data   : { _token: CSRF },
        }).done(function (resp) {
            if (resp.success) {
                dt.row($filaAEliminar[0]).remove().draw();
                $filaAEliminar = null;
                actualizarContador();
                mostrarAlerta('success', resp.mensaje);
            }
        }).fail(function () {
            mostrarAlerta('danger', 'No se pudo eliminar el producto.');
        });
    });

    // ── Helper alerta ────────────────────────────────────────────
    function mostrarAlerta(tipo, mensaje) {
        const $a = $('#alerta-resultado');
        $a.removeClass('d-none alert-success alert-danger alert-info alert-warning')
          .addClass('alert-' + tipo);
        $('#alerta-texto').html(mensaje);
        $a.show();
        $('html, body').animate({ scrollTop: 0 }, 300);
        setTimeout(() => $a.fadeOut(400, () => $a.addClass('d-none').show()), 5000);
    }

});
</script>
@stop
