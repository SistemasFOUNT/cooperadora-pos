# 🔄 Diferencias entre Entornos: Windows (Desarrollo) vs Ubuntu (Producción)

## 📋 Resumen de Configuraciones

### 🖥️ Entorno de Desarrollo (Windows)
- **Sistema Operativo**: Windows 10/11
- **Servidor Web**: `php artisan serve` (servidor integrado)
- **Base de Datos**: PostgreSQL para Windows
- **Cache**: File-based cache
- **Sesiones**: File-based sessions
- **Queues**: Sync (inmediato)
- **Logs**: Debug level, daily rotation

### 🐧 Entorno de Producción (Ubuntu Server)
- **Sistema Operativo**: Ubuntu Server 20.04+
- **Servidor Web**: Apache 2 + mod_php
- **Base de Datos**: PostgreSQL 14
- **PHP**: 8.4.18
- **Cache**: Redis
- **Sesiones**: Database/Redis
- **Queues**: Redis con workers
- **Logs**: Error level, optimized

---

## ⚙️ Configuraciones Específicas

### Variables de Entorno (.env)

| Variable | Desarrollo (Windows) | Producción (Ubuntu) |
|----------|---------------------|---------------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `LOG_LEVEL` | `debug` | `error` |
| `SESSION_DRIVER` | `file` | `database` |
| `CACHE_DRIVER` | `file` | `redis` |
| `QUEUE_CONNECTION` | `sync` | `redis` |
| `DB_HOST` | `127.0.0.1` | `localhost` |

### Comandos de Optimización

#### 🖥️ Desarrollo (Windows) - NO ejecutar:
```cmd
REM NO USAR EN DESARROLLO
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 🐧 Producción (Ubuntu) - Ejecutar:
```bash
# SÍ USAR EN PRODUCCIÓN
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 📁 Estructura de Archivos

### Rutas de Sistema

| Recurso | Windows | Ubuntu |
|---------|---------|---------|
| Separador de rutas | `\` | `/` |
| Logs | `storage\logs\` | `storage/logs/` |
| Cache | `storage\framework\cache\` | `/var/cache/cooperadora/` |
| Uploads | `storage\app\public\` | `storage/app/public/` |
| Config Apache | N/A | `/etc/apache2/sites-available/` |

### Permisos de Archivos

#### 🖥️ Windows
```cmd
REM Los permisos se manejan automáticamente
REM Si hay problemas, usar:
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

#### 🐧 Ubuntu
```bash
# Permisos específicos para Laravel
sudo chown -R www-data:www-data /var/www/cooperadora
sudo chmod -R 755 /var/www/cooperadora
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🚀 Comandos de Deployment

### Desde Windows (Desarrollo)
```cmd
REM Verificar antes de deployment
php artisan config:clear
php artisan test
git status

REM Preparar para producción
git add .
git commit -m "Feature: descripción"
git push origin main

REM El CI/CD se encarga del resto automáticamente
```

### En Ubuntu (Producción)
```bash
# Deployment manual (alternativo al CI/CD)
./deployment/deploy.sh production

# Configurar SSL
./deployment/ssl-setup.sh tu-dominio.com

# Verificar estado
./deployment/health-check.sh
```

---

## 🔧 Herramientas de Desarrollo

### 🖥️ Windows - Herramientas Recomendadas
- **IDE**: Visual Studio Code
- **Database**: pgAdmin 4
- **Git**: Git for Windows
- **Terminal**: PowerShell o Git Bash
- **PHP Server**: XAMPP, Laragon, o `php artisan serve`

### 🐧 Ubuntu - Servicios de Producción
- **Web Server**: Apache 2.4
- **Database**: PostgreSQL 14
- **Cache**: Redis Server
- **Process Manager**: Supervisor (para queues)
- **SSL**: Let's Encrypt (Certbot)

---

## 📊 Performance y Monitoreo

### Desarrollo (Windows)
```cmd
REM Ver logs en tiempo real
Get-Content -Path "storage/logs/laravel.log" -Wait -Tail 50

REM Debugging con Tinker
php artisan tinker

REM Testing local
php artisan test --parallel
```

### Producción (Ubuntu)
```bash
# Logs del sistema
tail -f /var/log/apache2/error.log
tail -f storage/logs/laravel.log

# Monitoreo de salud
./deployment/health-check.sh

# Performance monitoring
htop
sudo systemctl status apache2
sudo systemctl status postgresql
```

---

## ⚠️ Consideraciones Importantes

### Line Endings
```cmd
REM Configurar Git para manejar correctamente los line endings
git config --global core.autocrlf true
git config --global core.eol lf
```

### Encoding
- **Windows**: UTF-8 con BOM (algunos editores)
- **Ubuntu**: UTF-8 sin BOM (standard)

### Timezone
```env
# Ambos entornos
APP_TIMEZONE=America/Argentina/Buenos_Aires
```

### Database
```sql
-- En ambos entornos, configurar timezone en PostgreSQL
ALTER DATABASE cooperadora_pos SET timezone TO 'America/Argentina/Buenos_Aires';
```

---

## 🐛 Troubleshooting Común

### Problemas en Windows
1. **Permisos storage**: `icacls storage /grant Users:F /T`
2. **OpenSSL**: Verificar `extension=openssl` en php.ini
3. **PostgreSQL**: Verificar que el servicio esté corriendo
4. **Composer timeout**: `composer install --no-dev`

### Problemas en Ubuntu
1. **Permisos Apache**: `sudo chown -R www-data:www-data`
2. **PHP Extensions**: `sudo apt install php8.4-{pgsql,mbstring,xml}`
3. **PostgreSQL**: `sudo systemctl restart postgresql`
4. **SSL**: `sudo certbot renew --dry-run`

---

## 📋 Checklist de Deployment

### ✅ Antes del Deploy (Windows)
- [ ] Tests pasan: `php artisan test`
- [ ] Config limpia: `php artisan config:clear`
- [ ] Assets compilados: `npm run build`
- [ ] Código committed: `git status`
- [ ] Branch actualizado: `git push origin main`

### ✅ Después del Deploy (Ubuntu)
- [ ] Aplicación accesible: `curl -I https://tu-dominio.com`
- [ ] Base de datos conectada: `php artisan tinker --execute="DB::connection()->getPdo()"`
- [ ] SSL funcionando: `https://` accesible
- [ ] Backups configurados: `./deployment/backup.sh`
- [ ] Monitoreo activo: `./deployment/health-check.sh`

---

Este documento asegura que el desarrollo en Windows sea consistente con la producción en Ubuntu, manteniendo las mejores prácticas para cada entorno.
