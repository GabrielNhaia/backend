FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libcurl4-openssl-dev \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    && docker-php-ext-install curl mbstring pdo pdo_pgsql xml xmlwriter zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
