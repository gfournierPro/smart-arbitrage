FROM php:8.4-fpm-alpine

# Dépendances système
RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip \
    postgresql-dev linux-headers $PHPIZE_DEPS

# Extensions PHP requises par le projet
RUN docker-php-ext-install pdo pdo_pgsql bcmath

# Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Composer (copié depuis l'image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Utilisateur non-root (bonne pratique)
RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app
USER app