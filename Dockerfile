# Development / CI image for the RAD framework.
FROM php:8.3-cli

# System deps for the PHP extensions used by the framework/tooling.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libmemcached-dev zlib1g-dev \
    && docker-php-ext-install pdo pdo_mysql sysvmsg zip \
    && yes '' | pecl install redis memcached \
    && docker-php-ext-enable redis memcached \
    && rm -rf /var/lib/apt/lists/*

# Composer (from the official image).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
