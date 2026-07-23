FROM php:8.2-apache

# Dépendances système + extensions PHP nécessaires à Symfony
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl
## Activation du module rewrite d'Apache
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
# Installation des dépendances Symfony
RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 80
