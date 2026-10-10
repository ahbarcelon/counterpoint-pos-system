FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libjpeg62-turbo-dev libpng-dev libfreetype6-dev libonig-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl mysqli pdo_mysql gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

COPY . .
RUN mkdir -p writable/cache writable/logs writable/session public/uploads/products public/uploads/avatars \
    && chown -R www-data:www-data writable public/uploads
COPY docker/apache-pos.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/pos-entrypoint
RUN chmod +x /usr/local/bin/pos-entrypoint \
    && composer dump-autoload --no-dev --optimize --no-interaction

ENV PORT=8080
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/pos-entrypoint"]
CMD ["apache2-foreground"]
