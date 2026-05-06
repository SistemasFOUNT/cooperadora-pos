# GUÍA DE IMPLEMENTACIÓN - SISTEMA DE COBROS UNIFICADO

## 📋 CHECKLIST PARA NUEVOS MÓDULOS DE COBRO

### ✅ 1. ESTRUCTURA HTML OBLIGATORIA

#### Vista Principal (8/4 Layout)
```blade
<div class="row">
    <!-- Lista de items - Col 8 -->
    <div class="col-md-8">
        <div class="card card-[color]">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-[icon]"></i> [Título del Módulo]</h3>
            </div>
            <div class="card-body">
                <table id="tabla-[modulo]" class="table table-striped table-hover">
                    <!-- DataTable con items disponibles -->
                </table>
            </div>
        </div>
    </div>

    <!-- Carrito - Col 4 -->
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> [Carrito/Seleccionados]</h3>
            </div>
            <div class="card-body" style="min-height: 300px;">
                <div id="carrito-items">
                    <!-- Items del carrito dinámicos -->
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col">
                        <h4 class="text-success">Total: $<span id="total-general">0,00</span></h4>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-success" id="btn-proceder-pago" disabled 
                                data-toggle="modal" data-target="#modalPago">
                            <i class="fas fa-credit-card"></i> Proceder al Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- OBLIGATORIO: Incluir modal unificado --}}
@include('components.payment-modals')
```

### ✅ 2. JAVASCRIPT OBLIGATORIO

#### Variables Globales Estándar
```javascript
$(document).ready(function() {
    let carrito = [];
    let totalGeneral = 0;
    // Otras variables específicas del módulo...
```

#### Funciones Obligatorias
```javascript
// 1. FORMATEO DE PRECIOS (Obligatorio - Formato Argentino)
function formatearPrecio(precio) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2
    }).format(precio);
}

// 2. ACTUALIZAR CARRITO (Obligatorio - Lógica unificada)
function actualizarCarrito() {
    const $listaItems = $('#carrito-items');
    let html = '';
    totalGeneral = 0;

    if (carrito.length === 0) {
        html = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-shopping-cart fa-3x"></i>
                <p class="mt-2">No hay [items] seleccionados</p>
            </div>
        `;
        $('#btn-proceder-pago').prop('disabled', true);
    } else {
        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            totalGeneral += subtotal;
            // HTML del item...
        });
        $('#btn-proceder-pago').prop('disabled', false);
    }

    $listaItems.html(html);
    $('#total-general').text(formatearPrecio(totalGeneral));
    actualizarTotalesModal(); // ¡CRÍTICO!
}

// 3. ACTUALIZAR TOTALES MODAL (Obligatorio - Cálculos automáticos)
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

// 4. OVERRIDE OBLIGATORIO (Para que el componente funcione)
window.actualizarTotales = actualizarTotalesModal;
```

#### Event Handlers Obligatorios
```javascript
// Agregar item al carrito (Adaptable según el módulo)
$(document).on('click', '.agregar-[tipo]', function() {
    const item = {
        id: parseInt($(this).data('id')),
        nombre: $(this).data('nombre'),
        precio: parseFloat($(this).data('precio')),
        cantidad: 1
        // Campos específicos del módulo...
    };

    const itemExistente = carrito.find(i => i.id === item.id);
    if (itemExistente) {
        itemExistente.cantidad++;
    } else {
        carrito.push(item);
    }
    actualizarCarrito();
});

// Controles del carrito (Idénticos en todos los módulos)
$(document).on('click', '.btn-aumentar', function() {
    const itemId = parseInt($(this).data('id'));
    const item = carrito.find(i => i.id === itemId);
    if (item) {
        item.cantidad += 1;
        actualizarCarrito();
    }
});

$(document).on('click', '.btn-disminuir', function() {
    const itemId = parseInt($(this).data('id'));
    const item = carrito.find(i => i.id === itemId);
    if (item && item.cantidad > 1) {
        item.cantidad -= 1;
        actualizarCarrito();
    }
});

$(document).on('click', '.btn-eliminar', function() {
    const itemId = parseInt($(this).data('id'));
    carrito = carrito.filter(i => i.id !== itemId);
    actualizarCarrito();
});

