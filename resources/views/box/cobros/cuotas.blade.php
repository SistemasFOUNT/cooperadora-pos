@extends('adminlte::page')

@section('title', 'BOX - Cobros de Cuotas Estudiantiles - Cooperadora Odontología UNT')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate text-primary"></i> Cobros de Cuotas Estudiantiles</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cobros</a></li>
                <li class="breadcrumb-item active">Cuotas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Lista de Estudiantes -->
        <div class="col-md-8">
            <div class="card card-primary" id="card-lista-estudiantes">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Estudiantes de Tecnicaturas</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-estudiantes" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Legajo</th>
                                    <th>Nombre</th>
                                    <th>DNI</th>
                                    <th>Carrera</th>
                                    <th>Año</th>
                                    <th>Estado</th>
                                    <th>Cuotas Pendientes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="estudiantes-tbody">
                                <!-- Se llenan dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-info" id="card-estudiante" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Datos del Estudiante Seleccionado</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-light" id="btn-volver-lista">
                            <i class="fas fa-arrow-left"></i> Volver a la lista
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nombre:</strong> <span id="estudiante-nombre"></span><br>
                            <strong>DNI:</strong> <span id="estudiante-dni"></span><br>
                            <strong>Legajo:</strong> <span id="estudiante-legajo"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Carrera:</strong> <span id="estudiante-carrera"></span><br>
                            <strong>Año:</strong> <span id="estudiante-año"></span><br>
                            <strong>Estado:</strong> <span id="estudiante-estado" class="badge badge-success">Regular</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-warning" id="card-cuotas" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Cuotas Adeudadas</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-cuotas" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th><input type="checkbox" id="select-all-cuotas"></th>
                                    <th>Período</th>
                                    <th>Vencimiento</th>
                                    <th>Importe</th>
                                    <th>Recargo</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="cuotas-tbody">
                                <!-- Se llenarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito de Cuotas Seleccionadas -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Cuotas Seleccionadas</h3>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <div id="carrito-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x"></i>
                            <p class="mt-2">No hay cuotas seleccionadas</p>
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

