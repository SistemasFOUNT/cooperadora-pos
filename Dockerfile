FROM php:8.4-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar archivos de dependencias
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --no-scripts --no-autoloader && \
    composer clear-cache

# Copiar código fuente
COPY . .

# Completar instalación de Composer
RUN composer dump-autoload --no-dev --optimize

# Configurar permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Configurar Nginx
COPY deployment/docker/nginx.conf /etc/nginx/sites-available/default

# Configurar Supervisor
COPY deployment/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configurar PHP-FPM
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Crear script de inicio
COPY deployment/docker/start.sh /start.sh
RUN chmod +x /start.sh

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["/start.sh"]
