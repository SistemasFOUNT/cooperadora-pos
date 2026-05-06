@extends('adminlte::page')

@section('title', 'BOX - Otros Cobros - Cooperadora Odontología UNT')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-invoice-dollar text-info"></i> Otros Cobros</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">Otros</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Lista de Otros Cobros -->
        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Otros Conceptos de Cobro</h3>
                </div>
                <div class="card-body">
                    <div id="tabla-otros-wrapper">
                        <table id="tabla-otros" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-certificate text-warning"></i> Certificado Estudiantil</td>
                                    <td>Certificado de alumno regular</td>
                                    <td class="text-right"><strong>$1.200,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="1"
                                                data-codigo="CERT001"
                                                data-nombre="Certificado Estudiantil"
                                                data-precio="1200"
                                                data-descripcion="Certificado de alumno regular">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-id-card text-primary"></i> Duplicado Carnet</td>
                                    <td>Reimpresión de carnet estudiantil</td>
                                    <td class="text-right"><strong>$800,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="2"
                                                data-codigo="CARN001"
                                                data-nombre="Duplicado Carnet"
                                                data-precio="800"
                                                data-descripcion="Reimpresión de carnet estudiantil">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-graduation-cap text-success"></i> Derecho a Examen</td>
                                    <td>Habilitación para examen final</td>
                                    <td class="text-right"><strong>$2.500,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="3"
                                                data-codigo="EXAM001"
                                                data-nombre="Derecho a Examen"
                                                data-precio="2500"
                                                data-descripcion="Habilitación para examen final">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-file-alt text-secondary"></i> Analítico de Materias</td>
                                    <td>Certificado analítico de materias aprobadas</td>
                                    <td class="text-right"><strong>$3.200,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="4"
                                                data-codigo="ANAL001"
                                                data-nombre="Analítico de Materias"
                                                data-precio="3200"
                                                data-descripcion="Certificado analítico de materias aprobadas">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-redo text-danger"></i> Recurso de Revisión</td>
                                    <td>Solicitud de revisión de examen</td>
                                    <td class="text-right"><strong>$1.800,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="5"
                                                data-codigo="RECUR001"
                                                data-nombre="Recurso de Revisión"
                                                data-precio="1800"
                                                data-descripcion="Solicitud de revisión de examen">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-exchange-alt text-info"></i> Pase de Facultad</td>
                                    <td>Trámite de cambio de carrera</td>
                                    <td class="text-right"><strong>$4.500,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="6"
                                                data-codigo="PASE001"
                                                data-nombre="Pase de Facultad"
                                                data-precio="4500"
                                                data-descripcion="Trámite de cambio de carrera">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-ban text-warning"></i> Multa por Incumplimiento</td>
                                    <td>Multa por no presentación de documentos</td>
                                    <td class="text-right"><strong>$5.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-info btn-sm agregar-concepto"
                                                data-id="7"
                                                data-codigo="MULT001"
                                                data-nombre="Multa por Incumplimiento"
                                                data-precio="5000"
                                                data-descripcion="Multa por no presentación de documentos">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito de Conceptos -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Conceptos Seleccionados</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x"></i>
                            <p class="mt-2">No hay conceptos seleccionados</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <h4 class="text-success">Total: $<span id="total-general">0,00</span></h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success" id="btn-proceder-pago" disabled data-toggle="modal" data-target="#modalPago">
                                <i class="fas fa-credit-card"></i> Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Incluir componente de modal de pago unificado --}}
    @include('components.payment-modals')
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    let carrito = [];
    let totalGeneral = 0;

    // Inicializar DataTable
    $('#tabla-otros').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
        },
        "order": [[0, "asc"]],
        "pageLength": 10
    });

    // Función para formatear precios
    function formatearPrecio(precio) {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(precio);
    }

    // Agregar concepto al carrito
    $(document).on('click', '.agregar-concepto', function() {
        const concepto = {
            id: parseInt($(this).data('id')),
            codigo: $(this).data('codigo'),
            nombre: $(this).data('nombre'),
            descripcion: $(this).data('descripcion'),
            precio: parseFloat($(this).data('precio')),
            cantidad: 1
        };

        // Verificar si ya existe en el carrito
        const conceptoExistente = carrito.find(item => item.id === concepto.id);
        if (conceptoExistente) {
            conceptoExistente.cantidad++;
        } else {
            carrito.push(concepto);
        }

        actualizarCarrito();
    });

    // Funciones del carrito
    $(document).on('click', '.btn-aumentar', function() {
        const conceptoId = parseInt($(this).data('id'));
        const concepto = carrito.find(item => item.id === conceptoId);
        if (concepto) {
            concepto.cantidad += 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-disminuir', function() {
        const conceptoId = parseInt($(this).data('id'));
        const concepto = carrito.find(item => item.id === conceptoId);
        if (concepto && concepto.cantidad > 1) {
            concepto.cantidad -= 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-eliminar', function() {
        const conceptoId = parseInt($(this).data('id'));
        carrito = carrito.filter(item => item.id !== conceptoId);
        actualizarCarrito();
    });

    // Actualizar carrito visual
    function actualizarCarrito() {
        const $listaItems = $('#carrito-items');
        let html = '';
        totalGeneral = 0;

        if (carrito.length === 0) {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <p class="mt-2">No hay conceptos seleccionados</p>
                </div>
            `;
            $('#btn-proceder-pago').prop('disabled', true);
        } else {
            carrito.forEach(concepto => {
                const subtotal = concepto.precio * concepto.cantidad;
                totalGeneral += subtotal;

                html += `
                    <div class="concepto-item mb-2 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">${concepto.nombre}</strong><br>
                                <small class="text-muted">${formatearPrecio(concepto.precio)} x ${concepto.cantidad}</small>
                            </div>
                            <div class="text-right">
                                <div class="btn-group btn-group-sm mb-1">
                                    <button class="btn btn-outline-secondary btn-disminuir" data-id="${concepto.id}">-</button>
                                    <button class="btn btn-outline-secondary btn-aumentar" data-id="${concepto.id}">+</button>
                                    <button class="btn btn-outline-danger btn-eliminar" data-id="${concepto.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div>
                                    <strong class="text-success">${formatearPrecio(subtotal)}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#btn-proceder-pago').prop('disabled', false);
        }

        $listaItems.html(html);
        $('#total-general').text(formatearPrecio(totalGeneral));

        // Actualizar totales del modal
        actualizarTotalesModal();
    }

    // Función específica para actualizar totales del modal
    function actualizarTotalesModal() {
        $('#modal-subtotal').text(formatearPrecio(totalGeneral));

        // Calcular descuento
        let descuento = 0;
        const tipoDescuento = $('input[name="tipoDescuento"]:checked').val();
        const valorDescuento = parseFloat($('#valor-descuento').val()) || 0;

        if (tipoDescuento === 'porcentaje') {
            descuento = totalGeneral * (valorDescuento / 100);
        } else if (tipoDescuento === 'valor') {
            descuento = valorDescuento;
        }

        const totalFinal = Math.max(0, totalGeneral - descuento);

        $('#modal-descuento').text(formatearPrecio(descuento));
        $('#modal-total').text(formatearPrecio(totalFinal));
    }

    // Al abrir modal, llenar resumen de items
    $('#modalPago').on('shown.bs.modal', function() {
        let resumenHtml = '';
        carrito.forEach(concepto => {
            const subtotal = concepto.precio * concepto.cantidad;
            resumenHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${concepto.nombre} x${concepto.cantidad}</span>
                    <strong>${formatearPrecio(subtotal)}</strong>
                </div>
            `;
        });
        $('#resumen-items').html(resumenHtml);
        actualizarTotalesModal();
    });

    // Sobrescribir función de actualizar totales del componente
    window.actualizarTotales = actualizarTotalesModal;

    // Procesar pago
    $('#btn-procesar-pago').on('click', function() {
        // Lógica de procesamiento de pago aquí
        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();

        console.log('Procesando pago de otros conceptos:', {
            items: carrito,
            total: totalGeneral,
            metodoPago: metodoPago,
            tipoComprobante: tipoComprobante
        });

        // Simular procesamiento exitoso
        alert('Pago de conceptos procesado exitosamente');
        carrito = [];
        actualizarCarrito();
        $('#modalPago').modal('hide');
    });
});
</script>
@stop
