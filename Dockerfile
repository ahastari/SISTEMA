FROM dunglas/frankenphp:php8.3-bookworm

# Instalar extensiones comunes de Laravel + zip
RUN apt-get update && apt-get install -y unzip \
    && install-php-extensions bcmath pdo_mysql mbstring exif pcntl gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Cachear configuración y rutas
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Comando de inicio
CMD php artisan serve --host 0.0.0.0 --port $PORT
