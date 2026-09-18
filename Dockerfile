FROM php:8.4-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath zip \
    && a2enmod rewrite headers expires \
    && rm -rf /var/www/html/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && composer install --no-interaction --no-plugins --no-scripts --prefer-dist --no-progress \
    && php artisan key:generate --force \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["bash", "-lc", "php artisan migrate --force && apache2-foreground"]
