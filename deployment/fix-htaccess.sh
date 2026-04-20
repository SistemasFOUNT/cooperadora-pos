#!/bin/bash

# Script para habilitar .htaccess y mod_rewrite en Apache para Laravel
echo "🔧 CONFIGURANDO APACHE PARA LARAVEL (.htaccess + mod_rewrite)"
echo "============================================================="
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

# 1. Verificar si mod_rewrite está habilitado
echo "1. 📋 VERIFICANDO MOD_REWRITE"
echo "-----------------------------"

if apache2ctl -M | grep -q "rewrite_module"; then
    print_status "mod_rewrite ya está habilitado"
else
    print_warning "mod_rewrite NO está habilitado. Habilitando..."
    sudo a2enmod rewrite
    print_status "mod_rewrite habilitado"
fi

echo ""

# 2. Verificar si existe .htaccess en public/
echo "2. 📄 VERIFICANDO ARCHIVO .htaccess"
echo "-----------------------------------"

if [ -f "/var/www/cooperadora/public/.htaccess" ]; then
    print_status "Archivo .htaccess existe en public/"
    echo "Contenido actual:"
    cat /var/www/cooperadora/public/.htaccess
else
    print_warning "Archivo .htaccess NO existe. Creando..."

    # Crear .htaccess con la configuración estándar de Laravel
    sudo tee /var/www/cooperadora/public/.htaccess > /dev/null << 'EOF'
<IfModule mod_negotiation.c>
    Options -MultiViews -Indexes
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

    print_status "Archivo .htaccess creado"
fi

echo ""

# 3. Configurar el Virtual Host correctamente
echo "3. 🌐 CONFIGURANDO VIRTUAL HOST"
echo "-------------------------------"

SERVER_IP=$(hostname -I | awk '{print $1}')

# Crear configuración correcta del Virtual Host
print_warning "Recreando configuración de Apache..."

sudo tee /etc/apache2/sites-available/cooperadora.conf > /dev/null << EOF
<VirtualHost *:80>
    ServerName $SERVER_IP
    DocumentRoot /var/www/cooperadora/public

    <Directory /var/www/cooperadora/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/cooperadora>
        Options -Indexes
        AllowOverride None
        Require all denied
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/cooperadora_error.log
    CustomLog \${APACHE_LOG_DIR}/cooperadora_access.log combined
</VirtualHost>
EOF

print_status "Virtual Host configurado"

echo ""

# 4. Habilitar el sitio y deshabilitar default
echo "4. 🔄 HABILITANDO SITIO"
echo "----------------------"

# Deshabilitar sitio por defecto
sudo a2dissite 000-default 2>/dev/null || true
print_status "Sitio default deshabilitado"

# Habilitar nuestro sitio
sudo a2ensite cooperadora
print_status "Sitio cooperadora habilitado"

echo ""

# 5. Verificar configuración de Apache
echo "5. ✅ VERIFICANDO CONFIGURACIÓN"
echo "------------------------------"

if sudo apache2ctl configtest 2>/dev/null; then
    print_status "Configuración de Apache válida"
else
    print_error "Error en configuración de Apache"
    echo "Mostrando errores:"
    sudo apache2ctl configtest
fi

echo ""

# 6. Corregir permisos del .htaccess
echo "6. 🔧 CORRIGIENDO PERMISOS"
echo "-------------------------"

sudo chown www-data:www-data /var/www/cooperadora/public/.htaccess
sudo chmod 644 /var/www/cooperadora/public/.htaccess
print_status "Permisos de .htaccess corregidos"

# Asegurar permisos correctos del directorio
sudo chown -R www-data:www-data /var/www/cooperadora
sudo chmod -R 755 /var/www/cooperadora
sudo chmod -R 775 /var/www/cooperadora/storage
sudo chmod -R 775 /var/www/cooperadora/bootstrap/cache
print_status "Permisos de directorios corregidos"

echo ""

# 7. Reiniciar Apache
echo "7. 🔄 REINICIANDO APACHE"
echo "-----------------------"

sudo systemctl restart apache2

if systemctl is-active --quiet apache2; then
    print_status "Apache reiniciado correctamente"
else
    print_error "Error al reiniciar Apache"
    echo "Estado de Apache:"
    sudo systemctl status apache2
    exit 1
fi

echo ""

# 8. Probar el sitio
echo "8. 🌐 PROBANDO SITIO"
echo "-------------------"

echo "Probando respuesta del servidor..."
if curl -I "http://$SERVER_IP" 2>/dev/null | head -1; then
    print_status "Servidor responde"

    # Probar si Laravel responde
    if curl -s "http://$SERVER_IP" | grep -q "Laravel\|cooperadora\|login" 2>/dev/null; then
        print_status "Laravel está respondiendo correctamente"
    else
        print_warning "El servidor responde pero puede que Laravel no esté configurado correctamente"
    fi
else
    print_error "Servidor no responde"
fi

echo ""
echo "=============================================="
echo "✅ CONFIGURACIÓN COMPLETADA"
echo "=============================================="
echo ""
echo "🌐 URL del sitio: http://$SERVER_IP"
echo "👤 Usuario: admin"
echo "🔑 Password: admin123"
echo ""
echo "📋 Si aún hay problemas, verificar:"
echo ""
echo "# Ver logs de Apache:"
echo "sudo tail -f /var/log/apache2/cooperadora_error.log"
echo ""
echo "# Ver logs de Laravel:"
echo "sudo tail -f /var/www/cooperadora/storage/logs/laravel.log"
echo ""
echo "# Verificar módulos de Apache habilitados:"
echo "apache2ctl -M | grep rewrite"
echo ""
echo "# Test manual del .htaccess:"
echo "curl -v http://$SERVER_IP/test-non-existent-page"
