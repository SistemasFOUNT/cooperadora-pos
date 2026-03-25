@extends('layouts.app')

@section('title', 'Gestión de Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Productos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Productos</li>
            </ol>
        </nav>
    </div>
    @can('create_products')
    <a href="{{ route('products.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
    @endcan
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-box text-primary me-2"></i>
                    Lista de Productos
                </h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-striped table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $product->code }}</span>
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $product->description }}">
                                        {{ Str::limit($product->description, 40) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->stock > 10 ? 'bg-success' : ($product->stock > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('view_products')
                                        <a href="{{ route('products.show', $product) }}"
                                           class="btn btn-outline-info"
                                           title="Ver detalles"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan

                                        @can('edit_products')
                                        <a href="{{ route('products.edit', $product) }}"
                                           class="btn btn-outline-primary"
                                           title="Editar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can('delete_products')
                                        <button type="button"
                                                class="btn btn-outline-danger btn-delete"
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                title="Eliminar"
                                                data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable con configuración estándar para productos
    const table = DataTableConfig.initTable('#productsTable', 'products', {
        columnDefs: [
            {
                targets: 6, // Columna de acciones
                orderable: false,
                searchable: false
            }
        ]
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Manejar eliminación de productos
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');

        // Confirmación con SweetAlert2 si está disponible, sino usar confirm nativo
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: `¿Estás seguro de eliminar "${productName}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteProduct(productId);
                }
            });
        } else {
            if (confirm(`¿Estás seguro de eliminar "${productName}"? Esta acción no se puede deshacer.`)) {
                deleteProduct(productId);
            }
        }
    });

    // Función para eliminar producto
    function deleteProduct(productId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/products/${productId}`;
        deleteForm.submit();
    }
});
</script>
@endpush
