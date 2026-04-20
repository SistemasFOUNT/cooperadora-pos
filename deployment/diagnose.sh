#!/bin/bash

# Script de diagnóstico para Apache + Laravel
echo "🔍 DIAGNÓSTICO DEL SERVIDOR APACHE + LARAVEL"
echo "=============================================="
echo ""

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

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

echo "1. 📁 VERIFICANDO ESTRUCTURA DE ARCHIVOS"
echo "----------------------------------------"

if [ -d "/var/www/cooperadora" ]; then
    print_status "Directorio /var/www/cooperadora existe"
else
    print_error "Directorio /var/www/cooperadora NO existe"
    exit 1
fi

if [ -d "/var/www/cooperadora/public" ]; then
    print_status "Directorio /var/www/cooperadora/public existe"
else
    print_error "Directorio /var/www/cooperadora/public NO existe"
fi

if [ -f "/var/www/cooperadora/public/index.php" ]; then
    print_status "Archivo index.php existe en public/"
else
    print_error "Archivo index.php NO existe en public/"
fi

if [ -f "/var/www/cooperadora/.env" ]; then
    print_status "Archivo .env existe"
else
    print_error "Archivo .env NO existe"
fi

echo ""
echo "2. 🔧 VERIFICANDO PERMISOS"
echo "-------------------------"

echo "Permisos de /var/www/cooperadora:"
ls -la /var/www/cooperadora | head -5

echo ""
echo "Permisos de /var/www/cooperadora/public:"
ls -la /var/www/cooperadora/public | head -5

echo ""
echo "Permisos de /var/www/cooperadora/storage:"
ls -la /var/www/cooperadora/storage | head -3

echo ""
echo "3. 🌐 VERIFICANDO APACHE"
echo "-----------------------"

if systemctl is-active --quiet apache2; then
    print_status "Apache está corriendo"
else
    print_error "Apache NO está corriendo"
    echo "Intentando iniciar Apache..."
    sudo systemctl start apache2
fi

echo ""
echo "Sitios habilitados en Apache:"
apache2ctl -S 2>/dev/null | grep -E "(Main|namevhost|port|Syntax)"

echo ""
echo "4. 📝 VERIFICANDO CONFIGURACIÓN DEL SITIO"
echo "----------------------------------------"

if [ -f "/etc/apache2/sites-available/cooperadora.conf" ]; then
    print_status "Archivo de configuración cooperadora.conf existe"
    echo "Contenido:"
    cat /etc/apache2/sites-available/cooperadora.conf
else
    print_error "Archivo cooperadora.conf NO existe"
fi

echo ""
echo "Sitios habilitados:"
ls -la /etc/apache2/sites-enabled/

echo ""
echo "5. 🗄️ VERIFICANDO BASE DE DATOS"
echo "------------------------------"

cd /var/www/cooperadora 2>/dev/null || echo "No se puede acceder al directorio"

if sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexión BD: OK';" 2>/dev/null; then
    print_status "Base de datos conectada correctamente"
else
    print_error "Error de conexión a base de datos"
    echo "Verificando .env:"
    sudo cat /var/www/cooperadora/.env | grep -E "^DB_"
fi

echo ""
echo "6. 📄 VERIFICANDO LOGS"
echo "---------------------"

echo "Últimos errores de Apache:"
if [ -f "/var/log/apache2/error.log" ]; then
    sudo tail -10 /var/log/apache2/error.log
else
    echo "No hay logs de Apache"
fi

echo ""
echo "Últimos errores del sitio cooperadora:"
if [ -f "/var/log/apache2/cooperadora_error.log" ]; then
    sudo tail -10 /var/log/apache2/cooperadora_error.log
else
    echo "No hay logs específicos del sitio"
fi

echo ""
echo "Últimos errores de Laravel:"
if [ -f "/var/www/cooperadora/storage/logs/laravel.log" ]; then
    sudo tail -10 /var/www/cooperadora/storage/logs/laravel.log
else
    echo "No hay logs de Laravel"
fi

echo ""
echo "7. 🔧 VERIFICANDO PHP"
echo "--------------------"

echo "Versión de PHP:"
php --version | head -1

echo ""
echo "Extensiones PHP necesarias:"
REQUIRED_EXTENSIONS=("pgsql" "mbstring" "xml" "zip" "gd" "curl" "intl" "bcmath" "tokenizer" "json")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        print_status "$ext instalado"
    else
        print_error "$ext NO instalado"
    fi
done

echo ""
echo "8. 🌐 PROBANDO CONECTIVIDAD"
echo "---------------------------"

SERVER_IP=$(hostname -I | awk '{print $1}')
echo "IP del servidor: $SERVER_IP"

echo ""
echo "Probando respuesta HTTP:"
if curl -I "http://$SERVER_IP" 2>/dev/null; then
    print_status "Servidor responde a HTTP"
else
    print_error "Servidor NO responde a HTTP"
fi

echo ""
echo "=============================================="
echo "🎯 SOLUCIONES RÁPIDAS"
echo "=============================================="

echo ""
echo "Si Apache no está sirviendo Laravel correctamente, ejecuta:"
echo ""
echo "# 1. Corregir configuración Apache:"
echo "sudo tee /etc/apache2/sites-available/cooperadora.conf > /dev/null << 'EOF'"
echo "<VirtualHost *:80>"
echo "    ServerName $SERVER_IP"
echo "    DocumentRoot /var/www/cooperadora/public"
echo "    "
echo "    <Directory /var/www/cooperadora/public>"
echo "        AllowOverride All"
echo "        Require all granted"
echo "        "
echo "        RewriteEngine On"
echo "        RewriteCond %{REQUEST_FILENAME} !-d"
echo "        RewriteCond %{REQUEST_FILENAME} !-f"
echo "        RewriteRule ^(.*)$ index.php [QSA,L]"
echo "    </Directory>"
echo "    "
echo "    ErrorLog \${APACHE_LOG_DIR}/cooperadora_error.log"
echo "    CustomLog \${APACHE_LOG_DIR}/cooperadora_access.log combined"
echo "</VirtualHost>"
echo "EOF"
echo ""
echo "# 2. Habilitar sitio y reiniciar:"
echo "sudo a2enmod rewrite"
echo "sudo a2ensite cooperadora"
echo "sudo a2dissite 000-default"
echo "sudo systemctl restart apache2"
echo ""
echo "# 3. Corregir permisos:"
echo "sudo chown -R www-data:www-data /var/www/cooperadora"
echo "sudo chmod -R 755 /var/www/cooperadora"
echo "sudo chmod -R 775 /var/www/cooperadora/storage"
echo "sudo chmod -R 775 /var/www/cooperadora/bootstrap/cache"
