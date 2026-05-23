FROM php:8.4-fpm

# -------------------------------------------------
# 1. System dependencies
# -------------------------------------------------
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libwebp-dev \
    unzip \
    wget \
    git \
    nginx \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------
# 2. Composer
# -------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# -------------------------------------------------
# 3. PHP configuration
# -------------------------------------------------
RUN echo "memory_limit = -1"                         >  /usr/local/etc/php/conf.d/99-memory-limit.ini \
    && echo "max_input_vars = 5000"                  >  /usr/local/etc/php/conf.d/99-input-vars.ini \
    && echo "upload_max_filesize = 2G"               >  /usr/local/etc/php/conf.d/99-upload-max.ini \
    && echo "post_max_size = 2G"                     >  /usr/local/etc/php/conf.d/99-post-max.ini \
    && echo "opcache.enable=1"                       >  /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.enable_cli=1"                   >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.memory_consumption=128"         >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.interned_strings_buffer=8"      >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.max_accelerated_files=4000"     >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.revalidate_freq=2"              >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.fast_shutdown=1"                >> /usr/local/etc/php/conf.d/99-opcache.ini

# -------------------------------------------------
# 4. PHP extensions
# -------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        gd \
        bcmath \
        zip \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache

# -------------------------------------------------
# 5. Nginx — Debian uses sites-enabled/
# -------------------------------------------------
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-enabled/smartcatalog.conf

# -------------------------------------------------
# 6. Supervisord
# -------------------------------------------------
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# -------------------------------------------------
# 7. Application
# -------------------------------------------------
WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && composer clear-cache \
    && find bootstrap/cache -name "*.php" -delete 2>/dev/null || true \
    && mkdir -p \
         storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/logs \
         storage/app/public \
         bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

# APP_KEY is set via docker-compose environment — no key:generate needed.
# Run migrate + seed then hand off to supervisord (nginx + php-fpm).
CMD ["sh", "-c", \
    "php artisan migrate --force && \
     php artisan db:seed --force && \
     exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
