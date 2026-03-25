#!/bin/bash

# Script de deployment simplificado para servidor con stack ya instalado
# Uso: ./quick-deploy.sh

set -e

PROJECT_NAME="cooperadora"
DB_NAME="${PROJECT_NAME}_production"
DB_USER="${PROJECT_NAME}_user"
DB_PASSWORD="CooperadoraPos2026!"

echo "🚀 Iniciando deployment rápido en servidor Ubuntu existente"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto Laravel"
    exit 1
fi

# 1. Verificar versiones de PHP
print_status "Verificando versión de PHP..."
PHP_VERSION=$(php -v | head -n1 | awk '{print $2}' | cut -d. -f1,2)
echo "PHP Version: $PHP_VERSION"

# Instalar extensiones PHP faltantes si es necesario
print_status "Verificando extensiones PHP..."
REQUIRED_EXTENSIONS=("pgsql" "mbstring" "xml" "zip" "gd" "curl" "intl" "bcmath")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "$ext"; then
        print_warning "Instalando extensión php-$ext..."
        sudo apt install -y "php$PHP_VERSION-$ext"
    fi
done

# 2. Configurar base de datos PostgreSQL
print_status "Configurando base de datos..."

# Crear base de datos y usuario
sudo -u postgres psql << EOF
-- Crear base de datos si no existe
SELECT 'CREATE DATABASE $DB_NAME'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '$DB_NAME')\gexec

-- Crear usuario si no existe
DO \$\$
BEGIN
   IF NOT EXISTS (SELECT FROM pg_catalog.pg_user WHERE usename = '$DB_USER') THEN
      CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';
   END IF;
END
\$\$;

-- Otorgar permisos
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
ALTER USER $DB_USER CREATEDB;
EOF

print_status "Base de datos configurada"

# 3. Configurar directorio del proyecto
PROJECT_PATH="/var/www/${PROJECT_NAME}"
print_status "Configurando directorio del proyecto en $PROJECT_PATH"

sudo mkdir -p $PROJECT_PATH
sudo chown -R $USER:$USER $PROJECT_PATH

# Copiar archivos excluyendo archivos no necesarios
print_status "Copiando archivos del proyecto..."
rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' . $PROJECT_PATH/

# 4. Configurar permisos
print_status "Configurando permisos..."
sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/bootstrap/cache

# 5. Instalar dependencias de Composer
print_status "Instalando dependencias PHP..."
cd $PROJECT_PATH
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# 6. Configurar archivo .env
print_status "Configurando archivo .env..."
sudo -u www-data cp .env.production .env

# Reemplazar valores específicos en .env
sudo -u www-data sed -i "s/REPLACE_WITH_SECURE_PASSWORD/$DB_PASSWORD/g" .env
sudo -u www-data sed -i "s/your-domain.com/$(hostname -I | awk '{print $1}')/g" .env

# Generar clave de aplicación
sudo -u www-data php artisan key:generate --force

# 7. Ejecutar migraciones
print_status "Ejecutando migraciones de base de datos..."
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --class=InitialDataSeeder --force

# 8. Optimizar aplicación para producción
print_status "Optimizando aplicación..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 9. Configurar Apache
print_status "Configurando Apache..."
APACHE_CONF="/etc/apache2/sites-available/${PROJECT_NAME}.conf"

sudo tee $APACHE_CONF > /dev/null << EOF
<VirtualHost *:80>
    ServerName $(hostname -I | awk '{print $1}')
    DocumentRoot $PROJECT_PATH/public
    
    <Directory $PROJECT_PATH/public>
        AllowOverride All
        Require all granted
        
        # Laravel URL Rewriting
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_access.log combined
</VirtualHost>
EOF

# Habilitar mod_rewrite y el sitio
sudo a2enmod rewrite
sudo a2ensite ${PROJECT_NAME}
sudo a2dissite 000-default

# Reiniciar Apache
sudo systemctl restart apache2

print_status "Apache configurado y reiniciado"

# 10. Verificar estado del sistema
print_status "Verificando estado del sistema..."

# Test de conectividad a base de datos
cd $PROJECT_PATH
if sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexión a BD: OK';" 2>/dev/null; then
    print_status "Conexión a base de datos: OK"
else
    print_error "Error en conexión a base de datos"
fi

# Verificar Apache
if systemctl is-active --quiet apache2; then
    print_status "Apache está corriendo"
else
    print_error "Apache no está corriendo"
fi

# Mostrar información final
echo ""
echo "=============================================="
echo "🎉 DEPLOYMENT COMPLETADO"
echo "=============================================="
echo ""
echo "📍 Ubicación del proyecto: $PROJECT_PATH"
echo "🌐 URL de acceso: http://$(hostname -I | awk '{print $1}')"
echo "🗄️ Base de datos: $DB_NAME"
echo "👤 Usuario BD: $DB_USER"
echo ""
echo "👨‍💼 Usuario admin del sistema:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "📋 Comandos útiles:"
echo "   Ver logs: sudo tail -f /var/log/apache2/${PROJECT_NAME}_error.log"
echo "   Reiniciar Apache: sudo systemctl restart apache2"
echo "   Limpiar cache: cd $PROJECT_PATH && sudo -u www-data php artisan cache:clear"
echo ""
echo "✅ El sistema está listo para usar!"