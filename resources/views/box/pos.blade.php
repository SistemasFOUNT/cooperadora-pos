@extends('adminlte::page')

@section('title', 'BOX - Punto de Venta')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-cash-register text-primary"></i> Punto de Venta - BOX</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('box.dashboard') }}">BOX</a></li>
                <li class="breadcrumb-item active">Punto de Venta</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Lista de productos --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box"></i> Productos Disponibles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row" id="productos-lista">
                        @if(isset($productos) && $productos->count() > 0)
                            @foreach($productos as $producto)
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card card-outline card-secondary producto-item"
                                     data-id="{{ $producto->id }}"
                                     data-name="{{ $producto->name }}"
                                     data-code="{{ $producto->code }}"
                                     data-price="{{ $producto->price }}">
                                    <div class="card-body text-center p-2">
                                        <h6 class="card-title">{{ $producto->name }}</h6>
                                        <p class="text-muted small">{{ $producto->code }}</p>
                                        <h5 class="text-success">${{ number_format($producto->price, 0, ',', '.') }}</h5>
                                        <button class="btn btn-primary btn-sm agregar-producto">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No hay productos disponibles en este momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Carrito de compras --}}
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Carrito de Venta
                    </h3>
                </div>
                <div class="card-body">
                    <div id="carrito-items">
                        <p class="text-muted text-center">El carrito está vacío</p>
                    </div>

                    <hr>

                    {{-- Totales --}}
                    <div class="d-flex justify-content-between">
                        <strong>Subtotal:</strong>
                        <span id="subtotal">$0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Descuento:</strong>
                        <span id="descuento">$0</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                        <h5><strong>Total:</strong></h5>
                        <h5 id="total-final"><strong>$0</strong></h5>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success btn-block" id="procesar-venta" disabled>
                        <i class="fas fa-check"></i> Procesar Venta
                    </button>
                    <button class="btn btn-secondary btn-block mt-2" id="limpiar-carrito">
                        <i class="fas fa-broom"></i> Limpiar Carrito
                    </button>
                </div>
            </div>

            {{-- Métodos de pago --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card"></i> Método de Pago
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <select class="form-control" id="metodo-pago">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div id="pago-efectivo" class="pago-metodo">
                        <div class="form-group">
                            <label>Monto Recibido:</label>
                            <input type="number" class="form-control" id="monto-recibido" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Vuelto:</label>
                            <input type="number" class="form-control" id="vuelto" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .producto-item {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .producto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .carrito-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
    </style>
@stop

@section('js')
    <script>
        let carrito = [];

        $(document).ready(function() {
            // Agregar producto al carrito
            $('.agregar-producto').click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                const producto = $(this).closest('.producto-item');
                const item = {
                    id: producto.data('id'),
                    name: producto.data('name'),
                    code: producto.data('code'),
                    price: parseFloat(producto.data('price')),
                    quantity: 1
                };

                agregarAlCarrito(item);
            });

            // Cambio de método de pago
            $('#metodo-pago').change(function() {
                $('.pago-metodo').hide();
                $('#pago-' + $(this).val()).show();
            });

            // Calcular vuelto
            $('#monto-recibido').on('input', function() {
                const total = parseFloat($('#total-final').text().replace('$', '').replace(',', ''));
                const recibido = parseFloat($(this).val()) || 0;
                const vuelto = recibido - total;
                $('#vuelto').val(vuelto >= 0 ? vuelto.toFixed(2) : '0.00');
            });

            // Procesar venta
            $('#procesar-venta').click(function() {
                procesarVenta();
            });

            // Limpiar carrito
            $('#limpiar-carrito').click(function() {
                carrito = [];
                actualizarCarrito();
            });
        });

        function agregarAlCarrito(nuevoItem) {
            const existente = carrito.find(item => item.id === nuevoItem.id);

            if (existente) {
                existente.quantity++;
            } else {
                carrito.push(nuevoItem);
            }

            actualizarCarrito();
        }

        function actualizarCarrito() {
            const container = $('#carrito-items');

            if (carrito.length === 0) {
                container.html('<p class="text-muted text-center">El carrito está vacío</p>');
                $('#procesar-venta').prop('disabled', true);
            } else {
                let html = '';
                let subtotal = 0;

                carrito.forEach((item, index) => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;

                    html += `
                        <div class="carrito-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.name}</strong><br>
                                    <small class="text-muted">${item.code}</small>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="input-group input-group-sm" style="width: 100px;">
                                    <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                                    <span class="form-control text-center">${item.quantity}</span>
                                    <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                                </div>
                                <span><strong>$${itemTotal.toLocaleString()}</strong></span>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
                $('#subtotal').text('$' + subtotal.toLocaleString());
                $('#total-final').text('$' + subtotal.toLocaleString());
                $('#procesar-venta').prop('disabled', false);
            }
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            actualizarCarrito();
        }

        function cambiarCantidad(index, cambio) {
            carrito[index].quantity += cambio;
            if (carrito[index].quantity <= 0) {
                carrito.splice(index, 1);
            }
            actualizarCarrito();
        }

        function procesarVenta() {
            if (carrito.length === 0) return;

            const venta = {
                carrito: carrito,
                subtotal: parseFloat($('#subtotal').text().replace('$', '').replace(',', '')),
                descuento: 0,
                totalFinal: parseFloat($('#total-final').text().replace('$', '').replace(',', '')),
                metodoPago: $('#metodo-pago').val(),
                montoRecibido: $('#monto-recibido').val(),
                vuelto: $('#vuelto').val()
            };

            // Aquí iría la lógica para enviar la venta al servidor
            console.log('Procesando venta:', venta);

            // Simulación de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Venta Procesada!',
                text: 'La venta se ha registrado correctamente',
                timer: 2000
            });

            carrito = [];
            actualizarCarrito();
        }
    </script>
@stop
