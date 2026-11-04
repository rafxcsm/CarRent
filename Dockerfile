FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libcurl4-openssl-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" bcmath curl dom intl mbstring pdo_pgsql simplexml xml xmlreader xmlwriter zip opcache \
    && a2enmod rewrite headers \
    && printf 'upload_max_filesize=12M\npost_max_size=14M\n' > /usr/local/etc/php/conf.d/uploads.ini \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
    && printf 'ServerName localhost\n' >> /etc/apache2/apache2.conf \
    && printf '<IfModule mpm_prefork_module>\nStartServers 2\nMinSpareServers 2\nMaxSpareServers 4\nMaxRequestWorkers 10\nMaxConnectionsPerChild 500\n</IfModule>\n' \
        > /etc/apache2/conf-available/drivora-prefork.conf \
    && a2enconf drivora-prefork

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/uploads/vehicles \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads

COPY docker/entrypoint.sh /usr/local/bin/drivora-entrypoint
RUN chmod +x /usr/local/bin/drivora-entrypoint

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/drivora-entrypoint"]
CMD ["apache2-foreground"]
