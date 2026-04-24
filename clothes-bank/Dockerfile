# ---------- Stage 0: Composer ----------
FROM composer:2 AS composer_builder

WORKDIR /app

# Copy everything for composer (artisan must exist for post-autoload scripts)
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader


# ---------- Stage 1: Node / Vite build ----------
FROM node:22-alpine AS node_builder

# Install PHP so Wayfinder plugin can run artisan
RUN apk add --no-cache \
    php \
    php-cli \
    php-mbstring \
    php-tokenizer \
    php-json \
    php-bcmath \
    php-pdo \
    php-pdo_pgsql \
    bash

WORKDIR /app

ARG VITE_APP_NAME
ARG VITE_APP_URL
ENV VITE_APP_NAME=$VITE_APP_NAME
ENV VITE_APP_URL=$VITE_APP_URL

# Copy node dependencies and install
COPY package*.json ./
RUN npm install

# Copy full app code
COPY . .

# Copy vendor from composer stage (Artisan needs it)
COPY --from=composer_builder /app/vendor /app/vendor

# Use dummy env vars so artisan can boot during build
ENV APP_ENV=production
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=:memory:

# Build Vite assets
RUN npm run build -- --config vite.config.docker.js

# ---------- Stage 2: Final runtime ----------
FROM php:8.2-fpm-alpine

# Install system packages for runtime
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    bcmath \
    gd

WORKDIR /var/www/html

# Copy full app + composer vendor
COPY --from=composer_builder /app /var/www/html

# Copy built assets from Node stage
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Expose Fly.io port
EXPOSE 8080

# Use entrypoint script to generate Wayfinder at runtime (Fly secrets available)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]