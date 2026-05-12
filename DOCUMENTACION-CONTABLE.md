# Documentación del Sistema Contable Automático

## Descripción General

El sistema de Cooperadora incluye un módulo de contabilidad de **doble entrada automática**. Esto significa que cada venta registrada en el sistema genera automáticamente un asiento contable con los movimientos debit/credit correspondientes.

## Arquitectura

### 1. Flujo de Creación de Asiento

```
Usuario crea Venta (SaleController::store)
         ↓
    Sale guardada en BD
         ↓
    DB::commit() exitoso
         ↓
    SaleCreated::dispatch($sale)
         ↓
    GenerarAsientoVenta listener captura evento
         ↓
    ContabilidadService::generarAsientoVenta($sale)
         ↓
    Crea AsientoContable + 2 MovimientoContable
```

### 2. Componentes Principales

#### **ContabilidadService** (`app/Services/ContabilidadService.php`)

Servicio que coordina toda la lógica contable:

```php
// Generar asiento para una venta
$asiento = $contabilidad->generarAsientoVenta($sale);

// Obtener saldo de una cuenta
$saldo = $contabilidad->obtenerSaldoCuenta($cuenta, '2024-01-01', '2024-12-31');

// Balance de comprobación
$balance = $contabilidad->obtenerBalanceComprobacion();
```

#### **SaleCreated Event** (`app/Events/SaleCreated.php`)

Evento que se dispara automáticamente cuando se crea una venta.

#### **GenerarAsientoVenta Listener** (`app/Listeners/GenerarAsientoVenta.php`)

Listener que escucha el evento SaleCreated y ejecuta la lógica de generación de asientos.

## Configuración Requerida

### 1. Cuentas Contables Globales

Las siguientes cuentas DEBEN existir en la tabla `cuentas_contables`:

| Código | Nombre | Naturaleza | Descripción |
|--------|--------|-----------|-------------|
| 1101 | Caja General | Deudor | Cuenta de efectivo por defecto |
| 1301 | Deudores Tarjeta | Deudor | Cuenta para transacciones con tarjeta |
| 2101 | IVA por Pagar | Acreedor | Impuesto a registrar |
| 4100 | Ventas Generales | Acreedor | Cuenta de ventas por defecto |
| 4101 | Ventas de Productos | Acreedor | Venta de mercadería |
| 4102 | Cuotas Estudiantiles | Acreedor | Ingresos de estudiantes |
| 4103 | Prestaciones Clínicas | Acreedor | Servicios odontológicos |
| 4104 | Servicios Diversos | Acreedor | Otros servicios |

### 2. Configuración por Punto de Venta

Cada `PuntoVenta` DEBE tener configurados:

- **cuenta_caja_id**: ID de la cuenta contable para la caja del punto
- **cuenta_ventas_id**: ID de la cuenta contable para ventas del punto (opcional)

```php
// Ejemplo
$punto = PuntoVenta::find(1);
$punto->update([
    'cuenta_caja_id' => 1,      // ID de CuentaContable 1101
    'cuenta_ventas_id' => 10,   // ID de CuentaContable 4101
]);
```

## Validación de Configuración

Para verificar que todo está configurado correctamente, ejecuta:

```bash
php artisan contable:validar-configuracion
```

Este comando verifica:
- ✓ Existencia de todas las cuentas globales requeridas
- ✓ Configuración de cada punto de venta
- ✓ Referencias de IDs válidas

## Uso

### Automatizado (Recomendado)

El sistema es **completamente automático**. No se requiere intervención manual:

1. Usuario crea una venta en cualquier módulo (BOX, Postgrado, Odonto)
2. El sistema automáticamente genera el asiento contable
3. El asiento se registra con dos movimientos (DEBE/HABER)

### Manual (Testing)

Para probar o generar asientos de forma manual:

```bash
# Generar asiento para la última venta
php artisan contable:generar-prueba

# Generar asiento para una venta específica
php artisan contable:generar-prueba --sale-id=123
```

## Lógica de Asientos

### Estructura del Asiento

Cada asiento contable contiene:

