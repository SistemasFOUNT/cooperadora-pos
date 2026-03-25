# 🖥️ Configuración para Desarrollo Windows

Este archivo contiene instrucciones específicas para el desarrollo en entorno Windows.

## 🚀 Setup Inicial en Windows

### Prerrequisitos
- **XAMPP** o **Laragon** para PHP 8.2+
- **PostgreSQL 14+** para Windows
- **Node.js** LTS
- **Composer**
- **Git for Windows**

### Instalación Rápida

#### 1. Clonar el Repositorio
```cmd
git clone https://github.com/tu-usuario/cooperadora-pos.git
cd cooperadora-pos
```

#### 2. Configuración de Base de Datos
```sql
-- En PostgreSQL (pgAdmin o línea de comandos)
CREATE DATABASE cooperadora_pos;
CREATE USER cooperadora_user WITH PASSWORD 'tu_password';
GRANT ALL PRIVILEGES ON DATABASE cooperadora_pos TO cooperadora_user;
```

#### 3. Configuración del Entorno
```cmd
copy .env.example .env
```

Editar `.env` con configuración para Windows:
```env
# Configuración para desarrollo en Windows
APP_NAME="Sistema POS Cooperadora"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=America/Argentina/Buenos_Aires
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cooperadora_pos
DB_USERNAME=cooperadora_user
DB_PASSWORD=tu_password_aqui

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Configuraciones específicas de Windows
LOG_CHANNEL=daily
LOG_LEVEL=debug
```

#### 4. Instalación de Dependencias
```cmd
# Instalar dependencias PHP
composer install

# Generar clave de aplicación
php artisan key:generate

# Instalar dependencias Node.js
npm install

# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# Compilar assets para desarrollo
npm run dev
```

## 🔧 Comandos de Desarrollo

### Servidor de Desarrollo
```cmd
# Iniciar servidor Laravel
php artisan serve

# En otra terminal - compilar assets en modo watch
npm run dev
```

### Base de Datos
```cmd
# Resetear base de datos
php artisan migrate:fresh --seed

# Crear nueva migración
php artisan make:migration nombre_de_migracion

# Crear nuevo seeder
php artisan make:seeder NombreSeeder
```

### Cache y Optimizaciones (Solo para testing)
```cmd
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción (NO usar en desarrollo)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🐛 Debugging en Windows

### Logs
```cmd
# Ver logs en tiempo real (requiere PowerShell o Git Bash)
Get-Content -Path "storage/logs/laravel.log" -Wait -Tail 50
```

### Testing
```cmd
# Ejecutar tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage
```

## 📁 Estructura de Archivos en Windows

### Rutas Importantes
- **Logs**: `storage\logs\laravel.log`
- **Cache**: `storage\framework\cache\`
- **Sessions**: `storage\framework\sessions\`
- **Views**: `storage\framework\views\`

### Permisos (No aplicable en Windows)
Windows maneja permisos diferente a Linux. No es necesario ejecutar comandos chmod.

## 🔄 Sincronización con Producción

### Variables de Entorno
Asegúrate de que las siguientes variables estén configuradas apropiadamente:

#### Desarrollo (Windows)
```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

#### Producción (Ubuntu)
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🚀 Deployment desde Windows

### Preparación para CI/CD
```cmd
# Verificar que todo funcione
php artisan config:clear
php artisan test

# Commit y push
git add .
git commit -m "Feature: descripción del cambio"
git push origin main
```

### Testing Local del Environment de Producción
```cmd
# Usar Docker para simular producción
docker-compose up -d

# O usar el .env.production localmente (cuidado con la base de datos)
copy .env.production .env.testing.local
# Editar para usar base de datos local
php artisan config:clear --env=testing.local
```

## ⚠️ Consideraciones Específicas de Windows

### Line Endings
```cmd
# Configurar Git para manejar line endings correctamente
git config core.autocrlf true
git config core.eol lf
```

### Rutas
- Windows usa `\` para rutas, Laravel maneja esto automáticamente
- Los scripts de deployment son para Linux, no ejecutar en Windows
- Usar PowerShell o Git Bash para mejor compatibilidad

### Servicios
- En desarrollo no necesitas Apache/Nginx
- Usa `php artisan serve` para el servidor de desarrollo
- PostgreSQL debe estar ejecutándose como servicio de Windows

## 🔧 Troubleshooting Windows

### Error de Permisos
```cmd
# Si hay errores de permisos en storage/
# Verificar que el usuario actual tenga permisos de escritura
icacls storage /grant Users:F /T
```

### Error de OpenSSL
```cmd
# Si hay errores de SSL/TLS
# Verificar que php.ini tenga habilitado openssl
php -m | findstr openssl
```

### Error de Base de Datos
```cmd
# Verificar conexión a PostgreSQL
php artisan tinker
DB::connection()->getPdo();
```

---

**Nota**: Este archivo es específico para desarrollo en Windows. 
Para producción en Ubuntu, consulta `DEPLOYMENT-GUIDE.md`.