# 🚀 Guía de Deployment CI/CD - Sistema POS Cooperadora

## 📋 Índice
1. [Configuración del Servidor](#configuración-del-servidor)
2. [Secrets de GitHub](#secrets-de-github)
3. [Deployment Manual](#deployment-manual)
4. [Docker (Alternativa)](#docker-alternativa)
5. [Configuración SSL](#configuración-ssl)
6. [Backups](#backups)
7. [Monitoreo](#monitoreo)

## 🖥️ Configuración del Servidor

### Requisitos del Sistema
- **OS**: Ubuntu Server 20.04 LTS o superior
- **PHP**: 8.4.18
- **PostgreSQL**: 14
- **Apache**: 2.4
- **RAM**: Mínimo 2GB (recomendado 4GB)
- **Almacenamiento**: Mínimo 20GB SSD

### Preparación Inicial del Servidor

```bash
# 1. Actualizar sistema
sudo apt update && sudo apt upgrade -y

# 2. Instalar herramientas básicas
sudo apt install -y git curl wget unzip software-properties-common

# 3. Configurar firewall
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable

# 4. Crear usuario para deployment
sudo adduser deploy
sudo usermod -aG sudo deploy
sudo mkdir -p /home/deploy/.ssh
# Copiar tu clave SSH pública a /home/deploy/.ssh/authorized_keys
```

## 🔑 Secrets de GitHub

Configurar en GitHub: **Settings > Secrets and variables > Actions**

```yaml
HOST: tu-servidor.com
USERNAME: deploy
SSH_KEY: |
  -----BEGIN OPENSSH PRIVATE KEY-----
  [tu-clave-privada-ssh]
  -----END OPENSSH PRIVATE KEY-----
PORT: 22
```

### Generar Clave SSH para Deployment

```bash
# En tu máquina local
ssh-keygen -t ed25519 -C "deploy@cooperadora" -f ~/.ssh/cooperadora_deploy

# Copiar clave pública al servidor
ssh-copy-id -i ~/.ssh/cooperadora_deploy.pub deploy@tu-servidor.com

# La clave privada va en el secret SSH_KEY de GitHub
cat ~/.ssh/cooperadora_deploy
```

## 🔧 Deployment Manual

### Opción 1: Script Automatizado

```bash
# En el servidor
git clone https://github.com/tu-usuario/cooperadora.git /tmp/cooperadora
cd /tmp/cooperadora
chmod +x deployment/deploy.sh
sudo ./deployment/deploy.sh production
```

### Opción 2: Paso a Paso

```bash
# 1. Clonar repositorio
cd /var/www
sudo git clone https://github.com/tu-usuario/cooperadora.git
sudo chown -R www-data:www-data cooperadora

# 2. Instalar dependencias
cd cooperadora
sudo -u www-data composer install --no-dev --optimize-autoloader

# 3. Configurar .env
sudo -u www-data cp .env.production .env
sudo -u www-data php artisan key:generate

# 4. Configurar base de datos
sudo -u postgres createdb cooperadora_pos_prod
sudo -u postgres createuser cooperadora_user
sudo -u postgres psql -c "ALTER USER cooperadora_user WITH PASSWORD 'tu_password_segura';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE cooperadora_pos_prod TO cooperadora_user;"

# 5. Ejecutar migraciones
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force  # Solo para datos iniciales

# 6. Optimizar
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 7. Configurar Apache (usar script ssl-setup.sh para HTTPS)
```

## 🐳 Docker (Alternativa)

### Deployment con Docker Compose

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/cooperadora.git
cd cooperadora

# 2. Configurar variables de entorno
echo "DB_PASSWORD=tu_password_muy_segura" > .env.docker

# 3. Construir e iniciar
docker-compose up -d --build

# 4. Ejecutar migraciones
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

### Comandos Útiles Docker

```bash
# Ver logs
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash

# Backup de base de datos
docker-compose exec db pg_dump -U cooperadora_user cooperadora_pos > backup.sql

# Restaurar base de datos
docker-compose exec -i db psql -U cooperadora_user cooperadora_pos < backup.sql
```

## 🔒 Configuración SSL

### Let's Encrypt (Recomendado)

```bash
# Ejecutar script automatizado
cd /var/www/cooperadora
sudo chmod +x deployment/ssl-setup.sh
sudo ./deployment/ssl-setup.sh tu-dominio.com
```

### Configuración Manual SSL

```bash
# 1. Instalar Certbot
sudo apt install certbot python3-certbot-apache

# 2. Obtener certificado
sudo certbot --apache -d tu-dominio.com

# 3. Verificar renovación automática
sudo crontab -e
# Agregar: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 💾 Backups

### Backup Automatizado

```bash
# Configurar cron para backup diario
sudo crontab -e
# Agregar: 0 2 * * * /var/www/cooperadora/deployment/backup.sh >/dev/null 2>&1
```

### Backup Manual

```bash
cd /var/www/cooperadora
sudo chmod +x deployment/backup.sh
sudo ./deployment/backup.sh
```

### Restaurar Backup

```bash
cd /var/backups/cooperadora
sudo ./restore_YYYYMMDD_HHMMSS.sh
```

## 📊 Monitoreo

### Logs Importantes

```bash
# Laravel
tail -f /var/www/cooperadora/storage/logs/laravel.log

# Apache
tail -f /var/log/apache2/cooperadora_error.log

# PostgreSQL
sudo tail -f /var/log/postgresql/postgresql-14-main.log

# Sistema
sudo journalctl -f
```

### Comandos de Diagnóstico

```bash
# Estado de servicios
sudo systemctl status apache2 php8.4-fpm postgresql

# Uso de recursos
htop
df -h
free -h

# Conexiones de base de datos
sudo -u postgres psql -c "SELECT * FROM pg_stat_activity WHERE datname='cooperadora_pos_prod';"

# Verificar configuración Laravel
cd /var/www/cooperadora
sudo -u www-data php artisan about
sudo -u www-data php artisan config:show database
```

## 🔧 Troubleshooting

### Problemas Comunes

1. **Error 500 - Internal Server Error**
   ```bash
   # Verificar logs de Apache y Laravel
   sudo tail -f /var/log/apache2/cooperadora_error.log
   sudo tail -f /var/www/cooperadora/storage/logs/laravel.log
   
   # Verificar permisos
   sudo chown -R www-data:www-data /var/www/cooperadora
   sudo chmod -R 775 /var/www/cooperadora/storage /var/www/cooperadora/bootstrap/cache
   ```

2. **Error de conexión a base de datos**
   ```bash
   # Verificar servicio PostgreSQL
   sudo systemctl status postgresql
   
   # Probar conexión
   sudo -u postgres psql -h localhost -U cooperadora_user -d cooperadora_pos_prod
   
   # Verificar configuración .env
   grep DB_ /var/www/cooperadora/.env
   ```

3. **Problemas de SSL**
   ```bash
   # Verificar certificado
   sudo certbot certificates
   
   # Renovar manualmente
   sudo certbot renew
   
   # Verificar configuración Apache
   sudo apache2ctl configtest
   ```

## 📈 Optimizaciones de Producción

### PHP Optimizations

```ini
# /etc/php/8.4/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### PostgreSQL Optimizations

```sql
# postgresql.conf
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
```

### Apache Optimizations

```apache
# Habilitar compresión
LoadModule deflate_module modules/mod_deflate.so

<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png|ico)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>
```

## 🚀 Proceso de Release

1. **Desarrollo** → Push a `develop`
2. **Testing** → CI/CD ejecuta tests automáticamente
3. **Staging** → Merge a `main` → Deploy automático a staging
4. **Producción** → Tag release → Deploy manual o automático

```bash
# Crear release
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

---

**¡Tu sistema POS Cooperadora está listo para producción! 🎉**
