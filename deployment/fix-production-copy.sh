#!/bin/bash

# 🔧 Script para solucionar la copia de desarrollo a producción
# Soluciona permisos, dependencias y configuración

echo "🚀 Solucionando problemas después de copiar desde desarrollo..."

# Variables
APP_PATH="/var/www/cooperadora"
WEB_USER="www-data"

# 1. Ir al directorio de la aplicación
cd $APP_PATH

echo "📁 Directorio actual: $(pwd)"

# 2. Instalar dependencias de producción
echo "📦 Instalando dependencias de producción..."
composer install --optimize-autoloader --no-dev

# 3. Configurar permisos correctos
echo "🔒 Configurando permisos..."
# Propietario: www-data para todos los archivos
sudo chown -R $WEB_USER:$WEB_USER $APP_PATH

# Permisos de directorios
sudo find $APP_PATH -type d -exec chmod 755 {} \;

# Permisos de archivos
sudo find $APP_PATH -type f -exec chmod 644 {} \;

# Permisos especiales para storage y bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

# Permisos para artisan
sudo chmod 755 $APP_PATH/artisan

# 4. Crear directorios necesarios si no existen
echo "📂 Creando directorios necesarios..."
sudo mkdir -p $APP_PATH/storage/logs
sudo mkdir -p $APP_PATH/storage/app
sudo mkdir -p $APP_PATH/storage/framework/cache
sudo mkdir -p $APP_PATH/storage/framework/sessions
sudo mkdir -p $APP_PATH/storage/framework/views
sudo mkdir -p $APP_PATH/bootstrap/cache

# Asegurar permisos en directorios creados
sudo chown -R $WEB_USER:$WEB_USER $APP_PATH/storage
sudo chown -R $WEB_USER:$WEB_USER $APP_PATH/bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

# 5. Limpiar y configurar Laravel
echo "🧹 Limpiando caches y configurando Laravel..."
sudo -u $WEB_USER php artisan config:clear
sudo -u $WEB_USER php artisan route:clear
sudo -u $WEB_USER php artisan view:clear
sudo -u $WEB_USER php artisan cache:clear

# 6. Configurar clave de aplicación si es necesario
echo "🔑 Configurando clave de aplicación..."
if grep -q "APP_KEY=base64:" .env; then
    echo "✅ La clave de aplicación ya está configurada"
else
    sudo -u $WEB_USER php artisan key:generate
fi

# 7. Configurar el archivo .env para producción
echo "⚙️  Configurando .env para producción..."
sudo -u $WEB_USER cp .env.production .env 2>/dev/null || echo "⚠️  Archivo .env.production no encontrado, usando .env actual"

# 8. Optimizar para producción
echo "⚡ Optimizando para producción..."
sudo -u $WEB_USER php artisan config:cache
sudo -u $WEB_USER php artisan route:cache
sudo -u $WEB_USER php artisan view:cache

# 9. Verificar que la base de datos esté configurada
echo "🗄️  Verificando base de datos..."
sudo -u $WEB_USER php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Conexión a base de datos: OK\n';
} catch(\Exception \$e) {
    echo 'Error de conexión: ' . \$e->getMessage() . '\n';
}
"

# 10. Verificar permisos finales
echo "🔍 Verificación final de permisos..."
echo "Propietario de storage/logs: $(ls -la storage/ | grep logs)"
echo "Permisos de storage: $(ls -la storage/)"

echo ""
echo "✅ Configuración completada!"
echo ""
echo "🔗 Sitio disponible en: http://cooperadora.odontologia.unt.edu.ar"
echo "👤 Usuario: admin"
echo "🔑 Contraseña: admin123"
echo ""
echo "📋 Si persisten problemas, revisar:"
echo "   - Apache error log: /var/log/apache2/error.log"
echo "   - Laravel log: /var/www/cooperadora/storage/logs/laravel.log"
