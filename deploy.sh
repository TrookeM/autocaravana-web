#!/bin/bash

echo "=== Iniciando despliegue ==="

# Instalar dependencias de Composer
echo "Instalando Composer..."
composer install --no-dev --optimize-autoloader

# Instalar y compilar assets
echo "Instalando y compilando assets..."
npm install
npm run build

# Optimizar Laravel para producción
echo "Optimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones de base de datos
echo "Ejecutando migraciones..."
php artisan migrate --force

# Crear enlace de storage
echo "Creando enlace de storage..."
php artisan storage:link

# Establecer permisos finales
echo "Estableciendo permisos..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "=== Despliegue completado ==="

# Iniciar servidor Apache
apache2-foreground