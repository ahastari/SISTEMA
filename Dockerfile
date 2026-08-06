# Imagen base oficial de PHP con Apache
FROM php:8.3-apache

# Instalar dependencias necesarias para Laravel
RUN apt-get update && apt-get install -y \
    unzip curl git libpq-dev libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev libzip-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql bcmath gd zip

# Copiar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
