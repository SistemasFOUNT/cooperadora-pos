# ESTÁNDARES DEL PROYECTO - SISTEMA POS COOPERADORA

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