// Datos demo de estudiantes - 25 estudiantes para demostrar DataTables
    const estudiantesDemo = [
        {
            id: 1,
            nombre: "Ana María Rodríguez",
            dni: "35678901",
            legajo: "TPD-2023-001",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 1, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 25000, recargo: 2500, vencida: true },
                { id: 2, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 25000, recargo: 1250, vencida: true }
            ]
        },
        {
            id: 2,
            nombre: "Carlos Eduardo Fernández",
            dni: "33456789",
            legajo: "TPD-2023-002",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 3, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 25000, recargo: 0, vencida: false },
                { id: 4, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 25000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 3,
            nombre: "María Elena González",
            dni: "28987654",
            legajo: "TPD-2022-015",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "3°",
            estado: "Regular",
            cuotas: [
                { id: 5, periodo: "Febrero 2024", vencimiento: "2024-02-15", importe: 25000, recargo: 3750, vencida: true }
            ]
        },
        {
            id: 4,
            nombre: "Miguel Ángel López",
            dni: "32567890",
            legajo: "TAD-2023-008",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 6, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 20000, recargo: 2000, vencida: true },
                { id: 7, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 20000, recargo: 1000, vencida: true }
            ]
        },
        {
            id: 5,
            nombre: "Laura Beatriz Silva",
            dni: "29876543",
            legajo: "TAD-2023-012",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 8, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 6,
            nombre: "Roberto Carlos Méndez",
            dni: "31234567",
            legajo: "TPD-2023-003",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 9, periodo: "Enero 2024", vencimiento: "2024-01-15", importe: 25000, recargo: 5000, vencida: true },
                { id: 10, periodo: "Febrero 2024", vencimiento: "2024-02-15", importe: 25000, recargo: 3750, vencida: true }
            ]
        },
        {
            id: 7,
            nombre: "Patricia Alejandra Ruiz",
            dni: "27654321",
            legajo: "TAD-2022-005",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 11, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 8,
            nombre: "Fernando José Castro",
            dni: "34567890",
            legajo: "TPD-2023-004",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 12, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 25000, recargo: 2500, vencida: true }
            ]
        },
        {
            id: 9,
            nombre: "Claudia Beatriz Morales",
            dni: "26789012",
            legajo: "TAD-2023-009",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 13, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 20000, recargo: 0, vencida: false },
                { id: 14, periodo: "Junio 2024", vencimiento: "2024-06-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 10,
            nombre: "Diego Alejandro Vargas",
            dni: "30123456",
            legajo: "TPD-2022-018",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "3°",
            estado: "Regular",
            cuotas: [
                { id: 15, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 25000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 11,
            nombre: "Valentina Rosa Herrera",
            dni: "32987654",
            legajo: "TAD-2023-010",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 16, periodo: "Febrero 2024", vencimiento: "2024-02-15", importe: 20000, recargo: 3000, vencida: true }
            ]
        },
        {
            id: 12,
            nombre: "Sebastián Matías Torres",
            dni: "29456789",
            legajo: "TPD-2023-005",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 17, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 25000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 13,
            nombre: "Natalia Andrea Jiménez",
            dni: "31876543",
            legajo: "TAD-2023-011",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 18, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 20000, recargo: 2000, vencida: true }
            ]
        },
        {
            id: 14,
            nombre: "Andrés Felipe Ramírez",
            dni: "28345678",
            legajo: "TPD-2022-020",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 19, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 25000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 15,
            nombre: "Carolina Isabel Mendoza",
            dni: "30234567",
            legajo: "TAD-2022-007",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 20, periodo: "Enero 2024", vencimiento: "2024-01-15", importe: 20000, recargo: 4000, vencida: true }
            ]
        },
        {
            id: 16,
            nombre: "Maximiliano Cruz Peña",
            dni: "33654321",
            legajo: "TPD-2023-006",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 21, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 25000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 17,
            nombre: "Camila Sofia Ortega",
            dni: "27123456",
            legajo: "TAD-2023-013",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 22, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 18,
            nombre: "Joaquín Gabriel Sosa",
            dni: "32456789",
            legajo: "TPD-2022-025",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 23, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 25000, recargo: 2500, vencida: true }
            ]
        },
        {
            id: 19,
            nombre: "Isabella Victoria Luna",
            dni: "29789012",
            legajo: "TAD-2022-008",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 24, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 20,
            nombre: "Nicolás Emanuel Paz",
            dni: "31567890",
            legajo: "TPD-2023-007",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 25, periodo: "Febrero 2024", vencimiento: "2024-02-15", importe: 25000, recargo: 3750, vencida: true }
            ]
        },
        {
            id: 21,
            nombre: "Agustina Celeste Rojas",
            dni: "28678901",
            legajo: "TAD-2023-014",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 26, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 22,
            nombre: "Mateo Benjamín Vera",
            dni: "30987654",
            legajo: "TPD-2022-030",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 27, periodo: "Marzo 2024", vencimiento: "2024-03-15", importe: 25000, recargo: 2500, vencida: true }
            ]
        },
        {
            id: 23,
            nombre: "Martina Esperanza Aguirre",
            dni: "33234567",
            legajo: "TAD-2023-015",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 28, periodo: "Mayo 2024", vencimiento: "2024-05-15", importe: 20000, recargo: 0, vencida: false }
            ]
        },
        {
            id: 24,
            nombre: "Facundo Ignacio Molina",
            dni: "27890123",
            legajo: "TPD-2023-008",
            carrera: "Tecnicatura Universitaria en Prótesis Dental",
            año: "1°",
            estado: "Regular",
            cuotas: [
                { id: 29, periodo: "Enero 2024", vencimiento: "2024-01-15", importe: 25000, recargo: 5000, vencida: true }
            ]
        },
        {
            id: 25,
            nombre: "Antonella Milagros Cabrera",
            dni: "32123456",
            legajo: "TAD-2022-009",
            carrera: "Tecnicatura Universitaria en Asistencia Dental",
            año: "2°",
            estado: "Regular",
            cuotas: [
                { id: 30, periodo: "Abril 2024", vencimiento: "2024-04-15", importe: 20000, recargo: 0, vencida: false }
            ]
        }
    ];

