# Sistema POS Cooperadora - Facultad de Odontología

## 🏥 Descripción del Proyecto

Sistema integral de punto de venta y gestión administrativa para la cooperadora de una facultad de odontología, diseñado para manejar múltiples puntos de venta y cumplir con los requisitos fiscales argentinos.

## 🎯 Funcionalidades Principales

### 📊 **Gestión Multi-sucursal**
- **Cooperadora**: Venta de productos de laboratorio, cobro de cuotas estudiantiles
- **Postgrado**: Gestión de cuotas de cursos y diplomaturas
- **Centro Odontológico**: Facturación de tratamientos dentales

### 💰 **Sistema de Ventas**
- Punto de venta integrado
- Múltiples métodos de pago (efectivo, tarjeta, transferencia)
- Facturación electrónica AFIP
- Control de stock automático
- Sistema de descuentos y promociones

### 👥 **Gestión de Usuarios**
- Roles: Admin, Supervisor, Cajero, Auditor
- Permisos granulares por módulo
- Trazabilidad completa de acciones
- Autenticación segura

### 🎓 **Gestión Académica**
- **Estudiantes de Tecnicaturas**: Cuotas mensuales
- **Estudiantes de Grado**: Cuotas anuales
- **Postgrado**: Cursos y diplomaturas
- Control de pagos y estados académicos

### 🧾 **Compliance Fiscal Argentino**
- Integración con AFIP/ARCA
- Generación de comprobantes A, B, C
- CAE/CAEA automático
- Libro IVA Digital
- Percepciones y retenciones

### 📋 **Sistema de Auditoría**
- Logging completo de cambios
- Trazabilidad de modificaciones de precios
- Control de accesos por usuario
- Reportes de auditoría detallados

### 💼 **Gestión de Empleados**
- Control de nómina y sueldos
- Registro de datos laborales
- Historial de pagos

## 🏗️ **Arquitectura Técnica**

### **Stack Tecnológico**
- **Backend**: Laravel 11
- **Base de Datos**: PostgreSQL
- **Auditoría**: Laravel Auditing
- **Roles/Permisos**: Spatie Laravel Permission
- **Desarrollo**: Windows
- **Producción**: Linux Ubuntu

### **Estructura de Base de Datos**

#### **Tablas Principales**
- `branches` - Sucursales del sistema
- `users` - Usuarios del sistema con roles
- `products` - Productos y servicios
- `students` - Estudiantes y sus datos académicos
- `employees` - Empleados para nómina
- `sales` - Ventas realizadas
- `sale_items` - Detalles de cada venta
- `payment_methods` - Métodos de pago disponibles
- `chart_of_accounts` - Plan de cuentas contable
- `cash_movements` - Movimientos de caja
- `stock_movements` - Movimientos de inventario
- `audits` - Log completo de auditoría

#### **Tipos de Productos/Servicios**
- **Productos de Laboratorio**: Hipoclorito, agua destilada, kits de cirugía
- **Cuotas Estudiantiles**: Tecnicaturas (mensual), Grado (anual)
- **Tratamientos Odontológicos**: Consultas, cirugías, etc.
- **Cuotas de Postgrado**: Cursos, diplomaturas
- **Items Varios**: Servicios adicionales

## 📋 **Plan de Cuentas Contables**

*Estructura preparada para cargar plan de cuentas argentino estándar*

```
chart_of_accounts:
├── code (1.1.1.001)
├── parent_code (1.1.1)
├── name
├── type (asset/liability/equity/revenue/expense)
└── nature (debit/credit)
```

## 🚀 **Configuración Inicial**

### **Requisitos del Sistema**
- PHP 8.1+
- PostgreSQL 12+
- Composer
- Node.js (para assets)

### **Instalación**
```bash
# Clonar y configurar
composer install
cp .env.example .env

# Configurar base de datos PostgreSQL
DB_CONNECTION=pgsql
DB_DATABASE=cooperadora_pos
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# Ejecutar migraciones y seeders
php artisan key:generate
php artisan migrate
php artisan db:seed --class=InitialDataSeeder
```

### **Datos Iniciales**
- **Sucursales**: Cooperadora, Postgrado, Centro Odontológico
- **Usuario Admin**: admin@cooperadora.edu.ar / admin123
- **Métodos de Pago**: Efectivo, Tarjetas, Transferencia
- **Roles**: Admin, Supervisor, Cajero, Auditor

## 🔐 **Configuración Fiscal AFIP**

*Para configurar posteriormente:*
- Certificado digital AFIP
- Tipo de contribuyente
- Puntos de venta habilitados
- Configuración CAE/CAEA

## 📊 **Próximos Desarrollos**

1. **Integración AFIP**: Web Services de facturación electrónica
2. **Reportes Avanzados**: Dashboard analítico
3. **Módulo Contable**: Libros contables automáticos
4. **App Mobile**: Para consultas y ventas
5. **Integración Bancaria**: Conciliación automática
6. **Sistema de Compras**: Gestión de proveedores

## 🛡️ **Seguridad y Auditoría**

- Todas las operaciones críticas son auditadas
- Control de acceso basado en roles
- Logging de cambios de precios
- Trazabilidad completa de transacciones
- Backup automático de base de datos

## 📞 **Soporte**

Sistema desarrollado específicamente para las necesidades de la cooperadora de la facultad de odontología, con capacidad de expansión y cumplimiento normativo argentino.

---
*Documentación del sistema POS Cooperadora v1.0*
