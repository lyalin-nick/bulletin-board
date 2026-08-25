FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        git \
        unzip \
        supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        pdo_mysql \
        zip \
        intl \
        opcache \
        sockets \
        pcntl \
        exif \
    && yes '' | pecl install redis \
    && docker-php-ext-enable redis \
    && pecl clear-cache \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG APP_UID=1000
ARG APP_GID=1000
RUN groupmod -o -g "${APP_GID}" www-data \
    && usermod -o -u "${APP_UID}" -g "${APP_GID}" www-data \
    && chown -R www-data:www-data /var/www

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
