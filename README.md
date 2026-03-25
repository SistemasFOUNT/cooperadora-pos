# 🏪 Sistema POS Cooperadora - Facultad de Odontología

Sistema de punto de venta integral para la gestión de ventas, inventario, estudiantes y control fiscal de la Cooperadora de la Facultad de Odontología.

## 🚀 Características Principales

### 📋 Módulos Implementados

- **🛒 Punto de Venta (POS)**: Interfaz intuitiva para ventas rápidas
- **📦 Gestión de Productos**: Control de inventario con categorías específicas
- **🎓 Gestión de Estudiantes**: Administración de cuotas y pagos estudiantiles  
- **💰 Control de Caja**: Arqueos, movimientos y conciliación
- **📊 Auditoría Completa**: Trazabilidad total de operaciones
- **👥 Usuarios y Roles**: Sistema granular de permisos

### 🏢 Multi-Sucursal

- **Cooperadora**: Punto de venta principal
- **Postgrado**: Gestión de cuotas de postgrado
- **Centro Odontológico**: Facturación de tratamientos

### 🇦🇷 Cumplimiento Fiscal Argentino

- Preparado para integración con **AFIP**
- Soporte para facturación electrónica
- Tipos de comprobante A, B, C
- Generación de CAE/CAEA
- Libro IVA Digital

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 11
- **Database**: PostgreSQL
- **Frontend**: Bootstrap 5 + jQuery
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Auditing**: Laravel Auditing
- **Development**: Windows / Production: Ubuntu Linux

## 📋 Requisitos del Sistema

- PHP 8.2 o superior
- PostgreSQL 13+
- Composer 2.x
- Node.js 18+ (para assets)

## ⚡ Instalación

### 1. Clonar Repositorio

```bash
git clone https://github.com/tu-usuario/sistema-pos-cooperadora.git
cd sistema-pos-cooperadora
```

### 2. Configurar Dependencias

```bash
composer install
```

### 3. Configurar Entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar Base de Datos

Editar `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cooperadora_pos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 5. Ejecutar Migraciones

```bash
php artisan migrate
php artisan db:seed --class=InitialDataSeeder
```

### 6. Ejecutar Aplicación

```bash
php artisan serve
```

## 👤 Credenciales Iniciales

- **Email**: admin@cooperadora.edu.ar
- **Password**: admin123
- **Rol**: Administrador

> ⚠️ **Importante**: Cambiar credenciales en producción

## 🎯 Tipos de Productos

### Categorías Disponibles

1. **🧪 Laboratorio**: Insumos odontológicos (hipoclorito, agua destilada, kits de cirugía)
2. **🦷 Tratamientos**: Servicios odontológicos
3. **🎓 Cuotas Estudiantes**: Técnicaturas (mensual) y Grado (anual)
4. **📚 Postgrado**: Cursos y diplomaturas

## 🔐 Sistema de Roles

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso total al sistema |
| **Supervisor** | Gestión de ventas, productos, reportes |
| **Cajero** | Solo POS y caja |
| **Auditor** | Solo lectura y auditorías |

## 📊 Estructura Principal

### Tablas Clave
- `branches` - Sucursales
- `products` - Productos e insumos  
- `students` - Estudiantes
- `sales` / `sale_items` - Ventas y detalle
- `cash_movements` - Movimientos de caja
- `audits` - Log de auditoría

## 🚀 Próximas Funcionalidades

- [ ] **Integración AFIP**: Facturación electrónica
- [ ] **Dashboard Analytics**: Métricas en tiempo real
- [ ] **Reportes Avanzados**: PDF y Excel
- [ ] **App Móvil**: Para inventario

---

**Desarrollado para la Facultad de Odontología** 🦷

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
