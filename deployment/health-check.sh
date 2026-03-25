#!/bin/bash

# Script de health check para monitoreo
# Uso: ./health-check.sh

set -e

PROJECT_PATH="/var/www/cooperadora"
DB_NAME="cooperadora_pos_prod"
DOMAIN="your-domain.com"

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Contadores
CHECKS_PASSED=0
CHECKS_FAILED=0

check_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $2"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
    else
        echo -e "${RED}✗${NC} $2"
        CHECKS_FAILED=$((CHECKS_FAILED + 1))
    fi
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "🏥 Health Check - Sistema POS Cooperadora"
echo "==========================================="

# 1. Verificar servicios del sistema
echo ""
echo "📋 Verificando servicios del sistema..."

systemctl is-active --quiet apache2
check_status $? "Apache2 está activo"

systemctl is-active --quiet php8.4-fpm
check_status $? "PHP-FPM está activo"

systemctl is-active --quiet postgresql
check_status $? "PostgreSQL está activo"

# 2. Verificar conectividad HTTP/HTTPS
echo ""
echo "🌐 Verificando conectividad web..."

curl -f -s -o /dev/null http://localhost
check_status $? "HTTP responde correctamente"

if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
    curl -f -s -o /dev/null https://localhost -k
    check_status $? "HTTPS responde correctamente"

    # Verificar expiración del certificado SSL
    CERT_EXPIRY=$(openssl x509 -enddate -noout -in /etc/letsencrypt/live/$DOMAIN/fullchain.pem | cut -d= -f2)
    EXPIRY_DATE=$(date -d "$CERT_EXPIRY" +%s)
    CURRENT_DATE=$(date +%s)
    DAYS_UNTIL_EXPIRY=$(( (EXPIRY_DATE - CURRENT_DATE) / 86400 ))

    if [ $DAYS_UNTIL_EXPIRY -lt 30 ]; then
        warning "Certificado SSL expira en $DAYS_UNTIL_EXPIRY días"
    else
        check_status 0 "Certificado SSL válido por $DAYS_UNTIL_EXPIRY días"
    fi
else
    warning "No hay certificado SSL configurado"
fi

# 3. Verificar base de datos
echo ""
echo "🗄️ Verificando base de datos..."

sudo -u postgres psql -d $DB_NAME -c "SELECT 1;" >/dev/null 2>&1
check_status $? "Conexión a PostgreSQL"

# Verificar integridad de tablas principales
TABLES=("users" "products" "students" "sales" "branches")
for table in "${TABLES[@]}"; do
    COUNT=$(sudo -u postgres psql -d $DB_NAME -t -c "SELECT COUNT(*) FROM $table;" 2>/dev/null | xargs)
    if [[ $COUNT =~ ^[0-9]+$ ]]; then
        check_status 0 "Tabla $table accesible ($COUNT registros)"
    else
        check_status 1 "Error accediendo tabla $table"
    fi
done

# 4. Verificar aplicación Laravel
echo ""
echo "⚡ Verificando aplicación Laravel..."

cd $PROJECT_PATH

# Verificar permisos
if [ -w "storage/logs" ] && [ -w "bootstrap/cache" ]; then
    check_status 0 "Permisos de escritura correctos"
else
    check_status 1 "Problemas con permisos de escritura"
fi

# Verificar configuración Laravel
sudo -u www-data php artisan config:show --json >/dev/null 2>&1
check_status $? "Configuración de Laravel válida"

# Verificar cachés
CACHE_FILES=("bootstrap/cache/config.php" "bootstrap/cache/routes-v7.php")
for cache_file in "${CACHE_FILES[@]}"; do
    if [ -f "$cache_file" ]; then
        check_status 0 "Cache $cache_file presente"
    else
        warning "Cache $cache_file no encontrado"
    fi
done

# 5. Verificar recursos del sistema
echo ""
echo "💻 Verificando recursos del sistema..."

# Memoria
MEM_USAGE=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
if (( $(echo "$MEM_USAGE < 80" | bc -l) )); then
    check_status 0 "Uso de memoria: ${MEM_USAGE}%"
else
    warning "Alto uso de memoria: ${MEM_USAGE}%"
fi

# Disco
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 85 ]; then
    check_status 0 "Uso de disco: ${DISK_USAGE}%"
else
    warning "Alto uso de disco: ${DISK_USAGE}%"
fi

# CPU Load
LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}' | cut -d, -f1 | xargs)
CPU_CORES=$(nproc)
LOAD_PERCENTAGE=$(echo "scale=1; $LOAD_AVG * 100 / $CPU_CORES" | bc)

if (( $(echo "$LOAD_PERCENTAGE < 70" | bc -l) )); then
    check_status 0 "Carga CPU: ${LOAD_PERCENTAGE}% (${LOAD_AVG})"
else
    warning "Alta carga CPU: ${LOAD_PERCENTAGE}% (${LOAD_AVG})"
fi

# 6. Verificar logs recientes
echo ""
echo "📝 Verificando logs..."

# Errores recientes en Laravel
ERROR_COUNT=$(find storage/logs -name "*.log" -mtime -1 -exec grep -c "ERROR" {} \; 2>/dev/null | awk '{sum+=$1} END {print sum+0}')
if [ $ERROR_COUNT -eq 0 ]; then
    check_status 0 "Sin errores en logs Laravel (24h)"
else
    warning "$ERROR_COUNT errores en logs Laravel (24h)"
fi

# Errores recientes en Apache
APACHE_ERRORS=$(grep -c "error" /var/log/apache2/error.log 2>/dev/null | tail -1)
if [ $APACHE_ERRORS -lt 10 ]; then
    check_status 0 "Errores Apache bajo control ($APACHE_ERRORS)"
else
    warning "$APACHE_ERRORS errores en Apache log"
fi

# 7. Verificar backup reciente
echo ""
echo "💾 Verificando backups..."

BACKUP_DIR="/var/backups/cooperadora"
if [ -d "$BACKUP_DIR" ]; then
    LATEST_BACKUP=$(find $BACKUP_DIR -name "database_*" -mtime -1 | wc -l)
    if [ $LATEST_BACKUP -gt 0 ]; then
        check_status 0 "Backup reciente disponible"
    else
        warning "No hay backup reciente (últimas 24h)"
    fi
else
    warning "Directorio de backup no encontrado"
fi

# 8. Resumen final
echo ""
echo "📊 RESUMEN DEL HEALTH CHECK"
echo "==========================================="
echo "✅ Verificaciones exitosas: $CHECKS_PASSED"
echo "❌ Verificaciones fallidas: $CHECKS_FAILED"

if [ $CHECKS_FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 Sistema en perfecto estado!${NC}"
    exit 0
elif [ $CHECKS_FAILED -le 2 ]; then
    echo -e "${YELLOW}⚠️  Sistema funcional con advertencias menores${NC}"
    exit 1
else
    echo -e "${RED}🚨 Sistema requiere atención inmediata${NC}"
    exit 2
fi
