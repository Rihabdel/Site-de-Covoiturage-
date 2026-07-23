FROM php:8.2-apache

# la dépendance système avant d'installer l'extension PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
## Activation du module rewrite d'Apache
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
# Installation des dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Cache Symfony production
RUN php bin/console cache:clear --env=prod

EXPOSE 80
