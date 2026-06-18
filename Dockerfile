# Imagen base con PHP y FrankenPHP
FROM dunglas/frankenphp:php8.3-bookworm

# Instalar extensiones de PHP necesarias Y además Node.js + NPM
RUN apt-get update && apt-get install -y unzip nodejs npm \
    && install-php-extensions bcmath pdo_mysql mbstring exif pcntl gd zip

# Copiar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar el código del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Compilar assets de Vite (CSS/JS) ahora que NPM sí está instalado
RUN npm install && npm run build

# Comando de arranque correcto para usar FrankenPHP con tu Caddyfile customizado
CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
