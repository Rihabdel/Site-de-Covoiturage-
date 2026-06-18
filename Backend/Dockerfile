FROM php:8.2-apache

# la dépendance système avant d'installer l'extension PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
## Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer  
# Installation de l'extension zip
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip   

COPY . /var/www/html