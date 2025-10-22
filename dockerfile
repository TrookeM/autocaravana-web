FROM php:8.2-apache

# Instalar dependencias COMPLETAS
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev git \
    libicu-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar aplicación
COPY . /var/www/html/

# Configurar Apache para usar public/ como document root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Establecer permisos CORRECTOS
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

WORKDIR /var/www/html

# Comandos de despliegue
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN npm install
RUN npm run build

# Optimizar Laravel
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Comandos de inicio
CMD ["sh", "-c", "php artisan storage:link && apache2-foreground"]