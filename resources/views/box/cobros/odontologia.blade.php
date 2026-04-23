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
                                <tr>
                                    <td><i class="fas fa-teeth text-primary"></i> Limpieza Dental</td>
                                    <td>Profilaxis y limpieza profesional</td>
                                    <td class="text-right"><strong>$15.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="1"
                                                data-nombre="Limpieza Dental"
                                                data-precio="15000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-stethoscope text-primary"></i> Consulta General</td>
                                    <td>Revisión y diagnóstico completo</td>
                                    <td class="text-right"><strong>$8.500,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="2"
                                                data-nombre="Consulta General"
                                                data-precio="8500">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-tooth text-warning"></i> Extracción Simple</td>
                                    <td>Extracción de pieza dental</td>
                                    <td class="text-right"><strong>$12.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="3"
                                                data-nombre="Extracción Simple"
                                                data-precio="12000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-fill-drip text-info"></i> Empaste/Obturación</td>
                                    <td>Restauración con composite</td>
                                    <td class="text-right"><strong>$18.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="4"
                                                data-nombre="Empaste/Obturación"
                                                data-precio="18000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-x-ray text-dark"></i> Radiografía</td>
                                    <td>Radiografía intraoral</td>
                                    <td class="text-right"><strong>$6.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="5"
                                                data-nombre="Radiografía"
                                                data-precio="6000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-smile-beam text-warning"></i> Blanqueamiento</td>
                                    <td>Blanqueamiento dental profesional</td>
                                    <td class="text-right"><strong>$35.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="6"
                                                data-nombre="Blanqueamiento"
                                                data-precio="35000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-prescription-bottle text-danger"></i> Tratamiento de Conducto</td>
                                    <td>Endodoncia completa</td>
                                    <td class="text-right"><strong>$45.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="7"
                                                data-nombre="Tratamiento de Conducto"
                                                data-precio="45000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-crown text-warning"></i> Corona Dental</td>
                                    <td>Corona de porcelana</td>
                                    <td class="text-right"><strong>$55.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="8"
                                                data-nombre="Corona Dental"
                                                data-precio="55000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-tooth text-success"></i> Implante Dental</td>
                                    <td>Implante de titanio</td>
                                    <td class="text-right"><strong>$85.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="9"
                                                data-nombre="Implante Dental"
                                                data-precio="85000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-teeth-open text-secondary"></i> Prótesis Parcial</td>
                                    <td>Prótesis removible</td>
                                    <td class="text-right"><strong>$40.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="10"
                                                data-nombre="Prótesis Parcial"
                                                data-precio="40000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-grin-beam text-info"></i> Ortodoncia</td>
                                    <td>Brackets metálicos</td>
                                    <td class="text-right"><strong>$120.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="11"
                                                data-nombre="Ortodoncia"
                                                data-precio="120000">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-heart text-danger"></i> Periodoncia</td>
                                    <td>Tratamiento de encías</td>
                                    <td class="text-right"><strong>$28.000,00</strong></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm agregar-servicio"
                                                data-id="12"
                                                data-nombre="Periodoncia"
                                                data-precio="28000">
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

        <!-- Panel derecho: Carrito -->
        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Carrito de Servicios</h5>
                </div>
                <div class="card-body carrito-panel">
                    <div id="lista-servicios">
                        <p class="text-center text-muted">No hay servicios seleccionados</p>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4>Total: $<span id="total-general">0,00</span></h4>
                        <button class="btn btn-success btn-lg btn-block" id="btn-proceder-pago" disabled>
                            <i class="fas fa-credit-card"></i> Proceder al Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('js/modulo-pagos.js') }}"></script>

<script>
$(document).ready(function() {
    // Variables globales
    let carrito = [];
    let totalGeneral = 0;

    // Funciones para formatear números al estilo argentino
    function formatearPrecio(numero) {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numero);
    }

    function formatearNumero(numero) {
        return new Intl.NumberFormat('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numero);
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

    // Función para actualizar el carrito
    function actualizarCarrito() {
        const $listaServicios = $('#lista-servicios');

        if (carrito.length === 0) {
            $listaServicios.html('<p class="text-center text-muted">No hay servicios seleccionados</p>');
            $('#btn-proceder-pago').prop('disabled', true);
            totalGeneral = 0;
        } else {
            let html = '';
            totalGeneral = 0;

            carrito.forEach(servicio => {
                const subtotal = servicio.precio * servicio.cantidad;
                totalGeneral += subtotal;

                html += `
                    <div class="servicio-item mb-2 p-2 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${servicio.nombre}</strong><br>
                                <small>${formatearPrecio(servicio.precio)} x ${servicio.cantidad}</small>
                            </div>
                            <div class="text-right">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary btn-disminuir" data-id="${servicio.id}">-</button>
                                    <button class="btn btn-outline-secondary btn-aumentar" data-id="${servicio.id}">+</button>
                                    <button class="btn btn-outline-danger btn-eliminar" data-id="${servicio.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="mt-1">
                                    <strong>${formatearPrecio(subtotal)}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $listaServicios.html(html);
            $('#btn-proceder-pago').prop('disabled', false);
        }

        $('#total-general').text(formatearPrecio(totalGeneral));
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

    // Proceder al pago usando el módulo común
    $(document).on('click', '#btn-proceder-pago', function() {
        if (window.moduloPagos) {
            window.moduloPagos.setTipoModulo('servicios');
            window.moduloPagos.abrirModalPago(carrito, totalGeneral);
        } else {
            alert('Error: Módulo de pagos no disponible');
        }
    });
});
</script>
@stop

{{-- Incluir componente de modales de pago --}}
@include('components.payment-modals')