// Llenar tabla de estudiantes con DataTables
    function llenarTablaEstudiantes() {
        const tbody = $('#estudiantes-tbody');
        if (!tbody.length) return;

        let html = '';
        estudiantesDemo.forEach(estudiante => {
            const esProtesis = estudiante.carrera.includes('Prótesis');
            const badge = esProtesis ? 'badge-primary' : 'badge-info';
            const texto = esProtesis ? 'Prótesis Dental' : 'Asistencia Dental';

            html += `
                <tr>
                    <td>${estudiante.legajo}</td>
                    <td>${estudiante.nombre}</td>
                    <td>${estudiante.dni}</td>
                    <td><span class="badge ${badge}">${texto}</span></td>
                    <td>${estudiante.año}</td>
                    <td><span class="badge badge-success">${estudiante.estado}</span></td>
                    <td><span class="badge badge-warning">${estudiante.cuotas.length}</span></td>
                    <td>
                        <button class="btn btn-primary btn-sm btn-seleccionar" onclick="seleccionarEstudiante(${estudiante.id})">
                            <i class="fas fa-hand-pointer"></i> Seleccionar
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.html(html);

        // Inicializar DataTables
        $('#tabla-estudiantes').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "pageLength": 20,
            "order": [[1, "asc"]], // Ordenar por nombre
            "columnDefs": [
                {
                    "targets": [7], // Columna de acciones
                    "orderable": false,
                    "searchable": false
                }
            ]
        });
    }

    // Función global para seleccionar estudiante
    window.seleccionarEstudiante = function(estudianteId) {
        const estudiante = estudiantesDemo.find(e => e.id === estudianteId);
        if (estudiante) {
            // Guardar referencia del estudiante seleccionado
            window.estudianteSeleccionado = estudiante;

            // Llenar datos del estudiante
            $('#estudiante-nombre').text(estudiante.nombre);
            $('#estudiante-dni').text(estudiante.dni);
            $('#estudiante-legajo').text(estudiante.legajo);
            $('#estudiante-carrera').text(estudiante.carrera);
            $('#estudiante-año').text(estudiante.año);
            $('#estudiante-estado').text(estudiante.estado);

            // Cambiar a la vista de detalle para focalizar el flujo
            mostrarVistaDetalle();

            // Llenar cuotas
            llenarCuotasEstudiante(estudiante);
        }
    };

    function llenarCuotasEstudiante(estudiante) {
        const tbody = $('#cuotas-tbody');
        if (!tbody.length) return;

        function formatearPrecio(precio) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 2
            }).format(precio);
        }

        let html = '';
        estudiante.cuotas.forEach(cuota => {
            const total = cuota.importe + cuota.recargo;
            const claseVencida = cuota.vencida ? 'text-danger' : '';

            html += `
                <tr class="${cuota.vencida ? 'table-warning' : ''}">
                    <td>
                        <input type="checkbox" class="cuota-checkbox"
                               data-id="${cuota.id}"
                               data-periodo="${cuota.periodo}"
                               data-importe="${cuota.importe}"
                               data-recargo="${cuota.recargo}"
                               data-total="${total}"
                               onchange="manejarSeleccionCuota(this)">
                    </td>
                    <td>${cuota.periodo} ${cuota.vencida ? '<i class="fas fa-exclamation-triangle text-danger" title="Vencida"></i>' : ''}</td>
                    <td class="${claseVencida}">${cuota.vencimiento}</td>
                    <td class="text-right">${formatearPrecio(cuota.importe)}</td>
                    <td class="text-right ${cuota.recargo > 0 ? 'text-danger' : ''}">${formatearPrecio(cuota.recargo)}</td>
                    <td class="text-right"><strong>${formatearPrecio(total)}</strong></td>
                </tr>
            `;
        });

        tbody.html(html);
    }

    // Variables globales
    let carrito = [];
    let totalGeneral = 0;

    // Hacer la variable del estudiante verdaderamente global
    window.estudianteSeleccionado = null;

    function mostrarVistaDetalle() {
        $('#card-lista-estudiantes').hide();
        $('#card-estudiante').show();
        $('#card-cuotas').show();

        const cardEstudiante = $('#card-estudiante');
        if (cardEstudiante.length) {
            $('html, body').animate({
                scrollTop: Math.max(cardEstudiante.offset().top - 70, 0)
            }, 250);
        }
    }

    function volverAListaEstudiantes() {
        $('#card-estudiante').hide();
        $('#card-cuotas').hide();
        $('#card-lista-estudiantes').show();

        window.estudianteSeleccionado = null;
        carrito = [];
        totalGeneral = 0;

        $('#cuotas-tbody').empty();
        $('#select-all-cuotas').prop('checked', false);
        $('.cuota-checkbox').prop('checked', false);

        actualizarCarrito();
    }

    $('#btn-volver-lista').on('click', function() {
        volverAListaEstudiantes();
    });

    // Función global para actualizar totales del modal (reemplaza la del modal)
    window.actualizarTotales = function() {
        const totalElement = $('#total-carrito');
        const valorDescuentoElement = $('#valor-descuento');
        const tipoDescuentoElement = $('input[name="tipoDescuento"]:checked');

        let totalConDescuento = totalGeneral;

        if (tipoDescuentoElement.length && valorDescuentoElement.length) {
            const tipoDescuento = tipoDescuentoElement.val();
            const valorDescuento = parseFloat(valorDescuentoElement.val()) || 0;

            if (tipoDescuento === 'porcentaje') {
                totalConDescuento = totalGeneral - (totalGeneral * valorDescuento / 100);
            } else if (tipoDescuento === 'valor') {
                totalConDescuento = totalGeneral - valorDescuento;
            }
        }

        if (totalElement.length) {
            totalElement.text(new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 2
            }).format(totalConDescuento));
        }

        // Actualizar totales en el modal
        $('#modal-subtotal').text(new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS'
        }).format(totalGeneral));

        $('#modal-descuento').text(new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS'
        }).format(totalGeneral - totalConDescuento));

        $('#modal-total').text(new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS'
        }).format(totalConDescuento));
    };

    // Función global para manejar selección de cuotas
    window.manejarSeleccionCuota = function(checkbox) {
        const cuotaData = {
            id: parseInt(checkbox.dataset.id),
            periodo: checkbox.dataset.periodo,
            importe: parseFloat(checkbox.dataset.importe),
            recargo: parseFloat(checkbox.dataset.recargo),
            total: parseFloat(checkbox.dataset.total)
        };

        if (checkbox.checked) {
            carrito.push(cuotaData);
        } else {
            carrito = carrito.filter(item => item.id !== cuotaData.id);
        }

        actualizarCarrito();
    };

    function actualizarCarrito() {
        const carritoItems = $('#carrito-items');
        const btnPago = $('#btn-proceder-pago');
        const totalElement = $('#total-carrito');

        if (carritoItems.length === 0 || btnPago.length === 0 || totalElement.length === 0) return;

        function formatearPrecio(precio) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 2
            }).format(precio);
        }

        let html = '';
        totalGeneral = 0;

        if (carrito.length === 0) {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x"></i>
                    <p class="mt-2">No hay cuotas seleccionadas</p>
                </div>
            `;
            btnPago.prop('disabled', true);
        } else {
            carrito.forEach(cuota => {
                totalGeneral += cuota.total;
                html += `
                    <div class="cuota-item mb-2 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">${cuota.periodo}</strong><br>
                                <small class="text-muted">
                                    Cuota: ${formatearPrecio(cuota.importe)}
                                    ${cuota.recargo > 0 ? `+ Recargo: ${formatearPrecio(cuota.recargo)}` : ''}
                                </small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">${formatearPrecio(cuota.total)}</strong>
                            </div>
                        </div>
                    </div>
                `;
            });
            btnPago.prop('disabled', false);
        }

        carritoItems.html(html);
        totalElement.text(formatearPrecio(totalGeneral));
    }

    // Evento para el botón de proceder al pago
    $('#btn-proceder-pago').on('click', function() {
        if (carrito.length === 0) {
            alert('Debe seleccionar al menos una cuota para proceder al pago.');
            return;
        }

        // Llenar el resumen de items en el modal
        const resumenItems = $('#resumen-items');
        if (resumenItems.length) {
            let htmlResumen = '';
            carrito.forEach(cuota => {
                htmlResumen += `
                    <div class="d-flex justify-content-between mb-1">
                        <span>${cuota.periodo}</span>
                        <span class="font-weight-bold">${new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(cuota.total)}</span>
                    </div>
                `;
            });
            resumenItems.html(htmlResumen);
        }

        // Actualizar totales en el modal
        actualizarTotales();

        // Mostrar el modal
        $('#modalPago').modal('show');

        // Configurar auto-llenado después de que el modal esté visible
        setTimeout(() => {
            configurarDatosEstudiante();
        }, 300);
    });

    // Función para configurar auto-llenado de datos del estudiante
    function configurarDatosEstudiante() {
        if (!window.estudianteSeleccionado) {
            console.log('No hay estudiante seleccionado para auto-llenado');
            return;
        }

        console.log('Configurando auto-llenado para:', window.estudianteSeleccionado.nombre);

        // Función para llenar los campos
        function llenarDatosEstudiante() {
            if (window.estudianteSeleccionado) {
                const nombreField = document.getElementById('cliente-nombre');
                const documentoField = document.getElementById('cliente-documento');
                const direccionField = document.getElementById('cliente-direccion');
                const condicionField = document.getElementById('condicion-iva');

                if (nombreField) nombreField.value = window.estudianteSeleccionado.nombre;
                if (documentoField) documentoField.value = window.estudianteSeleccionado.dni;
                if (direccionField) direccionField.value = '';
                if (condicionField) condicionField.value = 'consumidor_final';

                console.log('Datos llenados:', {
                    nombre: nombreField ? nombreField.value : 'no encontrado',
                    documento: documentoField ? documentoField.value : 'no encontrado'
                });
            }
        }

        // Escuchar clics en las opciones de comprobante (usando el sistema del modal unificado)
        const opcionesComprobante = document.querySelectorAll('.comprobante-option');
        opcionesComprobante.forEach(opcion => {
            opcion.addEventListener('click', function() {
                const comprobante = this.dataset.comprobante;
                console.log('Tipo de comprobante seleccionado:', comprobante);

                if (comprobante === 'factura_local' || comprobante === 'factura_fiscal') {
                    // Esperar un poco para que se muestren los campos
                    setTimeout(llenarDatosEstudiante, 200);
                }
            });
        });

        // También escuchar cambios en los radio buttons directamente
        const radiosComprobante = document.querySelectorAll('input[name="tipoComprobante"]');
        radiosComprobante.forEach(radio => {
            radio.addEventListener('change', function() {
                const tipoComprobante = this.value;
                console.log('Radio comprobante cambiado:', tipoComprobante);

                if (tipoComprobante === 'factura_local' || tipoComprobante === 'factura_fiscal') {
                    setTimeout(llenarDatosEstudiante, 200);
                }
            });
        });
    }

    // Configurar el botón de procesar pago en el modal
    $('#btn-procesar-pago').on('click', function() {
        procesarPagoConFactura();
    });

    // Función para procesar pago con factura (similar a productos)
    function procesarPagoConFactura() {
        console.log('Procesando pago de cuotas con factura');

        const tipoComprobante = $('input[name="tipoComprobante"]:checked').val() || 'ticket';
        const metodoPago = $('input[name="metodoPago"]:checked').val() || 'efectivo';

        // Datos COMPLETOS incluyendo método de pago
        const datos = {
            datosCliente: {
                nombre: $('#cliente-nombre').val() || 'Cliente Genérico',
                documento: $('#cliente-documento').val() || '00000000',
                direccion: $('#cliente-direccion').val() || '',
                condicionIva: $('#condicion-iva').val() || 'consumidor_final'
            },
            tipoComprobante: tipoComprobante,
            metodoPago: metodoPago,
            totalFinal: totalGeneral,
            subtotal: totalGeneral,
            descuento: 0,
            observaciones: $('#observaciones').val() || '',
            carritoCuotas: carrito // Usar carritoCuotas para distinguir de productos
        };

        console.log('Enviando datos de cuotas:', datos);

        // Deshabilitar botón
        $('#btn-procesar-pago').prop('disabled', true).html('Procesando...');

        // XMLHttpRequest nativo para manejar blob correctamente
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("box.facturas.procesar-pago-factura") }}');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        xhr.responseType = 'blob';

        xhr.onload = function() {
            if (xhr.status === 200) {
                console.log('PDF de cuotas recibido exitosamente');

                // Crear blob y abrir
                const blob = new Blob([xhr.response], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const ventana = window.open(url, '_blank');

                if (ventana) {
                    console.log('PDF abierto en nueva ventana');
                } else {
                    alert('Bloqueador de pop-ups detectado. Verifique su configuración.');
                }

                // Limpiar y volver al estado inicial
                $('#modalPago').modal('hide');
                volverAListaEstudiantes();

            } else {
                console.error('Error HTTP:', xhr.status);
                alert('Error generando factura: Código ' + xhr.status);
            }

            // Restaurar botón
            $('#btn-procesar-pago').prop('disabled', false).html('Procesar Pago');
        };

        xhr.onerror = function() {
            console.error('Error de red');
            alert('Error de conexión al generar factura');
            $('#btn-procesar-pago').prop('disabled', false).html('Procesar Pago');
        };

        // Enviar datos
        const formData = new URLSearchParams();
        formData.append('datosCliente', JSON.stringify(datos.datosCliente));
        formData.append('tipoComprobante', datos.tipoComprobante);
        formData.append('metodoPago', datos.metodoPago);
        formData.append('totalFinal', datos.totalFinal);
        formData.append('subtotal', datos.subtotal);
        formData.append('descuento', datos.descuento);
        formData.append('observaciones', datos.observaciones);
        formData.append('carritoCuotas', JSON.stringify(datos.carritoCuotas));

        xhr.send(formData);
    }

    // Llenar tabla inicialmente
    llenarTablaEstudiantes();
});
</script>
@stop
