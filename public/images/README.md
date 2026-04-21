# Imágenes del Sistema FOUNT Contable

Esta carpeta contiene todas las imágenes personalizables del sistema, organizadas para facilitar el mantenimiento.

## Estructura de Directorios:

### `/public/images/system/`
- **fount-logo.png** - Logo principal del sistema
- **fount-favicon.ico** - Ícono del navegador
- **auth-bg.jpg** - Imagen de fondo para login/registro
- **dashboard-banner.jpg** - Banner del dashboard principal

### `/public/images/users/`
- **default-avatar.png** - Avatar por defecto para usuarios
- **admin-avatar.png** - Avatar específico para administradores
- **placeholder.png** - Imagen placeholder genérica

### `/public/images/logos/`
- **box-logo.png** - Logo específico del BOX Cooperadora
- **postgrado-logo.png** - Logo específico de Postgrado
- **odonto-logo.png** - Logo específico del Centro Odontológico

## Configuración:

Todas las referencias a estas imágenes están configuradas en:
- `config/adminlte.php` - Configuración principal de AdminLTE
- `app/Models/User.php` - Avatares de usuario
- Views específicas - Logos por punto de venta

## Recomendaciones:

### Formatos soportados:
- **PNG**: Para logos con transparencia
- **JPG**: Para fotografías y fondos
- **ICO**: Para favicons
- **SVG**: Para íconos escalables (opcional)

### Tamaños recomendados:
- **Logo principal**: 200x60px máximo
- **Favicons**: 32x32px, 16x16px
- **Avatares**: 160x160px
- **Banners**: 1200x300px

## Personalización:

1. Reemplaza cualquier imagen manteniendo el mismo nombre
2. Respeta los tamaños recomendados
3. Limpia caché después de cambios: `php artisan config:clear`

## Ventajas de esta organización:

✅ **Control total** sobre todas las imágenes del sistema
✅ **Fácil personalización** sin tocar código
✅ **Backup simple** - solo respalda esta carpeta
✅ **No dependes de AdminLTE** para imágenes personalizadas
✅ **Estructura clara** y mantenible
