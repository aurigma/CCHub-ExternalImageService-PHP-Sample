FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install zip

RUN echo "upload_max_filesize=20M\npost_max_size=20M" > /usr/local/etc/php/conf.d/custom.ini

RUN docker-php-ext-install fileinfo mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install
RUN cp .env.example .env

EXPOSE 8000

RUN chmod +x /var/www/entrypoint.sh

ENTRYPOINT ["/var/www/entrypoint.sh"]
CMD ["run"]