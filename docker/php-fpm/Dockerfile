FROM php:8.0-fpm
RUN apt-get update && apt-get install -y git

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN docker-php-ext-install pdo_mysql

WORKDIR /app
CMD composer install ; php-fpm
EXPOSE 9000