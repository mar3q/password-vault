FROM php:8.4-fpm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN pecl install xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${APP_GID} app \
    && useradd -u ${APP_UID} -g app -m app

WORKDIR /app

USER app
