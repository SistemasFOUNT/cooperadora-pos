@extends('layouts.app')

@section('title', 'Punto de Venta')
@section('page-title', 'Punto de Venta')

@section('content')
<div class="row">
    <!-- Panel de Productos -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>Buscar Productos
                    </h5>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Producto
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchProduct" 
                                   placeholder="Buscar por nombre, código o descripción...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="tratamiento">Tratamiento</option>
                            <option value="aranceles">Aranceles</option>
                        </select>
                    </div>
                </div>
                
                <div class="row" id="productsList">
                    @foreach($products as $product)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <div class="card h-100 product-card" style="cursor: pointer;" 
                             onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">
                            <div class="card-body text-center p-3">
                                <div class="mb-2">
                                    <i class="fas fa-box-open fa-2x text-primary"></i>
                                </div>
                                <h6 class="card-title">{{ $product->name }}</h6>
                                <p class="card-text text-muted small">{{ Str::limit($product->description, 50) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary">{{ strtoupper($product->category) }}</span>
                                    <strong class="text-success">${{ number_format($product->price, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Panel del Carrito -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Carrito de Compras
                </h5>
            </div>
            <div class="card-body">
                <!-- Búsqueda de Estudiante -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Cliente/Estudiante</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchStudent" 
                               placeholder="Buscar estudiante...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    </div>
                    <div id="selectedStudent" class="mt-2" style="display: none;">
                        <div class="alert alert-info py-2 mb-0">
                            <i class="fas fa-user me-1"></i>
                            <span id="studentInfo"></span>
                            <button type="button" class="btn-close float-end" onclick="clearStudent()"></button>
                        </div>
                    </div>
                </div>

                <!-- Items del Carrito -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Productos</label>
                    <div id="cartItems" class="border rounded p-2" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-cart-plus fa-3x mb-2"></i>
                            <p>Agrega productos al carrito</p>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="mb-3">
                    <div class="bg-light rounded p-3">
                        <div class="row">
                            <div class="col-6"><strong>Subtotal:</strong></div>
                            <div class="col-6 text-end">$<span id="subtotal">0.00</span></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>IVA (21%):</strong></div>
                            <div class="col-6 text-end">$<span id="tax">0.00</span></div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-6"><strong class="text-success">TOTAL:</strong></div>
                            <div class="col-6 text-end"><strong class="text-success fs-5">$<span id="total">0.00</span></strong></div>
                        </div>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Método de Pago</label>
                    <select class="form-select" id="paymentMethod">
                        @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" id="processPayment" onclick="processSale()">
                        <i class="fas fa-credit-card me-2"></i>Procesar Pago
                    </button>
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash me-2"></i>Limpiar Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let selectedStudent = null;

// Búsqueda de productos
$('#searchProduct').on('input', function() {
    let search = $(this).val();
    if (search.length >= 2) {
        $.get('{{ route("pos.search.products") }}', { search: search })
            .done(function(products) {
                displayProducts(products);
            });
    }
});

function displayProducts(products) {
    let html = '';
    
    if (products.length === 0) {
        html = '<div class="col-12"><div class="text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No se encontraron productos</p></div></div>';
    } else {
        products.forEach(function(product) {
            html += `
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <div class="card h-100 product-card" style="cursor: pointer;" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                        <div class="card-body text-center p-3">
                            <div class="mb-2"><i class="fas fa-box-open fa-2x text-primary"></i></div>
                            <h6 class="card-title">${product.name}</h6>
                            <p class="card-text text-muted small">${product.description?.substring(0, 50) || ''}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">${product.category?.toUpperCase() || ''}</span>
                                <strong class="text-success">$${parseFloat(product.price).toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#productsList').html(html);
}

// Funciones del carrito
function addToCart(productId, productName, price) {
    let existingItem = cart.find(item => item.product_id == productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            product_id: productId,
            product_name: productName,
            unit_price: parseFloat(price),
            quantity: 1
        });
    }
    updateCartDisplay();
}

function updateCartDisplay() {
    let html = '';
    let subtotal = 0;

    if (cart.length === 0) {
        html = '<div class="text-center text-muted py-4"><i class="fas fa-cart-plus fa-3x mb-2"></i><p>Agrega productos al carrito</p></div>';
    } else {
        cart.forEach(function(item, index) {
            let itemSubtotal = item.quantity * item.unit_price;
            subtotal += itemSubtotal;
            html += `
                <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.product_name}</h6>
                            <small class="text-muted">$${item.unit_price.toFixed(2)} c/u</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="text-end ms-2">
                            <strong>$${itemSubtotal.toFixed(2)}</strong><br>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    $('#cartItems').html(html);
    let tax = subtotal * 0.21;
    let total = subtotal + tax;
    $('#subtotal').text(subtotal.toFixed(2));
    $('#tax').text(tax.toFixed(2));
    $('#total').text(total.toFixed(2));
}

function updateQuantity(index, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        cart[index].quantity = newQuantity;
        updateCartDisplay();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function clearCart() {
    cart = [];
    selectedStudent = null;
    updateCartDisplay();
}

function clearStudent() {
    selectedStudent = null;
    $('#selectedStudent').hide();
}

function processSale() {
    if (cart.length === 0) {
        alert('El carrito está vacío');
        return;
    }

    let paymentMethodId = $('#paymentMethod').val();
    if (!paymentMethodId) {
        alert('Selecciona un método de pago');
        return;
    }

    let saleData = {
        items: cart,
        student_id: selectedStudent?.id || null,
        payment_method_id: paymentMethodId,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: '{{ route("pos.process.sale") }}',
        method: 'POST',
        data: saleData,
        success: function(response) {
            alert('Venta procesada exitosamente');
            clearCart();
        },
        error: function(xhr) {
            alert('Error al procesar la venta');
        }
    });
}

$(document).ready(function() {
    updateCartDisplay();
});
</script>
@endpush