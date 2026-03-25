#!/bin/bash

# Script de configuración SSL con Let's Encrypt
# Uso: ./ssl-setup.sh your-domain.com

set -e

DOMAIN=${1}

if [ -z "$DOMAIN" ]; then
    echo "Uso: ./ssl-setup.sh your-domain.com"
    exit 1
fi

PROJECT_NAME="cooperadora"

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_status "Configurando SSL para $DOMAIN..."

# 1. Instalar Certbot
sudo apt update
sudo apt install -y certbot python3-certbot-apache

# 2. Obtener certificado SSL
print_status "Obteniendo certificado SSL..."
sudo certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# 3. Configurar renovación automática
print_status "Configurando renovación automática..."
(sudo crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | sudo crontab -

# 4. Actualizar Virtual Host para redireccionar HTTP a HTTPS
print_status "Configurando redirección HTTPS..."
sudo tee /etc/apache2/sites-available/${PROJECT_NAME}.conf > /dev/null << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    Redirect permanent / https://${DOMAIN}/
</VirtualHost>

<VirtualHost *:443>
    ServerName ${DOMAIN}
    DocumentRoot /var/www/${PROJECT_NAME}/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/${DOMAIN}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/${DOMAIN}/privkey.pem

    <Directory /var/www/${PROJECT_NAME}/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php

        # Rewrite rules para Laravel
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Referrer-Policy strict-origin-when-cross-origin
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"

    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_ssl_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_ssl_access.log combined
</VirtualHost>
EOF

# 5. Habilitar módulos SSL
sudo a2enmod ssl
sudo a2enmod headers

# 6. Reiniciar Apache
sudo systemctl restart apache2

print_status "✅ SSL configurado exitosamente!"
echo "🔒 Tu sitio ahora está disponible en: https://${DOMAIN}"
