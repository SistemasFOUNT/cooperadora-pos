#!/bin/bash

# Esperar a que PostgreSQL esté disponible
until pg_isready -h db -p 5432 -U cooperadora_user; do
  echo "Esperando a PostgreSQL..."
  sleep 2
done

# Ejecutar migraciones
php artisan migrate --force

# Optimizar Laravel para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
