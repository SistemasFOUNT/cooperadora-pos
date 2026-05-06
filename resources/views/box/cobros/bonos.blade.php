@extends('adminlte::page')

@section('title', 'BOX - Bonos Estudiantiles - Cooperadora Odontología UNT')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-ticket-alt text-warning"></i> Bonos Estudiantiles</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">Bonos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Lista de Bonos Disponibles -->
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-ticket-alt"></i> Bonos Estudiantiles Disponibles</h3>
                </div>
                <div class="card-body">
                    <div id="tabla-bonos-wrapper">
                        <table id="tabla-bonos" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tipo de Bono</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-utensils text-warning"></i> Bono Comedor Estudiantil</td>
                                    <td>10 almuerzos en el comedor universitario</td>
                                    <td class="text-right"><strong>$12.500,00</strong></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm agregar-bono"
                                                data-id="1"
                                                data-codigo="BONO001"
                                                data-nombre="Bono Comedor Estudiantil"
                                                data-precio="12500"
                                                data-descripcion="10 almuerzos en el comedor universitario">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-book text-primary"></i> Bono Biblioteca</td>
                                    <td>Préstamo extendido de libros por 6 meses</td>
                                    <td class="text-right"><strong>$3.500,00</strong></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm agregar-bono"
                                                data-id="2"
                                                data-codigo="BONO002"
                                                data-nombre="Bono Biblioteca"
                                                data-precio="3500"
                                                data-descripcion="Préstamo extendido de libros por 6 meses">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-parking text-info"></i> Bono Estacionamiento</td>
                                    <td>Estacionamiento en campus universitario - mensual</td>
                                    <td class="text-right"><strong>$2.800,00</strong></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm agregar-bono"
                                                data-id="3"
                                                data-codigo="BONO003"
                                                data-nombre="Bono Estacionamiento"
                                                data-precio="2800"
                                                data-descripcion="Estacionamiento en campus universitario - mensual">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-dumbbell text-success"></i> Bono Gimnasio</td>
                                    <td>Acceso al gimnasio universitario por 3 meses</td>
                                    <td class="text-right"><strong>$4.200,00</strong></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm agregar-bono"
                                                data-id="4"
                                                data-codigo="BONO004"
                                                data-nombre="Bono Gimnasio"
                                                data-precio="4200"
                                                data-descripcion="Acceso al gimnasio universitario por 3 meses">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-print text-secondary"></i> Bono Impresión</td>
                                    <td>500 hojas de impresión en blanco y negro</td>
                                    <td class="text-right"><strong>$1.800,00</strong></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm agregar-bono"
                                                data-id="5"
                                                data-codigo="BONO005"
                                                data-nombre="Bono Impresión"
                                                data-precio="1800"
                                                data-descripcion="500 hojas de impresión en blanco y negro">
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

        <!-- Carrito de Bonos -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Carrito de Bonos</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x"></i>
                            <p class="mt-2">No hay bonos seleccionados</p>
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
    $('#tabla-bonos').DataTable({
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

    // Agregar bono al carrito
    $(document).on('click', '.agregar-bono', function() {
        const bono = {
            id: parseInt($(this).data('id')),
            codigo: $(this).data('codigo'),
            nombre: $(this).data('nombre'),
            descripcion: $(this).data('descripcion'),
            precio: parseFloat($(this).data('precio')),
            cantidad: 1
        };

        // Verificar si ya existe en el carrito
        const bonoExistente = carrito.find(item => item.id === bono.id);
        if (bonoExistente) {
            bonoExistente.cantidad++;
        } else {
            carrito.push(bono);
        }

        actualizarCarrito();
    });

    // Funciones del carrito
    $(document).on('click', '.btn-aumentar', function() {
        const bonoId = parseInt($(this).data('id'));
        const bono = carrito.find(item => item.id === bonoId);
        if (bono) {
            bono.cantidad += 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-disminuir', function() {
        const bonoId = parseInt($(this).data('id'));
        const bono = carrito.find(item => item.id === bonoId);
        if (bono && bono.cantidad > 1) {
            bono.cantidad -= 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-eliminar', function() {
        const bonoId = parseInt($(this).data('id'));
        carrito = carrito.filter(item => item.id !== bonoId);
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
                    <p class="mt-2">No hay bonos seleccionados</p>
                </div>
            `;
            $('#btn-proceder-pago').prop('disabled', true);
        } else {
            carrito.forEach(bono => {
                const subtotal = bono.precio * bono.cantidad;
                totalGeneral += subtotal;

                html += `
                    <div class="bono-item mb-2 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">${bono.nombre}</strong><br>
                                <small class="text-muted">${formatearPrecio(bono.precio)} x ${bono.cantidad}</small>
                            </div>
                            <div class="text-right">
                                <div class="btn-group btn-group-sm mb-1">
                                    <button class="btn btn-outline-secondary btn-disminuir" data-id="${bono.id}">-</button>
                                    <button class="btn btn-outline-secondary btn-aumentar" data-id="${bono.id}">+</button>
                                    <button class="btn btn-outline-danger btn-eliminar" data-id="${bono.id}">
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
        carrito.forEach(bono => {
            const subtotal = bono.precio * bono.cantidad;
            resumenHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${bono.nombre} x${bono.cantidad}</span>
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

        console.log('Procesando pago de bonos:', {
            items: carrito,
            total: totalGeneral,
            metodoPago: metodoPago,
            tipoComprobante: tipoComprobante
        });

        // Simular procesamiento exitoso
        alert('Pago de bonos procesado exitosamente');
        carrito = [];
        actualizarCarrito();
        $('#modalPago').modal('hide');
    });
});
</script>
@stop
