# Imagen base oficial de PHP con Apache
FROM php:8.3-apache

# Instalar dependencias necesarias para Laravel
RUN apt-get update && apt-get install -y \
    unzip curl git libpq-dev libpng-dev libjpeg-dev libfreetype6-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql bcmath gd

# Copiar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Compilar assets de Vite
RUN npm install && npm run build

# Exponer el puerto
EXPOSE 8080

# Comando de arranque: servidor interno de PHP
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
