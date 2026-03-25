#!/bin/bash

# Script de backup para el sistema POS Cooperadora
# Uso: ./backup.sh

set -e

PROJECT_NAME="cooperadora"
BACKUP_DIR="/var/backups/${PROJECT_NAME}"
DATE=$(date +%Y%m%d_%H%M%S)
PROJECT_PATH="/var/www/${PROJECT_NAME}"

# Configuración de PostgreSQL
DB_NAME="cooperadora_pos_prod"
DB_USER="cooperadora_user"

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_info "Iniciando backup del sistema POS Cooperadora..."

# Crear directorio de backup
sudo mkdir -p $BACKUP_DIR

# 1. Backup de la base de datos
print_status "Creando backup de la base de datos..."
sudo -u postgres pg_dump $DB_NAME | gzip > $BACKUP_DIR/database_${DATE}.sql.gz

# 2. Backup de archivos del proyecto
print_status "Creando backup de archivos..."
sudo tar -czf $BACKUP_DIR/files_${DATE}.tar.gz \
    -C /var/www \
    --exclude='${PROJECT_NAME}/storage/logs' \
    --exclude='${PROJECT_NAME}/storage/framework/cache' \
    --exclude='${PROJECT_NAME}/storage/framework/sessions' \
    --exclude='${PROJECT_NAME}/storage/framework/views' \
    --exclude='${PROJECT_NAME}/vendor' \
    --exclude='${PROJECT_NAME}/node_modules' \
    ${PROJECT_NAME}

# 3. Backup de configuración de Apache
print_status "Creando backup de configuración Apache..."
sudo cp /etc/apache2/sites-available/${PROJECT_NAME}.conf $BACKUP_DIR/apache_${DATE}.conf

# 4. Crear script de restauración
print_status "Creando script de restauración..."
sudo tee $BACKUP_DIR/restore_${DATE}.sh > /dev/null << EOF
#!/bin/bash
# Script de restauración para backup del ${DATE}

set -e

print_status() {
    echo -e "\033[0;32m✓\033[0m \$1"
}

echo "🔄 Iniciando restauración del backup ${DATE}..."

# Restaurar base de datos
print_status "Restaurando base de datos..."
sudo -u postgres dropdb ${DB_NAME} || true
sudo -u postgres createdb ${DB_NAME}
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
zcat database_${DATE}.sql.gz | sudo -u postgres psql ${DB_NAME}

# Restaurar archivos
print_status "Restaurando archivos..."
cd /var/www
sudo rm -rf ${PROJECT_NAME}_backup || true
if [ -d "${PROJECT_NAME}" ]; then
    sudo mv ${PROJECT_NAME} ${PROJECT_NAME}_backup
fi
sudo tar -xzf $BACKUP_DIR/files_${DATE}.tar.gz

# Restaurar permisos
print_status "Restaurando permisos..."
sudo chown -R www-data:www-data ${PROJECT_NAME}
sudo chmod -R 755 ${PROJECT_NAME}
sudo chmod -R 775 ${PROJECT_NAME}/storage ${PROJECT_NAME}/bootstrap/cache

# Restaurar configuración Apache
print_status "Restaurando configuración Apache..."
sudo cp apache_${DATE}.conf /etc/apache2/sites-available/${PROJECT_NAME}.conf

# Reiniciar servicios
print_status "Reiniciando servicios..."
sudo systemctl restart apache2
sudo systemctl restart php8.4-fpm

print_status "✅ Restauración completada!"
EOF

sudo chmod +x $BACKUP_DIR/restore_${DATE}.sh

# 5. Limpiar backups antiguos (mantener últimos 7 días)
print_status "Limpiando backups antiguos..."
find $BACKUP_DIR -name "database_*" -mtime +7 -delete || true
find $BACKUP_DIR -name "files_*" -mtime +7 -delete || true
find $BACKUP_DIR -name "apache_*" -mtime +7 -delete || true
find $BACKUP_DIR -name "restore_*" -mtime +7 -delete || true

# 6. Mostrar información del backup
BACKUP_SIZE=$(du -sh $BACKUP_DIR | cut -f1)
print_status "✅ Backup completado!"
echo ""
print_info "📁 Directorio de backup: $BACKUP_DIR"
print_info "📊 Tamaño total: $BACKUP_SIZE"
print_info "🗄️ Base de datos: database_${DATE}.sql.gz"
print_info "📄 Archivos: files_${DATE}.tar.gz"
print_info "⚙️ Apache config: apache_${DATE}.conf"
print_info "🔄 Script de restauración: restore_${DATE}.sh"
echo ""
print_info "Para restaurar este backup, ejecuta:"
print_info "cd $BACKUP_DIR && sudo ./restore_${DATE}.sh"
