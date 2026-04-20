#!/bin/bash

# 🔧 Script de post-despliegue para CI/CD
# Se ejecuta después de cada despliegue automático para verificar que todo esté correcto

echo "🔍 Verificando estado post-despliegue..."

# Variables
APP_PATH="/var/www/cooperadora"
WEB_USER="www-data"

cd $APP_PATH

# 1. Verificar permisos críticos
echo "📋 Verificando permisos..."
STORAGE_PERMS=$(ls -la storage/ 2>/dev/null | grep drwx || echo "ERROR")
CACHE_PERMS=$(ls -la bootstrap/cache/ 2>/dev/null | grep drwx || echo "ERROR")

if [[ $STORAGE_PERMS == *"www-data"* ]]; then
    echo "✅ Permisos de storage correctos"
else
    echo "⚠️  Corrigiendo permisos de storage..."
    sudo chown -R $WEB_USER:$WEB_USER storage/
    sudo chmod -R 775 storage/
fi

if [[ $CACHE_PERMS == *"www-data"* ]]; then
    echo "✅ Permisos de cache correctos"
else
    echo "⚠️  Corrigiendo permisos de cache..."
    sudo chown -R $WEB_USER:$WEB_USER bootstrap/cache/
    sudo chmod -R 775 bootstrap/cache/
fi

# 2. Verificar que Laravel funciona
echo "🚀 Verificando Laravel..."
LARAVEL_STATUS=$(sudo -u $WEB_USER php artisan --version 2>/dev/null || echo "ERROR")
if [[ $LARAVEL_STATUS == *"Laravel Framework"* ]]; then
    echo "✅ Laravel funcionando: $LARAVEL_STATUS"
else
    echo "❌ Error en Laravel: $LARAVEL_STATUS"
fi

# 3. Verificar conexión a base de datos
echo "🗄️  Verificando base de datos..."
DB_STATUS=$(sudo -u $WEB_USER php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'OK';
} catch(\Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
" 2>/dev/null)

if [[ $DB_STATUS == *"OK"* ]]; then
    echo "✅ Base de datos conectada"
else
    echo "❌ Error en base de datos: $DB_STATUS"
fi

# 4. Verificar que el sitio responde
echo "🌐 Verificando respuesta web..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ || echo "ERROR")
if [[ $HTTP_STATUS == "200" ]]; then
    echo "✅ Sitio web responde correctamente (HTTP 200)"
else
    echo "⚠️  Respuesta HTTP: $HTTP_STATUS"
fi

# 5. Verificar logs de errores recientes
echo "📝 Verificando logs recientes..."
if [ -f "storage/logs/laravel.log" ]; then
    RECENT_ERRORS=$(tail -n 20 storage/logs/laravel.log | grep -i error | wc -l)
    if [ $RECENT_ERRORS -eq 0 ]; then
        echo "✅ Sin errores recientes en logs"
    else
        echo "⚠️  $RECENT_ERRORS errores encontrados en logs recientes"
        echo "📋 Últimos errores:"
        tail -n 5 storage/logs/laravel.log | grep -i error
    fi
else
    echo "⚠️  Archivo de log no encontrado"
fi

# 6. Resumen final
echo ""
echo "📊 RESUMEN POST-DESPLIEGUE:"
echo "🔗 URL: http://cooperadora.odontologia.unt.edu.ar"
echo "👤 Login: admin / admin123"
echo "📱 Estado: $(date '+%Y-%m-%d %H:%M:%S')"

if [[ $LARAVEL_STATUS == *"Laravel Framework"* ]] && [[ $DB_STATUS == *"OK"* ]] && [[ $HTTP_STATUS == "200" ]]; then
    echo "🎉 DESPLIEGUE EXITOSO - Sistema funcionando correctamente"
    exit 0
else
    echo "⚠️  DESPLIEGUE CON PROBLEMAS - Revisar errores arriba"
    exit 1
fi
