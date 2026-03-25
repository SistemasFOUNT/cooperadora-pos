@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Products Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i> Productos</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" id="searchProduct" placeholder="Buscar producto o código...">
                        <button class="btn btn-outline-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="height: 400px; overflow-y: auto;">
                    <div class="row" id="productsGrid">
                        @foreach($products as $product)
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="pos-item p-3 rounded text-center h-100 d-flex flex-column"
                                     onclick="addToCart({{ $product->id }}, '{{ $product->code }}', '{{ $product->name }}', {{ $product->price }}, {{ $product->stock }}, {{ $product->track_stock ? 'true' : 'false' }})">
                                    <div class="mb-2">
                                        <i class="fas fa-cube fa-2x text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold">{{ $product->name }}</h6>
                                    <p class="text-muted small mb-2">{{ $product->code }}</p>
                                    <div class="mt-auto">
                                        <div class="fw-bold text-success">${{ number_format($product->price, 2) }}</div>
                                        @if($product->track_stock)
                                            <small class="text-muted">Stock: {{ $product->stock }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Student Search -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Buscar Estudiante (Opcional)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="searchStudent" placeholder="Número de estudiante o documento...">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-info w-100" onclick="searchStudent()">
                                <i class="fas fa-search me-2"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div id="studentInfo" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <strong id="studentName"></strong><br>
                            <small id="studentDetails"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Carrito</h5>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Limpiar
                    </button>
                </div>
                <div class="card-body" style="height: 300px; overflow-y: auto;">
                    <div id="cartItems">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-cart-plus fa-3x mb-3"></i>
                            <p>Agrega productos al carrito</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end fw-bold" id="subtotal">$0.00</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">IVA (21%):</div>
                        <div class="col-6 text-end" id="tax">$0.00</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-end fw-bold text-success fs-5" id="total">$0.00</div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label">Método de Pago:</label>
                        <select class="form-select" id="paymentMethod">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-success btn-pos w-100" onclick="processSale()" id="btnProcessSale" disabled>
                        <i class="fas fa-credit-card me-2"></i> Procesar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sale Success Modal -->
<div class="modal fade" id="saleSuccessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i> Venta Exitosa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-receipt fa-4x text-success"></i>
                </div>
                <h6>Venta procesada exitosamente</h6>
                <p>Número de venta: <strong id="saleNumber"></strong></p>
                <p>Total: <strong id="saleTotal"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i> Imprimir Recibo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let selectedStudent = null;

// Search products
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
    products.forEach(function(product) {
        html += `
            <div class="col-md-3 col-sm-4 col-6 mb-3">
                <div class="pos-item p-3 rounded text-center h-100 d-flex flex-column"
                     onclick="addToCart(${product.id}, '${product.code}', '${product.name}', ${product.price}, ${product.stock}, ${product.track_stock})">
                    <div class="mb-2">
                        <i class="fas fa-cube fa-2x text-primary"></i>
                    </div>
                    <h6 class="fw-bold">${product.name}</h6>
                    <p class="text-muted small mb-2">${product.code}</p>
                    <div class="mt-auto">
                        <div class="fw-bold text-success">$${parseFloat(product.price).toFixed(2)}</div>
                        ${product.track_stock ? `<small class="text-muted">Stock: ${product.stock}</small>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    $('#productsGrid').html(html);
}

function addToCart(productId, code, name, price, stock, trackStock) {
    let existingItem = cart.find(item => item.product_id === productId);

    if (existingItem) {
        if (trackStock && existingItem.quantity >= stock) {
            alert('Stock insuficiente');
            return;
        }
        existingItem.quantity++;
    } else {
        cart.push({
            product_id: productId,
            code: code,
            name: name,
            unit_price: price,
            quantity: 1,
            stock: stock,
            track_stock: trackStock
        });
    }

    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function updateQuantity(index, quantity) {
    if (quantity <= 0) {
        removeFromCart(index);
        return;
    }

    let item = cart[index];
    if (item.track_stock && quantity > item.stock) {
        alert('Stock insuficiente');
        return;
    }

    cart[index].quantity = parseInt(quantity);
    updateCartDisplay();
}

function updateCartDisplay() {
    let html = '';
    let subtotal = 0;

    if (cart.length === 0) {
        html = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-cart-plus fa-3x mb-3"></i>
                <p>Agrega productos al carrito</p>
            </div>
        `;
    } else {
        cart.forEach(function(item, index) {
            let itemSubtotal = item.quantity * item.unit_price;
            subtotal += itemSubtotal;

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">${item.code}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row mt-2">
                        <div class="col-4">
                            <input type="number" class="form-control form-control-sm"
                                   value="${item.quantity}" min="1"
                                   onchange="updateQuantity(${index}, this.value)">
                        </div>
                        <div class="col-4 text-center">
                            $${item.unit_price.toFixed(2)}
                        </div>
                        <div class="col-4 text-end fw-bold">
                            $${itemSubtotal.toFixed(2)}
                        </div>
                    </div>
                </div>
            `;
        });
    }

    $('#cartItems').html(html);

    let tax = subtotal * 0.21;
    let total = subtotal + tax;

    $('#subtotal').text('$' + subtotal.toFixed(2));
    $('#tax').text('$' + tax.toFixed(2));
    $('#total').text('$' + total.toFixed(2));

    $('#btnProcessSale').prop('disabled', cart.length === 0);
}

function clearCart() {
    cart = [];
    selectedStudent = null;
    $('#studentInfo').hide();
    updateCartDisplay();
}

function searchStudent() {
    let search = $('#searchStudent').val();
    if (!search) return;

    $.get('{{ route("pos.search.student") }}', { search: search })
        .done(function(student) {
            if (student) {
                selectedStudent = student;
                $('#studentName').text(student.first_name + ' ' + student.last_name);
                $('#studentDetails').text(`Estudiante #${student.student_number} - ${student.career_type}`);
                $('#studentInfo').show();
            } else {
                alert('Estudiante no encontrado');
                selectedStudent = null;
                $('#studentInfo').hide();
            }
        });
}

function processSale() {
    if (cart.length === 0) return;

    let subtotal = 0;
    let items = cart.map(function(item) {
        let itemSubtotal = item.quantity * item.unit_price;
        subtotal += itemSubtotal;

        return {
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            subtotal: itemSubtotal,
            tax_amount: itemSubtotal * 0.21,
            total: itemSubtotal * 1.21
        };
    });

    let tax = subtotal * 0.21;
    let total = subtotal + tax;

    let data = {
        items: items,
        payment_method_id: $('#paymentMethod').val(),
        student_id: selectedStudent ? selectedStudent.id : null,
        subtotal: subtotal,
        tax_amount: tax,
        total_amount: total,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: '{{ route("pos.process.sale") }}',
        method: 'POST',
        data: data,
        beforeSend: function() {
            $('#btnProcessSale').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');
        },
        success: function(response) {
            if (response.success) {
                $('#saleNumber').text(response.sale_number);
                $('#saleTotal').text('$' + total.toFixed(2));
                $('#saleSuccessModal').modal('show');
                clearCart();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            let error = xhr.responseJSON ? xhr.responseJSON.message : 'Error al procesar la venta';
            alert('Error: ' + error);
        },
        complete: function() {
            $('#btnProcessSale').prop('disabled', false).html('<i class="fas fa-credit-card me-2"></i> Procesar Venta');
        }
    });
}

function printReceipt() {
    // Aquí puedes implementar la lógica de impresión
    alert('Función de impresión no implementada aún');
}

// Initialize
$(document).ready(function() {
    updateCartDisplay();
});
</script>
@endpush
