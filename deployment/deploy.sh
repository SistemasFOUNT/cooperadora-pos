#!/bin/bash

# Script de deployment manual para Ubuntu Server
# Uso: ./deploy.sh [production|staging]

set -e

ENVIRONMENT=${1:-production}
PROJECT_NAME="cooperadora"
DOMAIN="your-domain.com"
DB_NAME="${PROJECT_NAME}_${ENVIRONMENT}"
DB_USER="${PROJECT_NAME}_user"

echo "🚀 Iniciando deployment para entorno: $ENVIRONMENT"

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

# 1. Instalar dependencias del sistema
print_status "Instalando dependencias del sistema..."
sudo apt update
sudo apt install -y software-properties-common

# Agregar repositorio de PHP 8.4
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# Instalar PHP 8.4 y extensiones necesarias
sudo apt install -y \
    php8.4 \
    php8.4-fpm \
    php8.4-cli \
    php8.4-common \
    php8.4-curl \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-zip \
    php8.4-gd \
    php8.4-pgsql \
    php8.4-intl \
    php8.4-bcmath \
    php8.4-soap \
    php8.4-xsl \
    php8.4-opcache \
    php8.4-readline

# Instalar Apache
sudo apt install -y apache2

# Instalar PostgreSQL 14
sudo apt install -y postgresql-14 postgresql-client-14 postgresql-contrib-14

print_status "Dependencias del sistema instaladas"

# 2. Configurar PostgreSQL
print_status "Configurando PostgreSQL..."

sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME};" || print_warning "La base de datos ya existe"
sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH PASSWORD 'SECURE_PASSWORD_HERE';" || print_warning "El usuario ya existe"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
sudo -u postgres psql -c "ALTER USER ${DB_USER} CREATEDB;"

print_status "PostgreSQL configurado"

# 3. Instalar Composer
if ! command -v composer &> /dev/null; then
    print_status "Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# 4. Configurar directorio del proyecto
PROJECT_PATH="/var/www/${PROJECT_NAME}"
sudo mkdir -p $PROJECT_PATH

# Copiar archivos del proyecto
print_status "Copiando archivos del proyecto..."
sudo cp -r . $PROJECT_PATH/
sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache

# 5. Instalar dependencias de PHP
print_status "Instalando dependencias de PHP..."
cd $PROJECT_PATH
sudo -u www-data composer install --no-dev --optimize-autoloader

# 6. Configurar .env
print_status "Configurando archivo .env..."
if [ ! -f ".env" ]; then
    sudo -u www-data cp .env.${ENVIRONMENT} .env
    sudo -u www-data php artisan key:generate
fi

# 7. Ejecutar migraciones
print_status "Ejecutando migraciones..."
sudo -u www-data php artisan migrate --force

# 8. Ejecutar seeders (solo en staging)
if [ "$ENVIRONMENT" = "staging" ]; then
    print_status "Ejecutando seeders..."
    sudo -u www-data php artisan db:seed --force
fi

# 9. Optimizar Laravel
print_status "Optimizando Laravel..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 10. Configurar Apache Virtual Host
print_status "Configurando Apache Virtual Host..."
sudo tee /etc/apache2/sites-available/${PROJECT_NAME}.conf > /dev/null << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${PROJECT_PATH}/public

    <Directory ${PROJECT_PATH}/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php

        # Rewrite rules para Laravel
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_access.log combined
</VirtualHost>
EOF

# Habilitar módulos de Apache
sudo a2enmod rewrite
sudo a2enmod ssl

# Habilitar el sitio
sudo a2ensite ${PROJECT_NAME}.conf
sudo a2dissite 000-default.conf

# 11. Configurar PHP-FPM
print_status "Configurando PHP-FPM..."
sudo systemctl enable php8.4-fpm
sudo systemctl start php8.4-fpm

# 12. Configurar permisos finales
print_status "Configurando permisos finales..."
sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache

# 13. Configurar cron jobs
print_status "Configurando cron jobs..."
(sudo crontab -u www-data -l 2>/dev/null; echo "* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1") | sudo crontab -u www-data -

# 14. Reiniciar servicios
print_status "Reiniciando servicios..."
sudo systemctl restart apache2
sudo systemctl restart php8.4-fpm
sudo systemctl restart postgresql

# 15. Verificar instalación
print_status "Verificando instalación..."
if curl -f -s http://localhost > /dev/null; then
    print_status "✅ Deployment completado exitosamente!"
    echo ""
    echo "🌐 El sitio debería estar disponible en: http://${DOMAIN}"
    echo "📁 Directorio del proyecto: ${PROJECT_PATH}"
    echo "🗄️ Base de datos: ${DB_NAME}"
    echo ""
    echo "📝 Próximos pasos:"
    echo "1. Configurar SSL con Let's Encrypt (recomendado)"
    echo "2. Configurar backups automáticos"
    echo "3. Configurar monitoreo"
    echo "4. Ajustar configuración de seguridad"
else
    print_error "Hubo un problema con el deployment. Revisa los logs:"
    echo "- Apache: sudo journalctl -u apache2"
    echo "- PHP-FPM: sudo journalctl -u php8.4-fpm"
    echo "- Laravel: tail -f ${PROJECT_PATH}/storage/logs/laravel.log"
fi
