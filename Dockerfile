# Stage 1: Build frontend assets
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package.json pnpm-lock.yaml ./
RUN npm install -g pnpm && pnpm install --frozen-lockfile

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

ARG VITE_APP_NAME=Todos
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
RUN pnpm run build

# Stage 2: PHP application
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache libzip-dev sqlite-dev \
    && docker-php-ext-install bcmath pcntl pdo_sqlite zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .
COPY --from=frontend /app/public/build public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
