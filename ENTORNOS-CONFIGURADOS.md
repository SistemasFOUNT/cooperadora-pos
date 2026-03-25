# ✅ CONFIGURACIÓN COMPLETA: WINDOWS (DEV) + UBUNTU (PROD)

## 🎯 Diferenciación de Entornos Configurada

### 🖥️ **Entorno de Desarrollo - WINDOWS**
- ✅ **Setup automatizado**: `setup-windows.bat`
- ✅ **Documentación específica**: `DESARROLLO-WINDOWS.md`
- ✅ **Configuraciones optimizadas**: `.env.example`
- ✅ **Comandos Windows**: PowerShell/CMD compatibles

#### Características Windows:
```
🔧 Servidor: php artisan serve (puerto 8000)
🗄️ Base de Datos: PostgreSQL para Windows
📁 Cache: File-based (storage/framework/cache)
⚡ Queues: Sync (inmediato)
📝 Logs: Debug level para desarrollo
🔧 Assets: npm run dev (watch mode)
```

### 🐧 **Entorno de Producción - UBUNTU**
- ✅ **CI/CD automatizado**: GitHub Actions
- ✅ **Scripts de deployment**: `deployment/*.sh`
- ✅ **Configuraciones optimizadas**: `.env.production`
- ✅ **Stack específico**: Apache + PostgreSQL 14 + Redis

#### Características Ubuntu:
```
🌐 Servidor: Apache 2 + mod_php 8.4.18
🗄️ Base de Datos: PostgreSQL 14
⚡ Cache: Redis (performance optimizado)
🔄 Queues: Redis con workers background
📝 Logs: Error level (producción)
🔒 SSL: Let's Encrypt automático
📊 Monitoreo: Health checks automatizados
💾 Backups: Diarios con rotación
```

---

## 📋 **Archivos de Configuración por Entorno**

### 🖥️ Desarrollo Windows
```
📁 DESARROLLO-WINDOWS.md     # Guía específica Windows
📁 setup-windows.bat         # Setup automatizado
📁 .env.example              # Configuración PostgreSQL local
📁 package.json              # Scripts npm para desarrollo
```

### 🐧 Producción Ubuntu
```
📁 .github/workflows/ci-cd.yml    # Pipeline CI/CD
📁 deployment/deploy.sh           # Deployment Ubuntu
📁 deployment/ssl-setup.sh        # SSL Let's Encrypt
📁 deployment/backup.sh           # Backups automáticos
📁 deployment/health-check.sh     # Monitoreo
📁 .env.production                # Variables producción
📁 Dockerfile                     # Container (opcional)
📁 docker-compose.yml             # Stack completo
```

---

## 🚀 **Workflows por Entorno**

### 🖥️ Desarrollo en Windows

#### Setup Inicial (Una sola vez):
```cmd
# Opción 1: Automatizado
setup-windows.bat

# Opción 2: Manual
git clone repo-url
cd Cooperadora
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate:fresh --seed
```

#### Workflow Diario:
```cmd
# Iniciar desarrollo
php artisan serve

# En otra terminal
npm run dev

# Testing
php artisan test

# Al finalizar
git add .
git commit -m "descripción"
git push origin main
```

### 🐧 Producción en Ubuntu

#### Setup Inicial (Una sola vez):
```bash
# Configurar GitHub Secrets
SERVER_HOST=tu_ip_ubuntu
SERVER_USER=tu_usuario
SERVER_KEY=clave_ssh_base64
DB_PASSWORD=password_seguro
APP_KEY=tu_app_key

# Deployment automático
git push origin main  # Se despliega automáticamente
```

#### Mantenimiento:
```bash
# Verificar estado
./deployment/health-check.sh

# Backup manual
./deployment/backup.sh

# Ver logs
tail -f storage/logs/laravel.log
```

---

## 🔧 **Variables de Entorno Diferenciadas**

### .env (Desarrollo Windows)
```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=cooperadora_pos

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### .env.production (Ubuntu)
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=cooperadora_pos_prod

SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 📊 **Comparativa de Performance**

| Aspecto | Windows (Dev) | Ubuntu (Prod) |
|---------|---------------|---------------|
| **Servidor** | PHP integrado | Apache 2 |
| **Performance** | Desarrollo | Optimizado |
| **Cache** | File | Redis |
| **Sesiones** | File | Database/Redis |
| **Queues** | Sync | Background |
| **SSL** | No requerido | Let's Encrypt |
| **Backups** | Manual | Automático |
| **Monitoreo** | Logs locales | Health checks |

---

## 🎯 **Resultado Final**

### ✅ **Lo que tienes ahora:**

1. **📝 Setup Windows Completo**
   - Script automatizado de instalación
   - Documentación específica para Windows
   - Configuraciones optimizadas para desarrollo

2. **🚀 Deployment Ubuntu Automatizado**
   - CI/CD con GitHub Actions
   - Scripts específicos para Ubuntu + Apache + PostgreSQL 14
   - SSL, backups y monitoreo automático

3. **🔄 Diferenciación Clara de Entornos**
   - Configuraciones específicas por entorno
   - Workflows diferenciados
   - Optimizaciones apropiadas para cada uso

### 🚀 **Para usar:**

#### En Windows (Desarrollo):
```cmd
setup-windows.bat
php artisan serve
```

#### En Ubuntu (Producción):
```bash
git push origin main
# ¡El CI/CD hace todo automáticamente!
```

---

**🎉 ¡SISTEMA COMPLETAMENTE PREPARADO PARA AMBOS ENTORNOS!**

*Desarrollo optimizado para Windows + Producción automatizada para Ubuntu Server*
