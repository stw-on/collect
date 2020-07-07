# Stage 1: Install dependencies (Composer)
FROM composer:latest as stage1
RUN composer global require hirak/prestissimo
WORKDIR /app
COPY . /app/
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Stage 2: Build UI
FROM node:alpine as stage2
COPY --from=stage1 /app /app
WORKDIR /app/ui
RUN yarn install \
    && yarn build \
    && mv dist/* /app/public \
    && mv /app/public/index.html /app/public/ui-index.html \
    && rm -rf /app/ui

# Stage 3: Service image
FROM php:7.4-apache

WORKDIR /app

RUN apt-get update -y \
    && a2enmod rewrite \
    && apt-get -y install libpq-dev wait-for-it libyaml-dev \
    && docker-php-ext-install pdo pgsql pdo_pgsql sockets yaml \
    && pecl install redis \
    && pecl install yaml \
    && docker-php-ext-enable redis yaml \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && ln -s /app/docker/php.ini /usr/local/etc/php/conf.d/99-app.ini

COPY --from=stage2 /app /app

ENTRYPOINT ["/app/docker/entrypoint.sh"]
