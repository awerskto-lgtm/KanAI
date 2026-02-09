ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpq-dev libzip-dev libicu-dev libonig-dev nodejs npm $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
