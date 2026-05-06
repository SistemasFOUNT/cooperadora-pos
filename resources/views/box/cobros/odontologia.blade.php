@extends('adminlte::page')

@section('title', 'BOX - Consultorios Odontológicos')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .carrito-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        max-height: 70vh;
        overflow-y: auto;
    }
    .servicio-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .servicio-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .precio-destacado {
        font-size: 1.2em;
        font-weight: bold;
    }
    .btn-agregar {
        border-radius: 20px;
    }
    @media (max-width: 768px) {
        .col-md-4 {
            margin-bottom: 15px;
        }
    }
    .servicio-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 5px;
    }
    .btn-disminuir, .btn-aumentar, .btn-eliminar {
        padding: 2px 8px;
        font-size: 0.8em;
    }

    /* DATATABLES - Mejoras de bordes */
    table.dataTable {
        border: 1px solid #6c757d !important;
        border-radius: 4px;
    }

    table.dataTable thead th,
    table.dataTable thead td {
        border-bottom: 2px solid #6c757d !important;
        background-color: #f8f9fa !important;
    }

    table.dataTable tbody td {
        border-top: 1px solid #6c757d !important;
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
    }

    table.dataTable tbody tr:hover td {
        background-color: #f1f3f4 !important;
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tooth text-primary"></i> Consultorios Odontológicos</h1>
        <div>
            <span class="badge badge-info p-2">
                <i class="fas fa-calendar"></i> {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Panel izquierdo: Lista de servicios con DataTables -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Servicios Odontológicos Disponibles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="serviciosTable" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicios as $servicio)
                                <tr>
                                    <td>
                                        <i class="fas fa-{{ $servicio->category == 'dental_treatment' ? 'tooth' : ($servicio->category == 'laboratory' ? 'flask' : ($servicio->category == 'student_fee' ? 'graduation-cap' : ($servicio->category == 'postgraduate_fee' ? 'user-graduate' : 'medical-kit'))) }} text-primary"></i>
                                        {{ $servicio->name }}
                                    </td>
                                    <td>{{ $servicio->description ?? 'Servicio odontológico profesional' }}</td>
                                    <td class="text-right"><strong>${{ number_format($servicio->price, 2) }}</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="{{ $servicio->id }}"
                                                data-codigo="{{ $servicio->code }}"
                                                data-nombre="{{ $servicio->name }}"
                                                data-precio="{{ $servicio->price }}"
                                                data-track-stock="{{ $servicio->track_stock ? 'true' : 'false' }}"
                                                data-stock="{{ $servicio->stock ?? 0 }}">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> No hay servicios odontológicos disponibles
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito de Servicios -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Carrito de Servicios</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x"></i>
                            <p class="mt-2">No hay servicios seleccionados</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <h4 class="text-success">Total: $<span id="total-carrito">0,00</span></h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success" id="btn-proceder-pago" disabled>
                                <i class="fas fa-credit-card"></i> Proceder al Pago
            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Incluir componente de modal de pago unificado --}}
@include('components.payment-modals')
@stop

@section('js')
$(document).ready(function() {
    // Variables globales
    let carrito = [];
    let totalGeneral = 0;

    // Función para formatear precios (estándar obligatorio)
    function formatearPrecio(precio) {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(precio);
    }

    // Inicializar DataTable
    $('#serviciosTable').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 to 0 of 0 entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']]
    });

    // Agregar servicio al carrito
    $(document).on('click', '.agregar-servicio', function() {
        const servicioId = parseInt($(this).data('id'));
        const servicioNombre = $(this).data('nombre');
        const servicioPrecio = parseFloat($(this).data('precio'));

        const servicioExistente = carrito.find(item => item.id === servicioId);

        if (servicioExistente) {
            servicioExistente.cantidad += 1;
        } else {
            carrito.push({
                id: servicioId,
                nombre: servicioNombre,
                precio: servicioPrecio,
                cantidad: 1
            });
        }

        actualizarCarrito();
    });

// Actualizar carrito visual (estándar obligatorio)
    function actualizarCarrito() {
        const $listaItems = $('#carrito-items');
        let html = '';
        totalGeneral = 0;

        if (carrito.length === 0) {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <p class="mt-2">No hay servicios seleccionados</p>
                </div>
            `;
            $('#btn-proceder-pago').prop('disabled', true);
        } else {
            carrito.forEach(servicio => {
                const subtotal = servicio.precio * servicio.cantidad;
                totalGeneral += subtotal;

                html += `
                    <div class="servicio-item mb-2 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">${servicio.nombre}</strong><br>
                                <small class="text-muted">${formatearPrecio(servicio.precio)} x ${servicio.cantidad}</small>
                            </div>
                            <div class="text-right">
                                <div class="btn-group btn-group-sm mb-1">
                                    <button class="btn btn-outline-secondary btn-disminuir" data-id="${servicio.id}">-</button>
                                    <button class="btn btn-outline-secondary btn-aumentar" data-id="${servicio.id}">+</button>
                                    <button class="btn btn-outline-danger btn-eliminar" data-id="${servicio.id}">
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
        $('#total-carrito').text(formatearPrecio(totalGeneral));

        // Actualizar totales del modal (obligatorio)
        actualizarTotalesModal();
    }

    // Eventos del carrito
    $(document).on('click', '.btn-aumentar', function() {
        const servicioId = parseInt($(this).data('id'));
        const servicio = carrito.find(item => item.id === servicioId);
        if (servicio) {
            servicio.cantidad += 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-disminuir', function() {
        const servicioId = parseInt($(this).data('id'));
        const servicio = carrito.find(item => item.id === servicioId);
        if (servicio && servicio.cantidad > 1) {
            servicio.cantidad -= 1;
            actualizarCarrito();
        }
    });

    $(document).on('click', '.btn-eliminar', function() {
        const servicioId = parseInt($(this).data('id'));
        carrito = carrito.filter(item => item.id !== servicioId);
        actualizarCarrito();
    });

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
        carrito.forEach(servicio => {
            const subtotal = servicio.precio * servicio.cantidad;
            resumenHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${servicio.nombre} x${servicio.cantidad}</span>
                    <strong>${formatearPrecio(subtotal)}</strong>
                </div>
            `;
        });
        $('#resumen-items').html(resumenHtml);
        actualizarTotalesModal();
    });

    // Sobrescribir función de actualizar totales del componente
    window.actualizarTotales = actualizarTotalesModal;

    // Procesar pago usando el modal unificado
    $('#btn-proceder-pago').on('click', function() {
        $('#modalPago').modal('show');
    });

    // Procesar pago
    $('#btn-procesar-pago').on('click', function() {
        // Lógica de procesamiento de pago aquí
        const metodoPago = $('input[name="metodoPago"]:checked').val();
        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();

        console.log('Procesando pago de servicios odontológicos:', {
            items: carrito,
            total: totalGeneral,
            metodoPago: metodoPago,
            tipoComprobante: tipoComprobante
        });

        // Simular procesamiento exitoso
        alert('Pago de servicios odontológicos procesado exitosamente');
        carrito = [];
        actualizarCarrito();
        $('#modalPago').modal('hide');
    });
});
@stop
