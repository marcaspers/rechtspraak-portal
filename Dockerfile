# syntax=docker/dockerfile:1

# --- Composer dependencies -----------------------------------------------------------------
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Frontend assets (Vite) -------------------------------------------------------------------
FROM node:20-alpine AS frontend-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Runtime image -----------------------------------------------------------------------------
# serversideup/php images are purpose-built for Laravel-on-Docker/Coolify (s6-overlay process
# supervision, non-root by default, well-documented Coolify deployment recipes).
FROM serversideup/php:8.3-fpm-nginx AS app

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=composer-deps --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend-build --chown=www-data:www-data /app/public/build ./public/build

# Migrations are run explicitly (Coolify post-deployment command, or `docker compose exec app
# php artisan migrate --force`) rather than automatically on container start, so a bad migration
# never silently blocks the web process from coming up.

# --- CLI runtime (queue worker + scheduler) -----------------------------------------------------
# Same codebase, no nginx/php-fpm supervisor — used by the `worker` and `scheduler` services in
# docker-compose.yml, each running a single long-lived artisan command.
FROM serversideup/php:8.3-cli AS cli

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=composer-deps --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend-build --chown=www-data:www-data /app/public/build ./public/build
