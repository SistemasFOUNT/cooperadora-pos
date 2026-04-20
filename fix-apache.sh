#!/bin/bash

echo "🔧 CORRIGIENDO APACHE PARA LARAVEL"
echo "=================================="

# Habilitar mod_rewrite
echo "📋 Habilitando mod_rewrite..."
sudo a2enmod rewrite

# Crear .htaccess si no existe
echo "📄 Creando archivo .htaccess..."
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

# Configurar Virtual Host
echo "🌐 Configurando Virtual Host..."
SERVER_IP=$(hostname -I | awk '{print $1}')

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

# Habilitar sitio
echo "🔄 Configurando sitios de Apache..."
sudo a2dissite 000-default 2>/dev/null || true
sudo a2ensite cooperadora

# Corregir permisos
echo "🔧 Corrigiendo permisos..."
sudo chown -R www-data:www-data /var/www/cooperadora
sudo chmod -R 755 /var/www/cooperadora
sudo chmod -R 775 /var/www/cooperadora/storage
sudo chmod -R 775 /var/www/cooperadora/bootstrap/cache
sudo chmod 644 /var/www/cooperadora/public/.htaccess

# Verificar configuración de Apache
echo "✅ Verificando configuración..."
if sudo apache2ctl configtest 2>/dev/null; then
    echo "✓ Configuración de Apache válida"
else
    echo "✗ Error en configuración de Apache"
    sudo apache2ctl configtest
fi

# Reiniciar Apache
echo "🔄 Reiniciando Apache..."
sudo systemctl restart apache2

if systemctl is-active --quiet apache2; then
    echo "✓ Apache reiniciado correctamente"
else
    echo "✗ Error al reiniciar Apache"
    sudo systemctl status apache2
    exit 1
fi

# Verificar que funciona
echo ""
echo "🌐 Probando sitio web..."
if curl -I "http://$SERVER_IP" 2>/dev/null | head -1; then
    echo "✓ Servidor responde correctamente"
else
    echo "✗ Servidor no responde"
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
echo "📋 Si hay problemas, revisar logs:"
echo "sudo tail -f /var/log/apache2/cooperadora_error.log"
