FROM php:8.3-cli

RUN apt-get update && apt-get install -y libssl-dev default-mysql-client unzip git \
    && docker-php-ext-install pdo pdo_mysql

RUN pecl install openswoole && docker-php-ext-enable openswoole

WORKDIR /var/www
COPY . .

EXPOSE 9501
CMD ["php", "server.php"]
