# ESTÁNDARES DEL PROYECTO - SISTEMA POS COOPERADORA

## 💳 ESTÁNDAR DE COBROS UNIFICADO (CRÍTICO)

**REGLA FUNDAMENTAL**: TODOS los procesos de cobro deben ser EXACTAMENTE IGUALES independientemente del módulo.

### 🎯 PRINCIPIOS OBLIGATORIOS:
- **Mecanización del proceso**: Una sola manera de proceder
- **Prevención de errores**: Sin confusión entre flujos diferentes  
- **Eficiencia operativa**: No aprender múltiples procesos
- **Consistencia de UX**: Experiencia uniforme

### 📋 FLUJO ESTÁNDAR OBLIGATORIO:

#### 1. **SELECCIÓN DE ITEMS** (Idéntico en todos los módulos)
```html
<!-- Botón estándar para agregar al carrito -->
<button class="btn btn-[color] btn-sm agregar-[tipo]" 
        data-id="{{ $item->id }}"
        data-nombre="{{ $item->name }}"
        data-precio="{{ $item->price }}">
    <i class="fas fa-plus"></i> Agregar
</button>
```

#### 2. **CARRITO UNIFICADO** (Panel derecho obligatorio)
- Controles: cantidad (+ / -), eliminar
- Total automático actualizado  
- Botón "Proceder al Pago" solo habilitado con items

#### 3. **MODAL DE PAGO UNIFICADO** (MISMO en todos los módulos)
```blade
{{-- OBLIGATORIO en todas las vistas de cobro --}}
@include('components.payment-modals')
```

#### 4. **JAVASCRIPT ESTÁNDAR** (Funciones obligatorias)
```javascript
// Funciones OBLIGATORIAS en todos los módulos de cobro
function formatearPrecio(precio) { /* Formato argentino */ }
function actualizarCarrito() { /* Gestión de items */ }
function actualizarTotalesModal() { /* Cálculos automáticos */ }
window.actualizarTotales = actualizarTotalesModal; // Override obligatorio
```

### 🛠️ IMPLEMENTACIÓN TÉCNICA OBLIGATORIA:

#### **Métodos de Pago (Idénticos siempre):**
- ✅ Efectivo (`efectivo`)
- ✅ Tarjeta (`tarjeta`)  
- ✅ Transferencia (`transferencia`)
- ✅ Mixto (`mixto`)

#### **Tipos de Comprobante (Idénticos siempre):**
- ✅ Ticket (`ticket`)
- ✅ Factura Local (`factura_local`)
- ✅ Factura Fiscal (`factura_fiscal`)

#### **CSS Estándar (Clases obligatorias):**
```css
.metodo-pago-option {
    /* Estilos de métodos de pago */
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.comprobante-option {
    /* Estilos de comprobantes */
    border: 2px solid #e9ecef; 
    transition: all 0.3s ease;
}
```

### 🚫 REGLA DE ORO:
> **"Si cambias el proceso de cobro en UN módulo, DEBES cambiarlo IGUAL en TODOS los módulos"**

### ✅ MÓDULOS IMPLEMENTADOS:
- ✅ Productos (`/box/cobros/productos`)
- ✅ Cuotas (`/box/cobros/cuotas`)
- ✅ Bonos (`/box/cobros/bonos`) 
- ✅ Odontología (`/box/cobros/odontologia`)
- ✅ Otros (`/box/cobros/otros`)

---

## 🎯 ESTÁNDAR DE CAMPOS BOOLEANOS

**REGLA**: Todas las tablas deben usar `is_active` (boolean) para estado activo/inactivo.

### ✅ TABLAS ESTANDARIZADAS:

| Tabla | Campo Estado | Tipo | Valores |
|-------|-------------|------|---------|
| `products` | `is_active` | boolean | true/false |
| `branches` | `is_active` | boolean | true/false |
| `payment_methods` | `is_active` | boolean | true/false |
| `chart_of_accounts` | `is_active` | boolean | true/false |
| `students` | `is_active` + `status` | boolean + enum | true/false + active/inactive/graduated/dropout |

### 📝 PATRÓN DE MODELOS:

```php
class ModeloEjemplo extends Model 
{
    protected $fillable = [
        // ... otros campos
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Scope para registros activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### 📝 PATRÓN DE CONTROLADORES:

```php
// ✅ CORRECTO - Usar is_active
public function index() {
    $records = Model::where('is_active', true)->get();
}

public function destroy(Model $model) {
    $model->update(['is_active' => false]);
}

// ❌ INCORRECTO - No usar otros campos para estado activo
public function index() {
    $records = Model::where('status', 'active')->get(); // ❌
}
```

## 🔧 CORRECCIONES APLICADAS:

### 1. **Tabla Students:**
- ✅ Agregada columna `is_active` (boolean)
- ✅ Migración automática de datos: `status='active'` → `is_active=true`
- ✅ Índice agregado para optimización
- ✅ Modelo actualizado con cast y scope
- ✅ Controlador corregido

### 2. **Consistencia en Controladores:**
- ✅ `StudentController` - Usa `is_active` 
- ✅ `SaleController` - Corregido para usar `is_active`
- ✅ `ProductController` - Ya correcto
- ✅ `PosController` - Ya correcto

## 📊 DATATABLES ESTÁNDAR:

**REGLA**: Todas las tablas paginadas deben usar DataTables con:
- ✅ 20 elementos por página por defecto
- ✅ Configuración en español
- ✅ Búsqueda en tiempo real
- ✅ Responsive design

```javascript
// Uso estándar
DataTableConfig.initTable('#miTabla', 'tipo', opciones);
```

## 🚫 ANTIPATRONES CORREGIDOS:

### ❌ ANTES (Inconsistente):
```php
// Diferentes campos para el mismo propósito
$students = Student::where('status', 'active')->get();     // ❌
$products = Product::where('is_active', true)->get();      // ✅
$branches = Branch::where('is_active', true)->get();       // ✅
```

### ✅ DESPUÉS (Consistente):
```php
// Mismo patrón para todas las tablas
$students = Student::where('is_active', true)->get();      // ✅
$products = Product::where('is_active', true)->get();      // ✅
$branches = Branch::where('is_active', true)->get();       // ✅

// O usando scopes
$students = Student::active()->get();                      // ✅
$products = Product::active()->get();                      // ✅
$branches = Branch::active()->get();                       // ✅
```

## 🎯 BENEFICIOS DE LA ESTANDARIZACIÓN:

1. **Consistencia**: Mismo patrón en todo el proyecto
2. **Mantenibilidad**: Código más fácil de mantener
3. **Performance**: Índices optimizados para `is_active`
4. **Escalabilidad**: Fácil agregar nuevas funcionalidades
5. **Legibilidad**: Código más claro y predecible

## 📋 CHECKLIST PARA NUEVAS TABLAS:

- [ ] Incluir campo `is_active` (boolean) con default true
- [ ] Agregar índice en `is_active`
- [ ] Cast booleano en el modelo
- [ ] Scope `active()` en el modelo
- [ ] Usar `is_active` en controladores
- [ ] Implementar DataTables para listados
- [ ] Soft delete usando `is_active = false`

---
**Fecha de actualización**: 25 de marzo de 2026  
**Versión**: 1.0  
**Estado**: ✅ IMPLEMENTADO
