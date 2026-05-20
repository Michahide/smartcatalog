# ── Stage 1: Composer — install all PHP dependencies ──────────────────────────
FROM docker.io/composer:2.7 AS vendor

WORKDIR /app
COPY composer.json ./

# Pull in the full Laravel framework + packages into vendor/
RUN composer install \
      --no-dev \
      --no-scripts \
      --no-autoloader \
      --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --no-dev --optimize --ignore-platform-reqs

# ── Stage 2: Runtime — PHP-FPM + Nginx + Supervisord ─────────────────────────
FROM docker.io/php:8.3-fpm-alpine

# Install system packages + build tools needed for PHP extensions
RUN apk add --no-cache \
      nginx \
      supervisor \
      libzip-dev \
      oniguruma-dev \
      autoconf \
      g++ \
      make \
    && docker-php-ext-install \
         pdo \
         pdo_mysql \
         mbstring \
         zip \
         pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Clean up build tools to reduce image size
    && apk del autoconf g++ make libzip-dev oniguruma-dev

WORKDIR /var/www/html

# Copy compiled application from vendor stage
COPY --from=vendor /app .

# Create required Laravel directories and set permissions
# Use numeric UID 82 (www-data equivalent in Alpine) for rootless Podman compatibility
RUN mkdir -p \
      storage/framework/sessions \
      storage/framework/views \
      storage/framework/cache/data \
      storage/logs \
      storage/app/public \
      bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R 82:82 storage bootstrap/cache

COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# php-fpm needs to run as the same user
RUN sed -i 's/user = www-data/user = nobody/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true \
    && sed -i 's/group = www-data/group = nobody/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

EXPOSE 8000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