// Modal events (Idénticos en todos los módulos)
$('#modalPago').on('shown.bs.modal', function() {
    let resumenHtml = '';
    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        resumenHtml += `
            <div class="d-flex justify-content-between mb-1">
                <span>${item.nombre} x${item.cantidad}</span>
                <strong>${formatearPrecio(subtotal)}</strong>
            </div>
        `;
    });
    $('#resumen-items').html(resumenHtml);
    actualizarTotalesModal();
});

// Procesar pago (Adaptable según el módulo)
$('#btn-procesar-pago').on('click', function() {
    const metodoPago = $('input[name="metodoPago"]:checked').val();
    const tipoComprobante = $('input[name="tipoComprobante"]:checked').val();
    
    console.log('Procesando pago de [módulo]:', {
        items: carrito,
        total: totalGeneral,
        metodoPago: metodoPago,
        tipoComprobante: tipoComprobante
    });
    
    // Simular o procesar pago real
    alert('Pago de [módulo] procesado exitosamente');
    carrito = [];
    actualizarCarrito();
    $('#modalPago').modal('hide');
});
```

### ✅ 3. CSS OBLIGATORIO

```css
/* Estilos mejorados para opciones de pago (OBLIGATORIO) */
.metodo-pago-option {
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    background-color: #f8f9fa;
}

.metodo-pago-option:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.metodo-pago-option.selected {
    border-color: #28a745;
    background-color: #d4edda;
    box-shadow: 0 0 10px rgba(40,167,69,0.3);
}

/* Estilos para opciones de comprobante (OBLIGATORIO) */
.comprobante-option {
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    background-color: #f8f9fa;
}

.comprobante-option:hover {
    border-color: #6f42c1;
    background-color: #f3e5f5;
    transform: translateY(-1px);
}

.comprobante-option.selected {
    border-color: #6f42c1;
    background-color: #e1bee7;
    box-shadow: 0 0 8px rgba(111,66,193,0.3);
}
```

### ✅ 4. VALIDACIONES OBLIGATORIAS

```javascript
// Validar que todas las funciones obligatorias estén definidas
if (typeof formatearPrecio !== 'function') {
    console.error('ERROR: formatearPrecio() no está definida');
}

if (typeof actualizarCarrito !== 'function') {
    console.error('ERROR: actualizarCarrito() no está definida');
}

if (typeof actualizarTotalesModal !== 'function') {
    console.error('ERROR: actualizarTotalesModal() no está definida');
}

if (typeof window.actualizarTotales !== 'function') {
    console.error('ERROR: window.actualizarTotales no está definida');
}
```

### ✅ 5. TESTING CHECKLIST

- [ ] **Agregar items al carrito funciona**
- [ ] **Controles de cantidad (+/-) funcionan**
- [ ] **Eliminar items funciona**
- [ ] **Total se calcula correctamente**
- [ ] **Modal se abre con items correctos**
- [ ] **Descuentos se calculan bien**
- [ ] **Cambio de método de pago funciona**
- [ ] **Cambio de tipo de comprobante funciona**
- [ ] **Datos de cliente aparecen solo para facturas**
- [ ] **Procesamiento simula correctamente**
- [ ] **Carrito se limpia después del pago**

### 🚫 ERRORES COMUNES A EVITAR

1. **No llamar `actualizarTotalesModal()` desde `actualizarCarrito()`**
2. **Olvidar definir `window.actualizarTotales`**
3. **No incluir `@include('components.payment-modals')`**
4. **Usar diferentes nombres para funciones equivalentes**
5. **No validar que el botón esté habilitado solo con items**
6. **Formatear precios de manera inconsistente**

---

## 🎯 RESULTADO ESPERADO

Después de implementar esta guía, el módulo debe:

1. **Comportarse EXACTAMENTE igual** a otros módulos de cobro
2. **Usar el mismo modal** con mismas opciones
3. **Tener interacciones visuales idénticas**
4. **Mantener la misma UX** para el usuario final

---

**FECHA:** Mayo 2026  
**AUTOR:** Sistema de Desarrollo Cooperadora  
**VERSIÓN:** 1.0 - Implementación inicial  
**ESTADO:** Estándar obligatorio para todos los módulos
