FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
    nginx \
    mysql-client \
    zip \
    unzip \
    git \
    nodejs \
    npm

RUN docker-php-ext-install pdo_mysql opcache

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
