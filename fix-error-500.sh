#!/bin/bash

echo "🔧 SOLUCIONANDO ERROR 500 DE LARAVEL"
echo "===================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Ir al directorio del proyecto
cd /var/www/cooperadora

echo "1. 📋 VERIFICANDO LOGS DE ERROR"
echo "-------------------------------"

echo "Últimos errores de Laravel:"
if [ -f "storage/logs/laravel.log" ]; then
    sudo tail -20 storage/logs/laravel.log
else
    print_warning "No hay logs de Laravel aún"
fi

echo ""
echo "Últimos errores de Apache:"
if [ -f "/var/log/apache2/cooperadora_error.log" ]; then
    sudo tail -10 /var/log/apache2/cooperadora_error.log
else
    print_warning "No hay logs específicos de Apache"
fi

echo ""
echo "2. 🔧 CORRIGIENDO PERMISOS"
echo "-------------------------"

# Corregir permisos críticos
sudo chown -R www-data:www-data /var/www/cooperadora
sudo chmod -R 755 /var/www/cooperadora
sudo chmod -R 775 /var/www/cooperadora/storage
sudo chmod -R 775 /var/www/cooperadora/bootstrap/cache

# Crear directorios si no existen
sudo -u www-data mkdir -p storage/logs
sudo -u www-data mkdir -p storage/framework/cache
sudo -u www-data mkdir -p storage/framework/sessions
sudo -u www-data mkdir -p storage/framework/views
sudo -u www-data mkdir -p bootstrap/cache

print_status "Permisos corregidos"

echo ""
echo "3. 🔑 VERIFICANDO .ENV Y APP_KEY"
echo "--------------------------------"

if [ ! -f ".env" ]; then
    print_warning "Archivo .env no existe. Creando..."
    sudo -u www-data cp .env.production .env
fi

# Verificar APP_KEY
if ! grep -q "APP_KEY=base64:" .env; then
    print_warning "APP_KEY no está configurado. Generando..."
    sudo -u www-data php artisan key:generate --force
else
    print_status "APP_KEY está configurado"
fi

echo ""
echo "4. 🗄️ VERIFICANDO BASE DE DATOS"
echo "------------------------------"

# Probar conexión a base de datos
if sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'BD OK';" 2>/dev/null; then
    print_status "Base de datos conectada"
else
    print_error "Error de conexión a base de datos"
    echo "Configuración actual de BD:"
    grep -E "^DB_" .env

    # Intentar ejecutar migraciones
    print_warning "Intentando ejecutar migraciones..."
    sudo -u www-data php artisan migrate --force
fi

echo ""
echo "5. 🧹 LIMPIANDO CACHE"
echo "--------------------"

# Limpiar todos los caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

print_status "Cache limpiado"

echo ""
echo "6. 🔧 VERIFICANDO EXTENSIONES PHP"
echo "---------------------------------"

REQUIRED_EXTENSIONS=("pgsql" "mbstring" "xml" "zip" "gd" "curl" "intl" "bcmath" "tokenizer" "json")

MISSING_EXTENSIONS=()
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "$ext"; then
        MISSING_EXTENSIONS+=("$ext")
        print_error "$ext NO instalado"
    else
        print_status "$ext instalado"
    fi
done

# Instalar extensiones faltantes
if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    print_warning "Instalando extensiones faltantes..."
    PHP_VERSION=$(php -v | head -n1 | awk '{print $2}' | cut -d. -f1,2)
    for ext in "${MISSING_EXTENSIONS[@]}"; do
        sudo apt install -y "php$PHP_VERSION-$ext"
    done

    # Reiniciar Apache después de instalar extensiones
    sudo systemctl restart apache2
fi

echo ""
echo "7. 📊 OPTIMIZANDO PARA PRODUCCIÓN"
echo "---------------------------------"

# Solo si no hay errores, optimizar
if sudo -u www-data php artisan tinker --execute="echo 'Test OK';" 2>/dev/null; then
    print_status "Laravel funciona, optimizando..."
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan route:cache
    sudo -u www-data php artisan view:cache
else
    print_warning "Laravel tiene errores, saltando optimización"
fi

echo ""
echo "8. 🌐 PROBANDO SITIO WEB"
echo "-----------------------"

SERVER_IP=$(hostname -I | awk '{print $1}')

# Probar respuesta del sitio
echo "Probando http://$SERVER_IP ..."
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "http://$SERVER_IP" 2>/dev/null)

if [ "$RESPONSE" = "200" ]; then
    print_status "Sitio web funcionando correctamente (HTTP 200)"
elif [ "$RESPONSE" = "500" ]; then
    print_error "Aún hay error 500"
    echo ""
    echo "Últimos errores después de correcciones:"
    sudo tail -5 storage/logs/laravel.log 2>/dev/null || echo "No hay logs nuevos"
else
    print_warning "Respuesta HTTP: $RESPONSE"
fi

echo ""
echo "=============================================="
echo "🎯 DIAGNÓSTICO COMPLETADO"
echo "=============================================="
echo ""
echo "🌐 URL del sitio: http://$SERVER_IP"
echo "👤 Usuario: admin"
echo "🔑 Password: admin123"
echo ""
echo "📋 Si persiste el error 500:"
echo ""
echo "# Ver logs detallados:"
echo "sudo tail -f /var/www/cooperadora/storage/logs/laravel.log"
echo ""
echo "# Ver logs de Apache:"
echo "sudo tail -f /var/log/apache2/cooperadora_error.log"
echo ""
echo "# Activar debug temporal:"
echo "sudo sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' /var/www/cooperadora/.env"
echo ""
echo "# Verificar manualmente:"
echo "cd /var/www/cooperadora && sudo -u www-data php artisan --version"
