FROM php:8.2-apache

# la dépendance système avant d'installer l'extension PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
## Activation du module rewrite d'Apache
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

WORKDIR /var/www/html