```
ASIENTO: Venta - [TIPO]
Fecha: [FECHA DE LA VENTA]
Referencia: Sale #[NUMERO_VENTA]
Punto de Venta: [PUNTO]
Usuario: [USUARIO_QUE_CREO_VENTA]

Movimientos:
├─ DEBE: Caja/Deudores = Total de Venta
└─ HABER: Ventas = Total de Venta
```

### Selección de Cuentas

#### Cuenta de Destino (DEBE)

Se selecciona según la forma de pago:

| Forma de Pago | Cuenta |
|---------------|--------|
| Tarjeta | 1301 - Deudores Tarjeta |
| Efectivo | Cuenta caja del punto |
| Transferencia | Cuenta caja del punto |
| Otro | Cuenta caja del punto |

#### Cuenta de Ventas (HABER)

Se selecciona según el tipo de operación:

| Tipo | Código | Descripción |
|------|--------|-------------|
| product_sale | 4101 | Ventas de Productos |
| student_fee | 4102 | Cuotas Estudiantiles |
| treatment | 4103 | Prestaciones Clínicas |
| service_sale | 4104 | Servicios Diversos |
| (otro) | 4100 | Ventas Generales |

Si el punto tiene `cuenta_ventas_id` configurada, se usa esa en lugar de la selección automática.

## Ejemplos de Asientos Generados

### Ejemplo 1: Venta de Producto en BOX

```
ASIENTO 240523-00001
Fecha: 23/05/2024
Concepto: Venta - product_sale

Movimientos:
├─ DEBE: 1101 Caja General = $1,210.00
└─ HABER: 4101 Ventas Productos = $1,000.00
└─ HABER: 2101 IVA por Pagar = $210.00
```

### Ejemplo 2: Cuota de Postgrado con Tarjeta

```
ASIENTO 240524-00002
Fecha: 24/05/2024
Concepto: Venta - student_fee

Movimientos:
├─ DEBE: 1301 Deudores Tarjeta = $5,000.00
└─ HABER: 4102 Cuotas Estudiantiles = $5,000.00
```

## Consultas

### Obtener un Asiento

```php
use App\Models\AsientoContable;

$asiento = AsientoContable::where('numero_asiento', '240523-00001')->first();
$asiento->movimientos; // Acceder a los movimientos
```

### Filtrar por Período

```php
// Asientos de mayo 2024
$asientos = AsientoContable::desdeMes(5, 2024)->get();

// Asientos de un punto específico
$asientos = AsientoContable::where('punto_venta_id', 1)->get();

// Verificar que balanceen
$balance = $asiento->verificarBalance(); // true/false
```

### Reportes

```php
use App\Services\ContabilidadService;

$contabilidad = new ContabilidadService();

// Balance de comprobación
$balance = $contabilidad->obtenerBalanceComprobacion('2024-05-01', '2024-05-31');

// Saldo de cuenta en período
$saldo = $contabilidad->obtenerSaldoCuenta($cuenta, '2024-05-01', '2024-05-31');
```

## Validaciones

El sistema realiza validaciones automáticas:

- ✓ Verifica que DEBE = HABER en cada asiento
- ✓ Valida que las cuentas existan y estén activas
- ✓ Verifica que el punto de venta tenga configuración contable
- ✓ Registra errores en los logs para auditoría

## Troubleshooting

### Asiento no se genera

**Causa posible**: Cuentas no configuradas

```bash
php artisan contable:validar-configuracion
```

Revisa la salida y asegúrate de que todas las cuentas existan.

### Error "Cuenta no encontrada"

**Causa posible**: La cuenta_caja_id o cuenta_ventas_id no existe

Verifica en la BD:
```sql
SELECT id, codigo, nombre FROM cuentas_contables WHERE codigo IN ('1101', '4101', '4102', '4103', '4104');
```

### Asientos no balancean

**Causa posible**: IVA mal calculado

Revisa que el tax_amount de la venta sea correcto. El asiento debe tener:
- DEBE = total de venta
- HABER = (total - tax) + tax (distribuido en dos líneas)

## Próximas Mejoras

- [ ] Integrar reversión de asientos
- [ ] Soporte para notas de crédito
- [ ] Generación automática de comprobante fiscal
- [ ] Reportes contables avanzados
- [ ] Auditoría de cambios en asientos

---

**Última actualización**: Mayo 2024
**Sistema**: Cooperadora v1.0 - Contabilidad Automática
