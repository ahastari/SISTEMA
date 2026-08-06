FROM dunglas/frankenphp:php8.3-bookworm

RUN apt-get update && apt-get install -y unzip curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && install-php-extensions bcmath pdo_pgsql mbstring exif pcntl gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
