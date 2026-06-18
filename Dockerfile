# Imagen base con PHP y FrankenPHP
FROM dunglas/frankenphp:php8.3-bookworm

# Instalar extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y unzip \
    && install-php-extensions bcmath pdo_mysql mbstring exif pcntl gd zip

# Copiar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar el código del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Compilar assets de Vite (CSS/JS)
RUN npm install && npm run build

# ⚠️ Importante: NO cachear config en build
# Railway inyecta variables en runtime, así que no hacemos:
# RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Comando de arranque: servir Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]

