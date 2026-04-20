# 🚀 Deployment Rápido en Ubuntu - GUÍA PASO A PASO

## ✅ Prerrequisitos Verificados
- ✅ Ubuntu Server
- ✅ PostgreSQL instalado
- ✅ PHP instalado  
- ✅ Composer instalado
- ✅ Apache2 instalado

## 📋 Pasos para Deployment

### 1. Transferir Código al Servidor

#### Opción A: Git Clone (Recomendado)
```bash
# En tu servidor Ubuntu
cd /tmp
git clone https://github.com/tu-usuario/cooperadora-pos.git
cd cooperadora-pos
```

#### Opción B: Subir archivos directamente
```bash
# Desde tu Windows, usando SCP o SFTP
# O copiar los archivos manualmente
```

### 2. Ejecutar Script de Deployment
```bash
# Hacer ejecutable el script
chmod +x deployment/quick-deploy.sh

# Ejecutar deployment
sudo ./deployment/quick-deploy.sh
```

### 3. ¡Listo! 
El script se encarga de:
- ✅ Verificar/instalar extensiones PHP necesarias
- ✅ Crear base de datos PostgreSQL
- ✅ Configurar usuario de base de datos
- ✅ Copiar archivos a `/var/www/cooperadora`
- ✅ Instalar dependencias con Composer
- ✅ Configurar `.env` para producción
- ✅ Ejecutar migraciones y seeders
- ✅ Optimizar Laravel para producción
- ✅ Configurar Apache Virtual Host
- ✅ Habilitar mod_rewrite

---

## 🔧 Configuraciones Aplicadas

### Base de Datos
```sql
Database: cooperadora_production
Username: cooperadora_user  
Password: CooperadoraPos2026!
```

### Apache Virtual Host
```apache
DocumentRoot: /var/www/cooperadora/public
ServerName: Tu_IP_del_Servidor
```

### Laravel Optimizado
```bash
Config cached ✅
Routes cached ✅  
Views cached ✅
Composer optimized ✅
```

---

## 🌐 Acceso al Sistema

### URL
```
http://TU_IP_DEL_SERVIDOR
```

### Usuario Administrador
```
Username: admin
Password: admin123
```

---

## 🔍 Verificación Post-Deployment

### 1. Verificar Apache
```bash
sudo systemctl status apache2
```

### 2. Verificar Base de Datos
```bash
cd /var/www/cooperadora
sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'BD OK';"
```

### 3. Ver Logs si hay problemas
```bash
# Logs de Apache
sudo tail -f /var/log/apache2/cooperadora_error.log

# Logs de Laravel
sudo tail -f /var/www/cooperadora/storage/logs/laravel.log
```

---

## ⚠️ Troubleshooting

### Si no puedes acceder
1. **Verificar IP del servidor**:
   ```bash
   ip addr show
   ```

2. **Verificar firewall**:
   ```bash
   sudo ufw status
   sudo ufw allow 80
   ```

3. **Verificar Apache**:
   ```bash
   sudo systemctl restart apache2
   sudo systemctl status apache2
   ```

### Si hay errores de permisos
```bash
sudo chown -R www-data:www-data /var/www/cooperadora
sudo chmod -R 775 /var/www/cooperadora/storage
sudo chmod -R 775 /var/www/cooperadora/bootstrap/cache
```

### Si hay errores de base de datos
```bash
cd /var/www/cooperadora
sudo -u www-data php artisan migrate:fresh --seed --force
```

---

## 📊 Comandos Post-Deployment

### Limpiar Cache
```bash
cd /var/www/cooperadora
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear
```

### Ver Estado del Sistema
```bash
# Apache
sudo systemctl status apache2

# PostgreSQL  
sudo systemctl status postgresql

# Espacio en disco
df -h

# Procesos PHP
ps aux | grep php
```

### Backup Manual
```bash
# Backup de base de datos
sudo -u postgres pg_dump cooperadora_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos
sudo tar -czf cooperadora_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/cooperadora
```

---

## 🎯 Resultado Esperado

Después de ejecutar `quick-deploy.sh`:

1. ✅ Sistema accesible en `http://TU_IP`
2. ✅ Login funcional con admin/admin123
3. ✅ Base de datos creada con datos iniciales
4. ✅ Todas las funcionalidades del POS disponibles
5. ✅ Apache configurado correctamente
6. ✅ Sistema optimizado para producción

---

**¡Tu sistema POS estará listo para usar!** 🎉
