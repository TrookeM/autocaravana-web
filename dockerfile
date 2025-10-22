FROM php:8.3-apache

# Instalar todas las dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-client \
    libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring

RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copiar el código de la aplicación
COPY . /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache