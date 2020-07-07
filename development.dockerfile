FROM php:7.4-apache

ENV APACHE_DOCUMENT_ROOT /app/public
WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update -y \
    && a2enmod rewrite \
    && apt-get -y install libpq-dev libzip-dev wait-for-it git unzip wipe libyaml-dev \
    && composer global require hirak/prestissimo \
    && docker-php-ext-install pdo pgsql pdo_pgsql zip sockets \
    && pecl install redis \
    && pecl install yaml \
    && docker-php-ext-enable redis yaml \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && ln -s /app/docker/php.ini /usr/local/etc/php/conf.d/99-app.ini

ENTRYPOINT ["/app/docker/entrypoint.dev.sh"]
