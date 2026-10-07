# ---- Étape 1 : dépendances PHP --------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist \
    --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# ---- Étape 2 : image finale (Apache + PHP 8.3) ----------------------------
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Apache sert le dossier public/ de Laravel.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Envoi de fichiers jusqu'à 5 Mo (+ marge pour le formulaire).
RUN printf "upload_max_filesize=6M\npost_max_size=8M\n" > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs storage/app/private bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

    # Keep a single Apache MPM: mod_php needs mpm_prefork only
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork
    
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
